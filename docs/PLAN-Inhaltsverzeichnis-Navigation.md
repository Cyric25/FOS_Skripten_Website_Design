# Plan: Inhaltsverzeichnis klappt über die Elternseite, Seiten für die Navigation sperrbar

_Erstellt am: 2026-08-21 · Eigenständiges Kleinvorhaben · Komponente: **Theme** (v1.5.79)_

**Dieser Plan ist unabhängig von den drei Geschwisterplänen** im Plugin
CDB-Designer (`docs/PLAN-Importer-Elternseite.md`,
`docs/PLAN-Aktionsleiste-Autoausblenden.md`,
`docs/PLAN-Formeln-in-Blocktiteln.md`). Er ist der einzige der vier, der das
**Theme** anfasst; alle vier dürfen gleichzeitig laufen.

## 0. Anweisungen für den ausführenden Agenten

Du hast keinen Zugriff auf das Gespräch, in dem dieser Plan entstand. Er ist
die einzige Wahrheitsquelle.

1. Bearbeite die Arbeitspakete der Reihe nach. Bleib strikt im Scope.
2. **Kein Prüfharnisch verlangt.** Der Nutzer nimmt dieses Vorhaben auf Sicht
   ab — das ist eine bewusste Entscheidung. **Aber:** Jedes AP nennt trotzdem
   seine Sichtprüfungen, und die sind durchzuführen, nicht zu behaupten.
3. Commit-Nachrichten **ohne Anführungszeichen** — die Shell dieses Projekts
   ist PowerShell und übergibt den Text sonst als Pathspec. Mehrzeilige
   Nachrichten im Bash-Werkzeug per echtem Heredoc, **keine**
   PowerShell-Here-Strings.
4. Kein `git add .` und kein `git add -A`.
5. **Das Theme hat einen Vite-Build.** Änderungen an `src/css/*` und
   `src/js/*` brauchen einen Bauschritt; Änderungen an `functions.php`,
   `includes/*.php` und `sidebar.php` nicht. Prüfe vor dem ersten Commit,
   welcher Befehl in `package.json` dafür vorgesehen ist, und führe ihn nach
   jeder Änderung an einer Quelldatei aus.
6. **PHP 7.4** ist die Zielumgebung (All-inkl Shared Hosting). `php -l` meldet
   8.0-Syntax **nicht** als Fehler; verzichte auf `match`, Nullsafe,
   Constructor Promotion, benannte Argumente, `str_contains`,
   `str_starts_with`, `str_ends_with`, `mixed` und Union Types.
7. **Keine Versionsnummer erhöhen** — das geschieht im Abnahme-AP.
8. **Markdown-Dateien nur mit dem Edit-Werkzeug ändern**, niemals per
   PowerShell-Lese-Schreib-Zyklus: `Get-Content` mit `Set-Content -Encoding
   UTF8` doppelkodiert alle Umlaute, und ein Latin-1-Reparaturversuch
   verliert Zeichen unwiederbringlich.

## 1. Ziel

Drei zusammenhängende Änderungen:

1. **Das Inhaltsverzeichnis klappt über die Elternseite auf**, nicht über
   eine zusätzliche Zeile „Unterseiten". Diese Zeile entfällt.
2. **Die Anzahl der Unterseiten steht neben der Elternseite**, wenn die
   Zählung eingeschaltet ist.
3. **Neue Seiten-Option „Für Navigation sperren".** Eine so markierte Seite
   lässt sich weder im Inhaltsverzeichnis noch in der Seitenleiste
   anklicken; im Inhaltsverzeichnis löst der Klick **nur** das Auf- und
   Zuklappen aus.

**Der Anwendungsfall, aus dem sich alles ergibt:** Der Nutzer legt leere
Elternseiten zur Gliederung an. Heute kann ein Leser sie öffnen und landet
auf einer leeren Seite. Mit der Sperre bleibt die Gliederung erhalten, ohne
dass jemand ins Leere klickt.

**Ab der dritten Ebene wird nicht mehr eingeklappt.** Nur die oberste Ebene
(im Code Ebene 0, die Kapitel) bekommt ein Aufklapp-Element; alles darunter
ist sichtbar, sobald das Kapitel offen ist.

Beispiel aus der Anforderung:

```
Elternseite            (Klick klappt auf und zu, Seite nicht zu öffnen)
 └ 1. Unterseitenebene (klappt mit der Elternseite ein)
    └ 2. Unterseite    (kann von Ebene 1 nicht eingeklappt werden)
```

## 2. Nicht-Ziele

- **Die Sperre ist kein Zugriffsschutz.** Eine gesperrte Seite bleibt über
  ihre URL erreichbar und erscheint in der Suche. Wer eine Seite wirklich
  verbergen will, nutzt „nur für Lehrpersonen"
  (`_simple_clean_nur_lehrpersonen`) — das ist eine andere Funktion mit
  anderen Folgen. **Das muss in der Oberfläche klar erkennbar sein**, sonst
  hält jemand die Sperre für Vertraulichkeit.
- **Kein Zwischenspeicher für den Seitenbaum.** Eine Messung am 2026-08-08
  hat gezeigt, dass er bei 258 Seiten rund 0,03 s kostet; ein Cache würde
  kein gemessenes Problem lösen, aber Fehlerquellen einführen (veraltete
  Ausgabe nach Sortierungen im Seitenmanager, der `post_parent` und
  `menu_order` an `save_post` vorbei schreibt). Belege in
  `docs/PLAN-Seitenindex.md`, Abschnitt 11.
- **Die Meta-Box-ID `simple_clean_hide_navigation` bleibt.** An ihr hängen
  die gespeicherten Bildschirmeinstellungen der Benutzer; eine Umbenennung
  verwürfe sie.
- **Kein Umbau der Darstellungen** (`cards`, `list`, `columns`) und keine
  Änderung an `maxDepth`, `rootPage` oder der Suche.
- **Die Plugins werden nicht geändert.**

## 3. Kontext & Constraints

- **Komponente:** `Theme/`, Version 1.5.79.
- **Umgebung produktiv:** All-inkl Shared Hosting, PHP 7.4.33.
- **Testumgebung:** `C:\allinkl-testserver`, Start `start-server.cmd`,
  WordPress unter `http://fos.localhost:8080/`. Installationspfad
  `C:\allinkl-testserver\www\htdocs\w0000001\fos`, Theme unter
  `wp-content/themes/fos-online-schulbuch`. Admin `admin` /
  `Testserver2026!`. Datenbank `d0000001` / `d0000001` /
  `EBZvYRyrEM34gtfmv3Z8`, Client
  `C:\allinkl-testserver\mariadb\bin\mysql.exe`.
  Bei HTTP 503 die `.maintenance`-Datei und `wp-content/upgrade/wordpress-*`
  löschen. Seiten mit Hierarchie: 69 → 70 → 71 → 72.
- **Konventionen:** `Theme/CLAUDE.md` und `Theme/reference_file_map.md` haben
  Vorrang.

## 4. Ausgangslage (aus der Datei-Map)

| Datei | Rolle heute | Änderung |
|---|---|---|
| `includes/page-index.php` | Block `fos/inhaltsverzeichnis`: `simple_clean_page_index_daten()` (Breitensuche, zwei schlanke Abfragen), `simple_clean_page_index_sanitize_attrs()`, `simple_clean_page_index_liste()` (rekursiv, kennt `$ebene`), `simple_clean_render_page_index()` | ändern |
| `functions.php` | Meta-Box `simple_clean_hide_navigation` („Navigation, Verzeichnis & Zugriff") ab ~Zeile 493, Callback `simple_clean_navigation_meta_box_callback()`, Speicherroutine | ändern |
| `sidebar.php` | `display_page_tree_item()` ab Zeile 52 — rekursive Ausgabe des Seitenbaums | ändern |
| `src/css/page-index.css` | Gestaltung des Blocks | ändern |
| `src/js/page-index-editor.js` | `registerBlockType()` — **Sichtbarkeit im Einfügen-Menü**; ohne sie ist der Block im Editor nicht auffindbar | nur lesen (voraussichtlich) |
| `blocks/inhaltsverzeichnis/block.json` | Metadaten und Attribute | nur lesen |
| `includes/sichtbarkeit.php` | Lehrpersonen-Sperre — **andere Funktion**, nicht verwechseln | nur lesen |

**Der heutige Aufbau je Eintrag** (`page-index.php`, etwa Zeile 327–352):

```html
<li class="page-index__page">
  <a class="page-index__page-link" href="…">Titel</a>
  <details class="page-index__sub">
    <summary class="page-index__sub-toggle">Unterseiten</summary>
    <ul>…</ul>
  </details>
</li>
```

`$attrs['showCounts']` schaltet die Beschriftung heute zwischen
„Unterseiten" und „N Unterseiten" um — die Zählung **existiert also
bereits**, sie sitzt nur an der falschen Stelle.

**Vorhandene Seiten-Metas:** `_simple_clean_hide_from_index`,
`_simple_clean_hide_navigation`, `_simple_clean_nur_lehrpersonen`. Der neue
Schlüssel folgt diesem Schema.

**Aufgeklappt wird über natives `<details>`** — barrierefrei,
tastaturbedienbar, funktioniert ohne JavaScript. Das bleibt so.

## 5. Architekturentscheidungen

| Entscheidung | Begründung | Verworfene Alternative |
|---|---|---|
| **Der Seitentitel wandert in das `<summary>`; die Zeile „Unterseiten" entfällt** | Genau die Anforderung. Nebeneffekt: ein Klickziel weniger und eine Zeile weniger Höhe je Kapitel | Die Zeile nur ausblenden: das Klappen hinge weiter an einem unsichtbaren Element |
| **Nur Ebene 0 bekommt ein `<details>`; ab Ebene 1 wird flach ausgegeben** | Die Anforderung nennt es ausdrücklich: Ebene 1 klappt **mit** der Elternseite ein, Ebene 2 lässt sich von Ebene 1 **nicht** einklappen. Ein `<details>` auf Ebene 1 würde genau das ermöglichen | Verschachtelte `<details>` auf jeder Ebene: widerspricht der Anforderung |
| **Bei einer gesperrten Seite steht im `<summary>` reiner Text, kein `<a>`** | Ein Link im `<summary>` würde beim Klick **beides** tun: navigieren und klappen. Für eine Seite, die man nicht öffnen soll, darf der Link gar nicht erst existieren — nicht bloß per JavaScript abgefangen werden, denn das griffe ohne JavaScript nicht | Link ausgeben und `preventDefault()`: ohne JavaScript wäre die Seite doch erreichbar |
| **Bei einer nicht gesperrten Elternseite bleibt der Titel ein Link** | Sonst wären gewöhnliche Kapitelseiten über das Verzeichnis nicht mehr erreichbar — ein Rückschritt für alle, die keine leeren Gliederungsseiten benutzen. **Folge:** Im `<summary>` klickt man auf den Titel, um zu navigieren, und daneben, um zu klappen. Dieses Verhalten ist in AP-4 ausdrücklich vom Nutzer zu beurteilen | Immer nur klappen: nimmt normalen Kapitelseiten ihre Verlinkung |
| **Die Zählung wird an derselben Stelle erzeugt wie bisher, nur anders platziert** | `showCounts` existiert samt Pluralbehandlung über `_n()`. Es geht um Platzierung, nicht um neue Logik | Neues Attribut: verdoppelt eine vorhandene Einstellung |
| **Neue Meta `_simple_clean_nav_gesperrt`, gesetzt in der vorhandenen Meta-Box** | Die Box heißt bereits „Navigation, Verzeichnis & Zugriff" und regelt drei verwandte Dinge. Eine vierte Checkbox dort ist der erwartbare Ort; eine eigene Box wäre Wildwuchs | Eigene Meta-Box: mehr Fläche, kein Gewinn |
| **Die Sperre wirkt nur auf die Darstellung, nie auf die Erreichbarkeit** | Vertraulichkeit leistet ausschließlich die Lehrpersonen-Sperre. Zwei Funktionen, die beide „irgendwie verbergen", sind ein Missverständnis, das früher oder später jemandem teuer wird — deshalb muss der Hilfetext das ausdrücklich sagen | Die Seite zusätzlich unerreichbar machen: dupliziert eine bestehende Funktion halbherzig |

## 6. Risiken

| Risiko | Auswirkung | Gegenmaßnahme |
|---|---|---|
| **Der Nutzer hält „Für Navigation sperren" für Zugriffsschutz** | **hoch** — eine Lösungsseite bliebe öffentlich erreichbar, während er sie für verborgen hält | Hilfetext direkt an der Checkbox, der auf „nur für Lehrpersonen" verweist. AP-2 hat den Wortlaut als Akzeptanzkriterium |
| **Gesperrte Seiten werden auch in der Suche oder im Menü unerreichbar** | mittel | Die Sperre wird an genau **zwei** Stellen gelesen: Inhaltsverzeichnis und Seitenleiste. Nirgends sonst. AP-4 prüft Suche und direkten URL-Aufruf gegen Regression |
| **Ein Kapitel ohne Unterseiten verliert seinen Link** | mittel | Ohne Kinder entsteht kein `<details>`; der Titel bleibt ein gewöhnlicher Link. Das ist der häufigste Fall im Bestand und muss unverändert bleiben |
| **Ein `<a>` im `<summary>` verhält sich in Browsern uneinheitlich** | mittel | Deshalb bei gesperrten Seiten gar kein `<a>`. Für den ungesperrten Fall ist das Verhalten in AP-4 auf echten Geräten zu prüfen, nicht nur im Entwicklungsbrowser |
| **Die Seitenleiste bricht — sie läuft auf jeder Seite** | **hoch** | `sidebar.php` ist die am häufigsten ausgeführte Datei des Themes. Eine PHP-Warnung dort erscheint auf jeder Seite. **Lehre aus v1.5.57→58:** `function_exists`-Wächter hoisten nicht; Template-Dateien nach Änderungen wirklich ausführen, nicht nur `php -l` |
| **Kein Prüfharnisch** | mittel | Bewusste Entscheidung des Nutzers. Ausgleich: AP-4 prüft eine feste Liste von Fällen auf dem Testserver, darunter ausdrücklich die Regressionen |

**Rollback:** Vor dem ersten AP `git tag vor-inhaltsverzeichnis-navigation`,
Rückweg `git reset --hard vor-inhaltsverzeichnis-navigation`. Die neue Meta
bleibt dabei in der Datenbank stehen; sie ist ohne den Code wirkungslos.

## 7. Arbeitspakete

### AP-1: Aufklappen über die Elternseite, Zählung daneben, nur Ebene 0 klappbar

**Modell:** opus
**Dateien:** `includes/page-index.php`, `src/css/page-index.css`

**Umsetzung in `simple_clean_page_index_liste()`:**

1. Die Bedingung `if ($attrs['collapsible'])` um `&& 0 === $ebene` erweitern.
   Ab Ebene 1 wird die Unterliste unverändert flach angehängt.
2. Auf Ebene 0 mit Kindern: `<details>` **um den Titel herum** legen, nicht
   dahinter. Der bisherige `<a>` wandert in das `<summary>`:
   - **gesperrte Seite:** `<span class="page-index__chapter-title">Titel</span>`
   - **sonst:** der bisherige `<a class="page-index__chapter-link">`
3. Die Zählung als eigenes Element im `<summary>` hinter dem Titel, wenn
   `showCounts` gesetzt ist — bestehende `_n()`-Behandlung übernehmen, nur
   die Beschriftung kürzen (die Zahl steht jetzt neben einem Titel, nicht
   allein in einer Zeile).
4. Die Klasse `page-index__sub-toggle` entfällt; für die neue Struktur eine
   sprechende Klasse vergeben und **in `Theme/CLAUDE.md` die
   Klassenaufzählung mitziehen** (AP-5).
5. CSS: `summary` ohne Standard-Dreieck, Titel und Zahl in einer Zeile,
   erkennbarer Klapp-Hinweis (etwa ein Chevron), Fokusrahmen erhalten. Keine
   freistehenden Farbwerte — im Inhaltsverzeichnis stehen Hexwerte
   ausschließlich als Rückfall in `var(--x, #wert)`, damit die
   Customizer-Farbe gewinnt. Der „plastische Look" bleibt außen vor, er ist
   dem Navigationsstreifen vorbehalten.

**Akzeptanzkriterien:**

- AK1: Ein Kapitel mit Unterseiten klappt durch Klick auf den Kapiteleintrag
  auf und zu; eine Zeile „Unterseiten" gibt es nicht mehr.
- AK2: Bei `showCounts` steht die Anzahl neben dem Kapiteltitel.
- AK3: Ebene 1 und tiefer haben **kein** eigenes Aufklapp-Element.
- AK4: Ein Kapitel **ohne** Unterseiten erscheint wie bisher als
  gewöhnlicher Link, ohne `<details>`.
- AK5: Ohne JavaScript funktioniert das Auf- und Zuklappen weiterhin (natives
  `<details>`), und die Tastaturbedienung ebenfalls.
- AK6: Die Suche im Block filtert weiterhin korrekt, auch über eingeklappte
  Kapitel hinweg.

**Sichtprüfung:** Seiten 69 → 70 → 71 → 72 auf dem Testserver, alle drei
Darstellungen (`cards`, `list`, `columns`), mit und ohne `showCounts`.

---

### AP-2: Seiten-Option „Für Navigation sperren"

**Modell:** sonnet
**Dateien:** `functions.php`

**Umsetzung:**

1. In `simple_clean_navigation_meta_box_callback()` eine vierte Checkbox mit
   der Meta `_simple_clean_nav_gesperrt`.
2. Beschriftung: „Für Navigation sperren". Hilfetext darunter, der **zwei**
   Dinge sagt: Die Seite ist im Inhaltsverzeichnis und in der Seitenleiste
   nicht mehr anklickbar, klappt im Verzeichnis aber ihre Unterseiten auf —
   **und sie bleibt über ihre Adresse erreichbar.** Wer eine Seite wirklich
   verbergen will, nutzt „nur für Lehrpersonen".
3. Speichern in der vorhandenen Routine, mit demselben Nonce
   (`simple_clean_save_navigation_meta`) und derselben Capability-Prüfung wie
   die drei bestehenden Felder. Kein zweiter Speicherweg.
4. Den Titel der Meta-Box prüfen — er nennt drei Dinge, künftig sind es vier.
   **Die Meta-Box-ID `simple_clean_hide_navigation` bleibt unverändert**, an
   ihr hängen die Bildschirmeinstellungen der Benutzer.

**Akzeptanzkriterien:**

- AK1: Die Checkbox erscheint in der bestehenden Box, nicht in einer neuen.
- AK2: Der Zustand überlebt Speichern und erneutes Öffnen.
- AK3: Der Hilfetext nennt ausdrücklich, dass die Seite erreichbar bleibt,
  und verweist auf „nur für Lehrpersonen".
- AK4: Die drei bestehenden Checkboxen funktionieren unverändert.
- AK5: Die Meta-Box-ID ist unverändert.

---

### AP-1.fix1: Die Suche findet klappbare Kapitel nicht mehr

**Modell:** sonnet
**Abhängigkeiten:** AP-1
**Dateien:** `src/js/page-index.js`
**Anlass:** Befund von AP-1. **Lücke in diesem Plan** — die Datei stand weder
im Dateiblock von AP-1 noch in der Ausgangslage-Tabelle, obwohl sie vom
Umbau betroffen ist.

**Der Befund.** `src/js/page-index.js:47` liest den Kapiteltitel so:

```js
var link = element.querySelector(':scope > a');
```

Seit AP-1 sitzt der Titel nicht mehr als direktes Kind im `<li>`, sondern in
`details > summary`. Der Ausdruck findet ihn nicht mehr, und die Suche
liefert für den Titel eines **klappbaren** Kapitels „Keine Treffer".
AP-1 hat es mit dem echten gebauten Skript gemessen:

| Suche | Ergebnis |
|---|---|
| Titel eines **klappbaren** Kapitels | **„Keine Treffer"** ← Regression |
| Titel eines Kapitels **ohne** Unterseiten | gefunden |
| Titel einer Unterseite (Ebene 1) | Kapitel wird sichtbar |
| Titel einer Unterseite (Ebene 2) | Kapitel wird sichtbar |

Betroffen ist also genau ein Fall — aber der häufigste, denn Kapitel **mit**
Unterseiten sind der Regelfall.

**Umsetzung.** AP-1 hat die Behebung an einer Kopie im Webroot verifiziert
(die Theme-Datei selbst blieb unangetastet):

```js
var summary = element.querySelector(':scope > details > summary');
var link = summary
    ? summary.querySelector('.page-index__chapter-link, .page-index__chapter-title')
    : element.querySelector(':scope > a, :scope > span');
```

Zwei Feinheiten, die nicht wegvereinfacht werden dürfen:

1. **Die Klassenauswahl statt `> a`** — sonst träfe der Ausdruck die
   Anzahl-Anzeige `.page-index__chapter-count`, die ebenfalls im `<summary>`
   sitzt, und die Suche vergliche gegen „3 Seiten" statt gegen den Titel.
2. **Die `span`-Varianten** decken die gesperrten Seiten aus AP-3 gleich mit
   ab. Wer sie weglässt, baut denselben Fehler ein zweites Mal, sobald AP-3
   fertig ist.

Danach `npm run build:js` — **nicht** `npm run build`. Letzteres ruft
`backup-and-build.js` und erhöht die Versionsnummer in `package.json` und
`style.css`, was Pflichtregel 7 dieses Plans untersagt.

**Akzeptanzkriterien:**

- AK1: Die Suche findet ein klappbares Kapitel über seinen Titel.
- AK2: Die drei bisher funktionierenden Fälle aus der Tabelle oben
  funktionieren weiterhin.
- AK3: Ein Suchbegriff, der nur in der Anzahl-Anzeige vorkommt (etwa
  „Seiten"), führt **nicht** zu einem Treffer auf jedem Kapitel.
- AK4: `node --check src/js/page-index.js` grün.
- AK5: `package.json` und `style.css` sind **unverändert** — Nachweis über
  `git diff`.
- AK6: Das gebaute Ergebnis liegt vor und ist auf den Testserver kopiert.

---

### AP-3: Sperre in Inhaltsverzeichnis und Seitenleiste auswerten

**Modell:** opus
**Abhängigkeiten:** AP-1, AP-2
**Dateien:** `includes/page-index.php`, `sidebar.php`

**Umsetzung:**

1. **Inhaltsverzeichnis:** Die gesperrten IDs **einmal** sammeln, nicht je
   Knoten einzeln abfragen. Vorbild ist die vorhandene Abfrage für
   `_simple_clean_hide_from_index` in `page-index.php` (etwa Zeile 158) — ein
   `$wpdb`-Aufruf über die Meta-Tabelle, Ergebnis als Nachschlagekarte.
   **Eine Abfrage je Seite wäre bei rund 260 Seiten der Anfang eines
   Lastproblems**, und der Block verzichtet bewusst auf jeden
   Zwischenspeicher, der so etwas kaschieren würde.
2. Gesperrte Knoten geben `<span>` statt `<a>` aus — **anders als
   `_simple_clean_hide_from_index` entfällt der Unterbaum NICHT.** Genau
   darum geht es: Die Seite bleibt als Gliederungspunkt sichtbar, nur nicht
   anklickbar.
3. **Seitenleiste:** In `display_page_tree_item()` (`sidebar.php` ab Zeile
   52) dasselbe — gesperrte Seiten als Text statt als Link. Die
   Kindsmap-Rekursion und das Aufklappverhalten bleiben unberührt.
4. Die gesperrten IDs auch hier **einmal** vor der Rekursion holen und
   durchreichen, nicht je Knoten.

**Akzeptanzkriterien:**

- AK1: Eine gesperrte Seite ist im Inhaltsverzeichnis nicht anklickbar,
  klappt aber ihre Unterseiten auf und zu.
- AK2: Ihre Unterseiten bleiben sichtbar und anklickbar.
- AK3: In der Seitenleiste ist sie nicht anklickbar, ihre Unterseiten schon.
- AK4: Die Seite bleibt über ihre URL erreichbar und erscheint in der Suche —
  **das ist gewollt** und der Unterschied zur Lehrpersonen-Sperre.
- AK5: Die Zahl der Datenbankabfragen wächst **nicht** mit der Seitenzahl.
  Nachweis über `$wpdb->num_queries` vor und nach der Ausgabe, einmal mit
  wenigen und einmal mit vielen Seiten.
- AK6: `_simple_clean_hide_from_index` verhält sich unverändert (Seite
  **samt** Unterbaum weg) — die beiden Funktionen dürfen sich nicht
  vermischen.

---

### AP-4: Abnahme auf dem Testserver

**Modell:** opus
**Abhängigkeiten:** AP-1, AP-2, AP-3

1. Theme-Dateien auf den Testserver bringen; nach Änderungen an `src/*` den
   Vite-Build ausführen.
2. Eine Prüfhierarchie mit **mindestens drei Ebenen** und einer leeren
   Elternseite anlegen (Seiten 69 → 70 → 71 → 72 sind vorhanden).
3. Die Elternseite sperren und prüfen: Klick klappt auf und zu, öffnet aber
   nicht; Unterseiten anklickbar; Ebene 2 immer sichtbar.
4. Dieselbe Seite in der **Seitenleiste** prüfen.
5. `showCounts` ein- und ausschalten.
6. Alle drei Darstellungen (`cards`, `list`, `columns`).
7. **Ohne JavaScript** prüfen (im Browser abschalten): Klappen muss weiter
   funktionieren.
8. **Mit der Tastatur** prüfen: Tab auf das Kapitel, Enter oder Leertaste
   klappt.
9. **Regressionen, einzeln:** Suche im Block; `_simple_clean_hide_from_index`
   (Seite samt Unterbaum weg); „nur für Lehrpersonen" (Seite für Abgemeldete
   verborgen); Kapitel **ohne** Unterseiten; direkter URL-Aufruf einer
   gesperrten Seite muss funktionieren.
10. `debug.log` und Browserkonsole ohne neue Meldungen.
11. **Urteil des Nutzers einholen** zu der in Abschnitt 5 offen benannten
    Frage: Bei einer **nicht** gesperrten Elternseite klickt man auf den
    Titel zum Navigieren und daneben zum Klappen. Fühlt sich das richtig an,
    oder soll dort ebenfalls nur geklappt werden?
12. Theme-Version in `style.css` erhöhen.

---

### AP-5: Dokumentation

**Modell:** sonnet
**Abhängigkeiten:** AP-4
**Dateien:** `Theme/CLAUDE.md`, `Theme/reference_file_map.md`, dieser Plan

1. `Theme/CLAUDE.md`, Abschnitt **„Inhaltsverzeichnis-Block"**: die neue
   Struktur beschreiben, die geänderte Klassenaufzählung mitziehen, und
   festhalten, dass **nur Ebene 0 klappbar** ist und warum.
2. Einen Abschnitt zur neuen Option ergänzen — mit der **Abgrenzung zu den
   beiden anderen Seiten-Metas** in einer Tabelle: `_simple_clean_nav_gesperrt`
   (nicht anklickbar, bleibt erreichbar), `_simple_clean_hide_from_index`
   (samt Unterbaum aus dem Verzeichnis, bleibt erreichbar),
   `_simple_clean_nur_lehrpersonen` (verborgen und gesperrt). Drei ähnlich
   klingende Schalter mit drei verschiedenen Wirkungen sind sonst eine
   sichere Quelle für Missverständnisse.
3. `Theme/reference_file_map.md`: Zeilen für `includes/page-index.php`,
   `functions.php`, `sidebar.php` und `src/css/page-index.css` ergänzen.
4. Abschnitt 8 und 9 dieses Plans füllen.
5. Nachweis: `grep -c 'Ã\|â€' <datei>` liefert 0.

## 8. Status

| AP | Titel | Modell | Abhängig von | Status |
|---|---|---|---|---|
| AP-1 | Aufklappen über die Elternseite, Zählung, nur Ebene 0 | opus | – | ☑ |
| AP-2 | Seiten-Option „Für Navigation sperren" | sonnet | – | ☑ |
| AP-1.fix1 | Die Suche findet klappbare Kapitel nicht mehr | sonnet | 1 | ☑ |
| AP-3 | Sperre in Verzeichnis und Seitenleiste auswerten | opus | 1, 2 | ☑ (vom Orchestrator gerettet und geprueft) |
| AP-4 | Abnahme auf dem Testserver | opus | 1, 2, 3 | ☐ |
| AP-5 | Dokumentation | sonnet | 4 | ☐ |

**AP-1 und AP-2 sind voneinander unabhängig und dürfen parallel laufen** —
AP-1 arbeitet in `includes/page-index.php` und `src/css/page-index.css`,
AP-2 ausschließlich in `functions.php`. AP-3 braucht beide.

## 9. Testprotokoll

| AP | Test | Ergebnis | Datum |
|---|---|---|---|
| AP-1 | Klappen über den Kapiteleintrag, keine Zeile „Unterseiten" | – | – |
| AP-1 | Nur Ebene 0 klappbar, Kapitel ohne Kinder unverändert | – | – |
| AP-2 | Checkbox speichert, Hilfetext nennt die Erreichbarkeit | – | – |
| AP-1.fix1 | Vier Suchfälle im echten Browser gegen das gebaute Skript | **bestanden.** Kontrollversuch mit dem alten Selektor reproduziert die Regression, mit dem neuen ist sie weg. Suche nach „Seiten" (nur im Zählbadge) liefert korrekt **keine** Treffer | 2026-08-21 |
| AP-3 | Ausführungsprüfung `sidebar.php` (Lehre v1.5.57→58) | **bestanden**, vom Orchestrator nachgeholt: Seiten 69, 70, 72, 33 alle HTTP 200, **null** Fatal- oder Parse-Errors |  2026-08-21 |
| AP-3 | Gesperrte Seite: klappt, öffnet nicht, Kinder anklickbar | **bestanden.** Sperre testweise auf Seite 70 gesetzt: In der Seitenleiste erscheint sie als `<span class="page-link page-link-gesperrt">`, ihr Kind bleibt ein `<a>`. Nach dem Entfernen wieder ein Link | 2026-08-21 |
| AP-3 | Abfragenzahl wächst nicht mit der Seitenzahl | **bestanden, in Produktivgröße gemessen.** Bei **274** veröffentlichten Seiten mit 42 gesperrten: `simple_clean_nav_gesperrte_seiten()` = **1 Abfrage**, zweiter Aufruf **0** (memoisiert); vollständiges Inhaltsverzeichnis über alle 274 Seiten = **3 Abfragen** insgesamt. Alle 42 gesperrten Knoten als `<span>` gerendert | 2026-08-21 |
| AP-3 | AK4: Gesperrte Seite bleibt erreichbar | **bestanden** — direkter Aufruf HTTP 200, Seite erscheint weiter in der Suche. Das ist der gewollte Unterschied zur Lehrpersonen-Sperre |  2026-08-21 |
| AP-3 | `hide_from_index` und Lehrpersonen-Sperre unverändert | – | – |
| AP-4 | Ohne JavaScript und mit der Tastatur | – | – |
| AP-4 | Alle drei Darstellungen, `showCounts` ein und aus | – | – |
| AP-4 | Urteil des Nutzers zum Klick auf ungesperrte Elternseiten | – | – |
| AP-5 | Mojibake-Kontrolle | – | – |
