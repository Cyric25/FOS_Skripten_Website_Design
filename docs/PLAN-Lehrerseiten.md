# Projektplan: Seiten nur für Lehrpersonen

_Erstellt am: 2026-08-11 · Letzte Aktualisierung: 2026-08-11 (AP-1.3 erledigt)_

Grundlage: `Theme/docs/ERWEITERUNGSANALYSE-Lehrerseiten.md` (vom Nutzer bestätigt).

## 0. Anweisungen für den ausführenden Agenten

Du arbeitest nach diesem Plan. Er ist die einzige Wahrheitsquelle – du hast
keinen Zugriff auf das Gespräch, in dem er entstand. Halte dich an diese Regeln:

**Rollen und Modelle:**

A. Wird die Abarbeitung von einem Orchestrator koordiniert (Opus), gilt:
   Der Orchestrator delegiert APs an Subagenten und implementiert NIEMALS
   selbst. Er gibt jedem Subagenten nur dessen AP-Text plus die Abschnitte
   0–3 dieses Plans als Kontext, prüft jede Rückmeldung gegen die
   Akzeptanzkriterien des APs, bevor er abhängige APs freigibt, und pflegt
   die Statustabelle.
B. Jedes AP nennt sein Ausführungsmodell (**Modell:** sonnet | opus).
   Subagenten mit genau diesem Modell starten.
C. Unabhängige APs derselben Phase dürfen parallel bearbeitet werden – in
   getrennten Git-Worktrees mit je eigenem Branch. **APs, die dieselben
   Dateien ändern, nie parallel ausführen.** Welche APs parallel laufen
   dürfen, steht in der Phasenübersicht (Abschnitt 6).

**Arbeitsweise:**

1. Bearbeite genau EIN Arbeitspaket (AP) pro Auftrag, sofern nicht anders
   beauftragt.
2. Prüfe vor Beginn die Abhängigkeiten deines APs in der Statustabelle
   (Abschnitt 8). Sind sie nicht ☑, brich ab und melde das.
3. Setze deinen AP-Status auf ◐ (in Arbeit), bevor du beginnst.
4. Bleibe strikt im Scope des APs. Fällt dir Verbesserungspotenzial außerhalb
   auf, notiere es in der Übergabenotiz – setze es nicht um.
5. Beachte die Nicht-Ziele (Abschnitt 2) und Constraints (Abschnitt 3).

**Tests (Pflicht, ein AP ohne bestandene Tests ist nicht fertig):**

6. Nach Abschluss: alle Akzeptanzkriterien einzeln nachweisen + die im AP
   definierten Tests durchführen.
7. Sieht das AP TDD vor: Tests zuerst schreiben, Fehlschlag bestätigen,
   rote Tests committen, dann implementieren bis grün. **Tests niemals
   abändern, damit sie bestehen.** Hältst du einen Test für inhaltlich
   falsch, dokumentiere das in der Übergabenotiz und stoppe – die
   Entscheidung liegt beim Nutzer/Orchestrator.
8. Ergebnis ins Testprotokoll (Abschnitt 9) eintragen.
9. Erst dann Status auf ☑. Bei Fehlschlag: Status ✗ (blockiert), Ursache in
   die Übergabenotiz, nicht mit abhängigen APs weitermachen.
10. Nach dem letzten Implementierungs-AP einer Phase zusätzlich:
    Integrationstest der Phase + Regressionscheck aller vorherigen Phasen.
    Eintrag ins Testprotokoll.
11. Danach folgt das Review-AP (`AP-<N>.rev`): Es wird von einem frischen
    Agenten ausgeführt, der KEINES der APs dieser Phase implementiert hat.
    Der Review-Agent arbeitet ausschließlich lesend und verändert keine
    Datei. Kritische Befunde führen zu Korrektur-APs (Regel 16); die Phase
    ist erst danach abgeschlossen.

**PHP-Prüfung – in JEDEM AP, das PHP anfasst:**

12. `php -l <datei>` für jede geänderte PHP-Datei. Läuft das nicht fehlerfrei,
    ist das AP nicht fertig.
    Zusätzlich bei Dateien im Plugin CDB-Designer:
    `php Plugins/CDB-Designer/tools/check-php74.php` – die Zielumgebung ist
    PHP 7.4.33, lokal läuft PHP 8.5, und `php -l` meldet 8.0-Syntax **nicht**
    als Fehler. Für Theme-Dateien gilt dieselbe Sprachgrenze (siehe
    Abschnitt 3), auch wenn dort kein eigenes Prüfwerkzeug liegt.

**Übergabe:**

13. Fülle die Übergabenotiz deines APs aus: was geändert wurde, getroffene
    Entscheidungen, was für Folge-APs relevant ist.
14. Hat dein AP Dateien angelegt, verschoben oder wesentlich geändert:
    aktualisiere deren Zeilen in der Datei-Map der betroffenen Komponente
    (`Theme/reference_file_map.md` bzw.
    `Plugins/CDB-Designer/reference_file_map.md`).
15. Aktualisiere „Letzte Aktualisierung" im Dateikopf dieses Plans.
16. Git: mindestens ein Commit mit AP-ID im Text, z. B.
    `AP-1.2: <Kurztitel>`. **Achtung, zwei getrennte Repositories** – Theme
    und Plugin haben je ein eigenes Git (siehe Abschnitt 3). Nach jedem
    abgeschlossenen AP den Phasen-Branch des betroffenen Repos pushen.
    Phasen-Branches erst nach bestandenem Integrationstest UND Review in
    `main` mergen, danach ebenfalls pushen.

**Umplanung:**

17. Zeigt sich während der Ausführung, dass der Plan nicht trägt (Review-
    Befunde, blockierte APs, falsche Annahmen), werden Korrektur-APs mit
    fortlaufender Nummer ergänzt (`AP-<N>.fix1`, …) und in Statustabelle
    und Testprotokoll aufgenommen. Bestehende APs und Übergabenotizen
    werden nie gelöscht, nur ergänzt.
18. Was sich als falsche Annahme der Planung herausstellt, gehört in
    Abschnitt 11 („Rückblick und offene Punkte") – so wie es
    `docs/PLAN-Seitenindex.md` und `Plugins/CDB-Designer/docs/PLAN-Seitenimport.md`
    vormachen.

## 1. Projektziel

Einzelne Seiten lassen sich als **„nur für Lehrpersonen"** kennzeichnen. Für
nicht angemeldete Besucher verschwinden sie vollständig aus Seitenleiste,
Inhaltsverzeichnis, Menü, Suche, REST und Sitemap; der direkte Aufruf endet mit
HTTP 403 auf einer Hinweisseite mit Anmelde-Link.

Container-Blöcke einer solchen Seite, die für eine Klasse als **„behandelt"**
markiert sind, bleiben in der bestehenden Klassenansicht
(`?classroom=<id>&token=<…>`) sichtbar — und zwar **nur diese**, auch im
HTML-Quelltext.

## 2. Nicht-Ziele

- **Keine neue Markierung für Blöcke.** Die vorhandene „behandelt"-Markierung
  (Tabelle `cbd_drawings`, gesetzt im Tafelmodus) wird wiederverwendet. Kein
  neues Block-Attribut, keine neue Editor-Oberfläche, keine neue Spalte.
- **Kein neues Rollen- oder Rechtesystem.** Keine neue WordPress-Rolle, keine
  neuen Capabilities.
- **Kein Umbau des Klassensystems.** Anmeldung per Klassenpasswort,
  Token-Transient, Tafelmodus, Zeichnungen und der Shortcode `[cbd_classroom]`
  bleiben unverändert.
- **Keine Änderung am Verhalten nicht gesperrter Seiten.** Der Klassenmodus
  arbeitet dort weiterhin rein clientseitig.
- **Keine Verschlüsselung, kein Kopierschutz.** Wer die Seite sehen darf, kann
  ihren Inhalt kopieren.
- **Keine neuen Fremdbibliotheken**, kein CDN.
- **Keine Sperre für Beiträge, Glossarbegriffe oder andere Inhaltstypen** —
  ausschließlich `post_type = 'page'`.
- **Keine Migration bestehender Inhalte.** Nach dem Einbau ist keine einzige
  Seite gesperrt, bis jemand das Häkchen setzt.

## 3. Kontext & Constraints

- **Umgebung:** WordPress 6.x auf All-inkl Shared Hosting, PHP 7.4.33, kein
  SSH, kein WP-CLI auf dem Produktivsystem.
- **Zwei Komponenten, zwei Git-Repositories:**

  | Komponente | Pfad | Remote |
  |---|---|---|
  | Theme „FOS Online Schulbuch" | `Theme/` | https://github.com/Cyric25/FOS_Skripten_Website_Design.git |
  | Plugin „Container Block Designer" | `Plugins/CDB-Designer/` | https://github.com/Cyric25/CBD---Container-Block-Desinger.git |

  Beide haben `main` als Hauptbranch und ein verbundenes Remote — es ist **kein
  Setup-AP nötig**.
- **Bestehende Konventionen:** `CLAUDE.md` und `reference_file_map.md` jeweils
  in `Theme/` und `Plugins/CDB-Designer/`, dazu `CLAUDE.md` im Projektstamm.
  Diese haben Vorrang vor allem, was hier nicht ausdrücklich steht.
- **Harte Grenzen:**
  - **PHP 7.4-Syntax** in beiden Komponenten. Kein `match`, keine
    Konstruktor-Promotion, keine benannten Argumente, kein Nullsafe-Operator,
    keine Union-Types in Signaturen, kein `str_contains`/`str_starts_with`
    ohne eigenen Rückfall.
  - Keine neuen Fremdbibliotheken, keine CDN-Einbindung (DSGVO).
  - Editor- und Frontend-JavaScript des Plugins ohne Build-Schritt: IIFE,
    Zugriff über `wp.*`-Globale, kein `import`/`export`.
  - `console.log` nur hinter `window.cbdDebug`; PHP-Informationslogs nur
    hinter `WP_DEBUG`.
  - Post-Meta-Werte werden als String `'1'` geschrieben und mit
    `delete_post_meta()` entfernt.
- **Testumgebung:** Lokaler All-inkl-Nachbau unter `C:\allinkl-testserver`
  (Apache + PHP 8.3 + MariaDB, Start per `start-server.cmd`).
  - WordPress: `C:\allinkl-testserver\www\htdocs\w0000001\fos`
  - **URL: http://fos.localhost:8080/** — nicht über den Pfad
    `localhost:8080/fos/`.
    `siteurl` steht auf dem Hostnamen `fos.localhost`; über den Pfad
    aufgerufen antwortet WordPress mit 404. (In AP-1.1 zunächst falsch
    angenommen und dort korrigiert.)
  - **Die Installation enthält keine Seiten.** Der Prüfaufbau muss angelegt
    werden — AP-1.3 legt ihn an (Kapitelseite mit Unterseiten, eine davon
    gesperrt) und beschreibt ihn, die folgenden APs bauen darauf auf.
  - Anmeldung: Benutzer `admin` (einziger Benutzer, Rolle Administrator).
  - Theme liegt dort als `wp-content/themes/fos-online-schulbuch`,
    die Plugins als `wp-content/plugins/container-block-designer` und
    `wp-content/plugins/modular-blocks-plugin`.
  - **Zum Testen werden die geänderten Dateien aus dem Arbeitsverzeichnis in
    die Testinstallation kopiert** (nicht umgekehrt — das Arbeitsverzeichnis
    ist die Quelle).
  - `WP_DEBUG` und `WP_DEBUG_LOG` in `wp-config.php` einschalten, Log unter
    `wp-content/debug.log` prüfen.
  - Achtung: Der Testserver läuft mit **PHP 8.3**, die Zielumgebung mit 7.4.
    Ein Test dort beweist keine 7.4-Tauglichkeit — dafür ist die Prüfung aus
    Abschnitt 0, Regel 12 zuständig.
- **Git-Strategie:** Branch pro Phase in dem Repository, das die Phase
  betrifft (`phase-1-lehrerseiten` im Theme, `phase-2-lehrerseiten` im
  Plugin). Commit pro AP mit AP-ID. Push nach jedem AP.
- **Ausrollreihenfolge auf dem Produktivsystem: erst Theme, dann Plugin.**
  Das Theme allein sperrt ohne Durchlass — die sichere Zwischenstufe. Umgekehrt
  gäbe es kurzzeitig einen Durchlass ohne Sperre, was harmlos, aber sinnlos ist.

## 4. Architekturentscheidungen

| Entscheidung | Begründung | Verworfene Alternative |
|---|---|---|
| **Sperre im Theme, Durchlass im Plugin** | Alle Stellen, an denen eine Seite sichtbar wird, liegen im Theme. `simple_clean_page_index_daten()` stellt rohe SQL-Abfragen ohne Filterpunkt — ein Plugin käme dort nicht heran. Umgekehrt weiß nur das Plugin, was eine Klasse ist | Alles im Plugin: hätte Theme-Änderungen trotzdem erzwungen. Alles im Theme: das Theme müsste `cbd_drawings` und die Token-Transients kennen |
| **Verbindung über den Filter `simple_clean_lehrerseite_freigeben` mit Standardwert `false`** | Fehlt das Plugin, ist es abgeschaltet oder greift der Filter nicht, bleibt die Seite gesperrt. Ein Fehler in der Naht zeigt zu wenig, nie zu viel | Direkter Funktionsaufruf vom Theme ins Plugin: koppelt beide fest und fällt bei fehlendem Plugin mit Fatal Error aus |
| **Eigene Datei `Theme/includes/sichtbarkeit.php`** statt weiterer 300 Zeilen in `functions.php` | `functions.php` hat bereits ~3900 Zeilen. Eine eigene Datei lässt sich headless mit wenigen Stubs testen; `functions.php` als Ganzes nicht. Entspricht der vorhandenen Struktur (`includes/page-index.php`) und ist von der ZIP-Whitelist (`includes/**/*.{php,js}`) bereits abgedeckt | Alles in `functions.php`: nicht testbar |
| **Ein Post-Meta `_simple_clean_nur_lehrpersonen`, keine Tabelle** | Folgt `_simple_clean_hide_navigation` und `_simple_clean_hide_from_index`. Keine Migration, kein Schema-Risiko | Eigene Tabelle: unnötig für ein Ja/Nein je Seite |
| **Die Sperre vererbt sich auf den gesamten Unterbaum** | Wie `_simple_clean_hide_from_index`. Eine Seite „Lösungen" mit Unterseiten je Kapitel ist der wahrscheinliche Aufbau; ein vergessenes Häkchen wäre ein Leck. Im Inhaltsverzeichnis und in der Seitenleiste ergibt sich das gratis, weil beide Bäume von der Wurzel abwärts laufen | Nur die markierte Seite: fehleranfällig |
| **„Lehrperson" = angemeldet, aber in EINER Funktion mit Filter** | Nutzerentscheidung. Schüler melden sich nie an. Die Kapselung sorgt dafür, dass die Verschärfung auf `current_user_can('cbd_edit_blocks')` später eine Zeile ist statt acht | Verstreute `is_user_logged_in()`-Prüfungen |
| **Serverseitige Reduktion nur auf gesperrten Seiten und nur für nicht Angemeldete** | Der heutige Klassenfilter arbeitet rein clientseitig — für Lösungsseiten kein Schutz. Überall serverseitig zu filtern würde dagegen das Verhalten aller Seiten ändern und der Lehrperson die Vorschau nehmen | Reduktion überall: zu großer Eingriff |
| **Reduktion über `the_content` (Priorität 8) mit `parse_blocks()` + `render_block()` je erlaubtem Block** | `do_blocks` hängt auf Priorität 9. Erlaubte Blöcke gehen unverändert durch den normalen Renderpfad; es wird nichts serialisiert, der bekannte Whitespace-Unterschied zwischen JS- und PHP-Serializer bleibt außen vor | `serialize_blocks()`-Rundlauf: unnötiges Risiko. `render_block`-Filter je Block: kennt die Verschachtelungstiefe nicht zuverlässig |
| **Hinweisseite statt 404** | Nutzerentscheidung. HTTP-Status trotzdem **403** und `noindex`, und **ohne Seitentitel** — sonst verriete die Hinweisseite, wie die Lösung heißt | 404: verriete weniger, war aber nicht gewünscht |

## 5. Risiken & Rollback

| Risiko | Wahrscheinlichkeit | Auswirkung | Gegenmaßnahme / Rollback |
|---|---|---|---|
| Eine der Ausblend-Stellen wird vergessen — Seite fehlt in der Seitenleiste, erscheint aber in der Suche | mittel | mittel | AP-3.1 geht alle Stellen mit einer Prüfliste durch. Die Hinweisseite ist die letzte Verteidigungslinie: selbst ein durchgerutschter Link führt nicht zum Inhalt |
| Reduktion greift versehentlich auf nicht gesperrten Seiten → Inhalte verschwinden im laufenden Betrieb | gering | **hoch** | Erste Bedingung der Reduktion ist `simple_clean_seite_nur_lehrpersonen()`. Eigener Testfall in AP-2.3, eigener Regressionspunkt in AP-2.rev |
| Reihenfolge auf `the_content` kollidiert mit Glossar-Verlinkung (Priorität 10000) oder LaTeX | mittel | mittel | Eigenes Akzeptanzkriterium in AP-2.3: Glossarbegriffe und Formeln müssen in den verbliebenen Blöcken korrekt erscheinen |
| Seiten-Cache liefert die Lehrer-Ansicht an nicht Angemeldete | gering | **hoch** | Hinweisseite sendet `nocache_headers()`. AP-3.1 prüft, ob ein Caching-Plugin aktiv ist und ob es für angemeldete Benutzer umgeht |
| Sperre öffnet sich still, sobald ein Konto ohne Lehrauftrag existiert | mittel | hoch | Eine Funktion, ein Filter, ausdrücklicher Warnhinweis in `Theme/CLAUDE.md` (AP-3.doc) |
| Altbestände tragen die `stableId` nur im HTML, nicht in den Attributen → korrekt markierte Blöcke verschwinden | mittel | mittel | Rückfall-Regex wie in `CBD_Block_Registration::render_block()`; eigener Testfall in AP-2.3 |
| Zusätzliche Abfragen bremsen Seitenleiste und Inhaltsverzeichnis | gering | mittel | Höchstens zwei zusätzliche Abfragen je Aufruf, und nur für nicht Angemeldete; ohne gesperrte Seiten genau eine. Messung mit `?sc_perf=1` in AP-1.rev |
| Refactoring in `class-cbd-classroom.php` (1462 Zeilen) bricht bestehende AJAX-Endpunkte | mittel | hoch | AP-2.1 ändert ausschließlich `ajax_get_page_classroom_data()` auf die neuen Helfer und lässt das Verhalten identisch; Regressionstest im AP |
| PHP-8.0-Syntax rutscht durch, weil lokal 8.5 läuft | mittel | hoch | Abschnitt 0, Regel 12: `tools/check-php74.php` ist Pflicht |

**Generelle Rollback-Strategie:** Branch pro Phase in dem jeweiligen Repository;
`main` bleibt bis zum bestandenen Review unangetastet. Beide Komponenten sind
reine Datei-Auslieferungen ohne Datenbank-Migration — ein Rollback ist das
Zurückspielen des vorherigen ZIPs. Das Theme sichert zusätzlich automatisch die
vorherige ZIP-Generation (`backup-and-build.js`). **Es gibt keine destruktive
Datenbankoperation in diesem Plan;** das einzige neue Datum ist ein Post-Meta,
das bei Nichtgebrauch schlicht nicht existiert.

## 6. Phasenübersicht

Jede Phase endet mit `AP-<N>.rev` (unabhängiges Review) und `AP-<N>.doc`
(Dokumentation) – in dieser Reihenfolge nach den Implementierungs-APs.

| Phase | Ziel | Lauffähiger Endzustand | APs |
|---|---|---|---|
| 1 | Theme: Sperre | Eine als „nur für Lehrpersonen" markierte Seite ist für nicht Angemeldete überall verschwunden und liefert beim direkten Aufruf die Hinweisseite. **Auch für die Klasse** — der Durchlass fehlt noch | AP-1.1 … AP-1.6, AP-1.rev, AP-1.doc |
| 2 | Plugin: Durchlass | Auf einer gesperrten Seite sind in der Klassenansicht genau die für diese Klasse als „behandelt" markierten Container sichtbar — auch im Quelltext. Nicht gesperrte Seiten verhalten sich unverändert | AP-2.1 … AP-2.4, AP-2.rev, AP-2.doc |
| 3 | Absicherung und Auslieferung | Alle Ausblend-Stellen nachweislich dicht, beide Verteilungspakete gebaut, Dokumentation und Datei-Maps nachgezogen | AP-3.1 … AP-3.2, AP-3.rev, AP-3.doc |

**Parallelisierbarkeit** (wichtig, weil mehrere APs dieselbe Datei anfassen):

| Phase | Sequenziell (gemeinsame Datei) | Parallel möglich |
|---|---|---|
| 1 | AP-1.1 → AP-1.2 → AP-1.3 → AP-1.5 (alle `functions.php` bzw. `includes/sichtbarkeit.php`) | AP-1.4 und AP-1.6 laufen parallel dazu, sobald AP-1.1 ☑ ist |
| 2 | AP-2.1 → AP-2.2 → AP-2.3 → AP-2.4 (bauen aufeinander auf) | – |
| 3 | AP-3.1 → AP-3.2 | – |

## 7. Arbeitspakete

### Phase 1: Theme – die Sperre

---

### AP-1.1: Zentrale Sichtbarkeitslogik mit Prüfharnisch (TDD)

**Status:** ☑ erledigt (2026-08-11)
**Umfang:** M
**Modell:** opus (sicherheitsrelevante Zugriffsentscheidung; definiert die Regel, auf der alles Weitere aufbaut)
**Abhängigkeiten:** keine

**Ziel & Kontext:**

Das Theme „FOS Online Schulbuch" (`Theme/`) soll Seiten kennen, die nur für
angemeldete Benutzer sichtbar sind. Dieses AP legt die **einzige Stelle** an, an
der diese Entscheidung getroffen wird. Alle weiteren APs rufen nur noch diese
Funktionen auf.

Neue Datei `Theme/includes/sichtbarkeit.php`, eingebunden aus `functions.php`.
Grund für die eigene Datei: `functions.php` hat ~3900 Zeilen und ruft beim Laden
Dutzende WordPress-Funktionen auf — headless nicht testbar. Eine eigene Datei
lässt sich mit wenigen Stubs prüfen. Sie ist von der ZIP-Whitelist in
`Theme/create-theme-zip.js` (Eintrag `includes/**/*.{php,js}`) bereits abgedeckt,
dort ist **keine** Änderung nötig.

Das Speicherformat folgt den zwei bestehenden Seiten-Metas des Themes
(`_simple_clean_hide_navigation`, `_simple_clean_hide_from_index`): Post-Meta mit
dem String `'1'`, Abwesenheit bedeutet „nicht gesetzt".

**Betroffene Dateien:**
- `Theme/includes/sichtbarkeit.php` (neu)
- `Theme/functions.php` (ändern – eine `require_once`-Zeile)
- `Theme/tools/test-sichtbarkeit.php` (neu – Prüfharnisch)
- `Theme/reference_file_map.md` (ändern)

**Vorgehen:**

1. **Zuerst den Prüfharnisch schreiben** (`Theme/tools/test-sichtbarkeit.php`),
   Muster: `Plugins/CDB-Designer/tools/test-icon-scale.php` — eigenständiges
   PHP-Skript, das ohne WordPress läuft, die zu prüfende Datei einbindet und
   die benötigten WordPress-Funktionen vorher als Stubs definiert. Gebraucht
   werden mindestens: `is_user_logged_in()`, `get_post_meta()`,
   `get_post_ancestors()`, `apply_filters()`, `add_action()`, `add_filter()`,
   sowie ein minimales `$wpdb`-Objekt mit `get_col()` und den Eigenschaften
   `posts` und `postmeta`. Die Stubs lesen aus globalen Testarrays, die jeder
   Testfall setzt. Ausgabe wie bei den CDB-Harnischen: je Prüfung eine Zeile
   `OK`/`FEHLER`, am Ende eine Bilanz und Exit-Code 1 bei Fehlern.
2. Testfälle anlegen (mindestens diese, Normalfall / Randfall / Fehlerfall):

   | Nr | Situation | Erwartung |
   |---|---|---|
   | 1 | nicht angemeldet | `simple_clean_ist_lehrperson()` === false |
   | 2 | angemeldet | `simple_clean_ist_lehrperson()` === true |
   | 3 | Filter `simple_clean_ist_lehrperson` erzwingt false trotz Anmeldung | false |
   | 4 | Seite ohne Meta | `simple_clean_seite_nur_lehrpersonen()` === false |
   | 5 | Seite mit Meta `'1'` | true |
   | 6 | Seite ohne Meta, **Elternseite** mit Meta | true (Vererbung) |
   | 7 | Seite ohne Meta, Großelternseite mit Meta | true |
   | 8 | Meta mit Wert `''` bzw. `'0'` | false |
   | 9 | keine gesperrte Seite im System | `simple_clean_gesperrte_seiten()` === leeres Array **und** `simple_clean_gesperrte_seiten_mit_unterbaum()` löst **keine** zweite Abfrage aus |
   | 10 | gesperrte Seite mit zwei Kindern und einem Enkel | `simple_clean_gesperrte_seiten_mit_unterbaum()` enthält alle vier IDs |
   | 11 | Zyklus in `post_parent` (A→B→A) | Funktion terminiert, kein Endlosdurchlauf |
   | 12 | angemeldet + gesperrte Seite | `simple_clean_seite_sichtbar()` === true |
   | 13 | nicht angemeldet + gesperrte Seite, Filter `simple_clean_lehrerseite_freigeben` gibt true | `simple_clean_seite_sichtbar()` === true |
   | 14 | nicht angemeldet + gesperrte Seite, kein Filter | false |
   | 15 | nicht angemeldet + nicht gesperrte Seite | true |

3. Harnisch laufen lassen — **alle Prüfungen müssen fehlschlagen** (die Datei
   `sichtbarkeit.php` existiert noch nicht bzw. ist leer). Diesen roten Stand
   committen.
4. `Theme/includes/sichtbarkeit.php` anlegen mit `if (!defined('ABSPATH')) exit;`
   am Anfang und folgenden Funktionen, jeweils in `function_exists`-Guards:

   ```php
   // Der einzige Ort, an dem „Lehrperson" definiert ist.
   function simple_clean_ist_lehrperson() : bool
   // IDs aller Seiten mit dem Meta, als array(ID => true). Eine Abfrage,
   // statisch für die Dauer des Aufrufs gehalten.
   function simple_clean_gesperrte_seiten() : array
   // Die IDs aus simple_clean_gesperrte_seiten() plus ALLE Nachfahren,
   // als array(ID => true). Statisch gehalten.
   function simple_clean_gesperrte_seiten_mit_unterbaum() : array
   // Ist diese Seite selbst oder einer ihrer Vorfahren gesperrt?
   function simple_clean_seite_nur_lehrpersonen($post_id) : bool
   // Die Gesamtentscheidung.
   function simple_clean_seite_sichtbar($post_id) : bool
   ```

5. Regeln für die Umsetzung:
   - `simple_clean_ist_lehrperson()` gibt
     `apply_filters('simple_clean_ist_lehrperson', is_user_logged_in())`
     zurück, als `bool` gecastet. Über dieser Funktion steht ein Kommentarblock,
     der festhält: Diese Definition trägt nur, solange es ausschließlich
     Lehrer-Konten gibt; sobald ein Konto ohne Lehrauftrag existiert, ist hier
     auf `current_user_can('cbd_edit_blocks')` zu verschärfen.
   - `simple_clean_gesperrte_seiten()`: **eine** Abfrage
     `SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_simple_clean_nur_lehrpersonen' AND meta_value = '1'`,
     Ergebnis über `array_flip(array_map('intval', …))` zu `ID => true`. Muster
     wörtlich wie in `Theme/includes/page-index.php`, Zeile 156–161.
   - `simple_clean_gesperrte_seiten_mit_unterbaum()`: Ist die Liste aus
     `simple_clean_gesperrte_seiten()` **leer, sofort ein leeres Array
     zurückgeben** — keine zweite Abfrage. Sonst
     `SELECT ID, post_parent FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status != 'trash'`
     laden, eine Eltern-Kind-Map bauen und von jeder gesperrten Seite abwärts
     laufen. Eine Besuchsliste verhindert Endlosdurchläufe bei Zyklen
     (dasselbe Vorgehen wie die Breitensuche in
     `simple_clean_page_index_daten()`).
   - `simple_clean_seite_nur_lehrpersonen($post_id)`: prüft
     `get_post_meta($post_id, '_simple_clean_nur_lehrpersonen', true) === '1'`
     und, falls nicht, jeden Eintrag von `get_post_ancestors($post_id)` gegen
     `simple_clean_gesperrte_seiten()`.
   - `simple_clean_seite_sichtbar($post_id)`: `true`, wenn
     `simple_clean_ist_lehrperson()`; sonst `true`, wenn die Seite nicht
     gesperrt ist; sonst
     `(bool) apply_filters('simple_clean_lehrerseite_freigeben', false, (int) $post_id)`.
     **Der Standardwert des Filters ist `false` und bleibt es** — er ist die
     Naht, an der sich das Plugin CDB-Designer einhängt (Phase 2). Ein
     ausbleibender Filter muss zur Sperre führen, nie zur Freigabe.
   - PHP 7.4: Rückgabetypen `: bool` / `: array` sind erlaubt, Union-Types nicht.
6. In `Theme/functions.php` die Zeile
   `require_once get_template_directory() . '/includes/sichtbarkeit.php';`
   ergänzen, bei den bestehenden `require_once`-Zeilen für `includes/`
   (dort wird u. a. `includes/page-index.php` eingebunden).
7. Harnisch erneut laufen lassen, bis alle Prüfungen grün sind. **Die Tests
   dabei nicht anfassen.**
8. Zeilen für `includes/sichtbarkeit.php` und `tools/test-sichtbarkeit.php` in
   `Theme/reference_file_map.md` ergänzen (Tabellen „Eingebundene Module" bzw.
   eine neue Tabelle „Werkzeuge und Tests"; bei `tools/` vermerken, dass der
   Ordner bewusst **nicht** in der ZIP-Whitelist steht).

**Akzeptanzkriterien:**
- [ ] `php Theme/tools/test-sichtbarkeit.php` meldet alle 15 Prüfungen als bestanden, Exit-Code 0.
- [ ] Es existiert ein Commit, der die Tests im roten Zustand enthält, **vor** dem Commit mit der Implementierung.
- [ ] `php -l` läuft fehlerfrei über `Theme/includes/sichtbarkeit.php`, `Theme/functions.php` und `Theme/tools/test-sichtbarkeit.php`.
- [ ] Keine PHP-8.0-Syntax: kein `match`, kein `?->`, keine Konstruktor-Promotion, keine benannten Argumente, kein `str_contains`/`str_starts_with` ohne Rückfall.
- [ ] Ohne gesperrte Seiten löst `simple_clean_gesperrte_seiten_mit_unterbaum()` genau **eine** Abfrage aus (Prüfung 9 zählt die Stub-Aufrufe).
- [ ] Der Filter `simple_clean_lehrerseite_freigeben` hat den Standardwert `false`.
- [ ] `Theme/reference_file_map.md` enthält Zeilen für beide neuen Dateien.

**Tests:**
- Smoke-Test: `php Theme/tools/test-sichtbarkeit.php` läuft ohne Fatal Error durch und gibt eine Bilanzzeile aus.
- Prüfschritt WordPress: Theme-Dateien in die Testinstallation
  `C:\allinkl-testserver\www\htdocs\w0000001\fos\wp-content\themes\fos-online-schulbuch\`
  kopieren, `start-server.cmd` starten, http://fos.localhost:8080/ aufrufen.
  Die Seite muss unverändert erscheinen (dieses AP ändert noch kein Verhalten),
  und `wp-content/debug.log` darf keine neuen Notices/Warnings enthalten.
- Regressionsrelevanz: `functions.php` bekommt nur eine `require_once`-Zeile.
  Prüfen, dass Startseite, eine Unterseite mit Seitenleiste und eine Seite mit
  dem Block „Inhaltsverzeichnis" weiterhin normal laden.

**Übergabenotiz:**

Erledigt am 2026-08-11. **17 statt der geplanten 15 Prüfungen** — zwei kamen
hinzu (16: ein Filter, der `$frei` nur durchreicht, darf nicht freigeben;
17: ein Kind einer gesperrten Seite ist ebenfalls unsichtbar). Beide sichern
die Naht ab, an der Phase 2 andockt. Roter Lauf mit 20 Fehlern ist in Commit
`d42989b` festgehalten, die Tests wurden danach nicht mehr angefasst.

**Abweichung vom Plan — keine `function_exists`-Guards.** Der Plan verlangte
sie. Ich habe die Funktionen unbedingt deklariert, weil bedingt deklarierte
Funktionen von PHP **nicht gehoistet** werden — genau daran ist dieses Theme
schon einmal gescheitert (`sidebar.php`, v1.5.57→58, siehe `CLAUDE.md`). Die
Datei wird per `require_once` genau einmal geladen; Guards brächten keinen
Nutzen, nur das Hoisting-Risiko. Die Absicherung liegt stattdessen bei den
Aufrufern: `sidebar.php` und `page-index.php` (AP-1.4) prüfen ihrerseits mit
`function_exists()`, falls die Datei einmal fehlt. Begründung steht als
Kommentarblock im Dateikopf.

**Zusätzlich eingebaut:** `simple_clean_sichtbarkeit_generation()` und
`simple_clean_sichtbarkeit_cache_leeren()`. Die beiden Listenfunktionen halten
ihr Ergebnis in `static`-Variablen; ohne einen Weg, den Zwischenspeicher zu
verwerfen, wären sie nicht testbar (jeder Testfall braucht einen frischen
Zustand). Nützlich ist das auch für WP-CLI und Importe, die innerhalb eines
Aufrufs Metas ändern.

**Für Folge-APs wichtig:**
- Die Rückgabe von `simple_clean_gesperrte_seiten()` ist `array(ID => true)`,
  gebaut mit `array_fill_keys()` — **nicht** `array_flip()` wie im Plan
  angedeutet. Die Union `$a + $b` in AP-1.4 funktioniert damit wie
  beschrieben.
- `simple_clean_gesperrte_seiten_mit_unterbaum()` gibt ebenfalls
  `array(ID => true)` zurück. Für `post__not_in` in AP-1.5 also
  `array_keys()` verwenden.
- Ohne gesperrte Seite kostet das Ganze **genau eine** Abfrage; der Baumaufbau
  entfällt dann vollständig.

**Zwei Befunde zur Testumgebung, beide im Plan (Abschnitt 3) nachgetragen:**
1. Die richtige Adresse ist **http://fos.localhost:8080/**, nicht
   `http://localhost:8080/fos/`. Über den Pfad antwortet WordPress mit 404,
   weil `siteurl` auf dem Hostnamen steht. Das hat mich kurz glauben lassen,
   die Änderung hätte die Startseite zerschossen — hatte sie nicht. Alle sechs
   URL-Vorkommen im Plan sind korrigiert.
2. **Die Installation enthält keine einzige Seite.** Der Prüfaufbau muss
   angelegt werden; AP-1.3 übernimmt das.

Smoke-Test bestanden: `http://fos.localhost:8080/` liefert HTTP 200 mit dem
Titel „FOS Online Schulbuch (Test)", alle fünf Funktionen sind unter
WordPress vorhanden, `simple_clean_gesperrte_seiten()` liefert gegen die echte
Datenbank ein leeres Array. `wp-content/debug.log` enthält ausschließlich die
vorbestehenden Meldungen der beiden Plugins, keine Zeile aus dem Theme.

PHP 7.4 geprüft: Das Werkzeug `tools/check-php74.php` liegt im CDB-Plugin und
setzt ein `vendor/` neben dem Prüfziel voraus, das dem Theme fehlt. Geprüft
wurde deshalb mit einem Wegwerf-Skript, das den `nikic/php-parser` des Plugins
lädt und die drei Theme-Dateien gegen 7.4 parst — alle drei sauber.
**Empfehlung für AP-3.doc:** überlegen, ob das Theme einen eigenen,
dauerhaften 7.4-Prüfer bekommen soll; derzeit gibt es keinen.

---

### AP-1.2: Häkchen „Nur für Lehrpersonen" in der Meta-Box

**Status:** ☑ erledigt (2026-08-11)
**Umfang:** S
**Modell:** sonnet (Lösungsweg vollständig vorgezeichnet, zwei bestehende Felder als Vorlage)
**Abhängigkeiten:** AP-1.1 (Meta-Name und Konvention stehen fest)

**Ziel & Kontext:**

Die Seiten-Meta-Box des Themes trägt heute den Titel „Navigation &
Inhaltsverzeichnis" und enthält zwei Häkchen. Sie bekommt ein drittes:
**„Nur für Lehrpersonen sichtbar"**, das das Meta
`_simple_clean_nur_lehrpersonen` mit dem String `'1'` setzt bzw. löscht.

Die Box liegt in `Theme/functions.php`:
- Registrierung `simple_clean_add_navigation_meta_box()` ab Zeile 492
- Ausgabe `simple_clean_navigation_meta_box_callback()` ab Zeile 509
- Speichern `simple_clean_save_navigation_meta()` ab Zeile 581

**Die Meta-Box-ID `simple_clean_hide_navigation` bleibt unverändert** — daran
hängen die gespeicherten Bildschirmeinstellungen der Benutzer (auf-/zugeklappt,
Reihenfolge). Der angezeigte **Titel** wird angepasst, weil die Box nun drei
Dinge regelt.

Ebenfalls unverändert bleibt, dass es **nur ein Nonce** für die ganze Box gibt
(`simple_clean_navigation_nonce`) — das ist eine bewusste Entscheidung aus der
Einführung des zweiten Feldes.

**Betroffene Dateien:**
- `Theme/functions.php` (ändern)

**Vorgehen:**
1. In `simple_clean_add_navigation_meta_box()` den Titel von
   `'Navigation & Inhaltsverzeichnis'` auf `'Navigation, Verzeichnis & Zugriff'`
   ändern. Die Meta-Box-ID, den Post-Typ `'page'`, die Position `'side'` und den
   Kontext `'default'` **nicht** anfassen.
2. In `simple_clean_navigation_meta_box_callback()` nach dem Abschnitt für
   `_simple_clean_hide_from_index` einen dritten Block im gleichen Stil
   ergänzen:
   - `$nur_lehrpersonen = get_post_meta($post->ID, '_simple_clean_nur_lehrpersonen', true);`
   - Checkbox `name="simple_clean_nur_lehrpersonen"`, `value="1"`,
     `checked($nur_lehrpersonen, '1')`
   - Beschriftung: **„Nur für Lehrpersonen sichtbar"**
   - Beschreibungstext (`<p class="description">`), der genau das sagt:
     „Die Seite verschwindet für nicht angemeldete Besucher aus Seitenleiste,
     Inhaltsverzeichnis, Menü und Suche; der direkte Aufruf zeigt einen Hinweis.
     **Auch alle Unterseiten** sind dann gesperrt. Blöcke, die im Tafelmodus für
     eine Klasse als *behandelt* markiert sind, bleiben in der Klassenansicht
     sichtbar."
   - Statusmeldung wie bei den anderen beiden Feldern: bei gesetztem Häkchen ein
     rot hinterlegter Hinweis „🔒 Status: Diese Seite und ihre Unterseiten sind
     nur für angemeldete Lehrpersonen sichtbar".
3. In `simple_clean_save_navigation_meta()` nach dem bestehenden Block für
   `_simple_clean_hide_from_index` ergänzen:
   ```php
   if (isset($_POST['simple_clean_nur_lehrpersonen']) && $_POST['simple_clean_nur_lehrpersonen'] === '1') {
       update_post_meta($post_id, '_simple_clean_nur_lehrpersonen', '1');
   } else {
       delete_post_meta($post_id, '_simple_clean_nur_lehrpersonen');
   }
   ```
   Die vorhandenen Prüfungen der Funktion (Nonce, `DOING_AUTOSAVE`,
   `current_user_can('edit_post', $post_id)`, `get_post_type() === 'page'`)
   gelten damit automatisch mit — **keine zweite Prüfung ergänzen**.

**Akzeptanzkriterien:**
- [ ] Die Meta-Box zeigt im Seiten-Editor drei Häkchen; die ersten beiden verhalten sich unverändert.
- [ ] Häkchen setzen + Seite aktualisieren → `_simple_clean_nur_lehrpersonen` hat in `wp_postmeta` den Wert `1`.
- [ ] Häkchen entfernen + Seite aktualisieren → die Meta-Zeile ist **gelöscht**, nicht auf `0` gesetzt.
- [ ] Die Meta-Box-ID im Code lautet weiterhin `simple_clean_hide_navigation`.
- [ ] Es gibt genau ein `wp_nonce_field` in der Meta-Box.
- [ ] `php -l Theme/functions.php` fehlerfrei, keine PHP-8.0-Syntax.

**Tests:**
- Smoke-Test: In der Testinstallation http://fos.localhost:8080/wp-admin/ eine
  Seite öffnen. Die Box „Navigation, Verzeichnis & Zugriff" erscheint rechts mit
  drei Häkchen; keine PHP-Warnung im Editor, `debug.log` ohne neue Einträge.
- Prüfschritt Speichern: Häkchen setzen, aktualisieren, Seite neu laden →
  Häkchen ist noch gesetzt. In phpMyAdmin (http://pma.localhost:8080/,
  Datenbank `d0000001`) in `wp_postmeta` die Zeile prüfen.
- Prüfschritt Löschen: Häkchen entfernen, aktualisieren → Zeile in
  `wp_postmeta` ist verschwunden.
- Regressionsprüfung: Beide bestehenden Häkchen einmal setzen und wieder
  entfernen; Seitenleiste (`_simple_clean_hide_navigation`) und
  Inhaltsverzeichnis (`_simple_clean_hide_from_index`) reagieren wie vorher.

**Übergabenotiz:**

Erledigt am 2026-08-11. Meta-Box-Titel jetzt „Navigation, Verzeichnis &
Zugriff"; Meta-Box-ID unverändert `simple_clean_hide_navigation`.

**Geprüft wurde skriptgesteuert, nicht per Mausklick.** Ein Skript im Webroot
(danach wieder entfernt) legt eine Testseite mit Elternseite an, ruft
`simple_clean_navigation_meta_box_callback()` und `simple_clean_save_navigation_meta()`
mit echtem Nonce auf und liest anschließend `wp_postmeta` direkt aus. Damit
läuft derselbe Codeweg wie beim Speichern im Editor. 20 Prüfungen, alle grün;
die Testseiten werden am Ende wieder gelöscht.

Geprüft wurden dabei auch drei Dinge, die über die Akzeptanzkriterien
hinausgehen und für Folge-APs nützlich sind:
- Ein **ungültiges Nonce** schreibt nichts — die vorhandene Prüfung deckt das
  neue Feld mit ab, wie beabsichtigt.
- Das Meta wirkt sofort in `simple_clean_seite_nur_lehrpersonen()` (nach
  `simple_clean_sichtbarkeit_cache_leeren()`), die Elternseite bleibt frei.
  Die Vererbung läuft also nur abwärts, nicht aufwärts.
- Die zwei bestehenden Häkchen setzen und löschen unverändert ihre Metas, ohne
  das neue zu berühren.

**Eine Prüfung war zunächst rot — der Fehler lag in meiner Messung.** Sie
zählte, wie oft die Zeichenkette `simple_clean_navigation_nonce` im HTML
vorkommt, und erwartete 1. Tatsächlich sind es 2, weil `wp_nonce_field()` den
Namen sowohl als `id=` als auch als `name=` ausgibt — es ist trotzdem genau
**ein** Feld aus genau **einem** Aufruf (per `sed` im Quelltext gegengeprüft).
Die Messung zählt jetzt `name="…"`. Das Kriterium selbst wurde nicht
aufgeweicht.

Für AP-1.6 relevant: Das Meta wird als String `'1'` geschrieben und per
`delete_post_meta()` entfernt — die Sammelaktionen müssen es genauso halten.

---

### AP-1.3: Durchsetzung beim Seitenaufruf und die Hinweisseite

**Status:** ☑ erledigt (2026-08-11)
**Umfang:** M
**Modell:** opus (Zugriffsdurchsetzung; Reihenfolge der `template_redirect`-Haken ist heikel)
**Abhängigkeiten:** AP-1.1

**Ziel & Kontext:**

Der direkte Aufruf einer gesperrten Seite durch einen nicht angemeldeten
Besucher soll den Inhalt **nicht** ausliefern, sondern eine Hinweisseite zeigen.

Am Hook `template_redirect` hängen im Theme bereits zwei Prüfungen, deren
Reihenfolge Bedeutung hat:

| Priorität | Funktion | Zweck |
|---|---|---|
| 1 | `simple_clean_block_ai_user_agents()` | 403 für bekannte AI-Crawler |
| 10 (Standard) | `simple_clean_password_protection_check()` | Passwortschutz der gesamten Website |

Die neue Prüfung kommt mit **Priorität 20**, also nach beiden. Ein anonymer
Besucher, der das Website-Passwort nicht kennt, darf nicht über die Hinweisseite
daran vorbeikommen.

**Betroffene Dateien:**
- `Theme/includes/sichtbarkeit.php` (ändern – Datei aus AP-1.1)

**Vorgehen:**
1. Funktion `simple_clean_lehrerseite_pruefen()` anlegen und mit
   `add_action('template_redirect', 'simple_clean_lehrerseite_pruefen', 20);`
   registrieren.
2. Sofort zurückkehren, wenn eine dieser Bedingungen zutrifft — die Prüfung gilt
   ausschließlich für die öffentliche Anzeige einzelner Seiten:
   - `is_admin()`
   - `defined('DOING_AJAX') && DOING_AJAX`
   - `defined('DOING_CRON') && DOING_CRON`
   - `defined('REST_REQUEST') && REST_REQUEST`
   - `!is_singular('page')`
3. Seiten-ID über `get_queried_object_id()` holen. Ist sie 0, zurückkehren.
4. `simple_clean_seite_sichtbar($seiten_id)` aufrufen (aus AP-1.1). Liefert sie
   `true`, nichts tun.
5. Sonst die Hinweisseite ausgeben, in dieser Reihenfolge:
   - `status_header(403);`
   - `nocache_headers();`
   - `add_filter('wp_robots', 'wp_robots_no_robots');`
   - `get_header();`
   - Das Hinweis-Markup (siehe Schritt 6)
   - `get_footer();`
   - `exit;`
6. Markup der Hinweisseite, umschlossen von
   `<main class="site-main"><div class="container"><div class="sc-lehrerhinweis">`:
   - Überschrift `<h1>Nur für Lehrpersonen</h1>`
   - Ein Absatz: Diese Seite ist nur für angemeldete Lehrpersonen sichtbar.
   - Ein Absatz mit dem Anmelde-Link:
     `wp_login_url(get_permalink($seiten_id))`, Beschriftung „Anmelden".
   - Falls die Seite eine Elternseite hat, die selbst sichtbar ist
     (`simple_clean_seite_sichtbar()` prüfen!), ein Link dorthin mit deren
     Titel. Ist die Elternseite ebenfalls gesperrt, stattdessen ein Link zur
     Startseite (`home_url('/')`).
   - **Den Titel der gesperrten Seite nirgends ausgeben** — er verriete, wie die
     Lösung heißt. Auch nicht im `<title>` des Dokuments: dafür einen Filter auf
     `pre_get_document_title` setzen, der „Nur für Lehrpersonen" zurückgibt,
     **bevor** `get_header()` läuft.
   - Alle Ausgaben durch `esc_html()` bzw. `esc_url()`.
7. In `Theme/style.css` am Dateiende einen Abschnitt `.sc-lehrerhinweis`
   ergänzen: zentrierter Kasten, großzügiges Padding, Hintergrund
   `var(--color-ui-surface-light)`, Rahmen `var(--color-sidebar-border)`,
   Überschrift in `var(--color-special-text)`, der Anmelde-Link als Knopf in
   `var(--color-ui-surface)` mit weißer Schrift und
   `var(--color-ui-surface-dark)` beim Überfahren. **Keine freistehenden
   Hexwerte** — ausschließlich die vorhandenen CSS-Variablen, damit die
   Customizer-Farben durchschlagen. Den „plastischen Look" **nicht** verwenden;
   der ist dem Navigations-Streifen und dem PDF-Knopf vorbehalten.

**Akzeptanzkriterien:**
- [ ] Nicht angemeldet + gesperrte Seite direkt aufgerufen → Hinweisseite, HTTP-Status **403**, Kopfleiste und Fußbereich des Themes vorhanden.
- [ ] Der Titel der gesperrten Seite steht **weder** im sichtbaren HTML **noch** im `<title>`-Element.
- [ ] Die Antwort enthält `noindex` (Meta-Robots oder Header) und Cache-Verbotsheader (`Cache-Control: no-cache`).
- [ ] Angemeldet + dieselbe Seite → Seite wird normal angezeigt, kein 403.
- [ ] Nicht angemeldet + **Unterseite** einer gesperrten Seite → ebenfalls Hinweisseite (Vererbung).
- [ ] Nicht angemeldet + nicht gesperrte Seite → unverändert.
- [ ] Der Anmelde-Link führt nach erfolgreicher Anmeldung zurück auf die aufgerufene Seite.
- [ ] Bei aktivem Website-Passwortschutz sieht ein Besucher ohne Passwort weiterhin die Passwortabfrage, **nicht** die Hinweisseite.
- [ ] `php -l Theme/includes/sichtbarkeit.php` fehlerfrei, keine PHP-8.0-Syntax.

**Tests:**
- Smoke-Test: In der Testinstallation eine Seite anlegen, Häkchen setzen,
  abmelden bzw. privates Browserfenster, Seite aufrufen → Hinweisseite.
- Prüfschritt Status: In den Entwicklerwerkzeugen (Netzwerk-Tab) prüfen, dass
  das Dokument mit **403** und `Cache-Control: no-cache` ausgeliefert wird.
- Prüfschritt Titel: `Strg+U` (Seitenquelltext) → der Titel der gesperrten Seite
  kommt im gesamten Dokument nicht vor.
- Prüfschritt Vererbung: Unterseite unter die gesperrte Seite hängen (Häkchen
  dort **nicht** setzen), abgemeldet aufrufen → Hinweisseite.
- Prüfschritt Passwortschutz: Unter „Passwortschutz" im Admin ein
  Website-Passwort setzen, abgemeldet die gesperrte Seite aufrufen → es
  erscheint die **Passwortabfrage**. Anschließend Passwortschutz wieder
  abschalten.
- Regressionsprüfung: Startseite, eine normale Unterseite und eine Seite mit dem
  Block „Inhaltsverzeichnis" abgemeldet aufrufen → alle laden normal,
  `debug.log` ohne neue Einträge.

**Übergabenotiz:**

Erledigt am 2026-08-11. `simple_clean_lehrerseite_pruefen()` auf
`template_redirect` Priorität 20, Ausgabe in
`simple_clean_lehrerhinweis_ausgeben()`, Titelersatz über
`simple_clean_lehrerhinweis_titel()` am Filter `pre_get_document_title`.
CSS unter `.sc-lehrerhinweis` am Ende von `style.css`, ausschließlich mit
Theme-Variablen.

**Der Prüfaufbau ist angelegt** und bleibt für die folgenden APs stehen
(Seiten-IDs in der Testinstallation):

| Seite | ID | Eltern | gesperrt | Kunstwort im Text |
|---|---|---|---|---|
| Kapitel Test | 29 | – | nein | Ankerkraut |
| Normale Unterseite | 30 | 29 | nein | Rankenpilz |
| Loesungen Test | 31 | 29 | **ja** | Zwirbelquark, Fabelbirne, Nebelkeks |
| Loesungen Detail | 32 | 31 | nein (erbt) | Distelbohne |

Das Seed-Skript ist idempotent; ein erneuter Aufruf legt keine Dubletten an.
`simple_clean_gesperrte_seiten()` liefert gegen diese Daten `31`,
`simple_clean_gesperrte_seiten_mit_unterbaum()` liefert `31,32` — die
Vererbung stimmt also auch gegen die echte Datenbank.

**Gemessene Ergebnisse:**
- Gesperrte Seite abgemeldet: `HTTP/1.1 403 Forbidden`,
  `Cache-Control: no-cache, must-revalidate, max-age=0, no-store, private`,
  `noindex` im Dokument.
- `<title>` lautet „Nur für Lehrpersonen – FOS Online Schulbuch (Test)".
  Der Seitentitel „Loesungen Test" kommt **0-mal** im ganzen Dokument vor,
  die drei Lösungswörter ebenfalls 0-mal.
- Unterseite „Loesungen Detail" abgemeldet: ebenfalls 403, „Distelbohne"
  0-mal → die Vererbung greift auch im HTTP-Weg.
- Angemeldet: beide Seiten HTTP 200 mit vollständigem Inhalt, keine
  Hinweisseite.
- Anmelde-Link:
  `wp-login.php?redirect_to=…/kapitel-test/loesungen-test/` — führt also
  zurück auf die aufgerufene Seite.
- Rücksprung-Link zeigt auf die sichtbare Elternseite „Kapitel Test".
- Normale Seiten und Startseite unverändert HTTP 200 mit ihren Kunstwörtern.
- `debug.log` ohne eine einzige Theme-Zeile.

**Passwortschutz-Prüfung bestanden — der wichtigste Einzelfall.** Bei aktivem
Website-Passwort liefert die gesperrte Seite abgemeldet die **Passwortabfrage**
(HTTP 200), nicht die Hinweisseite, und kein Lösungswort steht im HTML. Die
Priorität 20 nach dem Passwortschutz (10) wirkt wie beabsichtigt. Der
Passwortschutz wurde danach wieder abgeschaltet.

**Umgesetzt, aber im Plan nicht ausdrücklich verlangt:** Die Prüfung steigt
zusätzlich bei `is_feed()` früh aus. `template_redirect` läuft auch für Feeds;
ohne diesen Ausstieg hinge das Verhalten allein daran, dass
`is_singular('page')` dort falsch ist — eine Annahme, die ich nicht tragen
wollte.

**Für AP-1.4 und AP-1.5 wichtig:** Die Hinweisseite ist die letzte
Verteidigungslinie und steht. Die beiden folgenden APs schließen die Links,
sind also Verschleierung und Komfort, nicht der Schutz selbst. Wenn dort etwas
durchrutscht, führt es nicht zum Inhalt.

**Getestet wurde per `curl` gegen die laufende Installation**, für den
angemeldeten Fall mit einem kurzlebigen Hilfsskript im Webroot, das per
`wp_set_auth_cookie()` ein Anmelde-Cookie setzt (Einmal-Token, Prüfung auf
127.0.0.1, unmittelbar danach gelöscht). Der Webroot ist nachweislich wieder
frei von Hilfsskripten.

---

### AP-1.4: Ausblenden in Seitenleiste und Inhaltsverzeichnis

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet (Vorgehen an beiden Stellen exakt beschrieben, bestehendes Muster)
**Abhängigkeiten:** AP-1.1

**Ziel & Kontext:**

Gesperrte Seiten sollen für nicht angemeldete Besucher gar nicht erst als Link
erscheinen. Dieses AP behandelt die zwei Navigationsbäume des Themes. Beide
laufen von einer Wurzel abwärts — **wird ein Knoten entfernt, fällt sein
gesamter Unterbaum automatisch mit heraus.** Genau so wirkt bereits das Meta
`_simple_clean_hide_from_index`.

Dieses AP ändert **keine** Datei, die AP-1.2, AP-1.3 oder AP-1.5 anfassen; es
darf parallel dazu laufen.

**Betroffene Dateien:**
- `Theme/sidebar.php` (ändern)
- `Theme/includes/page-index.php` (ändern)

**Vorgehen:**

1. **Seitenleiste** (`Theme/sidebar.php`): Der Baum entsteht ab Zeile 130 aus
   einer einzigen `get_pages()`-Abfrage, deren Ergebnis in `$children_map`
   (Eltern-ID → Kinder) umgebaut wird. Direkt **vor** dem Aufbau von
   `$children_map` (Zeile 137) einfügen:
   ```php
   // Gesperrte Seiten für nicht angemeldete Besucher aus dem Baum nehmen.
   // Ihre Kinder bleiben zwar in $all_pages, werden aber nie erreicht,
   // weil der Baum von der Wurzel abwärts läuft — der Unterbaum entfällt
   // dadurch automatisch.
   if (function_exists('simple_clean_ist_lehrperson') && !simple_clean_ist_lehrperson()) {
       $gesperrt = simple_clean_gesperrte_seiten();
       $all_pages = array_filter($all_pages, function ($seite) use ($gesperrt) {
           return !isset($gesperrt[$seite->ID]);
       });
   }
   ```
   Die `function_exists`-Prüfung ist Absicht: `sidebar.php` wird als Template
   geladen; fehlt die Datei aus AP-1.1, soll die Seitenleiste weiterhin
   funktionieren statt mit einem Fatal Error auszusteigen.
   **Es darf keine zusätzliche Abfrage pro Baumknoten entstehen** — nur der eine
   Aufruf von `simple_clean_gesperrte_seiten()`.
   Zusätzlich absichern: Ist die **Wurzelseite selbst** gesperrt und der
   Besucher nicht angemeldet, gibt `sidebar.php` gar keinen Baum aus (dieser
   Fall tritt nur auf, wenn eine sichtbare Seite unter einer gesperrten Wurzel
   hängt — er sollte durch AP-1.3 nie erreichbar sein, die Absicherung kostet
   aber nichts).

2. **Inhaltsverzeichnis** (`Theme/includes/page-index.php`): In
   `simple_clean_page_index_daten()` wird ab Zeile 156 eine Ausschlussliste aus
   dem Meta `_simple_clean_hide_from_index` gebaut und mit `array_flip()` in die
   Form `ID => Position` gebracht; geprüft wird sie später ausschließlich per
   `isset()`. Direkt nach dieser Stelle ergänzen:
   ```php
   // Gesperrte Seiten kommen für nicht angemeldete Besucher hinzu.
   // Vereinigung über die Schlüssel; die Werte spielen keine Rolle,
   // weil unten nur isset() geprüft wird.
   if (function_exists('simple_clean_ist_lehrperson') && !simple_clean_ist_lehrperson()) {
       $ausgeschlossen = $ausgeschlossen + simple_clean_gesperrte_seiten();
   }
   ```
   Die Breitensuche darunter erledigt den Unterbaum von selbst.
   **Wichtig:** Das Ergebnis von `simple_clean_page_index_daten()` liegt in einer
   statischen Variablen. Das bleibt korrekt, weil der Anmeldestatus innerhalb
   eines Aufrufs nicht wechselt. Es darf **kein** persistenter Zwischenspeicher
   (Transient, Option) eingeführt werden — der würde Titel gesperrter Seiten an
   Nichtberechtigte ausliefern. Diese Entscheidung als Kommentar festhalten.

**Akzeptanzkriterien:**
- [ ] Abgemeldet: Eine gesperrte Seite erscheint **nicht** in der Seitenleiste.
- [ ] Abgemeldet: Die **Unterseiten** einer gesperrten Seite erscheinen ebenfalls nicht in der Seitenleiste.
- [ ] Abgemeldet: Eine gesperrte Seite und ihr Unterbaum fehlen im Block „Inhaltsverzeichnis".
- [ ] Angemeldet: Beide Bäume zeigen die gesperrte Seite wie bisher.
- [ ] Die Zahl der Datenbankabfragen steigt gegenüber vorher um höchstens **eine** je Seitenaufruf (Messung mit `?sc_perf=1` als Administrator, siehe Tests).
- [ ] Ohne gesperrte Seiten im System ist die Ausgabe beider Bäume zeichengleich zu vorher.
- [ ] `php -l` fehlerfrei für beide Dateien, keine PHP-8.0-Syntax.
- [ ] `Theme/reference_file_map.md`: Zeilen zu `sidebar.php` und `includes/page-index.php` um den neuen Meta-Bezug ergänzt.

**Tests:**
- Smoke-Test: Testinstallation, abgemeldet eine Seite mit Seitenleiste aufrufen
  → Baum erscheint, `debug.log` ohne neue Einträge.
- Prüfschritt Seitenleiste: Kapitelseite mit mehreren Unterseiten anlegen, eine
  davon sperren, diese Unterseite mit einer eigenen Unterseite versehen.
  Abgemeldet: beide fehlen. Angemeldet: beide da.
- Prüfschritt Inhaltsverzeichnis: Seite mit dem Block „Inhaltsverzeichnis"
  aufrufen, abgemeldet und angemeldet vergleichen.
- Prüfschritt Abfragezahl: Als Administrator eine Seite mit
  `?sc_perf=1` aufrufen und im Seitenquelltext die Zeile
  `<!-- SC-PERF queries=… -->` ablesen. Denselben Aufruf vor und nach der
  Änderung vergleichen (den Wert vorher notieren, bevor die Änderung kopiert
  wird). Differenz höchstens 1.
- Regressionsprüfung: Das Häkchen „Nicht im Inhaltsverzeichnis anzeigen" an
  einer anderen Seite setzen → wirkt weiterhin, auch für Angemeldete. Das
  Häkchen „Seitenleiste ausblenden" wirkt weiterhin.

**Übergabenotiz:**

---

### AP-1.5: Ausblenden in Menü, Suche, REST und Sitemap

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet (vier gleichartige Filter, Vorgehen vorgezeichnet)
**Abhängigkeiten:** AP-1.1, AP-1.3 (ändert dieselbe Datei — nicht parallel)

**Ziel & Kontext:**

Neben den zwei Navigationsbäumen aus AP-1.4 gibt es vier weitere Wege, auf denen
eine gesperrte Seite sichtbar würde: das Hauptmenü, die Suche, die REST-API und
die XML-Sitemap. Alle vier werden hier geschlossen.

Anders als bei den Bäumen greift hier **keine** automatische Vererbung — eine
Unterseite einer gesperrten Seite ist in einer flachen Liste selbstständig
sichtbar. Deshalb wird durchgehend
`simple_clean_gesperrte_seiten_mit_unterbaum()` aus AP-1.1 verwendet, das die
gesperrten Seiten **einschließlich aller Nachfahren** liefert.

**Betroffene Dateien:**
- `Theme/includes/sichtbarkeit.php` (ändern)

**Vorgehen:**

1. Hilfsfunktion `simple_clean_gesperrte_ids_liste()` anlegen: gibt für nicht
   Angemeldete `array_keys(simple_clean_gesperrte_seiten_mit_unterbaum())`
   zurück, für Angemeldete ein leeres Array. Alle vier Filter unten nutzen sie,
   damit die Regel nur einmal im Code steht.
2. **Hauptmenü** — Filter `wp_get_nav_menu_items` (Priorität 10, drei
   Argumente). Einträge entfernen, bei denen
   `$item->object === 'page'` und die Objekt-ID in der Liste steht. Danach in
   einem zweiten Durchlauf alle Einträge entfernen, deren `menu_item_parent`
   auf einen bereits entfernten Eintrag zeigt — und das so lange wiederholen,
   bis sich nichts mehr ändert (verschachtelte Menüs). Das Ergebnis mit
   `array_values()` neu indizieren.
3. **Menü-Rückfall** — `Theme/functions.php` enthält
   `simple_clean_fallback_menu()`, das `wp_list_pages()` aufruft, wenn kein Menü
   zugewiesen ist. Statt diese Funktion anzufassen, den Filter
   `wp_list_pages_excludes` bedienen und die Liste anhängen. Das wirkt auf jeden
   `wp_list_pages()`-Aufruf im Theme.
4. **Suche und Archive** — `pre_get_posts`. Nur eingreifen, wenn
   `!is_admin()` **und** `$query->is_main_query()`. Dann
   `$query->set('post__not_in', array_merge((array) $query->get('post__not_in'), $ids))`.
   Bestehende Werte also **nicht überschreiben**.
5. **REST-API** — Filter `rest_page_query` (zwei Argumente: `$args`,
   `$request`). `$args['post__not_in']` um die Liste ergänzen, bestehende Werte
   erhalten.
6. **Sitemap** — Filter `wp_sitemaps_posts_query_args` (zwei Argumente:
   `$args`, `$post_type`). Nur bei `$post_type === 'page'` eingreifen,
   `$args['post__not_in']` ergänzen.
7. Alle Filter registrieren sich **immer**, prüfen aber intern über
   `simple_clean_gesperrte_ids_liste()`: ist die Liste leer, sofort
   unverändert zurückgeben. So kostet das Ganze auf einer Website ohne gesperrte
   Seiten genau eine Abfrage und keine Verarbeitung.

**Akzeptanzkriterien:**
- [ ] Abgemeldet: Eine gesperrte Seite, die im Hauptmenü verlinkt ist, erscheint dort nicht.
- [ ] Abgemeldet: Ein Menüeintrag, dessen **Elterneintrag** eine gesperrte Seite ist, erscheint ebenfalls nicht.
- [ ] Abgemeldet: Die Suche nach einem Wort, das nur auf der gesperrten Seite vorkommt, liefert keinen Treffer.
- [ ] Abgemeldet: `…/wp-json/wp/v2/pages` enthält die gesperrte Seite **und ihre Unterseiten** nicht.
- [ ] Abgemeldet: `…/wp-sitemap-posts-page-1.xml` enthält die gesperrte Seite nicht.
- [ ] Angemeldet: alle vier Stellen zeigen die Seite wie bisher.
- [ ] Ein bereits vorhandenes `post__not_in` in einer Abfrage wird ergänzt, nicht ersetzt.
- [ ] `php -l Theme/includes/sichtbarkeit.php` fehlerfrei, keine PHP-8.0-Syntax.

**Tests:**
- Smoke-Test: Abgemeldet die Startseite aufrufen → Menü erscheint,
  `debug.log` ohne neue Einträge.
- Prüfschritt Menü: Unter Design → Menüs die gesperrte Seite ins Hauptmenü
  aufnehmen, darunter als Untereintrag eine sichtbare Seite hängen. Abgemeldet:
  beide Einträge fehlen. Angemeldet: beide da.
- Prüfschritt Suche: Auf der gesperrten Seite ein eindeutiges Kunstwort
  einfügen (z. B. „Zwirbelquark"), Seite speichern. Abgemeldet
  `…/?s=Zwirbelquark` → kein Treffer. Angemeldet → Treffer.
- Prüfschritt REST: Abgemeldet `http://fos.localhost:8080/wp-json/wp/v2/pages?per_page=100`
  aufrufen und nach der Seiten-ID suchen → nicht enthalten.
- Prüfschritt Sitemap: Abgemeldet `http://fos.localhost:8080/wp-sitemap-posts-page-1.xml`
  aufrufen → die URL der gesperrten Seite fehlt.
- Regressionsprüfung: Suche nach einem Wort auf einer normalen Seite liefert
  weiterhin Treffer; das Hauptmenü zeigt alle nicht gesperrten Einträge in
  unveränderter Reihenfolge.

**Übergabenotiz:**

---

### AP-1.6: Seitenmanager – Sammelaktionen und Kennzeichnung

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet (acht bestehende Sammelaktionen als wörtliche Vorlage)
**Abhängigkeiten:** AP-1.1, AP-1.2

**Ziel & Kontext:**

Der Seitenmanager des Themes (`Theme/includes/admin/page-manager.php`,
Menüpunkt „Seitenmanager") zeigt den Seitenbaum mit Drag-Sortierung und besitzt
seit Version 1.5.76 **Sammelaktionen**: Auswahlkästchen je Zeile, eine Leiste
`.page-bulk-bar` und acht Aktionen, die in `bulk_aktionen()` (Zeile 45) als
**Whitelist** stehen. Der Wert aus `$_POST` wird ausschließlich gegen diese
Liste geprüft und nie in einen Methodennamen übersetzt — dieses Muster ist
beizubehalten.

Ergänzt werden zwei Aktionen und eine sichtbare Kennzeichnung im Baum, damit
man gesperrte Seiten auf einen Blick erkennt.

**Betroffene Dateien:**
- `Theme/includes/admin/page-manager.php` (ändern)
- `Theme/src/css/page-manager.css` (ändern)
- `Theme/reference_file_map.md` (ändern)

**Vorgehen:**
1. In `bulk_aktionen()` (Zeile 45–56) zwei Einträge ergänzen:
   ```php
   'lock_teacher'   => 'Nur für Lehrpersonen sichtbar',
   'unlock_teacher' => 'Wieder öffentlich sichtbar',
   ```
2. In `ajax_bulk_action()` (ab Zeile 667) die zwei Fälle im bestehenden
   `switch`/`if`-Zweig für Meta-Aktionen ergänzen, **wörtlich nach dem Muster
   von `hide_index` / `show_index`** (Zeile 789–794):
   - `lock_teacher` → `update_post_meta($id, '_simple_clean_nur_lehrpersonen', '1');`
   - `unlock_teacher` → `delete_post_meta($id, '_simple_clean_nur_lehrpersonen');`
   Die vorhandene Rechteprüfung je Einzelseite (`edit_page`) und das Sammeln von
   Fehlern statt Abbruch beim ersten Problem gelten damit automatisch mit.
3. Im Antwort-Array bleibt `reload` für diese beiden Aktionen **`false`** —
   genau wie bei den vier bestehenden Meta-Aktionen. Der Baum ändert sich nicht,
   eine Statusmeldung genügt.
4. In der Ausgabe einer Baumzeile (dort, wo heute die Kennzeichnungen für
   `_simple_clean_hide_navigation` und `_simple_clean_hide_from_index` gesetzt
   werden — falls es dort noch keine gibt, an derselben Stelle wie der
   Seitentitel) ein Schloss-Symbol `🔒` mit
   `title="Nur für Lehrpersonen sichtbar"` und der CSS-Klasse
   `page-lehrer-marker` ausgeben, wenn das Meta gesetzt ist.
   **Das Meta darf nicht je Zeile einzeln abgefragt werden.** Der Baum entsteht
   aus einer `get_pages()`-Abfrage (Zeile 130); die Liste der gesperrten IDs
   einmal über `simple_clean_gesperrte_seiten()` holen und beim Rendern per
   `isset()` prüfen.
5. In `Theme/src/css/page-manager.css` eine Regel für `.page-lehrer-marker`
   ergänzen (kleiner Abstand links, `cursor: help`, gedämpfte Farbe über eine
   vorhandene CSS-Variable). Danach `npm run build` im Ordner `Theme/`
   ausführen, damit `dist/css/page-manager.css` neu entsteht.
   **Achtung:** `npm run build` erhöht über `backup-and-build.js` selbstständig
   die Patch-Version in `package.json` und `style.css` und baut ein neues ZIP.
   Das ist erwünscht und muss im Commit enthalten sein.

**Akzeptanzkriterien:**
- [ ] Die Auswahlleiste im Seitenmanager bietet zehn Aktionen; die acht bestehenden funktionieren unverändert.
- [ ] Drei Seiten auswählen → „Nur für Lehrpersonen sichtbar" → alle drei haben `_simple_clean_nur_lehrpersonen = '1'` in `wp_postmeta`.
- [ ] „Wieder öffentlich sichtbar" → die Meta-Zeilen sind **gelöscht**.
- [ ] Nach beiden Aktionen bleibt die Seite stehen (kein Neuladen), es erscheint eine Statusmeldung.
- [ ] Gesperrte Seiten tragen im Baum ein 🔒 mit Titel-Attribut.
- [ ] Die Zahl der Abfragen der Seitenmanager-Ansicht steigt um höchstens **eine**, unabhängig von der Zahl der Seiten.
- [ ] Der Wert aus `$_POST['bulk_action']` wird weiterhin nur gegen die Whitelist geprüft und nie als Methodenname verwendet.
- [ ] `php -l Theme/includes/admin/page-manager.php` fehlerfrei, keine PHP-8.0-Syntax.
- [ ] `Theme/reference_file_map.md`: Zeile zu `includes/admin/page-manager.php` um die zwei neuen Aktionen ergänzt.

**Tests:**
- Smoke-Test: http://fos.localhost:8080/wp-admin/admin.php?page=page-manager
  aufrufen → Baum erscheint, Browser-Konsole ohne Fehler.
- Prüfschritt Sammelaktion: Drei Seiten anhaken, „Nur für Lehrpersonen
  sichtbar" ausführen → Statusmeldung „3 Seiten geändert", 🔒 erscheint nach
  einem Neuladen bei allen dreien. In phpMyAdmin die drei Meta-Zeilen prüfen.
- Prüfschritt Rücknahme: Dieselben drei auswählen, „Wieder öffentlich sichtbar"
  → Meta-Zeilen verschwunden.
- Prüfschritt Rechte: Als Benutzer der Rolle „Block-Redakteur" anmelden und die
  Aktion an einer Seite ausführen, die dieser Benutzer bearbeiten darf → sie
  gelingt. (Die Prüfung `edit_page` je Einzelseite ist bestehender Code und darf
  nicht umgangen werden.)
- Regressionsprüfung: Die vier bestehenden Meta-Aktionen (`hide_index`,
  `show_index`, `hide_nav`, `show_nav`) einmal ausführen → wirken wie bisher,
  ohne Neuladen. Die Aktion „Veröffentlichen" ausführen → Seite wird
  veröffentlicht **und** der Baum lädt neu. Eine Seite per Drag verschieben →
  Sortierung wird gespeichert.

**Übergabenotiz:**

---

### AP-1.rev: Unabhängiges Review Phase 1

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus
**Abhängigkeiten:** AP-1.1, AP-1.2, AP-1.3, AP-1.4, AP-1.5, AP-1.6 (inkl. Phasen-Integrationstest)

**Ziel & Kontext:**

Unabhängige Qualitätsprüfung der Phase 1 durch einen Agenten, der an keiner
Implementierung beteiligt war. Nur lesend arbeiten (Read/Grep/Glob) — **KEINE
Datei verändern.**

Der Gegenstand ist eine Zugriffssperre. Ein übersehenes Loch fällt im Alltag
nicht auf, weil alles zu funktionieren scheint — deshalb ist dieses Review
wichtiger als bei einem gewöhnlichen Feature.

**Vorgehen:**
1. Für jedes AP der Phase (AP-1.1 bis AP-1.6): Code gegen dessen
   Akzeptanzkriterien prüfen. Im Quelltext nachsehen, nicht den
   Übergabenotizen glauben.
2. **Vollständigkeitsprüfung der Sperre.** Mit `grep` alle Stellen suchen, an
   denen das Theme Seiten auflistet oder verlinkt:
   `get_pages`, `wp_list_pages`, `wp_nav_menu`, `WP_Query`, `get_posts`,
   `$wpdb->get_results` mit `post_type = 'page'`, `get_permalink`,
   `wp_sitemaps`, `rest_`. Für jede Fundstelle beurteilen: Ist sie abgedeckt,
   und wenn nicht — warum ist das unbedenklich? Ergebnis als Liste in die
   Übergabenotiz.
3. Prüfen, dass `simple_clean_ist_lehrperson()` tatsächlich die **einzige**
   Stelle ist, die den Anmeldestatus für die Sperre auswertet: `grep` nach
   `is_user_logged_in` und `current_user_can` in
   `Theme/includes/sichtbarkeit.php`, `Theme/sidebar.php`,
   `Theme/includes/page-index.php`.
4. Prüfen, dass der Filter `simple_clean_lehrerseite_freigeben` den
   Standardwert `false` hat und nirgends im Theme selbst auf `true` gesetzt
   wird.
5. Reihenfolge der `template_redirect`-Haken prüfen: AI-Blocker (1),
   Passwortschutz (10), Lehrersperre (20).
6. Phasen-Endzustand prüfen: Ist eine gesperrte Seite für nicht Angemeldete
   überall verschwunden und liefert der direkte Aufruf 403?
7. Scope-Check gegen Abschnitt 2 (Nicht-Ziele): Wurde eine neue Rolle, ein
   neues Block-Attribut oder eine Änderung am Klassensystem eingebaut? Das
   wäre eine Verletzung.
8. Qualitäts-Check: ungeprüfte Ausgaben (fehlendes `esc_html`/`esc_url` auf der
   Hinweisseite), PHP-8.0-Syntax, freistehende Hexfarben im neuen CSS,
   zusätzliche Datenbankabfragen pro Baumknoten, Debug-Ausgaben ohne
   `WP_DEBUG`-Gate.
9. Befunde als Bericht in die Übergabenotiz: je Befund Schweregrad
   (kritisch / mittel / gering), betroffenes AP, Datei und Fundstelle.

**Akzeptanzkriterien:**
- [ ] Jedes AP der Phase wurde gegen seine Akzeptanzkriterien geprüft.
- [ ] Die Liste aus Schritt 2 ist vollständig und jede Fundstelle beurteilt.
- [ ] Alle Befunde mit Schweregrad, Datei und Fundstelle dokumentiert.
- [ ] Keine Datei wurde verändert.

**Tests:**
- entfällt (Review-AP; das Ergebnis ist der Bericht).

**Übergabenotiz:**

---

### AP-1.doc: Dokumentation Phase 1

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-1.rev

**Ziel & Kontext:**

`Theme/CLAUDE.md` und `Theme/reference_file_map.md` auf den Stand nach Phase 1
bringen, damit die Sperre ohne Kenntnis dieses Plans verstanden und erweitert
werden kann.

**Betroffene Dateien:**
- `Theme/CLAUDE.md` (ändern)
- `Theme/reference_file_map.md` (ändern)

**Vorgehen:**
1. Alle Übergabenotizen von AP-1.1 bis AP-1.rev durchgehen.
2. In `Theme/CLAUDE.md` einen neuen Abschnitt **„Seiten nur für Lehrpersonen"**
   ergänzen, nach dem Abschnitt zum Inhaltsverzeichnis-Block. Er hält fest:
   - Meta `_simple_clean_nur_lehrpersonen`, gesetzt über das dritte Häkchen der
     Meta-Box und über zwei Sammelaktionen im Seitenmanager.
   - Die fünf Funktionen aus `includes/sichtbarkeit.php` mit einer Zeile Zweck
     je Funktion.
   - **Der Warnhinweis zur Rollen-Definition:** „Lehrperson" heißt derzeit
     schlicht „angemeldet". Das trägt nur, solange es ausschließlich
     Lehrer-Konten gibt. Sobald ein Konto ohne Lehrauftrag existiert (Abonnent,
     Testkonto, Schülerzugang), öffnet sich die Sperre still. Verschärft wird
     an **einer** Stelle: `simple_clean_ist_lehrperson()` bzw. der
     gleichnamige Filter.
   - Die Vererbung auf den Unterbaum und **warum** sie in Seitenleiste und
     Inhaltsverzeichnis gratis kommt (Bäume laufen von der Wurzel abwärts), in
     den flachen Listen dagegen `simple_clean_gesperrte_seiten_mit_unterbaum()`
     nötig ist.
   - Die Reihenfolge der `template_redirect`-Haken (1 / 10 / 20) und warum sie
     zählt.
   - Der Hinweis, dass **kein persistenter Zwischenspeicher** für die
     Seitenbäume eingeführt werden darf — er würde Titel gesperrter Seiten an
     Nichtberechtigte ausliefern.
   - Der Filter `simple_clean_lehrerseite_freigeben` als **die** Erweiterungs-
     schnittstelle, mit dem ausdrücklichen Hinweis, dass sein Standardwert
     `false` ist und bleiben muss.
3. `Theme/reference_file_map.md`: Zeilen für `includes/sichtbarkeit.php`,
   `tools/test-sichtbarkeit.php` sowie die geänderten Zeilen zu `functions.php`,
   `sidebar.php`, `includes/page-index.php`, `includes/admin/page-manager.php`
   und `src/css/page-manager.css` aktualisieren. „Stand"-Datum und
   Theme-Version im Kopf nachziehen.
4. „Letzte Aktualisierung" im Kopf dieses Plans aktualisieren.

**Akzeptanzkriterien:**
- [ ] Jede in Phase 1 neue oder geänderte Datei hat eine aktuelle Zeile in `Theme/reference_file_map.md`.
- [ ] Der Abschnitt in `Theme/CLAUDE.md` enthält den Warnhinweis zur Rollen-Definition **und** die Aussage, dass der Filter-Standardwert `false` bleiben muss.
- [ ] Kein Verweis in der Dokumentation zeigt auf nicht existierende Dateien oder Funktionen.

**Tests:**
- Stichprobe: Zwei zufällige Zeilen der Datei-Map gegen den echten Dateiinhalt
  prüfen (genannte Funktionen existieren wirklich).
- Stichprobe: Jeden im neuen CLAUDE.md-Abschnitt genannten Funktionsnamen per
  `grep` in `Theme/includes/sichtbarkeit.php` wiederfinden.

**Übergabenotiz:**

---

### Phase 2: Plugin – der Durchlass

---

### AP-2.1: Geteilte Helfer für behandelte Container herauslösen

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet (mechanische Extraktion, Zielsignaturen vorgegeben)
**Abhängigkeiten:** keine (kann parallel zu Phase 1 begonnen werden, gehört aber zur Auslieferung von Phase 2)

**Ziel & Kontext:**

Im Plugin `Plugins/CDB-Designer/` liegt die Klassenansicht in
`includes/class-cbd-classroom.php` (1462 Zeilen). Die Methode
`ajax_get_page_classroom_data()` (ab Zeile 1288) enthält zwei Regeln, die in
Phase 2 ein zweites Mal gebraucht werden:

1. **Zerlegung der `container_id`.** Mehrseitige Tafelbilder werden als
   `"<stableId>:p<N>"` gespeichert; der Basis-Bezeichner ist alles vor dem
   Doppelpunkt (Zeile 1351: `preg_match('/^(.+):p(\d+)$/', …)`).
2. **Ermittlung der behandelten Container** einer Seite für eine Klasse aus der
   Tabelle `CBD_TABLE_DRAWINGS` (Spalten `class_id`, `page_id`, `container_id`,
   `is_behandelt`).

Zwei Fassungen derselben Regel würden auseinanderlaufen. Dieses AP löst beide
als statische Helfer heraus und stellt die bestehende Methode darauf um —
**ohne ihr Verhalten zu ändern.**

**Betroffene Dateien:**
- `Plugins/CDB-Designer/includes/class-cbd-classroom.php` (ändern)
- `Plugins/CDB-Designer/tools/test-classroom-gate.php` (neu – Prüfharnisch)
- `Plugins/CDB-Designer/reference_file_map.md` (ändern)

**Vorgehen:**
1. **Zuerst den Prüfharnisch anlegen** (`tools/test-classroom-gate.php`),
   Muster: `Plugins/CDB-Designer/tools/test-icon-value.php` — eigenständiges
   PHP-Skript ohne WordPress, mit Stubs für die benötigten Funktionen und einem
   `$wpdb`-Doppel, das vorbereitete Ergebnisse zurückgibt. Ausgabe je Prüfung
   `OK`/`FEHLER`, Bilanz, Exit-Code 1 bei Fehlern.
2. Testfälle für die Zerlegung (`CBD_Classroom::basis_container_id()`):

   | Eingabe | Erwartung |
   |---|---|
   | `cbd-123-abcd` | `cbd-123-abcd` |
   | `cbd-123-abcd:p0` | `cbd-123-abcd` |
   | `cbd-123-abcd:p12` | `cbd-123-abcd` |
   | `cbd-123-abcd:px` | `cbd-123-abcd:px` (kein gültiges Suffix) |
   | `cbd-123:p1:p2` | `cbd-123:p1` (nur das letzte Suffix zählt) |
   | `` (leer) | `` |

3. Testfälle für `CBD_Classroom::behandelte_container($class_id, $page_id)`:
   - Drei Zeilen mit `is_behandelt = 1`, davon zwei mit Suffix `:p0`/`:p1`
     desselben Basis-Bezeichners → Rückgabe enthält jeden Basis-Bezeichner
     **genau einmal**.
   - Zeilen mit `is_behandelt = 0` erscheinen nicht in der Rückgabe.
   - Keine Zeilen → leeres Array.
   - `class_id` oder `page_id` kleiner/gleich 0 → leeres Array **ohne**
     Datenbankabfrage.
4. Harnisch laufen lassen, roten Stand bestätigen und committen.
5. In `CBD_Classroom` zwei **statische, öffentliche** Methoden ergänzen:
   ```php
   public static function basis_container_id($container_id)   // : string
   public static function behandelte_container($class_id, $page_id)  // : array – Liste der Basis-Bezeichner
   ```
   `behandelte_container()` stellt **eine** Abfrage mit `$wpdb->prepare()`
   (Prepared Statement ist Pflicht) und gibt die eindeutigen Basis-Bezeichner
   als indiziertes Array zurück.
6. `ajax_get_page_classroom_data()` auf die neuen Helfer umstellen: Die
   `preg_match`-Zeile 1351 wird durch `self::basis_container_id()` ersetzt. Die
   Struktur der AJAX-Antwort (`class_name`, `treated_containers`, `drawings`
   mit `pages` je Seitenindex) bleibt **unverändert** — das Frontend
   `assets/js/classroom-page-filter.js` und `assets/js/board-mode.js` hängen
   daran.
7. Harnisch grün machen, ohne die Tests anzufassen.
8. Zeile für `tools/test-classroom-gate.php` in
   `Plugins/CDB-Designer/reference_file_map.md` ergänzen (Tabelle „Werkzeuge und
   Tests"), Zeile zu `class-cbd-classroom.php` um die zwei neuen Helfer
   erweitern.

**Akzeptanzkriterien:**
- [ ] `php Plugins/CDB-Designer/tools/test-classroom-gate.php` meldet alle Prüfungen bestanden, Exit-Code 0.
- [ ] Es existiert ein Commit mit den roten Tests **vor** dem Implementierungs-Commit.
- [ ] `ajax_get_page_classroom_data()` liefert für dieselben Daten exakt dieselbe Antwortstruktur wie vor der Änderung.
- [ ] Die Zerlegungsregel steht nur noch **einmal** im Code (`grep` nach `:p` bzw. `preg_match` in `class-cbd-classroom.php` findet genau eine Fundstelle).
- [ ] `behandelte_container()` nutzt `$wpdb->prepare()`.
- [ ] `php -l` fehlerfrei **und** `php Plugins/CDB-Designer/tools/check-php74.php` ohne Befund.

**Tests:**
- Smoke-Test: `php Plugins/CDB-Designer/tools/test-classroom-gate.php` läuft
  ohne Fatal Error durch.
- Regressionstest Klassenmodus (wichtig, dieses AP fasst laufenden Code an):
  Plugin-Dateien in die Testinstallation
  `…\fos\wp-content\plugins\container-block-designer\` kopieren. In den
  Plugin-Einstellungen das Klassensystem einschalten, eine Klasse mit Passwort
  anlegen, auf einer Seite mit Container-Blöcken im Tafelmodus einen Block als
  „behandelt" markieren. Dann eine Seite mit dem Shortcode `[cbd_classroom]`
  anlegen, abgemeldet dort mit dem Klassenpasswort anmelden und die Seite
  öffnen → **nur** der markierte Container ist sichtbar, wie vor der Änderung.
- Prüfschritt Log: `wp-content/debug.log` nach dem Durchlauf ohne neue
  Notices/Warnings.

**Übergabenotiz:**

---

### AP-2.2: Klassensitzung serverseitig prüfen und den Filter bedienen

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus (Zugriffsentscheidung auf Basis eines Tokens; sicherheitsrelevant)
**Abhängigkeiten:** AP-1.3 (der Filter `simple_clean_lehrerseite_freigeben` existiert), AP-2.1

**Ziel & Kontext:**

Das Theme sperrt Seiten und fragt per Filter nach, ob jemand sie freigeben
möchte (aus AP-1.1/AP-1.3):

```php
apply_filters('simple_clean_lehrerseite_freigeben', false, $post_id)
```

Das Plugin hängt sich hier ein und gibt `true` zurück, wenn **beides** gilt:
eine gültige Klassensitzung liegt vor **und** die Seite enthält Container, die
für diese Klasse als „behandelt" markiert sind.

So funktioniert die bestehende Sitzung: Der Schüler meldet sich über den
Shortcode `[cbd_classroom]` mit dem Klassenpasswort an
(`CBD_Classroom::ajax_student_auth()`, Zeile 690). Erfolgreich erzeugt sie ein
Token und legt es als Transient `cbd_classroom_<token>` mit
`array('class_id' => …, 'class_name' => …, 'created' => …)` für 24 Stunden ab.
Anschließend hängen alle Links die Parameter `?classroom=<class_id>&token=<token>`
an (`classroom-page-filter.js`, `interceptLinks()`).

**Betroffene Dateien:**
- `Plugins/CDB-Designer/includes/class-cbd-classroom-gate.php` (neu)
- `Plugins/CDB-Designer/container-block-designer.php` (ändern – eine `require_once`-Zeile)
- `Plugins/CDB-Designer/tools/test-classroom-gate.php` (ändern – Prüfharnisch erweitern)
- `Plugins/CDB-Designer/reference_file_map.md` (ändern)

**Vorgehen:**
1. Testfälle in `tools/test-classroom-gate.php` ergänzen (zuerst, rot
   bestätigen, committen):

   | Nr | Situation | Erwartung |
   |---|---|---|
   | 1 | kein `classroom`/`token` in der URL | Sitzung = null |
   | 2 | Token vorhanden, Transient fehlt (abgelaufen) | null |
   | 3 | Token gültig, aber `classroom`-Parameter passt nicht zur `class_id` im Transient | null |
   | 4 | Token gültig, `classroom` passt | Sitzung mit `class_id` |
   | 5 | gültige Sitzung, Seite ohne behandelte Container | Filter gibt `false` |
   | 6 | gültige Sitzung, Seite mit behandelten Containern | Filter gibt `true` |
   | 7 | Filter bekommt `true` herein (jemand anderes hat freigegeben) | Rückgabe bleibt `true` |
   | 8 | Klassensystem abgeschaltet (`CBD_Classroom::is_enabled()` false) | Filter gibt `false` |

2. Neue Datei `includes/class-cbd-classroom-gate.php` mit
   `if (!defined('ABSPATH')) exit;` und der Klasse `CBD_Classroom_Gate`,
   Singleton nach dem Muster von `CBD_Classroom` (private Konstruktor,
   `get_instance()`, Initialisierung am Dateiende).
3. Methoden:
   ```php
   // Gültige Klassensitzung aus URL-Parametern und Transient, oder null.
   // Ergebnis für die Dauer des Aufrufs statisch merken.
   public static function sitzung()            // : ?array  → PHP 7.4: kein Rückgabetyp, Doc-Block
   // Bedient den Theme-Filter.
   public function seite_freigeben($frei, $post_id)  // : bool
   ```
4. Regeln für `sitzung()`:
   - Zuerst `CBD_Classroom::is_enabled()` prüfen; ist das Klassensystem
     abgeschaltet, sofort `null`.
   - `$_GET['classroom']` über `intval()`, `$_GET['token']` über
     `sanitize_text_field(wp_unslash(...))`. Fehlt eines, `null`.
   - `get_transient('cbd_classroom_' . $token)` laden. Kein Ergebnis oder kein
     `class_id` → `null`.
   - **Die `class_id` aus dem Transient muss mit dem `classroom`-Parameter
     übereinstimmen**, sonst `null`. Der Transient ist die Wahrheit, nicht der
     URL-Parameter.
   - Rückgabe: `array('class_id' => (int) …, 'class_name' => …)`.
   - Das Ergebnis in einer statischen Variablen halten (mehrere Aufrufe je
     Seitenaufbau).
5. Regeln für `seite_freigeben($frei, $post_id)`:
   - Ist `$frei` bereits `true`, unverändert zurückgeben.
   - Sitzung holen; keine → `false`.
   - `CBD_Classroom::behandelte_container($class_id, $post_id)` (aus AP-2.1)
     aufrufen; leeres Ergebnis → `false`, sonst `true`.
   - Registrierung:
     `add_filter('simple_clean_lehrerseite_freigeben', array($this, 'seite_freigeben'), 10, 2);`
   - **Niemals `true` zurückgeben, ohne beide Bedingungen geprüft zu haben.**
     Ein Kommentarblock über der Methode hält fest, dass der Standardwert des
     Filters im Theme `false` ist und diese Methode die einzige Stelle ist, die
     ihn öffnet.
6. In `container-block-designer.php` in `load_dependencies()` die Zeile
   `require_once CBD_PLUGIN_DIR . 'includes/class-cbd-classroom-gate.php';`
   **nach** der Zeile für `class-cbd-classroom.php` ergänzen.
7. Harnisch grün machen.
8. Datei-Map ergänzen.

**Akzeptanzkriterien:**
- [ ] Alle Prüfungen in `tools/test-classroom-gate.php` bestanden, Exit-Code 0; Commit mit rotem Stand liegt davor.
- [ ] Ohne gültige Sitzung gibt `seite_freigeben()` immer `false` zurück.
- [ ] Ein `token`, dessen Transient auf eine andere `class_id` zeigt als der `classroom`-Parameter, führt zu `false`.
- [ ] Bei abgeschaltetem Klassensystem gibt der Filter `false` zurück.
- [ ] Der Filter verändert einen bereits übergebenen Wert `true` nicht.
- [ ] `php -l` fehlerfrei **und** `php Plugins/CDB-Designer/tools/check-php74.php` ohne Befund (insbesondere **kein** Nullsafe-Operator und **kein** `?array`-Rückgabetyp).
- [ ] `Plugins/CDB-Designer/reference_file_map.md` enthält eine Zeile für die neue Datei.

**Tests:**
- Smoke-Test: Plugin in der Testinstallation deaktivieren und wieder
  aktivieren → keine Fehlermeldung, `debug.log` ohne neue Einträge.
- Prüfschritt Durchlass: Voraussetzung ist ein aktives Theme mit Phase 1. Eine
  Seite mit zwei Container-Blöcken anlegen, einen davon im Tafelmodus für die
  Testklasse als „behandelt" markieren, die Seite als „Nur für Lehrpersonen"
  sperren. Abgemeldet über die Klassenanmeldung auf die Seite navigieren →
  die Seite **öffnet sich** (kein 403).
- Prüfschritt Sperre bleibt: Dieselbe Seite abgemeldet **ohne** die Parameter
  `?classroom=&token=` aufrufen → Hinweisseite mit 403.
- Prüfschritt Manipulation: Die URL mit gültigem `token`, aber falschem
  `classroom`-Wert aufrufen → Hinweisseite mit 403.
- Prüfschritt ohne Markierung: Eine gesperrte Seite **ohne** behandelte Blöcke
  mit gültiger Sitzung aufrufen → Hinweisseite mit 403.
- Regressionsprüfung: Klassenmodus auf einer **nicht** gesperrten Seite
  verhält sich unverändert.

**Übergabenotiz:**

---

### AP-2.3: Serverseitige Reduktion des Seiteninhalts

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus (Eingriff in den Renderpfad; Fehler wirken sich auf alle Seiten aus)
**Abhängigkeiten:** AP-2.2

**Ziel & Kontext:**

Bis hierher öffnet sich eine gesperrte Seite für eine Klasse mit behandelten
Blöcken — sie liefert aber noch **den vollständigen Inhalt** aus. Der bestehende
Filter `assets/js/classroom-page-filter.js` versteckt die übrigen Container nur
im Browser (`$container.hide()`); im Quelltext stehen sie weiterhin. Für eine
Lösungsseite ist das kein Schutz.

Dieses AP entfernt nicht freigegebene Blöcke **serverseitig**.

**Der Geltungsbereich ist eng und muss es bleiben:** Die Reduktion greift nur,
wenn **alle** vier Bedingungen erfüllt sind:
1. Es ist die Ausgabe des Hauptinhalts einer einzelnen Seite (`is_singular('page')`, Hauptabfrage, `in_the_loop()`).
2. Der Besucher ist **nicht** angemeldet (`simple_clean_ist_lehrperson()` ist false).
3. Die Seite ist gesperrt (`simple_clean_seite_nur_lehrpersonen()`).
4. Es liegt eine gültige Klassensitzung vor (`CBD_Classroom_Gate::sitzung()`).

Fehlt eine davon, wird der Inhalt **unverändert** durchgereicht. Eine
Reduktion auf einer nicht gesperrten Seite wäre ein schwerer Fehler — sie würde
Inhalte im laufenden Betrieb verschwinden lassen.

**Betroffene Dateien:**
- `Plugins/CDB-Designer/includes/class-cbd-classroom-gate.php` (ändern)
- `Plugins/CDB-Designer/tools/test-classroom-gate.php` (ändern)

**Vorgehen:**
1. Testfälle ergänzen (zuerst, rot bestätigen, committen). Geprüft wird die
   reine Auswahlfunktion aus Schritt 3, nicht der WordPress-Hook:

   | Nr | Eingabe (Blockliste + Freigabeliste) | Erwartung |
   |---|---|---|
   | 1 | drei Container, einer freigegeben | nur dieser bleibt |
   | 2 | Container mit `stableId` **nur im HTML** (nicht in `attrs`), freigegeben | bleibt (Rückfall greift) |
   | 3 | Absatz und Überschrift außerhalb jedes Containers | entfallen (Standard ist Ablehnung) |
   | 4 | Container ohne `stableId` | entfällt |
   | 5 | freigegebener Container mit verschachtelten Blöcken | bleibt vollständig, samt Inhalt |
   | 6 | leere Freigabeliste | Ergebnis ist leer |
   | 7 | „Blöcke" ohne `blockName` (rohes HTML zwischen Blöcken) | entfallen |

2. Methode `inhalt_reduzieren($content)` in `CBD_Classroom_Gate` anlegen und mit
   `add_filter('the_content', array($this, 'inhalt_reduzieren'), 8);`
   registrieren. **Priorität 8 ist wesentlich:** `do_blocks()` hängt auf
   Priorität 9; die Reduktion muss vorher greifen, solange der Inhalt noch
   Blockmarkup ist.
3. Ablauf der Methode:
   - Die vier Bedingungen des Geltungsbereichs prüfen (siehe oben), sonst
     `$content` unverändert zurückgeben. Die Theme-Funktionen jeweils mit
     `function_exists()` absichern — fehlt das Theme, gibt es keine Sperre und
     damit nichts zu reduzieren.
   - Freigabeliste holen:
     `CBD_Classroom::behandelte_container($class_id, get_the_ID())`.
   - `parse_blocks($content)` aufrufen. **Nur die oberste Ebene** durchgehen.
   - Je Block ermitteln, ob er bleibt (Auswahlfunktion aus Schritt 4).
   - Erlaubte Blöcke einzeln mit `render_block($block)` rendern und die
     Ergebnisse verketten. Nicht serialisieren — der Whitespace-Unterschied
     zwischen dem JavaScript-Serializer und `serialize_blocks()` (siehe
     `Plugins/CDB-Designer/CLAUDE.md`, Abschnitt „Block-Serializer") bleibt so
     ohne Bedeutung.
   - Bleibt nichts übrig, einen kurzen Hinweisabsatz zurückgeben:
     „Für diese Klasse ist auf dieser Seite noch nichts freigegeben."
4. Statische Auswahlfunktion, getrennt testbar:
   ```php
   public static function block_erlaubt($block, $freigegeben)  // : bool
   ```
   - `$block['blockName']` muss mit `container-block-designer/` beginnen
     (Vergleich über `strpos($name, '…') === 0`, **nicht** `str_starts_with` —
     das gibt es erst ab PHP 8.0).
   - `stableId` zuerst aus `$block['attrs']['stableId']` lesen.
   - Ist sie leer, Rückfall auf das gespeicherte HTML: `preg_match('/data-stable-id="([^"]+)"/', $block['innerHTML'] …)`.
     **Dieser Rückfall ist Pflicht** — er entspricht
     `CBD_Block_Registration::render_block()` ab Zeile 899 und ist der einzige
     Weg, wie Container aus Altbeständen ihre Kennung tragen. Ohne ihn
     verschwänden korrekt markierte Blöcke stillschweigend.
   - Die gefundene Kennung durch `CBD_Classroom::basis_container_id()` (AP-2.1)
     schicken und gegen `$freigegeben` prüfen.
   - Alles andere: `false`.
5. Harnisch grün machen.

**Akzeptanzkriterien:**
- [ ] Alle Prüfungen bestanden, Exit-Code 0; Commit mit rotem Stand liegt davor.
- [ ] Gesperrte Seite + gültige Klassensitzung: Im **Seitenquelltext** (`Strg+U`) kommen die Texte der nicht freigegebenen Container nicht vor.
- [ ] Der freigegebene Container erscheint vollständig, mit seinem Design und seinen verschachtelten Blöcken.
- [ ] Ein Container, dessen `stableId` nur im gespeicherten HTML steht, wird korrekt erkannt.
- [ ] **Nicht gesperrte Seite + Klassensitzung: der Inhalt ist unverändert vollständig** (Reduktion greift nicht).
- [ ] Angemeldet + gesperrte Seite: Inhalt vollständig.
- [ ] Glossarbegriffe in den verbliebenen Blöcken sind weiterhin verlinkt.
- [ ] LaTeX-Formeln in den verbliebenen Blöcken werden weiterhin gerendert.
- [ ] `php -l` fehlerfrei **und** `php Plugins/CDB-Designer/tools/check-php74.php` ohne Befund.

**Tests:**
- Smoke-Test: `php Plugins/CDB-Designer/tools/test-classroom-gate.php` grün.
- Prüfschritt Quelltext: Gesperrte Seite mit drei Container-Blöcken, einer als
  „behandelt" markiert. Über die Klassenanmeldung abgemeldet aufrufen,
  `Strg+U` → nur der markierte Container steht im HTML. Nach einem eindeutigen
  Wort aus einem der anderen beiden Container suchen (`Strg+F`) → kein Treffer.
- Prüfschritt Vollständigkeit: Der freigegebene Container zeigt Kopfzeilen-Icon,
  Design und seine inneren Blöcke.
- Prüfschritt Glossar: In den freigegebenen Container einen Begriff aufnehmen,
  der im Glossar steht. Nach dem Speichern die Seite in der Klassenansicht
  aufrufen → der Begriff ist verlinkt. (Läuft der Glossar-Filter auf Priorität
  10000, also nach der Reduktion — falls die Verlinkung ausbleibt, ist das ein
  Befund und die Reihenfolge zu prüfen.)
- Prüfschritt LaTeX: Eine Formel `$\sum x^2$` in den freigegebenen Container
  aufnehmen → wird in der Klassenansicht gerendert.
- Prüfschritt Nichteingriff (**der wichtigste**): Eine **nicht** gesperrte Seite
  mit denselben drei Containern in der Klassenansicht aufrufen → alle drei
  stehen im Quelltext, der JavaScript-Filter versteckt zwei davon. Danach die
  Klassenparameter entfernen und die Seite normal aufrufen → alle drei sichtbar.
- Regressionsprüfung: Startseite, eine normale Unterseite und eine Seite mit
  dem Block „Inhaltsverzeichnis" abgemeldet aufrufen → unverändert.
  `debug.log` ohne neue Einträge.

**Übergabenotiz:**

---

### AP-2.4: Klassenfilter im Browser an die Reduktion anpassen

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet (kleine, klar umrissene Änderung an einer Datei)
**Abhängigkeiten:** AP-2.3

**Ziel & Kontext:**

`Plugins/CDB-Designer/assets/js/classroom-page-filter.js` vergleicht die vom
Server gemeldeten behandelten Container mit den im DOM gefundenen. Fehlt einer
im DOM, zeigt es eine gelbe Warnung: „Diese Seite wurde bearbeitet. N
markierte(r) Block/Blöcke wurde(n) auf der Seite nicht gefunden."
(Funktion `filterContainers()`, ab Zeile 104.)

Das ist auf normalen Seiten richtig und soll dort bleiben. Auf einer
**reduzierten** Seite (AP-2.3) ist es dagegen falsch herum: Dort sind alle im
DOM vorhandenen Container freigegeben, aber es können freigegebene Container
fehlen, die auf **anderen** Seiten liegen — die Warnung wäre irreführend.

Zusätzlich läuft der Filter auf einer reduzierten Seite ins Leere: Er würde
jeden Container prüfen und keinen verstecken. Das ist harmlos, aber die
Navigationsleiste und die Zeichnungs-Abschnitte müssen weiterhin entstehen.

**Betroffene Dateien:**
- `Plugins/CDB-Designer/includes/class-cbd-classroom.php` (ändern – ein zusätzlicher Wert für das Frontend)
- `Plugins/CDB-Designer/assets/js/classroom-page-filter.js` (ändern)

**Vorgehen:**
1. In `CBD_Classroom::enqueue_frontend_assets()` (Zeile 1031) wird der Filter
   per `wp_localize_script()` mit `cbdClassroomPageData` versorgt
   (`ajaxUrl`, `pageId`). Einen dritten Wert ergänzen:
   ```php
   'reduziert' => (bool) ( function_exists('simple_clean_seite_nur_lehrpersonen')
       && simple_clean_seite_nur_lehrpersonen(get_the_ID())
       && function_exists('simple_clean_ist_lehrperson')
       && !simple_clean_ist_lehrperson() ),
   ```
   Das sagt dem Browser: Diese Seite kam bereits reduziert vom Server.
2. In `classroom-page-filter.js` in `filterContainers()`:
   - Den Warnblock für `missingContainers` (Zeile 104–113) nur ausführen, wenn
     `cbdClassroomPageData.reduziert` **nicht** gesetzt ist.
   - Das Verstecken nicht behandelter Container (Zeile 128–137) bleibt
     unverändert — auf einer reduzierten Seite findet es nichts zu verstecken,
     das ist in Ordnung und braucht keine Sonderbehandlung.
   - Die Navigationsleiste (`injectClassroomNavBar()`), die
     Link-Weiterreichung (`interceptLinks()`) und die Zeichnungs-Abschnitte
     müssen weiterhin unverändert laufen.
   - Alle neuen Konsolenausgaben hinter `window.cbdDebug &&` setzen.

**Akzeptanzkriterien:**
- [ ] Auf einer reduzierten Seite erscheint **keine** gelbe Warnung über nicht gefundene Blöcke.
- [ ] Auf einer nicht gesperrten Seite erscheint die Warnung weiterhin, wenn ein markierter Block tatsächlich fehlt (etwa nach dem Löschen eines Blocks im Editor).
- [ ] Die Klassen-Navigationsleiste mit dem Knopf „✕ Verlassen" erscheint auf **beiden** Seitenarten.
- [ ] Interne Links behalten in beiden Fällen die Parameter `classroom` und `token`.
- [ ] Der Zeichnungs-Abschnitt („📋 Tafelbild anzeigen") erscheint weiterhin, wenn eine Zeichnung vorliegt.
- [ ] Die Browser-Konsole bleibt frei von Fehlern; Ausgaben nur bei `window.cbdDebug = true`.
- [ ] `php -l Plugins/CDB-Designer/includes/class-cbd-classroom.php` fehlerfrei, `check-php74.php` ohne Befund.

**Tests:**
- Smoke-Test: Gesperrte Seite über die Klassenanmeldung aufrufen → Seite lädt,
  Konsole ohne Fehler.
- Prüfschritt Warnung: Auf einer **nicht** gesperrten Seite einen als
  „behandelt" markierten Container im Editor löschen und speichern. Seite in
  der Klassenansicht aufrufen → die gelbe Warnung erscheint (unverändertes
  Verhalten). Dieselbe Situation auf einer gesperrten Seite → keine Warnung.
- Prüfschritt Navigation: Auf der reduzierten Seite prüfen, dass die
  Klassen-Kopfleiste da ist, ein Klick auf einen internen Link die Parameter
  mitnimmt und „✕ Verlassen" die Klassenansicht beendet.
- Regressionsprüfung: Tafelbild-Anzeige auf einer normalen Seite im
  Klassenmodus funktioniert wie zuvor (Zeichnung ein-/ausklappen, bei mehreren
  Seiten die Blättern-Knöpfe).

**Übergabenotiz:**

---

### AP-2.rev: Unabhängiges Review Phase 2

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus
**Abhängigkeiten:** AP-2.1, AP-2.2, AP-2.3, AP-2.4 (inkl. Phasen-Integrationstest)

**Ziel & Kontext:**

Unabhängige Qualitätsprüfung der Phase 2 durch einen Agenten, der an keiner
Implementierung beteiligt war. Nur lesend arbeiten — **KEINE Datei verändern.**

Der Kern dieser Phase ist ein Eingriff in den Renderpfad **aller** Seiten. Der
schwerste denkbare Fehler ist nicht ein Loch in der Sperre, sondern eine
Reduktion, die zu weit greift und im laufenden Betrieb Inhalte verschwinden
lässt.

**Vorgehen:**
1. Für jedes AP der Phase (AP-2.1 bis AP-2.4): Code gegen dessen
   Akzeptanzkriterien prüfen, im Quelltext, nicht über die Übergabenotizen.
2. **Geltungsbereich der Reduktion prüfen (wichtigster Punkt).** In
   `CBD_Classroom_Gate::inhalt_reduzieren()` nachvollziehen, dass alle vier
   Bedingungen aus AP-2.3 geprüft werden und die Methode bei jeder fehlenden
   Bedingung `$content` **unverändert** zurückgibt. Prüfen, dass keine
   Bedingung durch eine Oder-Verknüpfung aufgeweicht wurde.
3. Prüfen, dass `seite_freigeben()` nur mit gültiger Sitzung **und**
   nichtleerer Freigabeliste `true` liefert, und dass die `class_id` aus dem
   Transient gegen den URL-Parameter geprüft wird.
4. Prüfen, dass die Zerlegungsregel der `container_id` genau **einmal** im Code
   steht (`grep` nach `preg_match` in `includes/class-cbd-classroom.php` und
   `includes/class-cbd-classroom-gate.php`).
5. Prüfen, dass der Rückfall auf `data-stable-id` im HTML vorhanden ist.
6. Prüfen, dass `behandelte_container()` ein Prepared Statement nutzt und dass
   keine Nutzereingabe ungeprüft in eine Abfrage gelangt.
7. Prüfen, dass die Antwortstruktur von `ajax_get_page_classroom_data()`
   unverändert ist (Schlüssel `class_name`, `treated_containers`, `drawings`
   mit `pages`).
8. Scope-Check gegen Abschnitt 2: kein neues Block-Attribut, keine neue Rolle,
   keine Änderung an Anmeldung, Tafelmodus oder Zeichnungen.
9. Qualitäts-Check: PHP-8.0-Syntax (`php Plugins/CDB-Designer/tools/check-php74.php`
   ausführen — das ist ein lesender Aufruf und erlaubt), `console.log` ohne
   `window.cbdDebug`-Gate, `error_log` ohne `WP_DEBUG`-Gate, fehlende
   Ausgabemaskierung.
10. Befunde als Bericht in die Übergabenotiz: Schweregrad, AP, Datei,
    Fundstelle.

**Akzeptanzkriterien:**
- [ ] Jedes AP der Phase wurde gegen seine Akzeptanzkriterien geprüft.
- [ ] Der Geltungsbereich der Reduktion ist Zeile für Zeile nachvollzogen und im Bericht beschrieben.
- [ ] Alle Befunde mit Schweregrad, Datei und Fundstelle dokumentiert.
- [ ] Keine Datei wurde verändert.

**Tests:**
- entfällt (Review-AP; das Ergebnis ist der Bericht).

**Übergabenotiz:**

---

### AP-2.doc: Dokumentation Phase 2

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-2.rev

**Ziel & Kontext:**

`Plugins/CDB-Designer/CLAUDE.md` und
`Plugins/CDB-Designer/reference_file_map.md` auf den Stand nach Phase 2 bringen.

**Betroffene Dateien:**
- `Plugins/CDB-Designer/CLAUDE.md` (ändern)
- `Plugins/CDB-Designer/reference_file_map.md` (ändern)

**Vorgehen:**
1. Übergabenotizen von AP-2.1 bis AP-2.rev durchgehen.
2. In `CLAUDE.md` einen Abschnitt **„Klassen-Durchlass für gesperrte Seiten"**
   ergänzen, der festhält:
   - Wozu er dient und wie er mit der Theme-Sperre zusammenspielt.
   - **Die Naht:** Filter `simple_clean_lehrerseite_freigeben` mit Standardwert
     `false` im Theme; das Plugin ist die einzige Stelle, die ihn öffnet.
     Fehlt das Theme, gibt es keine Sperre — deshalb überall
     `function_exists()`.
   - Der **Geltungsbereich der Reduktion** mit allen vier Bedingungen und dem
     ausdrücklichen Hinweis, dass eine Aufweichung Inhalte auf normalen Seiten
     verschwinden ließe.
   - Warum Priorität 8 auf `the_content` (vor `do_blocks` auf 9) und warum
     `render_block()` statt `serialize_blocks()`.
   - Der Rückfall auf `data-stable-id` im HTML und warum er nötig ist.
   - `CBD_Classroom::basis_container_id()` und
     `CBD_Classroom::behandelte_container()` als geteilte Helfer — mit dem
     Hinweis, dass sie **nicht** dupliziert werden dürfen.
   - Der neue Wert `reduziert` in `cbdClassroomPageData`.
3. Datei-Map ergänzen: `includes/class-cbd-classroom-gate.php`,
   `tools/test-classroom-gate.php`; geänderte Zeilen zu
   `includes/class-cbd-classroom.php`, `container-block-designer.php` und
   `assets/js/classroom-page-filter.js`. „Stand"-Datum und Plugin-Version im
   Kopf nachziehen.
4. **Die in der Analyse gefundene Doku-Lücke schließen:** In `Theme/CLAUDE.md`
   einen kurzen Abschnitt „Klassenansicht (kommt aus dem CDB-Plugin)"
   ergänzen. Er hält fest, dass `classroom-page-filter.js` im Klassenmodus
   `.site-header` ausblendet, eine eigene Kopfleiste einsetzt und den Inhalt
   von `#sidebar` durch die Klassen-Navigation ersetzt — mit Verweis auf
   `Plugins/CDB-Designer/CLAUDE.md`. Wer nur die Theme-Doku liest, hält beides
   sonst für unangetastet.
5. „Letzte Aktualisierung" im Kopf dieses Plans aktualisieren.

**Akzeptanzkriterien:**
- [ ] Jede in Phase 2 neue oder geänderte Datei hat eine aktuelle Zeile in `Plugins/CDB-Designer/reference_file_map.md`.
- [ ] Der neue CLAUDE.md-Abschnitt nennt alle vier Bedingungen des Geltungsbereichs.
- [ ] `Theme/CLAUDE.md` enthält den Abschnitt zur Klassenansicht mit dem Hinweis auf `.site-header` und `#sidebar`.
- [ ] Kein Verweis zeigt auf nicht existierende Dateien oder Funktionen.

**Tests:**
- Stichprobe: Zwei zufällige Zeilen der Datei-Map gegen den echten Dateiinhalt
  prüfen.
- Stichprobe: Jeden genannten Funktionsnamen per `grep` im Plugin wiederfinden.

**Übergabenotiz:**

---

### Phase 3: Absicherung und Auslieferung

---

### AP-3.1: Durchgang durch alle Sperrstellen

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus (Beurteilung, ob die Sperre wirklich dicht ist)
**Abhängigkeiten:** AP-2.doc

**Ziel & Kontext:**

Die Sperre wurde in zwei Phasen an acht Stellen eingebaut. Dieses AP prüft sie
als Ganzes gegen eine feste Liste — in der laufenden Testinstallation, nicht am
Quelltext. Gefundene Lücken werden **nicht hier behoben**, sondern als
Korrektur-APs (`AP-3.1.fix1`, …) angelegt und in Statustabelle und
Testprotokoll aufgenommen.

**Betroffene Dateien:**
- keine (Prüf-AP); Ergebnis ist die Übergabenotiz plus ggf. neue Korrektur-APs
  in diesem Plan

**Vorgehen:**

1. Testinstallation vorbereiten: `start-server.cmd` starten, aktuelle Stände
   von Theme und Plugin nach
   `C:\allinkl-testserver\www\htdocs\w0000001\fos\wp-content\` kopieren,
   `WP_DEBUG` und `WP_DEBUG_LOG` aktiv, `wp-content/debug.log` vorher leeren.
2. Prüfaufbau anlegen:
   - Kapitelseite „Kapitel Test" mit drei Unterseiten.
   - Eine Unterseite „Lösungen Test" sperren; darunter eine weitere Seite
     „Lösungen Detail" **ohne** eigenes Häkchen.
   - Auf „Lösungen Test" drei Container-Blöcke mit je einem eindeutigen
     Kunstwort im Text (z. B. „Zwirbelquark", „Fabelbirne", „Nebelkeks").
   - Klasse „Testklasse" anlegen, im Tafelmodus **nur** den Container mit
     „Zwirbelquark" als behandelt markieren.
   - Eine Seite mit dem Block „Inhaltsverzeichnis" und eine Seite mit dem
     Shortcode `[cbd_classroom]`.
   - „Lösungen Test" ins Hauptmenü aufnehmen.
3. Die Prüfliste vollständig abarbeiten. Jede Zeile mit Ergebnis (bestanden /
   Befund) in die Übergabenotiz:

   | # | Prüfung (jeweils **abgemeldet**, privates Browserfenster) | Erwartung |
   |---|---|---|
   | 1 | Seitenleiste auf „Kapitel Test" | „Lösungen Test" und „Lösungen Detail" fehlen |
   | 2 | Block „Inhaltsverzeichnis" | beide fehlen |
   | 3 | Hauptmenü auf jeder Seite | Eintrag fehlt |
   | 4 | Suche nach „Fabelbirne" | kein Treffer |
   | 5 | Suche nach „Zwirbelquark" | kein Treffer (auch der freigegebene Block ist außerhalb der Klassenansicht gesperrt) |
   | 6 | `…/wp-json/wp/v2/pages?per_page=100` | beide IDs fehlen |
   | 7 | `…/wp-json/wp/v2/search?search=Fabelbirne` | kein Treffer |
   | 8 | `…/wp-sitemap-posts-page-1.xml` | beide URLs fehlen |
   | 9 | Direkter Aufruf von „Lösungen Test" | Hinweisseite, HTTP 403 |
   | 10 | Direkter Aufruf von „Lösungen Detail" | Hinweisseite, HTTP 403 |
   | 11 | Quelltext der Hinweisseite | Titel der gesperrten Seite kommt nicht vor, auch nicht im `<title>` |
   | 12 | Aufruf mit angehängtem `?p=<ID>` bzw. `?page_id=<ID>` | Hinweisseite, kein Inhalt |
   | 13 | Vorschau-Link (`?preview=true`) ohne Anmeldung | kein Inhalt |
   | 14 | Feed `…/?feed=rss2` | die gesperrten Seiten kommen nicht vor (Seiten stehen normalerweise nicht im Feed — prüfen und Ergebnis festhalten) |
   | 15 | Klassenanmeldung, dann „Lösungen Test" | Seite öffnet sich |
   | 16 | Quelltext dieser Seite | „Zwirbelquark" vorhanden, „Fabelbirne" und „Nebelkeks" **nicht** |
   | 17 | Klassenansicht auf „Lösungen Detail" (keine behandelten Blöcke) | Hinweisseite, HTTP 403 |
   | 18 | Gültiges Token, `classroom`-Parameter auf eine andere Klasse geändert | Hinweisseite, HTTP 403 |
   | 19 | Token nach Ablauf des Transients (Transient in phpMyAdmin löschen) | Hinweisseite, HTTP 403 |
   | 20 | Eine **nicht** gesperrte Seite in der Klassenansicht | alle Container im Quelltext, JS versteckt die nicht behandelten |
   | 21 | Angemeldet: alle obigen Seiten | vollständig sichtbar, keine Reduktion |

4. `wp-content/debug.log` prüfen: keine neuen Notices, Warnings oder
   Deprecations aus Theme oder Plugin.
5. Abfragezahl vergleichen: Als Administrator eine Seite mit `?sc_perf=1`
   aufrufen und den Wert `queries=` festhalten. Zum Vergleich denselben Aufruf
   auf einer Installation ohne gesperrte Seiten (alle Häkchen entfernen).
   Differenz notieren; mehr als zwei zusätzliche Abfragen ist ein Befund.
6. **Caching prüfen:** Unter „Plugins" nachsehen, ob ein Caching-Plugin aktiv
   ist. Falls ja: prüfen und festhalten, ob es für angemeldete Benutzer den
   Cache umgeht — andernfalls ist es ein Befund und muss in die Dokumentation
   als Betriebshinweis.
7. Befunde nach Schweregrad in die Übergabenotiz. Für jeden kritischen Befund
   ein Korrektur-AP `AP-3.1.fixN` in Abschnitt 7 dieses Plans ergänzen und in
   Statustabelle und Testprotokoll aufnehmen.

**Akzeptanzkriterien:**
- [ ] Alle 21 Zeilen der Prüfliste sind mit Ergebnis dokumentiert.
- [ ] `debug.log` enthält keine neuen Einträge aus Theme oder Plugin.
- [ ] Die Differenz der Abfragezahl ist gemessen und dokumentiert.
- [ ] Der Caching-Status ist geprüft und dokumentiert.
- [ ] Für jeden kritischen Befund existiert ein Korrektur-AP im Plan.

**Tests:**
- Die Prüfliste **ist** der Test.
- Zusätzlich Smoke-Test nach dem Aufräumen: Prüfaufbau wieder entfernen
  (Häkchen lösen, Testseiten in den Papierkorb) → Website verhält sich wie vor
  dem AP.

**Übergabenotiz:**

---

### AP-3.2: Verteilungspakete bauen und Ausrollen vorbereiten

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet (feste Bauabläufe, in den CLAUDE.md-Dateien beschrieben)
**Abhängigkeiten:** AP-3.1 (inkl. aller Korrektur-APs daraus)

**Ziel & Kontext:**

Beide Komponenten in auslieferbare ZIPs bringen und die Reihenfolge
festschreiben. **Erst Theme, dann Plugin** — das Theme allein sperrt ohne
Durchlass und ist damit die sichere Zwischenstufe.

Die Bauabläufe sind vorgegeben und dürfen nicht abgekürzt werden. Insbesondere
darf das Plugin-ZIP **niemals von Hand** gezippt werden: `create-plugin-zip.js`
stellt vor dem Packen `composer dump-autoload --no-dev --optimize` her und
danach den Dev-Autoloader wieder. Ein mit Dev-Paketen erzeugter Autoloader bindet
phpunit ein und ergibt HTTP 500 auf der Zielinstallation (passiert bei den ZIPs
v3.1.63–3.1.65).

**Betroffene Dateien:**
- `Theme/` (Bau, kein Quellcode-Eingriff)
- `Plugins/CDB-Designer/` (Bau, kein Quellcode-Eingriff)
- `DOKUMENTATION.md` im Projektstamm (ändern)

**Vorgehen:**
1. **Theme bauen:**
   ```
   cd Theme
   for file in *.php; do php -l "$file" || exit 1; done
   npm run build
   ```
   `npm run build` erhöht über `backup-and-build.js` selbstständig die
   Patch-Version in `package.json` und `style.css`, sichert die vorherige
   ZIP-Generation und erzeugt `dist/simple-clean-theme-v<version>.zip`.
2. **Inhalt des Theme-ZIPs prüfen:** `includes/sichtbarkeit.php` muss enthalten
   sein (abgedeckt durch die Whitelist-Zeile `includes/**/*.{php,js}` in
   `create-theme-zip.js`), `tools/test-sichtbarkeit.php` und `docs/` dürfen
   **nicht** enthalten sein. Mit `unzip -l dist/simple-clean-theme-v*.zip`
   nachsehen.
3. **Plugin bauen:**
   ```
   cd Plugins/CDB-Designer
   for file in *.php includes/*.php includes/Database/*.php; do php -l "$file" || exit 1; done
   node create-plugin-zip.js
   ```
   Das Skript führt `tools/check-php74.php` selbstständig aus und **bricht ab**,
   wenn PHP-8.0-Syntax gefunden wird.
4. **Inhalt des Plugin-ZIPs prüfen:** `includes/class-cbd-classroom-gate.php`
   muss enthalten sein, `tools/` darf **nicht** enthalten sein.
5. **Autoloader-Prüfung nach dem Bau** (Pflicht laut
   `Plugins/CDB-Designer/CLAUDE.md`): ZIP in ein temporäres Verzeichnis
   entpacken und
   `php -r 'define("ABSPATH","/"); require "<pfad>/vendor/autoload.php";'`
   ausführen — muss ohne Fatal Error laufen.
6. **Beide ZIPs in einer frischen Testinstallation prüfen:** In der
   Testinstallation das Theme über Design → Themes → hochladen und das Plugin
   über Plugins → hochladen installieren (also nicht die kopierten Dateien,
   sondern die Pakete). Danach den Kern-Prüffall wiederholen: gesperrte Seite
   abgemeldet → Hinweisseite; über die Klassenanmeldung → nur der freigegebene
   Container.
7. **`DOKUMENTATION.md` im Projektstamm ergänzen** — ein Abschnitt „Vorhaben
   „Seiten nur für Lehrpersonen" (2026-08)" nach dem Muster der zwei
   vorhandenen Vorhaben-Abschnitte, mit Verweis auf
   `Theme/docs/PLAN-Lehrerseiten.md` und
   `Theme/docs/ERWEITERUNGSANALYSE-Lehrerseiten.md`. Dazu der Hinweis, dass
   dies die **zweite** Stelle ist, an der Theme und Plugin über eine
   Schnittstelle zusammenwirken (neben dem Menü-Slug `page-manager`) — hier
   über den Filter `simple_clean_lehrerseite_freigeben`, dessen Standardwert
   `false` sein muss.
8. **Ausrollanleitung** in die Übergabenotiz und in den Abschnitt aus Schritt 7:
   1. Theme-ZIP hochladen und aktivieren.
   2. Prüfen, dass die Website normal läuft (noch ist keine Seite gesperrt —
      es darf sich nichts ändern).
   3. Plugin-ZIP hochladen.
   4. Erst danach das erste Häkchen an einer Lösungsseite setzen.
   5. Abgemeldet in einem privaten Fenster gegenprüfen.

**Akzeptanzkriterien:**
- [ ] `Theme/dist/simple-clean-theme-v<version>.zip` existiert und enthält `includes/sichtbarkeit.php`.
- [ ] Das Theme-ZIP enthält **kein** `tools/` und **kein** `docs/`.
- [ ] `Plugins/CDB-Designer/container-block-designer-v<version>.zip` existiert und enthält `includes/class-cbd-classroom-gate.php`.
- [ ] Das Plugin-ZIP enthält **kein** `tools/`.
- [ ] Die Autoloader-Prüfung aus Schritt 5 läuft ohne Fatal Error.
- [ ] Beide Pakete lassen sich in der Testinstallation installieren; der Kern-Prüffall aus Schritt 6 besteht.
- [ ] `DOKUMENTATION.md` im Projektstamm nennt das Vorhaben und die zweite Theme-Plugin-Schnittstelle.

**Tests:**
- Smoke-Test: Nach der Installation beider Pakete die Startseite abgemeldet
  aufrufen → lädt normal, `debug.log` ohne neue Einträge.
- Prüfschritt Deaktivierung: Das Plugin CDB-Designer **deaktivieren**, während
  eine Seite gesperrt ist, und diese Seite in der Klassenansicht aufrufen →
  Hinweisseite mit 403. Das belegt, dass die Naht geschlossen ausfällt. Danach
  Plugin wieder aktivieren.
- Prüfschritt Theme-Wechsel: Kurz auf „Twenty Twenty-Five" wechseln und eine
  zuvor gesperrte Seite abgemeldet aufrufen → sie ist sichtbar (die Sperre
  gehört zum Theme; das ist erwartetes Verhalten und gehört in die
  Dokumentation). Danach zurückwechseln.

**Übergabenotiz:**

---

### AP-3.rev: Unabhängiges Review Phase 3

**Status:** ☐ offen
**Umfang:** S
**Modell:** opus
**Abhängigkeiten:** AP-3.1, AP-3.2

**Ziel & Kontext:**

Abschließende unabhängige Prüfung durch einen Agenten, der an keiner
Implementierung beteiligt war. Nur lesend arbeiten — **KEINE Datei verändern.**

**Vorgehen:**
1. Übergabenotiz von AP-3.1 lesen: Ist jede der 21 Prüfzeilen mit einem
   Ergebnis versehen? Wurde für jeden kritischen Befund ein Korrektur-AP
   angelegt und abgeschlossen?
2. Übergabenotiz von AP-3.2 lesen und die genannten ZIP-Dateien im Dateisystem
   nachprüfen (Existenz und Zeitstempel).
3. Gesamtscope prüfen: Wurden über die drei Phasen hinweg Nicht-Ziele aus
   Abschnitt 2 verletzt?
4. Dokumentation gegenprüfen: Enthalten `Theme/CLAUDE.md`,
   `Plugins/CDB-Designer/CLAUDE.md`, beide `reference_file_map.md` und
   `DOKUMENTATION.md` die in AP-1.doc, AP-2.doc und AP-3.2 verlangten Inhalte?
   Stichprobenartig drei genannte Funktionsnamen im Code wiederfinden.
5. Prüfen, dass Abschnitt 11 dieses Plans („Rückblick und offene Punkte")
   ausgefüllt ist.
6. Befunde mit Schweregrad in die Übergabenotiz.

**Akzeptanzkriterien:**
- [ ] Alle drei Punkte aus Schritt 1, 2 und 4 sind geprüft und dokumentiert.
- [ ] Der Scope-Check gegen Abschnitt 2 ist dokumentiert.
- [ ] Keine Datei wurde verändert.

**Tests:**
- entfällt (Review-AP).

**Übergabenotiz:**

---

### AP-3.doc: Abschlussdokumentation und Rückblick

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-3.rev

**Ziel & Kontext:**

Den Plan abschließen: Abschnitt 11 füllen, offene Punkte festhalten, die
Dokumentation der drei Komponenten auf den Endstand bringen.

**Betroffene Dateien:**
- `Theme/docs/PLAN-Lehrerseiten.md` (ändern – Abschnitt 11)
- `Theme/CLAUDE.md`, `Plugins/CDB-Designer/CLAUDE.md` (ändern, falls das Review Lücken fand)
- `DOKUMENTATION.md` im Projektstamm (prüfen)

**Vorgehen:**
1. Alle Übergabenotizen aller drei Phasen durchgehen.
2. **Abschnitt 11 dieses Plans ausfüllen**, nach dem Vorbild von
   `Theme/docs/PLAN-Seitenindex.md` (Abschnitt 11) und
   `Plugins/CDB-Designer/docs/PLAN-Seitenimport.md` (Abschnitt 11):
   - Welche Annahmen der Planung haben sich als falsch erwiesen?
   - Welche Fallen kannte der Plan nicht?
   - Was wurde bewusst nicht umgesetzt?
   - Welche Messwerte kamen heraus (Abfragezahlen aus AP-3.1)?
   Das ist der wertvollste Teil für spätere Vorhaben — konkret schreiben, nicht
   allgemein.
3. Befunde mittlerer und geringer Schwere aus allen drei Reviews, die nicht
   behoben wurden, als „Offene Punkte" in Abschnitt 11 aufnehmen.
4. Betriebshinweise ergänzen, die sich in AP-3.1 und AP-3.2 gezeigt haben:
   Caching-Verhalten, das Verhalten beim Theme-Wechsel (die Sperre gehört zum
   Theme), die Ausrollreihenfolge.
5. „Letzte Aktualisierung" im Kopf dieses Plans setzen.

**Akzeptanzkriterien:**
- [ ] Abschnitt 11 enthält Rückblick, offene Punkte und die gemessenen Abfragezahlen.
- [ ] Jeder nicht behobene Befund aus den drei Reviews steht unter „Offene Punkte".
- [ ] Die Betriebshinweise (Caching, Theme-Wechsel, Ausrollreihenfolge) sind dokumentiert.
- [ ] Statustabelle und Testprotokoll dieses Plans sind vollständig gefüllt.

**Tests:**
- Stichprobe: Drei Aussagen aus Abschnitt 11 gegen die Übergabenotizen prüfen,
  aus denen sie stammen.

**Übergabenotiz:**

---

## 8. Status

Wird während der Ausführung gepflegt. Legende: ☐ offen · ◐ in Arbeit · ☑ erledigt · ✗ blockiert

| AP | Titel | Modell | Status | Abhängig von | Repository | Notiz |
|---|---|---|---|---|---|---|
| AP-1.1 | Zentrale Sichtbarkeitslogik mit Prüfharnisch (TDD) | opus | ☑ | – | Theme | 17 Prüfungen grün; keine `function_exists`-Guards (Hoisting), Begründung in der Übergabenotiz |
| AP-1.2 | Häkchen „Nur für Lehrpersonen" in der Meta-Box | sonnet | ☑ | AP-1.1 | Theme | 20 Prüfungen grün; Box heißt jetzt „Navigation, Verzeichnis & Zugriff" |
| AP-1.3 | Durchsetzung beim Seitenaufruf und die Hinweisseite | opus | ☑ | AP-1.1 | Theme | 403 + Hinweisseite; Passwortschutz gewinnt weiterhin; Prüfaufbau (IDs 29–32) angelegt |
| AP-1.4 | Ausblenden in Seitenleiste und Inhaltsverzeichnis | sonnet | ☐ | AP-1.1 | Theme | parallel zu 1.2/1.3/1.5 |
| AP-1.5 | Ausblenden in Menü, Suche, REST und Sitemap | sonnet | ☐ | AP-1.1, AP-1.3 | Theme | |
| AP-1.6 | Seitenmanager – Sammelaktionen und Kennzeichnung | sonnet | ☐ | AP-1.1, AP-1.2 | Theme | parallel zu 1.3/1.4/1.5 |
| AP-1.rev | Unabhängiges Review Phase 1 | opus | ☐ | AP-1.1 … AP-1.6 | Theme | |
| AP-1.doc | Dokumentation Phase 1 | sonnet | ☐ | AP-1.rev | Theme | |
| AP-2.1 | Geteilte Helfer für behandelte Container herauslösen | sonnet | ☐ | – | Plugin | |
| AP-2.2 | Klassensitzung prüfen und den Filter bedienen | opus | ☐ | AP-1.3, AP-2.1 | Plugin | |
| AP-2.3 | Serverseitige Reduktion des Seiteninhalts | opus | ☐ | AP-2.2 | Plugin | |
| AP-2.4 | Klassenfilter im Browser anpassen | sonnet | ☐ | AP-2.3 | Plugin | |
| AP-2.rev | Unabhängiges Review Phase 2 | opus | ☐ | AP-2.1 … AP-2.4 | Plugin | |
| AP-2.doc | Dokumentation Phase 2 | sonnet | ☐ | AP-2.rev | Plugin + Theme | |
| AP-3.1 | Durchgang durch alle Sperrstellen | opus | ☐ | AP-2.doc | beide | |
| AP-3.2 | Verteilungspakete bauen und Ausrollen vorbereiten | sonnet | ☐ | AP-3.1 | beide | |
| AP-3.rev | Unabhängiges Review Phase 3 | opus | ☐ | AP-3.1, AP-3.2 | beide | |
| AP-3.doc | Abschlussdokumentation und Rückblick | sonnet | ☐ | AP-3.rev | beide | |

## 9. Testprotokoll

Wird während der Ausführung gepflegt. Ein Eintrag pro abgeschlossenem AP und pro Phasenabschluss.

| Datum | AP / Phase | Getestet | Ergebnis | Getestet von |
|---|---|---|---|---|
| 2026-08-11 | AP-1.1 | `php tools/test-sichtbarkeit.php` (17 Prüfungen); `php -l` auf drei Dateien; PHP-7.4-Parse aller drei Dateien; Smoke-Test http://fos.localhost:8080/ ; `debug.log` | **bestanden** — 17/17 grün, Exit 0. Roter Vorlauf: 20 Fehler (Commit `d42989b`). PHP 7.4 sauber. Startseite HTTP 200. `debug.log` ohne Theme-Einträge | Claude (Opus) |
| 2026-08-11 | AP-1.2 | Skript im Webroot: Meta-Box rendern, mit echtem Nonce speichern, `wp_postmeta` direkt auslesen, Löschen prüfen, Regression der zwei bestehenden Häkchen, ungültiges Nonce; `php -l`; PHP-7.4-Parse | **bestanden** — 20/20 grün. Eine Prüfung war anfangs rot (Messfehler: `wp_nonce_field` schreibt den Namen als `id=` **und** `name=`; es ist genau ein Feld). `debug.log` ohne Theme-Einträge | Claude (Opus) |
| 2026-08-11 | AP-1.3 | `curl` gegen die Testinstallation: gesperrte Seite und Unterseite abgemeldet und angemeldet, Statuszeile, Cache-Header, `noindex`, Titel- und Kunstwort-Leck, Anmelde- und Rücksprung-Link, normale Seiten, Website-Passwortschutz ein/aus; `php -l`; PHP-7.4-Parse; Harnisch aus AP-1.1 erneut | **bestanden** — 403 mit `no-store`, Seitentitel und Lösungswörter 0-mal im Dokument, Vererbung greift, angemeldet HTTP 200 vollständig, bei aktivem Website-Passwort erscheint die Passwortabfrage (nicht die Hinweisseite). `debug.log` ohne Theme-Einträge, Webroot ohne Reste | Claude (Opus) |
| | AP-1.4 | | | |
| | AP-1.5 | | | |
| | AP-1.6 | | | |
| | **Phase 1** Integration + Regression | | | |
| | AP-1.rev | | | |
| | AP-1.doc | | | |
| | AP-2.1 | | | |
| | AP-2.2 | | | |
| | AP-2.3 | | | |
| | AP-2.4 | | | |
| | **Phase 2** Integration + Regression | | | |
| | AP-2.rev | | | |
| | AP-2.doc | | | |
| | AP-3.1 | | | |
| | AP-3.2 | | | |
| | **Phase 3** Integration + Regression | | | |
| | AP-3.rev | | | |
| | AP-3.doc | | | |

## 10. Dokumentation

Das Projekt hat eine gewachsene Doku-Struktur; sie wird **erweitert, nicht
ersetzt**.

| Datei | Rolle | Gepflegt in |
|---|---|---|
| `DOKUMENTATION.md` (Projektstamm) | Wegweiser: wo liegt welche Doku, welche Vorhaben gab es | AP-3.2 |
| `Theme/CLAUDE.md` | Architektur- und Arbeitsdoku des Themes, inkl. neuem Abschnitt „Seiten nur für Lehrpersonen" | AP-1.doc, AP-2.doc |
| `Theme/reference_file_map.md` | Datei-Map des Themes | jedes AP, das Dateien anfasst; abschließend AP-1.doc |
| `Plugins/CDB-Designer/CLAUDE.md` | Architektur- und Arbeitsdoku des Plugins, inkl. neuem Abschnitt „Klassen-Durchlass für gesperrte Seiten" | AP-2.doc |
| `Plugins/CDB-Designer/reference_file_map.md` | Datei-Map des Plugins | jedes AP, das Dateien anfasst; abschließend AP-2.doc |
| `Theme/docs/ERWEITERUNGSANALYSE-Lehrerseiten.md` | Analyse, die zu diesem Plan führte | unverändert (historisches Dokument) |
| `Theme/docs/PLAN-Lehrerseiten.md` | dieser Plan, inkl. Status, Testprotokoll und Rückblick | laufend |

**Es gibt keine `DOKUMENTATION.md` je Komponente** — diese Rolle übernehmen in
diesem Projekt die `CLAUDE.md`-Dateien. Keine Parallelstruktur aufbauen.

## 11. Rückblick und offene Punkte

_Wird in AP-3.doc ausgefüllt. Hier gehört hinein, was die Planung falsch
angenommen hat, welche Fallen der Plan nicht kannte, welche Messwerte
herauskamen und was bewusst offen bleibt — nach dem Vorbild von
`Theme/docs/PLAN-Seitenindex.md` und
`Plugins/CDB-Designer/docs/PLAN-Seitenimport.md`._