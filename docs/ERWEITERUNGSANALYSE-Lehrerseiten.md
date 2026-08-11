# Erweiterungsanalyse: Seiten nur für Lehrpersonen

_Erstellt: 2026-08-11 · Theme 1.5.76 · CDB-Designer 3.1.85_

Grundlage für den Projektplan. Vorbild und Format: `ERWEITERUNGSANALYSE-Seitenindex.md`
und `Plugins/CDB-Designer/docs/ERWEITERUNGSANALYSE-Seitenimport.md`.

## 1. Kurzbeschreibung

Einzelne Seiten lassen sich als **„nur für Lehrpersonen"** kennzeichnen. Für
alle anderen Besucher verschwinden sie vollständig — aus Seitenleiste,
Inhaltsverzeichnis, Menü, Suche und Sitemap — und der direkte Aufruf endet auf
einer Hinweisseite mit Anmelde-Link. So lassen sich Lösungsseiten hinterlegen,
ohne sie zu verstecken oder zu entwerfen.

**Der Durchlass:** Blöcke einer gesperrten Seite, die für eine Klasse als
**„behandelt"** markiert sind, bleiben in der Klassenansicht sichtbar. Die
Lösung wird also nicht dauerhaft freigegeben, sondern genau dann, wenn die
Lehrperson sie im Unterricht freigibt.

## 2. Verständnis des Ist-Projekts

**Projektzweck:** WordPress-Website „FOS Online Schulbuch" — Unterrichtsskripten
als Seitenbaum, aufgebaut aus Container-Blöcken (Plugin CDB-Designer) und
interaktiven Lernblöcken (Plugin Eigene WP Blocks).

**Berührte Module:**

| Modul | Rolle für diese Erweiterung |
|---|---|
| `Theme/functions.php` | Meta-Box „Navigation & Inhaltsverzeichnis", zwei bestehende Seiten-Metas, Passwortschutz auf `template_redirect`, Menü-Rückfall |
| `Theme/sidebar.php` | Seitenbaum links, **eine** `get_pages()`-Abfrage plus Eltern-Kind-Map |
| `Theme/includes/page-index.php` | Block „Inhaltsverzeichnis", zwei schlanke SQL-Abfragen, Ausschlussliste per Meta |
| `Theme/includes/admin/page-manager.php` | Seitenmanager mit Sammelaktionen (Whitelist in `bulk_aktionen()`) |
| `Plugins/CDB-Designer/includes/class-cbd-classroom.php` | **Die Klassenansicht.** Klassen mit Passwort, Token-Sitzung, „behandelt"-Markierung je Container |
| `Plugins/CDB-Designer/assets/js/classroom-page-filter.js` | Filtert Container im Browser, ersetzt Kopfleiste und Seitenleiste durch die Klassen-Navigation |

**Die Klassenansicht gibt es schon — sie wird nicht neu gebaut.** Schüler melden
sich über den Shortcode `[cbd_classroom]` mit Klassenpasswort an, bekommen ein
Token (Transient `cbd_classroom_<token>`, 24 h) und rufen normale Seiten mit
`?classroom=<id>&token=<…>` auf. Markierungen liegen in `cbd_drawings`
(`class_id`, `page_id`, `container_id`, `is_behandelt`); der Schlüssel ist die
`stableId` des Container-Blocks.

**Konventionen, die gelten:**

- **PHP 7.4** ist Zielumgebung für beide Komponenten (`tools/check-php74.php`
  läuft im ZIP-Bau des Plugins und bricht bei 8.0-Syntax ab).
- **Keine CDN-Einbindungen** (DSGVO) — gilt hier mangels neuer Fremdbibliotheken
  ohnehin.
- Editor-Skripte greifen über `wp.*`-Globale zu, kein `import`/`export`.
- `console.log` nur hinter `window.cbdDebug`.
- Neue Dateitypen müssen in `create-theme-zip.js` bzw. `create-plugin-zip.js`
  **freigegeben** werden, sonst fehlen sie im Verteilungspaket und der Fehler
  zeigt sich erst auf der Live-Site.
- Meta-Werte werden als String `'1'` geschrieben (`update_post_meta` /
  `delete_post_meta`) — abweichende Schreibweisen erkennen `sidebar.php` und
  `page-index.php` nicht.

## 3. Einordnung in die Architektur

### 3.1 Zwei Komponenten, klare Aufgabenteilung

**Die Sperre gehört ins Theme.** Sie ist eine Eigenschaft von Seiten, und alle
Stellen, an denen eine Seite sichtbar wird, liegen im Theme: Seitenbaum,
Inhaltsverzeichnis, Menü, Suche, `template_redirect`. Ein Plugin könnte
`simple_clean_page_index_daten()` nicht einmal filtern — die Funktion stellt
rohe SQL-Abfragen ohne Filterpunkt.

**Der Durchlass gehört ins Plugin.** Nur dort ist bekannt, was eine Klasse ist,
ob ein Token gilt und welche Container behandelt sind.

### 3.2 Die Naht zwischen beiden: ein Filter, der geschlossen ausfällt

Das Theme sperrt und fragt per Filter nach, ob jemand anderes die Seite
freigeben möchte:

```php
// Theme, Standardwert false
$frei = apply_filters('simple_clean_lehrerseite_freigeben', false, $post_id);
```

Das CDB-Plugin hängt sich ein und gibt `true` zurück, wenn eine gültige
Klassensitzung vorliegt **und** die Seite behandelte Container dieser Klasse
enthält.

**Warum diese Richtung:** Der Standardwert ist `false`. Fehlt das Plugin, ist es
abgeschaltet oder greift der Filter nicht, bleibt die Seite gesperrt. Ein Fehler
in der Naht führt also dazu, dass zu wenig sichtbar ist — nie zu viel. Das ist
der Unterschied zur bestehenden Naht am Menü-Slug `page-manager`, wo ein Bruch
nur einen Menüeintrag verschiebt; hier hinge Vertraulichkeit daran.

Die zweite Naht ist die umgekehrte: Das Plugin muss wissen, ob eine Seite
gesperrt ist, um zu entscheiden, ob es den Inhalt serverseitig reduziert. Es
ruft dafür `simple_clean_seite_nur_lehrpersonen()` auf, abgesichert mit
`function_exists()`. Fehlt das Theme, gibt es keine Sperre — dann ist auch
nichts zu reduzieren, und das alte Verhalten gilt unverändert.

### 3.3 „Lehrperson" = angemeldet

Nach Festlegung des Nutzers gilt jeder angemeldete WordPress-Benutzer als
Lehrperson. Das vereinfacht jede Durchsetzungsstelle auf `is_user_logged_in()` —
Schüler melden sich nie an, sie kommen über das Klassenpasswort.

Die Prüfung steht trotzdem in **einer** Funktion
`simple_clean_ist_lehrperson()` mit eigenem Filter. Grund: Sobald es ein Konto
gibt, das keine Lehrperson ist — ein Abonnent, ein Schülerzugang, ein
Redaktions-Testkonto —, öffnet sich die Sperre still. Dann muss die Regel an
genau einer Stelle nachgezogen werden (etwa auf `current_user_can('cbd_edit_blocks')`,
das Rolle „Block-Redakteur" und Administrator umfasst), nicht an acht.

### 3.4 Serverseitig statt im Browser

Der heutige Klassenfilter arbeitet ausschließlich im Browser: Der vollständige
Seiteninhalt steht im HTML, `classroom-page-filter.js` versteckt ihn nur
(`$container.hide()`). Für den bisherigen Zweck — behandelte Inhalte
hervorheben — genügt das. Für Lösungsseiten nicht: „Seitenquelltext anzeigen"
zeigt alles.

Auf **gesperrten** Seiten wird der Inhalt deshalb serverseitig reduziert. Nicht
freigegebene Blöcke verlassen den Server nicht.

**Nur dort, und nur für nicht angemeldete Besucher.** Auf allen anderen Seiten
bleibt der Klassenmodus unverändert clientseitig — sonst änderte sich das
Verhalten überall, und die Lehrperson verlöre die Vorschau, in der sie sieht,
was die Klasse sieht.

## 4. Betroffene Dateien

### Theme

> **Nachtrag beim Planen (2026-08-11):** Die Logik bekommt eine **eigene Datei**
> `includes/sichtbarkeit.php` statt weiterer 300 Zeilen in der `functions.php`.
> Grund: Die `functions.php` hat ~3900 Zeilen und ruft beim Laden Dutzende
> WordPress-Funktionen auf — headless nicht testbar. Eine eigene Datei lässt
> sich mit wenigen Stubs prüfen, entspricht der vorhandenen Struktur
> (`includes/page-index.php`) und ist von der ZIP-Whitelist
> (`includes/**/*.{php,js}`) bereits abgedeckt. In der `functions.php` bleiben
> nur das Häkchen der Meta-Box und dessen Speicherung. Maßgeblich ist der Plan
> `PLAN-Lehrerseiten.md`.

| Datei | Rolle heute | Änderung |
|---|---|---|
| `includes/sichtbarkeit.php` | – | **neu**: zentrale Helfer, Durchsetzung auf `template_redirect`, Hinweisseite, Filter für Menü/Suche/REST/Sitemap |
| `functions.php` | Meta-Box, Passwortschutz, Menü-Rückfall | ändern: dritte Checkbox in der Meta-Box, Speichern, eine `require_once`-Zeile |
| `sidebar.php` | Seitenbaum, eine `get_pages()`-Abfrage | ändern: gesperrte Seiten samt Unterbaum aus der Eltern-Kind-Map nehmen |
| `includes/page-index.php` | Block „Inhaltsverzeichnis" | ändern: zweite Ausschlussliste analog `_simple_clean_hide_from_index` (Zeile 156–161) |
| `includes/admin/page-manager.php` | Seitenmanager, Sammelaktionen | ändern: zwei Aktionen in die Whitelist `bulk_aktionen()`, Kennzeichnung in der Baumzeile |
| `src/css/page-manager.css` | Gestaltung Seitenmanager | ändern: Kennzeichnung gesperrter Seiten |
| `style.css` | Haupt-Stylesheet | ändern: Gestaltung der Hinweisseite |
| `tools/test-sichtbarkeit.php` | – | **neu**: Prüfharnisch für die Sichtbarkeitslogik (Muster: `Plugins/CDB-Designer/tools/test-*.php`, Stubs statt WordPress) |
| `create-theme-zip.js` | Whitelist des Verteilungspakets | **prüfen**: `tools/` darf nicht ins ZIP; keine neuen Dateitypen nötig, solange keine neue Vorlagendatei entsteht |

### CDB-Designer

| Datei | Rolle heute | Änderung |
|---|---|---|
| `includes/class-cbd-classroom-gate.php` | – | **neu**: Klassensitzung serverseitig prüfen, Filter bedienen, Inhalt reduzieren |
| `includes/class-cbd-classroom.php` | Klassensystem, 1462 Zeilen | ändern: Zerlegung der `container_id` (Suffix `:pN`) und die Abfrage der behandelten Container als **geteilte** statische Helfer herauslösen — heute stecken sie in `ajax_get_page_classroom_data()` |
| `container-block-designer.php` | Bootstrap, `load_dependencies()` | ändern: eine `require_once`-Zeile |
| `includes/class-cbd-block-registration.php` | Rendering der Container | nur lesen — Zeile 899–903 zeigt, dass `stableId` auch aus dem HTML kommen kann |
| `assets/js/classroom-page-filter.js` | Klassenfilter im Browser | ändern: auf reduzierten Seiten keine Warnung „Block nicht gefunden" auslösen |
| `tools/test-classroom-gate.php` | – | **neu**: Prüfharnisch für Tokenprüfung, Container-Auswahl und Reduktion |
| `create-plugin-zip.js` | ZIP-Bau, PHP-7.4-Prüfung | nur lesen — neue Datei liegt in `includes/`, ist damit automatisch enthalten |

### Dokumentation

| Datei | Änderung |
|---|---|
| `Theme/CLAUDE.md` | neuer Abschnitt „Seiten nur für Lehrpersonen" |
| `Theme/reference_file_map.md` | neue/geänderte Dateien eintragen |
| `Plugins/CDB-Designer/CLAUDE.md` | neuer Abschnitt „Klassen-Durchlass" |
| `Plugins/CDB-Designer/reference_file_map.md` | neue Dateien eintragen |
| `DOKUMENTATION.md` (Wurzel) | Vorhaben eintragen, wie bei Seitenindex und Seitenimport |

## 5. Wiederverwendung statt Neubau

| Vorhandenes | Wofür |
|---|---|
| Meta-Box „Navigation & Inhaltsverzeichnis" (`functions.php:492`) | dritte Checkbox statt neuer Box. **Meta-Box-ID `simple_clean_hide_navigation` bleibt** — daran hängen die Bildschirmeinstellungen der Benutzer |
| Ausschlussmuster in `simple_clean_page_index_daten()` (Zeile 156–161) | zweite Liste nach demselben Muster: eine Meta-Abfrage, `array_flip`, `isset()`-Prüfung |
| Breitensuche in `page-index.php` | **Vererbung auf den Unterbaum gratis** — wer von der Wurzel aus nicht erreichbar ist, fällt samt Kindern heraus. Genau wie bei `_simple_clean_hide_from_index` |
| `bulk_aktionen()`-Whitelist im Seitenmanager | zwei weitere Aktionen; Meta-Aktionen schreiben direkt per `update_post_meta()`, `reload` bleibt `false` |
| Transient-Sitzung `cbd_classroom_<token>` | Tokenprüfung serverseitig, kein neues Sitzungsverfahren |
| Tabelle `cbd_drawings` mit `is_behandelt` | die Markierung selbst — **keine neue Spalte, kein neues Feld** |
| Tafelmodus (`board-mode.js`) mit Klassenwahl | die Bedienung der Markierung bleibt, wie sie ist |
| Prüfharnisch-Muster `tools/test-*.php` | Tests ohne WordPress-Installation, mit wenigen Stubs |

## 6. Integrationspunkte & Schnittstellen

### 6.1 Datenhaltung

**Ein neues Post-Meta, sonst nichts.** Keine Tabelle, keine Spalte, keine
Migration.

```
_simple_clean_nur_lehrpersonen = '1'   (oder nicht vorhanden)
```

Namensschema folgt `_simple_clean_hide_navigation` und
`_simple_clean_hide_from_index`.

### 6.2 Neue Funktionen im Theme

| Funktion | Aufgabe |
|---|---|
| `simple_clean_ist_lehrperson()` | `is_user_logged_in()`, durch Filter `simple_clean_ist_lehrperson` überschreibbar. **Einziger Ort, an dem „Lehrperson" definiert ist** |
| `simple_clean_seite_nur_lehrpersonen($post_id)` | Meta der Seite **oder eines Vorfahren** gesetzt? |
| `simple_clean_gesperrte_seiten()` | IDs aller gesperrten Seiten aus einer Meta-Abfrage, `array_flip`, statisch gehalten |
| `simple_clean_seite_sichtbar($post_id)` | die Gesamtentscheidung: Lehrperson **oder** nicht gesperrt **oder** Filter gibt frei |

### 6.3 Durchsetzungsstellen im Theme

| Hook / Stelle | Wirkung |
|---|---|
| `template_redirect` (nach dem Passwortschutz, Priorität > 10) | gesperrte Seite ohne Freigabe → Hinweisseite, HTTP 403, `noindex` |
| `sidebar.php` | gesperrte Seiten samt Unterbaum aus der Eltern-Kind-Map |
| `simple_clean_page_index_daten()` | zweite Ausschlussliste |
| `wp_get_nav_menu_items` | Menüeinträge auf gesperrte Seiten entfernen |
| `wp_list_pages_excludes` | Menü-Rückfall (`simple_clean_fallback_menu()`) |
| `pre_get_posts` (nur Frontend, nicht Admin) | Suche und Archive |
| `rest_page_query` | REST-Lesezugriffe |
| `wp_sitemaps_posts_query_args` | Sitemap |

### 6.4 Die Hinweisseite

Eigene Ausgabe innerhalb von `get_header()` / `get_footer()`, damit sie wie eine
Seite der Website aussieht: Überschrift „Nur für Lehrpersonen", kurze Erklärung,
Anmelde-Link mit Rücksprung (`wp_login_url(get_permalink())`), Link zur
Elternseite. HTTP-Status **403**, dazu `wp_robots_no_robots()`.

Der Seitentitel wird **nicht** ausgegeben — sonst verriete die Hinweisseite, wie
die Lösung heißt.

### 6.5 Datenfluss des Durchlasses

```
Schüler ruft auf:  /loesungen/kapitel-3/?classroom=4&token=abc…
                            │
  Theme  template_redirect  │  Seite gesperrt? ja.  Lehrperson? nein.
                            ▼
        apply_filters('simple_clean_lehrerseite_freigeben', false, $post_id)
                            │
  Plugin CBD_Classroom_Gate │  Token gültig?  Klasse == 4?
                            │  Hat Seite behandelte Container dieser Klasse?
                            ▼
                    true → Seite wird ausgeliefert
                            │
  Plugin  the_content (8)   │  parse_blocks(), nur behandelte Container behalten
                            ▼
        Ausgabe enthält ausschließlich freigegebene Blöcke
```

### 6.6 Die Reduktion

Gehängt an `the_content` mit Priorität **8**, also vor `do_blocks` (Priorität 9):
`parse_blocks()` auf das Rohmarkup, oberste Ebene durchgehen, erlaubte Blöcke
einzeln mit `render_block()` ausgeben, Ergebnis verketten.

**Standard ist Ablehnung:** Was kein Container-Block mit freigegebener
`stableId` ist, entfällt — auch freistehende Absätze und Überschriften. Auf
einer Lösungsseite ist alles Lösung, solange nichts anderes gesagt wurde.

**`stableId` kann an zwei Stellen stehen.** Neue Blöcke tragen sie in den
Attributen (`$block['attrs']['stableId']`), Altbestände nur im gespeicherten
HTML. `CBD_Block_Registration::render_block()` löst das ab Zeile 899 mit einem
Rückfall-Regex — die Reduktion muss denselben Rückfall verwenden, sonst
verschwinden alte, korrekt markierte Blöcke stillschweigend.

**Die Suffix-Regel `container_id = "<stableId>:pN"`** (mehrseitige Tafelbilder)
steckt heute in `ajax_get_page_classroom_data()`. Sie muss herausgelöst und
geteilt werden; zwei Fassungen derselben Regel liefen sonst auseinander.

## 7. Regressionsfläche

Was nach jeder Phase nachweislich noch laufen muss:

| Bestehende Funktion | Warum gefährdet |
|---|---|
| **Klassenmodus auf normalen Seiten** | Der Durchlass fasst denselben Codeweg an. Eine Seite ohne Sperre muss sich exakt wie bisher verhalten: alle Container im HTML, JS blendet die nicht behandelten aus |
| **Inhaltsverzeichnis-Block** | Zweite Ausschlussliste in `simple_clean_page_index_daten()`. Messwert 0,03 s bei 258 Seiten darf nicht wegbrechen; **eine** zusätzliche Abfrage, nicht eine pro Seite |
| **Seitenleiste** | Muss bei **einer** `get_pages()`-Abfrage bleiben. Ein `get_post_meta()` je Knoten wäre ein Rückschritt um Dutzende Abfragen |
| **Glossar-Verlinkung** | Hängt am `the_content`-Filter (Priorität 10000) und am Meta `_glossar_scan_version`. Die Reduktion greift bei Priorität 8 — die Reihenfolge muss geprüft werden, sonst verlinkt das Glossar Inhalt, den es gar nicht mehr gibt, oder gar nichts mehr |
| **Sammelaktionen im Seitenmanager** | Whitelist wird erweitert. Die acht bestehenden Aktionen müssen unverändert laufen, insbesondere die Trennung der Schreibwege (`wp_update_post()` beim Status **wegen** des Glossar-Scans) |
| **Passwortschutz der Website** | Zweiter `template_redirect`-Haken. Reihenfolge: AI-Blocker (1) → Passwortschutz (10) → Lehrersperre. Ein anonymer Besucher darf nicht an der Passwortabfrage vorbei auf die Hinweisseite gelangen |
| **Seitenimport aus Markdown** | Legt Seiten als Entwurf an, ohne das neue Meta. Muss unverändert laufen |
| **Nummerierung der Container** | `block-numbering.js` zählt im Browser. Auf einer reduzierten Seite beginnt die Zählung neu — **hinnehmbar**, aber zu dokumentieren |
| **PDF-Export, Tafelmodus, Zeichnungen** | Laufen auf reduzierten Seiten mit weniger Inhalt. Kein Fehler, aber zu prüfen |
| **Diagnoseausgabe `?sc_perf=1`** | Muss weiter funktionieren und die zusätzlichen Abfragen zeigen |

## 8. Konventions-Konformität

- Meta-Name `_simple_clean_nur_lehrpersonen` folgt den zwei bestehenden Metas;
  geschrieben wird der String `'1'`, entfernt per `delete_post_meta()`.
- Meta-Box-ID bleibt `simple_clean_hide_navigation` (Bildschirmeinstellungen).
- Kein zweites Nonce in der Meta-Box — das vorhandene deckt die ganze Box ab,
  wie beim zweiten Feld bereits entschieden.
- Neue Funktionsnamen deutsch, Präfix `simple_clean_` bzw. Klasse
  `CBD_Classroom_Gate` — entspricht der jüngeren Codebasis (`bulk_aktionen()`,
  `simple_clean_page_index_daten()`).
- PHP 7.4: keine `match`-Ausdrücke, keine Konstruktor-Promotion, keine
  benannten Argumente. `php tools/check-php74.php` läuft im ZIP-Bau des Plugins.
- Sammelaktionen ausschließlich über die Whitelist; der `$_POST`-Wert wird nie
  in einen Methodennamen übersetzt.
- Alle Debug-Ausgaben hinter `WP_DEBUG` bzw. `window.cbdDebug`.
- Keine neuen Fremdbibliotheken, keine CDN-Einbindung.

## 9. Risiken & offene Fragen

| Risiko | Gegenmaßnahme |
|---|---|
| **Ein Loch in einer der acht Durchsetzungsstellen** — Seite verschwindet aus der Seitenleiste, taucht aber in der Suche auf | Prüfharnisch listet alle Stellen; ein eigenes Arbeitspaket geht sie mit einer Prüfliste durch. Die Hinweisseite ist die letzte Verteidigungslinie: selbst wenn ein Link durchrutscht, ist der Inhalt nicht erreichbar |
| **„Lehrperson = angemeldet" öffnet sich still**, sobald es ein Konto ohne Lehrauftrag gibt | Ein Filter, eine Funktion. Umstellung auf `current_user_can('cbd_edit_blocks')` ist danach eine Zeile. Ausdrücklich in `CLAUDE.md` vermerken |
| **Seiten-Cache liefert Lehrer-Ansicht an Schüler** (oder umgekehrt) | Die Unterscheidung hängt am Anmeldestatus; Caching-Plugins umgehen den Cache für angemeldete Benutzer standardmäßig. Muss beim Ausrollen geprüft und dokumentiert werden. Die Hinweisseite bekommt `nocache_headers()` |
| **Reduktion zerschneidet gültiges Blockmarkup** | Es wird nur die oberste Ebene gefiltert; erlaubte Blöcke gehen unverändert durch `render_block()`. Kein Serialisieren, kein Rundlauf über `serialize_blocks()` — der Whitespace-Unterschied zwischen JS- und PHP-Serializer (siehe CDB-`CLAUDE.md`) bleibt damit außen vor |
| **Reihenfolge auf `the_content`** kollidiert mit der Glossar-Verlinkung (10000) und der LaTeX-Verarbeitung | Eigenes Akzeptanzkriterium: auf einer reduzierten Seite müssen Glossarbegriffe und Formeln in den verbliebenen Blöcken korrekt erscheinen |
| **Zwei Repositories, ein Plan** | Der Plan liegt in `Theme/docs/`; die Plugin-Commits verweisen darauf. Reihenfolge beim Ausrollen: **erst Theme, dann Plugin** — das Theme allein sperrt, ohne Durchlass, und ist damit die sichere Zwischenstufe |
| **Altbestände ohne `stableId` in den Attributen** | Rückfall-Regex wie in `render_block()`, mit eigenem Testfall |

**Doku-Lücke, in Schritt 1 gefunden:** `Theme/CLAUDE.md` beschreibt die
Klassenansicht nirgends, obwohl sie tief ins Theme greift (ersetzt Kopfleiste
und Seitenleiste per JavaScript). Wer nur die Theme-Doku liest, hält
`.site-header` für unangetastet. Der Dokumentations-AP der letzten Phase
schließt das mit.

**Offene Frage, die im Plan als Entscheidung festgehalten wird:** Die Sperre
**vererbt sich auf den gesamten Unterbaum** — wie `_simple_clean_hide_from_index`.
Begründung: Eine Seite „Lösungen" mit einer Unterseite je Kapitel ist der
wahrscheinliche Aufbau, und ein vergessenes Häkchen an einer Unterseite wäre ein
Leck. Wer eine einzelne Unterseite offen halten will, hängt sie außerhalb des
gesperrten Zweigs ein.

## 10. Grobzuschnitt für den Projektplan

**Mehrphasig** — es sind rund zehn Arbeitspakete in zwei Repositories, und nach
jeder Phase muss ein lauffähiger Zwischenstand stehen.

| Phase | Inhalt | Zwischenergebnis |
|---|---|---|
| **1 — Theme: Sperre** | Meta + Meta-Box + Speichern, zentrale Helfer, `template_redirect` mit Hinweisseite, Ausblenden in Seitenleiste / Inhaltsverzeichnis / Menü / Suche / REST / Sitemap, Sammelaktionen im Seitenmanager | Seiten lassen sich sperren und sind für Nicht-Angemeldete vollständig weg. **Auch für die Klasse** — noch ohne Durchlass |
| **2 — Plugin: Durchlass** | Klassensitzung serverseitig prüfen, geteilte Helfer aus `class-cbd-classroom.php` herauslösen, Filter bedienen, Inhalt serverseitig reduzieren, JS-Filter anpassen | Behandelte Blöcke gesperrter Seiten erscheinen in der Klassenansicht — und nur diese, auch im Quelltext |
| **3 — Absicherung und Auslieferung** | Prüfharnische beider Komponenten, Durchgang durch alle Durchsetzungsstellen, PHP-7.4-Prüfung, ZIP-Bau, Ausrollreihenfolge, Dokumentation | Beide Pakete gebaut, Dokumentation und Datei-Maps nachgezogen |

Jede Phase mit Review-AP (`AP-<N>.rev`) und Dokumentations-AP (`AP-<N>.doc`)
nach den Regeln des projektplan-skills.