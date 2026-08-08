# Projektplan: Seitenindex & Inhaltsverzeichnis-Block (Theme „FOS Online Schulbuch")

_Erstellt am: 2026-08-07 · Letzte Aktualisierung: 2026-08-08_

## 0. Anweisungen für den ausführenden Agenten

Du arbeitest nach diesem Plan. Er ist die einzige Wahrheitsquelle – du hast
keinen Zugriff auf das Gespräch, in dem er entstand. Halte dich an diese Regeln:

**Rollen und Modelle:**
A. Wird die Abarbeitung von einem Orchestrator koordiniert (Opus), gilt:
   Der Orchestrator delegiert APs an Subagenten und implementiert NIEMALS
   selbst. Er gibt jedem Subagenten nur dessen AP-Text plus die Abschnitte
   0–5 dieses Plans als Kontext, prüft jede Rückmeldung gegen die
   Akzeptanzkriterien des APs, bevor er abhängige APs freigibt, und pflegt
   die Statustabelle.
B. Jedes AP nennt sein Ausführungsmodell (**Modell:** sonnet | opus).
   Subagenten mit genau diesem Modell starten.
C. Unabhängige APs derselben Phase (keine gemeinsamen Abhängigkeiten,
   disjunkte Dateien) dürfen parallel bearbeitet werden. **Achtung in diesem
   Projekt:** Sehr viele APs ändern `Theme/functions.php`. Diese APs dürfen
   NIE parallel laufen. Wo Parallelität möglich ist, steht es im AP.

**Arbeitsweise:**
1. Bearbeite genau EIN Arbeitspaket (AP) pro Auftrag, sofern nicht anders beauftragt.
2. Prüfe vor Beginn die Abhängigkeiten deines APs in der Statustabelle
   (Abschnitt 8). Sind sie nicht ☑, brich ab und melde das.
3. Setze deinen AP-Status auf ◐ (in Arbeit), bevor du beginnst.
4. Bleibe strikt im Scope des APs. Fällt dir Verbesserungspotenzial außerhalb
   auf, notiere es in der Übergabenotiz – setze es nicht um.
5. Beachte die Nicht-Ziele (Abschnitt 2) und Constraints (Abschnitt 3).

**Tests (Pflicht, ein AP ohne bestandene Tests ist nicht fertig):**
6. Nach Abschluss: alle Akzeptanzkriterien einzeln nachweisen + die im AP
   definierten Tests durchführen.
7. **Vor JEDEM Erzeugen eines Theme-ZIPs zwingend Syntaxprüfung:**
   im Ordner `Theme/` `php -l` über jede PHP-Datei laufen lassen
   (Wurzel **und** `includes/`, `blocks/`). Erst bei null Fehlern bauen.
   Ein Syntaxfehler in `functions.php` erzeugt auf der Live-Site eine
   weiße Seite über die gesamte Website.
8. Getestet wird auf der **Live-Site** (siehe Abschnitt 3). Das heißt für
   dich: Änderungen erst dann hochladen, wenn die Syntaxprüfung sauber ist,
   und nach dem Hochladen sofort die drei Sichtprüfungen aus Abschnitt 3
   durchführen, bevor du das AP als erledigt meldest.
9. Ergebnis ins Testprotokoll (Abschnitt 9) eintragen.
10. Erst dann Status auf ☑. Bei Fehlschlag: Status ✗ (blockiert), Ursache in
    die Übergabenotiz, nicht mit abhängigen APs weitermachen. Bei einem
    Fehler auf der Live-Site zuerst das Rollback nach Abschnitt 5 ausführen,
    dann analysieren.
11. Nach dem letzten Implementierungs-AP einer Phase zusätzlich:
    Integrationstest der Phase + Regressionscheck aller vorherigen Phasen
    (deren „lauffähiger Endzustand" muss weiterhin funktionieren) sowie die
    Regressionsliste aus Abschnitt 5.2. Eintrag ins Testprotokoll.
12. Danach folgt das Review-AP (`AP-<N>.rev`): Es wird von einem frischen
    Agenten ausgeführt, der KEINES der APs dieser Phase implementiert hat.
    Der Review-Agent arbeitet ausschließlich lesend und verändert keine
    Datei. Kritische Befunde führen zu Korrektur-APs (siehe Regel 17);
    die Phase ist erst danach abgeschlossen.

**Übergabe:**
13. Fülle die Übergabenotiz deines APs aus: was geändert wurde, getroffene
    Entscheidungen, was für Folge-APs relevant ist.
14. Hat dein AP Dateien angelegt, verschoben oder wesentlich geändert:
    aktualisiere deren Zeilen in `Theme/reference_file_map.md` (Datei |
    Zweck | wichtige Funktionen | Abhängigkeiten). Diese Datei wird in
    AP-5.3 erstmals vollständig angelegt; existiert sie zum Zeitpunkt
    deines APs noch nicht, notiere die Zeilen stattdessen in deiner
    Übergabenotiz, damit AP-5.3 sie übernehmen kann.
15. Aktualisiere „Letzte Aktualisierung" im Dateikopf dieses Plans.
16. Git: mindestens ein Commit mit AP-ID im Text, z. B.
    `AP-1.2: Messbasis für die Inhaltsverzeichnisseite`. Nach jedem
    abgeschlossenen AP den Phasen-Branch zum Remote pushen
    (`git push -u origin <branch>`). Phasen-Branches erst nach bestandenem
    Integrationstest UND Review nach `main` mergen, danach `main` pushen.
    **Das Git-Repo liegt in `Theme/`, nicht im Website-Wurzelverzeichnis.**

**Umplanung:**
17. Zeigt sich während der Ausführung, dass der Plan nicht trägt (Review-
    Befunde, blockierte APs, falsche Annahmen), werden Korrektur-APs mit
    fortlaufender Nummer ergänzt (`AP-<N>.fix1`, …) und in Statustabelle
    und Testprotokoll aufgenommen. Bestehende APs und Übergabenotizen
    werden nie gelöscht, nur ergänzt.

**Zwei projektspezifische Fallen, die du kennen musst:**
- `npm run build` ruft `backup-and-build.js` auf. Das Skript **erhöht die
  Patch-Versionsnummer selbstständig** in `package.json` und `style.css` und
  verschiebt das bisherige ZIP nach `backups/fos-online-schulbuch-backup.zip`
  (nur EINE Generation). Setze Versionsnummern also **niemals von Hand** –
  sonst springt die Version doppelt.
- In `sidebar.php` stehen Funktionsdefinitionen in `function_exists`-Guards
  bewusst am Dateianfang. Bedingt deklarierte Funktionen werden von PHP nicht
  gehoistet; verschiebst du sie ans Dateiende, gibt es einen Fatal Error auf
  allen Seiten mit Sidebar. Das gilt sinngemäß für jede neue Template-Datei.

## 1. Projektziel

Die Inhaltsverzeichnisseite der Website lädt auch bei mehreren hundert Seiten
zügig. Erreicht wird das durch (a) einen vorberechneten, schlanken Seitenindex
im Theme, der bei jeder Seitenänderung ungültig wird und beim nächsten Lesen
neu entsteht, (b) einen darauf aufsetzenden, serverseitig gerenderten
Gutenberg-Block `fos/inhaltsverzeichnis` mit Fragment-Cache, der den Core-Block
`core/page-list` ersetzt, und (c) die Behebung eines Fallback-Fehlers im
Glossar-Autolinker, der auf textarmen Seiten alle Glossarbegriffe lädt und
über das gesamte gerenderte HTML laufen lässt.

Gestaltung ist steuerbar: pro Einfügung über Block-Optionen (Startseite, Tiefe,
Layout, Spalten, Suchfeld), global über eine neue Customizer-Sektion.

## 2. Nicht-Ziele

- **Die Sidebar (`Theme/sidebar.php`) wird nicht umgebaut.** Sie ist bereits
  auf eine `get_pages()`-Query optimiert und lädt nur den Teilbaum der
  aktuellen Wurzelseite. Sie bleibt in diesem Vorhaben unverändert.
- **`simple_clean_fallback_menu()` wird nicht angefasst.**
- **Kein Nachladen per AJAX/REST.** Der Index wird vollständig serverseitig
  gerendert. Sollte das DOM trotz Tiefenbegrenzung zu groß bleiben, ist das
  ein späteres, eigenes Vorhaben.
- **Keine anderen Post-Types als `page`.** Beiträge und Glossareinträge
  erscheinen nicht im Inhaltsverzeichnis.
- **Keine Entwürfe.** Der Index enthält ausschließlich `post_status = 'publish'`,
  auch für angemeldete Redakteure.
- **Keine Fremdbibliothek, keine CDN-Einbindung** (DSGVO-Linie des Projekts).
  Aufklappen über natives `<details>`, Suchfeld über wenige Zeilen Vanilla JS.
- **Der „plastische Look" wird nicht auf das Inhaltsverzeichnis ausgeweitet.**
  Er bleibt laut `Theme/CLAUDE.md` auf `.sidebar-toggle-btn` beschränkt.
- **Keine neue Datenbanktabelle**, keine Schema-Migration.
- **Kein Umbau des Glossar-Systems.** In Phase 1 wird ausschließlich die
  Fallback-Bedingung korrigiert, nichts weiter.
- **Kein Caching-Plugin, kein persistenter Object-Cache** wird eingeführt.

## 3. Kontext & Constraints

- **Erhobener Ist-Stand (2026-08-08, AP-1.2):** 258 veröffentlichte Seiten,
  **1049 Glossareinträge**. Es gibt **kein einzelnes Inhaltsverzeichnis**,
  sondern **drei Kapitelübersichten** mit `core/page-list`:
  `/organische-chemie-und-biochemie/` (128 Links), `/analytische-chemie/`
  (79 Links), `/laborsicherheit/` (26 Links). Vergleichswerte ohne
  Verzeichnis: Startseite 41 Queries / 0,053 s / 67 KB, Skriptenseite
  53 Queries / 0,067 s / 170 KB.
  **Wichtig zur Einordnung der Linkzahlen:** Sie sagen nichts über die
  Serverlast. `core/page-list` lädt über `get_pages()` immer *alle* Seiten
  der Website und filtert erst danach nach `parentPageID` — die Übersicht mit
  26 sichtbaren Links kostet den Server genauso viel wie die mit 128.
- **Gestaltungsfreiheit (Entscheidung des Nutzers, 2026-08-08):** Die
  Verzeichnisse dürfen **vollständig anders aufgebaut** werden. Die
  ursprüngliche Vorgabe „1:1-Ersatz für den Core-Block, damit nur ein Block
  getauscht werden muss" ist damit aufgehoben. Aufbau, Darstellung und
  Aufteilung auf Seiten sind frei wählbar, solange der Nutzen erhalten
  bleibt.
- **Umgebung:** WordPress 6.x auf All-Inkl Shared Hosting. PHP 7.4 als
  Mindestversion (Theme-Header `Requires PHP: 7.4`). Kein SSH, kein WP-CLI.
  Kein persistenter Object-Cache (Redis/Memcached) – deshalb wird über
  `wp_options`/Transients zwischengespeichert, nicht über `wp_cache_*`.
- **Bestehende Konventionen:** `Theme/CLAUDE.md` (Funktionsübersicht der
  `functions.php`, Build- und Syntaxprüfungsregeln, Regeln zum plastischen
  Look), Wurzel-`CLAUDE.md` (Farbschema, Plugin-Zusammenspiel),
  `Theme/docs/ERWEITERUNGSANALYSE-Seitenindex.md` (vollständige
  Ursachenanalyse und Dateiliste – **vor Phase 2 lesenswert**).
- **Harte Grenzen:**
  - Kein PHP-Sprachmittel ab 8.0 (keine Union Types, kein `match`, keine
    Constructor Property Promotion, keine Enums, kein Nullsafe-Operator).
  - Funktionspräfix `simple_clean_` für alle neuen PHP-Funktionen.
  - Assets immer mit `file_exists()`-Guard und `filemtime()` als Version
    einhängen, nie mit fester Versionsnummer.
  - Farben ausschließlich über CSS-Variablen, keine festen Hexwerte im
    neuen CSS.
  - Alle Ausgaben durch `esc_html()` / `esc_attr()` / `esc_url()`.
- **Testumgebung: die Live-Site.** Es existiert keine lokale
  WordPress-Installation. Getestet wird, indem das gebaute ZIP unter
  Design → Themes → Theme hochladen eingespielt und die Website im Browser
  geprüft wird. Daraus folgt für **jedes** AP mit PHP-Änderung:
  1. `php -l` über alle PHP-Dateien im Theme (Pflicht, siehe Abschnitt 0).
  2. Nach dem Hochladen drei Sichtprüfungen: Startseite lädt · eine normale
     Skriptenseite mit Sidebar lädt · WP-Admin lädt. Keine weiße Seite,
     keine sichtbare PHP-Meldung.
  3. Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.
  4. Bei Problemen sofort das Rollback-ZIP der laufenden Phase einspielen
     (Abschnitt 5).
- **Messwerkzeug:** In AP-1.2 entsteht eine schlanke Messausgabe, die nur für
  angemeldete Administratoren und nur mit dem Parameter `?sc_perf=1` greift.
  Sie ist die Grundlage aller Vorher-/Nachher-Vergleiche in diesem Plan und
  bleibt dauerhaft im Theme.
- **Git-Strategie:** Repository liegt in `Theme/` (Remote siehe unten),
  Hauptbranch `main`. Branch pro Phase (`phase-1-glossar-fallback`,
  `phase-2-seitenindex`, `phase-3-block`, `phase-4-design`,
  `phase-5-umstellung`), mindestens ein Commit pro AP mit AP-ID im Text,
  Push nach jedem AP.
- **Remote-Repository:** https://github.com/Cyric25/FOS_Skripten_Website_Design.git
  (bereits verbunden, kein Einrichtungs-AP nötig).
- **Ausgangszustand:** Der Arbeitsbaum enthält beim Planstart nicht committete
  Änderungen (Version 1.5.68: plastischer Look, Cache-Busting-Fix). AP-1.1
  sichert diesen Stand als eigenen Commit auf `main`, bevor irgendetwas
  verzweigt wird.

## 4. Architekturentscheidungen

| Entscheidung | Begründung | Verworfene Alternative |
|---|---|---|
| Eigener Block `fos/inhaltsverzeichnis` statt `render_block`-Filter auf `core/page-list` | Explizit im Editor sichtbar, pro Einfügung konfigurierbar, unabhängig von Core-Änderungen. Der Umstieg ist ein Blocktausch und jederzeit umkehrbar. | Core-Block per Filter kapern – unsichtbar für Redakteure, bricht bei Core-Umbauten, keine eigenen Optionen |
| `rootPage` ist Kernfunktion, nicht Kür | Es gibt drei Kapitelübersichten, die jeweils nur ihren eigenen Teilbaum zeigen (siehe Ist-Stand in Abschnitt 3). Ein Verzeichnis über alle 258 Seiten wäre auf keiner der drei Seiten gewollt. `rootPage` wird im Editor je Einfügung gesetzt und ist damit eine **feste Zahl im Blockattribut** – die Ausgabe bleibt requestunabhängig und der Fragment-Cache gültig. | „Automatisch die Seite nehmen, auf der der Block steht" – bequemer, macht die Ausgabe aber requestabhängig. Wäre nur zulässig, wenn die aufgelöste Seiten-ID in den Cache-Schlüssel eingeht; das ist als spätere Bequemlichkeitsoption möglich, aber nicht der Standardweg |
| Index in `wp_options` mit `autoload = false` | Funktioniert ohne persistenten Object-Cache, also auf All-Inkl. Eine autoloadende Option dieser Größe würde auf jedem Request der gesamten Site geladen – schlimmer als das Ausgangsproblem. | `wp_cache_*` (ohne Redis nur pro Request wirksam), eigene DB-Tabelle (Migration ohne Not) |
| Read-Through statt Eager Rebuild | Beim Speichern wird nur ein Versionszähler erhöht; der Neuaufbau passiert beim nächsten Lesezugriff. Eine Drag-Sortierung über 50 Seiten würde sonst 50 Neuaufbauten auslösen. Der Aufbau kostet eine indizierte Abfrage. | Rebuild direkt im `save_post`, Rebuild per WP-Cron (auf Shared Hosting unzuverlässig getaktet) |
| Fragment-Cache mit Indexversion **im Schlüssel** | Invalidierung geschieht automatisch: nach einer Änderung greifen die alten Schlüssel nicht mehr und laufen per TTL aus. Kein Suchlauf über die Options-Tabelle bei jedem Speichern. | Gezieltes Löschen aller Fragment-Transients bei jeder Änderung – teurer Options-Scan bei jedem Save |
| Eigene URI-Berechnung aus der Elternkette statt `get_permalink()` je Seite | `get_permalink()` löst für hierarchische Seiten die Elternkette einzeln auf. Der Index kennt die gesamte Struktur bereits und baut alle URIs in einem Durchlauf. | `get_permalink()` pro Knoten (der teuerste Einzelposten im Core-Block) |
| Blockausgabe ist bewusst **request-unabhängig** | Keine Hervorhebung der aktuellen Seite, keine benutzerabhängigen Inhalte. Nur so ist ein Fragment-Cache über alle Besucher hinweg gültig. | Aktuelle Seite hervorheben – würde den Fragment-Cache pro Seite vervielfachen |
| Glossar-Fix über das vorhandene Meta `_glossar_scan_version` | Das Meta existiert bereits und wird sowohl von `simple_clean_update_glossar_candidates()` als auch vom Bulk-Scan gesetzt. Es unterscheidet zuverlässig „nie gescannt" von „gescannt, null Treffer". | Leeres Array als Sentinel speichern (kollidiert mit `empty()`), eigenes neues Meta einführen (überflüssig) |
| Neue Dateien unter `Theme/includes/` und `Theme/blocks/` statt in `functions.php` | Die `functions.php` hat bereits ~3850 Zeilen. Das Muster existiert: `includes/admin/page-manager.php` wird per `require_once` geladen. | Alles in `functions.php` – erschwert Review und Wiederauffinden |
| Aufklappen über natives `<details>/<summary>` | Barrierefrei, tastaturbedienbar, funktioniert ohne JavaScript, keine Fremdbibliothek (DSGVO). | JS-Akkordeon mit Fremdbibliothek |

## 5. Risiken & Rollback

### 5.1 Risikotabelle

| Risiko | Wahrscheinlichkeit | Auswirkung | Gegenmaßnahme / Rollback |
|---|---|---|---|
| Syntaxfehler in `functions.php` → weiße Seite auf der gesamten Live-Site | mittel | sehr hoch | `php -l` über alle PHP-Dateien ist Pflicht vor jedem ZIP (Abschnitt 0, Regel 7). Rollback: Phasen-Rollback-ZIP einspielen |
| Index veraltet nach Drag-Sortierung im Seitenmanager | hoch (ohne AP-2.3) | mittel | AP-2.3 setzt Invalidierung in allen vier schreibenden AJAX-Handlern; AP-2.4 liefert einen manuellen „Neu aufbauen"-Knopf als Sicherheitsventil |
| Option `simple_clean_page_index` landet autoloadend in der DB und bremst die ganze Site | mittel | hoch | Akzeptanzkriterium in AP-2.1: SQL-Prüfung, dass `autoload` den Wert `no` hat |
| Glossar-Fix nimmt nie gescannten Altseiten die Verlinkung | mittel | mittel | Fix greift nur bei gesetztem `_glossar_scan_version`; AP-1.5 stellt per SQL-Zählung sicher, dass keine Seite ohne dieses Meta übrig bleibt |
| `blocks/`-Verzeichnis fehlt im ZIP → Block auf der Live-Site unbekannt, Seite zeigt Blockfehler | hoch (ohne AP-3.4) | hoch | AP-3.4 erweitert `create-theme-zip.js`; Akzeptanzkriterium listet den ZIP-Inhalt auf |
| Editor-Script bricht, weil Vite ES-Module ausgibt | mittel | mittel | Keine `import`/`export` in `page-index-editor.js`; Muster von `src/js/glossar-editor.js` (Zugriff über `wp.*`-Globals) einhalten |
| Fragment-Cache liefert veraltetes HTML aus | gering | mittel | Indexversion steckt im Transient-Schlüssel; zusätzlich löscht der Rebuild-Knopf aus AP-2.4 alle Fragment-Transients |
| Rollback-ZIP wird vom nächsten Build überschrieben | hoch | hoch | `backup-and-build.js` hält nur EINE Backup-Generation. Jede Phase legt in AP-`<N>`.1 ein eigenes, benanntes Rollback-ZIP ab (siehe 5.3) |
| Live-Test macht einen Fehler sofort öffentlich sichtbar | mittel | mittel | Änderungen in Randzeiten einspielen; nach jedem Upload die drei Sichtprüfungen aus Abschnitt 3; Rollback-ZIP griffbereit halten |

### 5.2 Regressionsliste (nach jeder Phase abzuarbeiten)

| Nr | Prüfung | Bestanden, wenn |
|---|---|---|
| R1 | Normale Skriptenseite mit Sidebar aufrufen | Seitenbaum links erscheint, Aufklappen funktioniert, kein Fatal |
| R2 | Glossarbegriff auf einer Skriptenseite | Begriff ist markiert, Klick öffnet Tooltip/Sidebar mit Definition |
| R3 | Seite im Seitenmanager per Drag verschieben | Erfolgsmeldung erscheint, neue Position bleibt nach Neuladen bestehen |
| R4 | Meta-Box „Seitenleiste (Sidebar) Einstellungen" an einer Seite umschalten | Sidebar verschwindet bzw. erscheint entsprechend |
| R5 | Customizer → Farbeinstellungen, eine Farbe ändern | Vorschau lädt, Farbe wirkt im Frontend |
| R6 | Container-Block (CDB) mit eingebettetem Lerninhalt aufrufen | Rendert wie zuvor, kein JS-Fehler in der Konsole |
| R7 | Bild in einem Beitrag anklicken | Custom-Lightbox öffnet mit Zoom-Animation |
| R8 | `npm run build` im Ordner `Theme/` | Läuft durch, erzeugt `dist/js/*` und `dist/css/*` inklusive aller bestehenden Entries |

### 5.3 Rollback-Strategie

1. **Vor jeder Phase** legt das jeweils erste AP eine benannte Kopie des
   aktuell auf der Live-Site laufenden ZIPs ab:
   `Theme/backups/fos-online-schulbuch-rollback-phase<N>.zip`.
   Das ist nötig, weil `backup-and-build.js` nur eine einzige
   Backup-Generation vorhält und sie bei jedem Build überschreibt.
2. **Bei einem Fehler auf der Live-Site:** dieses ZIP unter
   Design → Themes → Theme hochladen einspielen. Das stellt den Stand vor
   der Phase wieder her.
3. **Im Repository:** Jede Phase liegt auf einem eigenen Branch. Solange
   nicht nach `main` gemerged wurde, ist `main` jederzeit der letzte
   abgenommene Stand.
4. **Datenbank:** Es gibt keine destruktiven DB-Operationen. Der Plan legt
   ausschließlich Optionen, Transients und ein Post-Meta an. Vollständiges
   Aufräumen von Hand, falls je nötig:
   `DELETE FROM wp_options WHERE option_name IN ('simple_clean_page_index','simple_clean_page_index_version');`
   `DELETE FROM wp_options WHERE option_name LIKE '\_transient%sc_pidx\_%';`
   `DELETE FROM wp_postmeta WHERE meta_key = '_simple_clean_hide_from_index';`
   (Tabellenpräfix ggf. anpassen.)

## 6. Phasenübersicht

Jede Phase endet mit `AP-<N>.rev` (unabhängiges Review) und `AP-<N>.doc`
(Dokumentation) – in dieser Reihenfolge nach den Implementierungs-APs.

| Phase | Ziel | Lauffähiger Endzustand | APs |
|---|---|---|---|
| 1 | Glossar-Fallback korrigieren – die Sofortbremse lösen | Die **bestehende** Inhaltsverzeichnisseite mit `core/page-list` lädt messbar schneller; Glossar funktioniert auf allen anderen Seiten unverändert | AP-1.1 … AP-1.6, AP-1.rev, AP-1.doc |
| 2 | Indexschicht aufbauen und korrekt halten | Der Seitenindex existiert, bleibt bei jeder Seitenänderung korrekt und ist über einen Knopf neu aufbaubar; im Frontend ist noch nichts verändert | AP-2.1 … AP-2.4, AP-2.rev, AP-2.doc |
| 3 | Block und Rendering | Der Block `fos/inhaltsverzeichnis` ist im Editor einfügbar, rendert aus dem Index, wird zwischengespeichert und ist im ZIP enthalten | AP-3.1 … AP-3.4, AP-3.rev, AP-3.doc |
| 4 | Gestaltung steuerbar machen | Kapitelkarten mit Aufklappebenen, Suchfeld, Block-Optionen im Editor, globale Regler im Customizer, Seiten einzeln ausschließbar | AP-4.1 … AP-4.4, AP-4.rev, AP-4.doc |
| 5 | Umstellung, Absicherung, Dokumentation | Die echte Inhaltsverzeichnisseite nutzt den neuen Block, die Verbesserung ist gegen die Phase-1-Messung belegt, Datei-Map und Doku sind aktuell | AP-5.1 … AP-5.4, AP-5.rev, AP-5.doc |

## 7. Arbeitspakete

### Phase 1: Glossar-Fallback korrigieren

Hintergrund für alle APs dieser Phase: `simple_clean_glossar_auto_link_content_optimized()`
in `Theme/functions.php` prüft die Kandidatenliste einer Seite mit
`if (empty($candidates) || !is_array($candidates))`. Diese Bedingung kann
„die Seite wurde nie gescannt" nicht von „die Seite wurde gescannt und
enthält keinen einzigen Glossarbegriff" unterscheiden. Im zweiten Fall wird
fälschlich der teure Fallback ausgelöst: **alle** Glossarbegriffe werden
geladen, über `simple_clean_get_glossar_term_variants()` in Wortvarianten
expandiert, zu einem einzigen Alternations-Regex zusammengesetzt und über das
gesamte gerenderte Seiten-HTML geschickt. Genau das passiert auf der
Inhaltsverzeichnisseite, deren `post_content` praktisch nur aus dem
Block-Kommentar besteht und daher keinen Fließtext enthält.

Das Unterscheidungsmerkmal existiert bereits: das Post-Meta
`_glossar_scan_version` wird sowohl von `simple_clean_update_glossar_candidates()`
(Hook `save_post`) als auch vom Bulk-Scan
`simple_clean_glossar_bulk_scan_batch_ajax()` auf den Wert `1` gesetzt.

---

#### AP-1.1: Ausgangszustand sichern und Phasen-Branch anlegen

**Status:** ☑ erledigt (2026-08-08)
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** keine

**Ziel & Kontext:**
Im Git-Arbeitsbaum von `Theme/` liegen nicht committete Änderungen (Theme-Version
1.5.68: plastischer Look für den Sidebar-Streifen, Cache-Busting über
`filemtime()` für `style.css`, ergänzte `CLAUDE.md`). Diese Arbeit ist
abgeschlossen, aber nicht gesichert. Bevor irgendetwas verzweigt wird, muss
dieser Stand als eigener Commit auf `main` liegen – sonst wandern fremde
Änderungen in den Phasen-Branch und Review wie Rollback werden unübersichtlich.
Zusätzlich wird ein benanntes Rollback-ZIP abgelegt, weil `backup-and-build.js`
nur eine einzige Backup-Generation vorhält und sie bei jedem Build überschreibt.

**Betroffene Dateien:**
- `Theme/backups/fos-online-schulbuch-rollback-phase1.zip` (neu, Kopie)
- keine Quelldatei wird inhaltlich verändert

**Vorgehen:**
1. Im Ordner `Theme/`: `git status` und `git diff --stat` ausführen und den
   Umfang in der Übergabenotiz festhalten.
2. Prüfen, dass `package.json` und `style.css` beide die Version `1.5.68`
   tragen (sie müssen übereinstimmen).
3. `php -l` über jede PHP-Datei in `Theme/` und `Theme/includes/` laufen
   lassen. Bei einem Fehler abbrechen und melden – nicht committen.
4. Alles committen:
   `git add -A` und
   `git commit -m "v1.5.68: Plastischer Look Sidebar-Streifen, Cache-Busting style.css"`
   Der Ordner `docs/` (mit `ERWEITERUNGSANALYSE-Seitenindex.md` und dieser
   `PLAN-Seitenindex.md`) wird mit committet. Ein eventuell vorhandener
   Ordner `.claude/` wird **nicht** committet – falls er nicht bereits in
   `.gitignore` steht, dort ergänzen.
5. `git push origin main`.
6. Falls unter `Theme/dist/fos-online-schulbuch.zip` ein ZIP existiert: nach
   `Theme/backups/fos-online-schulbuch-rollback-phase1.zip` **kopieren**
   (nicht verschieben). Existiert keines, einmal `npm run build` ausführen
   und danach kopieren; in dem Fall in der Übergabenotiz vermerken, dass
   sich die Versionsnummer dabei auf 1.5.69 erhöht hat.
7. Phasen-Branch anlegen: `git checkout -b phase-1-glossar-fallback` und
   `git push -u origin phase-1-glossar-fallback`.

**Akzeptanzkriterien:**
- [ ] `git status` im Ordner `Theme/` meldet einen sauberen Arbeitsbaum (bis auf ignorierte Dateien).
- [ ] `git log -1 --stat` auf `main` zeigt den Commit mit `functions.php`, `style.css`, `package.json`, `CLAUDE.md`.
- [ ] `main` ist zum Remote gepusht (`git status` meldet kein „ahead of origin/main").
- [ ] Die Datei `Theme/backups/fos-online-schulbuch-rollback-phase1.zip` existiert und ist größer als 50 KB.
- [ ] Der aktive Branch ist `phase-1-glossar-fallback` und existiert auch am Remote.

**Tests:**
- Smoke-Test: `git log --oneline -n 3` zeigt den neuen Commit an oberster Stelle.
- Prüfschritt: `php -l` über alle PHP-Dateien meldet „No syntax errors detected" für jede Datei.
- Prüfschritt: Die Rollback-ZIP-Datei lässt sich öffnen und enthält im Wurzelverzeichnis den Ordner `fos-online-schulbuch/` mit `style.css` und `functions.php`.

**Übergabenotiz:** (2026-08-08)

**Was geändert wurde:**
- Commit `c9322e2` auf `main`: „v1.5.68: Plastischer Look Sidebar-Streifen,
  Cache-Busting style.css" – 7 Dateien, 3314 Einfügungen, 18 Löschungen.
  Enthalten: `style.css`, `functions.php`, `package.json`, `CLAUDE.md`,
  `.gitignore` sowie neu `docs/ERWEITERUNGSANALYSE-Seitenindex.md` und
  `docs/PLAN-Seitenindex.md`. Nach `origin/main` gepusht
  (`d252f81..c9322e2`).
- `.gitignore`: Eintrag `.claude/` im Abschnitt „IDE / Editor" ergänzt. Der
  Ordner war zuvor unversioniert und wäre sonst mit committet worden.
- `backups/fos-online-schulbuch-rollback-phase1.zip` angelegt (Kopie von
  `dist/fos-online-schulbuch.zip`, 79 145 Bytes, 22 Einträge, `style.css`
  meldet Version 1.5.68).
- Branch `phase-1-glossar-fallback` von `main` abgezweigt und mit
  `-u origin` gepusst. Aktiver Branch. Beide Branches stehen auf `c9322e2`.

**Getroffene Entscheidungen:**
- `npm run build` war **nicht** nötig: `dist/fos-online-schulbuch.zip` existierte
  bereits und trägt Version 1.5.68, passend zum committeten Stand. Die
  Versionsnummer wurde daher **nicht** erhöht – sie steht weiterhin auf
  1.5.68 in `package.json` und `style.css`.

**Für Folge-APs relevant:**
- **Kein PHP 7.4 verfügbar.** `php` im Pfad ist Version 8.5.1
  (`/c/php/php`), unter `C:\allinkl-testserver\php\` liegt nur 8.3. `php -l`
  weist damit Syntaxfehler zuverlässig nach, kann aber **nicht** erkennen,
  wenn versehentlich PHP-8-only-Syntax verwendet wird (`match`, Union Types,
  Nullsafe-Operator, Constructor Property Promotion). Die Zielumgebung
  verlangt laut `style.css` PHP 7.4. AP-1.3, AP-1.4, AP-2.1 und AP-3.2
  müssen das **durch Codelektüre** sicherstellen; `php -l` allein genügt hier
  nicht. Die Review-APs prüfen es ohnehin gesondert.
- Der Befehl für die Syntaxprüfung über alle PHP-Dateien (Git Bash, im Ordner
  `Theme/`), `node_modules` ausgenommen:
  `for f in $(find . -path ./node_modules -prune -o -name "*.php" -print); do php -l "$f"; done`
  Geprüft werden damit 11 Dateien: 9 im Wurzelverzeichnis plus
  `includes/admin/clipboard-uploader.php` und `includes/admin/page-manager.php`.
  `functions.php.backup` wird nicht erfasst (keine `.php`-Endung) – das ist so
  gewollt, die Datei ist ein Altbestand und nicht Teil des Themes.
- `dist/` und `*.zip` sind in `.gitignore`; das Rollback-ZIP unter `backups/`
  wird also **nicht** versioniert. Es existiert nur lokal. Vor einem
  Rechnerwechsel wäre es gesondert zu sichern.
- Die Live-Site wurde in diesem AP **nicht** angefasst – es gab keine
  Codeänderung, die eingespielt werden müsste. Der auf der Website laufende
  Stand entspricht dem Rollback-ZIP.

---

#### AP-1.2: Messbasis für die Inhaltsverzeichnisseite schaffen

**Status:** ◐ in Arbeit — Code fertig und gebaut (v1.5.69, Commit `36ad832`);
offen sind Schritt 5 (ZIP einspielen), Schritt 6 (Messung) und Schritt 7
(Seiten- und Begriffsanzahl). Diese Schritte verlangen Zugriff auf die
Live-Site und werden vom Nutzer ausgeführt.
**Umfang:** M
**Modell:** sonnet
**Abhängigkeiten:** AP-1.1

**Ziel & Kontext:**
Ohne Zahlen lässt sich der Erfolg dieses Vorhabens nicht belegen und nicht
gegen Rückschritte absichern. Es gibt keine lokale Testinstallation und keinen
SSH-Zugang, also entsteht eine schlanke Messausgabe direkt im Theme, die nur
für angemeldete Administratoren und nur auf ausdrückliche Anforderung greift.
Sie bleibt dauerhaft im Theme und wird in AP-1.6 und AP-5.2 erneut verwendet.

**Betroffene Dateien:**
- `Theme/functions.php` (ändern – neue Funktion am Dateiende, vor eventuell vorhandenem Schlusskommentar)

**Vorgehen:**
1. Neue Funktion `simple_clean_perf_footer()` in `Theme/functions.php`
   anlegen, eingehängt mit `add_action('wp_footer', 'simple_clean_perf_footer', 9999)`.
2. Die Funktion gibt **nur** dann etwas aus, wenn beide Bedingungen erfüllt
   sind: `current_user_can('manage_options')` und
   `isset($_GET['sc_perf'])`. Andernfalls sofort `return`.
3. Ausgegeben wird ein HTML-Kommentar in genau diesem Format (eine Zeile,
   damit er sich leicht aus dem Quelltext kopieren lässt):
   `<!-- SC-PERF queries=<n> time=<sekunden>s peak=<speicher> -->`
   Werte: `get_num_queries()`, `timer_stop(0, 3)`,
   `size_format(memory_get_peak_usage(true))`.
4. Die Funktion enthält einen erklärenden Kommentarblock darüber, wozu sie
   dient und dass sie bewusst dauerhaft im Theme bleibt.
5. `php -l Theme/functions.php` ausführen, dann `npm run build` im Ordner
   `Theme/` und das ZIP auf der Live-Site einspielen.
6. Messung durchführen und die Werte im Testprotokoll (Abschnitt 9) und in
   der Übergabenotiz festhalten. Gemessen wird als angemeldeter Administrator,
   je URL **drei** Aufrufe mit hartem Neuladen (Strg+Shift+R), notiert wird
   der mittlere der drei Werte:
   - a) Die Inhaltsverzeichnisseite mit `?sc_perf=1`.
   - b) Eine gewöhnliche Skriptenseite mit Fließtext und Sidebar, ebenfalls
     mit `?sc_perf=1` – als Vergleichswert.
   - c) Die Startseite mit `?sc_perf=1` – als zweiter Vergleichswert.
7. Zusätzlich notieren: die ungefähre Gesamtzahl veröffentlichter Seiten
   (WP-Admin → Seiten, Zähler „Veröffentlicht") und die Anzahl der
   Glossareinträge (WP-Admin → Glossar).

**Akzeptanzkriterien:**
- [ ] Aufruf der Inhaltsverzeichnisseite **ohne** `?sc_perf=1` enthält im Seitenquelltext keinen `SC-PERF`-Kommentar.
- [ ] Aufruf als abgemeldeter Besucher **mit** `?sc_perf=1` enthält ebenfalls keinen `SC-PERF`-Kommentar.
- [ ] Aufruf als Administrator mit `?sc_perf=1` enthält genau eine `SC-PERF`-Zeile mit drei gefüllten Werten.
- [ ] Für alle drei URLs (a, b, c) sind Queryzahl, Zeit und Speicher im Testprotokoll eingetragen.
- [ ] Seitenanzahl und Glossar-Begriffsanzahl sind in der Übergabenotiz festgehalten.

**Tests:**
- Smoke-Test: Nach dem Einspielen des ZIPs laden Startseite, eine Skriptenseite mit Sidebar und das WP-Admin ohne weiße Seite.
- Prüfschritt: Seitenquelltext der Inhaltsverzeichnisseite mit `?sc_perf=1` per „Seitenquelltext anzeigen" öffnen und nach `SC-PERF` suchen – genau ein Treffer.
- Prüfschritt: Fehlerlog im All-Inkl-KAS nach dem Test auf neue Warnings/Notices prüfen; es dürfen keine mit Bezug zu `simple_clean_perf_footer` auftreten.

**Übergabenotiz:**

---

#### AP-1.3: Kandidaten-Fallback im Autolinker an `_glossar_scan_version` koppeln

**Status:** ◐ in Arbeit — Code fertig, Verzweigungslogik mit einem Stub-Harness
gegen 6 Fälle geprüft (alle bestanden). Offen: Build und Live-Prüfung. Diese
warten bewusst, bis die Ausgangsmessung aus AP-1.2 auf v1.5.69 vorliegt —
sobald der Fix ausgeliefert ist, lässt sich der Vorher-Wert nicht mehr
erheben.
**Umfang:** M
**Modell:** opus (die Änderung verändert das Verhalten aller Seiten mit leerer Kandidatenliste – die Bedingung muss exakt sitzen)
**Abhängigkeiten:** AP-1.2

**Ziel & Kontext:**
In `Theme/functions.php` steht in der Funktion
`simple_clean_glossar_auto_link_content_optimized($content)` die Bedingung:

```php
$candidates = get_post_meta($post_id, '_glossar_term_candidates', true);

if (empty($candidates) || !is_array($candidates)) {
    // Fallback: Verwende alle Glossar-Begriffe
    $all_terms = simple_clean_get_glossar_terms();
    ...
    $candidates = array_column($all_terms, 'id');
}
```

Ein leeres Kandidaten-Array ist ein **gültiges Scan-Ergebnis** („diese Seite
enthält keinen Glossarbegriff") und darf nicht in den Fallback führen. Der
Fallback ist ausschließlich für Seiten gedacht, die nie gescannt wurden. Das
Unterscheidungsmerkmal ist das Post-Meta `_glossar_scan_version`, das von
`simple_clean_update_glossar_candidates()` und vom Bulk-Scan auf `1` gesetzt
wird.

**Betroffene Dateien:**
- `Theme/functions.php` (ändern – nur die Funktion `simple_clean_glossar_auto_link_content_optimized`)

**Vorgehen:**
1. Direkt nach dem Laden von `$candidates` zusätzlich lesen:
   `$scan_version = get_post_meta($post_id, '_glossar_scan_version', true);`
2. Die Fallback-Bedingung so umbauen, dass sie greift, wenn **keine gültige
   Scan-Version** vorliegt **oder** die Kandidaten kein Array sind:
   - Ist `$scan_version` nicht leer (also die Seite wurde gescannt) und
     `$candidates` ein Array, dann gelten die Kandidaten als maßgeblich –
     auch wenn das Array leer ist.
   - Ist das Kandidaten-Array in diesem Fall leer, wird `$content`
     **unverändert** zurückgegeben (früher Ausstieg, ohne
     `simple_clean_process_glossar_links_optimized()` aufzurufen).
   - Nur wenn `$scan_version` leer ist oder `$candidates` kein Array ist,
     läuft der bisherige Fallback über alle Begriffe.
3. Den bestehenden Debug-Log-Aufruf im Fallback-Zweig erhalten und seine
   Meldung so ergänzen, dass sie „nie gescannt" ausdrückt statt „keine
   Kandidaten".
4. Über der geänderten Bedingung einen Kommentarblock einfügen, der erklärt,
   warum `empty()` allein nicht genügt und welche Rolle
   `_glossar_scan_version` spielt. Der nächste Bearbeiter darf die Bedingung
   nicht versehentlich „vereinfachen".
5. Die Funktion `simple_clean_process_glossar_links_optimized()` bleibt
   unverändert – ihr eigener `empty($candidates)`-Ausstieg ist an dieser
   Stelle korrekt.

**Akzeptanzkriterien:**
- [ ] Eine Seite mit gesetztem `_glossar_scan_version` und leerem `_glossar_term_candidates` erzeugt im Frontend **kein** einziges `<span class="glossar-term">`.
- [ ] Eine Seite mit gesetztem `_glossar_scan_version` und gefüllter Kandidatenliste verlinkt ihre Begriffe unverändert wie zuvor.
- [ ] Eine Seite **ohne** `_glossar_scan_version` verhält sich unverändert (Fallback über alle Begriffe greift weiterhin).
- [ ] `php -l Theme/functions.php` meldet keinen Syntaxfehler.
- [ ] Die Funktion `simple_clean_process_glossar_links_optimized()` ist unverändert (per `git diff` nachweisbar).

**Tests:**
- Smoke-Test: ZIP bauen und einspielen; Startseite, eine Skriptenseite mit Sidebar und das WP-Admin laden ohne weiße Seite.
- Prüfschritt R2 (Regression): Eine Skriptenseite mit bekannten Glossarbegriffen aufrufen. Die Begriffe sind weiterhin hervorgehoben, ein Klick öffnet Tooltip bzw. Sidebar mit der Definition.
- Prüfschritt: Die Inhaltsverzeichnisseite aufrufen und im Seitenquelltext nach `glossar-term` suchen – es darf kein Treffer erscheinen, sofern die Seite bereits gescannt wurde (falls doch Treffer erscheinen, ist die Seite noch nicht gescannt; das behebt AP-1.5, hier dann nur vermerken).
- Prüfschritt: Messung mit `?sc_perf=1` auf der Inhaltsverzeichnisseite wiederholen und die Werte in der Übergabenotiz dem Ergebnis aus AP-1.2 gegenüberstellen.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.

**Übergabenotiz:**

---

#### AP-1.4: Gleiche Korrektur für die Auslieferung von `glossarData`

**Status:** ☐ offen
**Umfang:** S
**Modell:** opus (dieselbe Fallback-Logik, dieselbe Sorgfalt bei der Bedingung)
**Abhängigkeiten:** AP-1.3

**Ziel & Kontext:**
Die Funktion `simple_clean_glossar_assets()` in `Theme/functions.php` enthält
denselben Denkfehler wie der Autolinker. Sie ermittelt die Begriffe, die per
`wp_localize_script()` als `glossarData` an das Frontend-JavaScript übergeben
werden:

```php
$candidates = get_post_meta(get_the_ID(), '_glossar_term_candidates', true);
if (is_array($candidates)) {
    $terms_for_page = simple_clean_get_glossar_terms_by_ids($candidates);
} else {
    // Not scanned yet: same fallback as the server-side linking
    $terms_for_page = simple_clean_get_glossar_terms();
}
```

Hier ist zwar `is_array()` statt `empty()` geprüft, die Bedingung stützt sich
aber weiterhin nur auf das Kandidaten-Meta. Damit sie zum Verhalten aus AP-1.3
passt und dieselbe Entscheidung trifft, muss auch hier `_glossar_scan_version`
ausschlaggebend sein. Ohne die Angleichung könnten Autolinker und Asset-Ausgabe
auf derselben Seite unterschiedlich entscheiden. Zusätzlich lädt der Fallback
alle Begriffe **samt Definitionen** und schickt sie als JSON an den Browser –
auf einer textarmen Seite ist das reine Verschwendung.

**Betroffene Dateien:**
- `Theme/functions.php` (ändern – nur die Funktion `simple_clean_glossar_assets`)

**Vorgehen:**
1. `$scan_version = get_post_meta(get_the_ID(), '_glossar_scan_version', true);`
   zusätzlich laden.
2. Entscheidungslogik angleichen: Ist `$scan_version` nicht leer und
   `$candidates` ein Array, gelten die Kandidaten – ein leeres Array führt zu
   `$terms_for_page = array()`. Nur wenn `$scan_version` leer ist oder
   `$candidates` kein Array ist, greift der Fallback auf alle Begriffe.
3. Der bestehende `if (!empty($terms_for_page) && file_exists($glossar_js))`-Guard
   sorgt bereits dafür, dass bei leerem Ergebnis weder Script noch `glossarData`
   ausgeliefert werden – dieser Guard bleibt unverändert.
4. Das Einhängen des Stylesheets `dist/css/glossar-style.css` bleibt
   unverändert (es wird bewusst immer geladen).
5. Kommentar analog zu AP-1.3 ergänzen, der auf die Kopplung an
   `_glossar_scan_version` und die Spiegelung des Autolinker-Verhaltens
   hinweist.

**Akzeptanzkriterien:**
- [ ] Auf der Inhaltsverzeichnisseite enthält der Seitenquelltext **kein** `glossarData`-Script-Element und **kein** `dist/js/glossar.js`.
- [ ] Auf einer Skriptenseite mit Glossarbegriffen enthält der Seitenquelltext weiterhin `glossarData` mit den Begriffen dieser Seite.
- [ ] Das Stylesheet `glossar-style.css` wird auf beiden Seiten weiterhin geladen.
- [ ] `php -l Theme/functions.php` meldet keinen Syntaxfehler.

**Tests:**
- Smoke-Test: ZIP bauen und einspielen; Startseite, Skriptenseite und WP-Admin laden ohne weiße Seite.
- Prüfschritt R2 (Regression): Auf einer Skriptenseite einen Glossarbegriff anklicken – Tooltip bzw. Sidebar öffnet mit Definition. Browser-Konsole ohne Fehler.
- Prüfschritt: Auf der Inhaltsverzeichnisseite die Browser-Konsole prüfen – es darf kein Fehler wegen fehlender Variable `glossarData` erscheinen (das JS wird in diesem Fall gar nicht geladen).
- Prüfschritt: Messung mit `?sc_perf=1` auf der Inhaltsverzeichnisseite wiederholen; zusätzlich die übertragene Dokumentgröße im Netzwerk-Tab der Entwicklerwerkzeuge notieren und mit AP-1.2 vergleichen.

**Übergabenotiz:**

---

#### AP-1.5: Flächendeckenden Scan-Stand herstellen und nachweisen

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet
**Abhängigkeiten:** AP-1.4

**Ziel & Kontext:**
Die Korrekturen aus AP-1.3 und AP-1.4 stützen sich auf das Post-Meta
`_glossar_scan_version`. Seiten, die nie gescannt wurden, fallen weiterhin in
den teuren Fallback. Damit die Wirkung flächendeckend eintritt und keine Seite
in einem unklaren Zustand bleibt, muss der vorhandene Bulk-Scan einmal über
alle Seiten und Beiträge laufen. Das Werkzeug existiert bereits: die
Glossar-Einstellungsseite im WP-Admin startet über die AJAX-Aktionen
`glossar_bulk_scan` und `glossar_bulk_scan_batch` einen Durchlauf in Stapeln zu
je zehn Beiträgen; dabei werden `_glossar_term_candidates`,
`_glossar_scan_version` und `_glossar_last_scanned` gesetzt. Es ist **nichts zu
programmieren** – dieses AP führt den Scan durch und weist das Ergebnis nach.

**Betroffene Dateien:**
- keine Codeänderung; Ergebnis ist der Nachweis im Testprotokoll

**Vorgehen:**
1. Im WP-Admin die Glossar-Einstellungsseite öffnen (Untermenü des
   Glossar-Eintrags) und dort den Bulk-Scan starten. Den Durchlauf bis zum
   Ende laufen lassen und die gemeldete Gesamtzahl verarbeiteter Beiträge
   notieren.
2. Über phpMyAdmin im All-Inkl-KAS nachweisen, dass keine Seite ohne
   Scan-Version übrig ist. Abfrage (Tabellenpräfix ggf. anpassen):

   ```sql
   SELECT COUNT(*) AS ungescannt
   FROM wp_posts p
   WHERE p.post_type IN ('post','page')
     AND p.post_status IN ('publish','draft','pending')
     AND NOT EXISTS (
       SELECT 1 FROM wp_postmeta m
       WHERE m.post_id = p.ID AND m.meta_key = '_glossar_scan_version'
     );
   ```

   Das Ergebnis muss `0` sein. Ist es größer als 0, den Bulk-Scan erneut
   starten (er arbeitet in Stapeln und kann bei einem Timeout abgebrochen
   sein) und danach erneut zählen.
3. Zusätzlich zur Einordnung erheben, wie viele Seiten ein leeres
   Kandidaten-Array haben – das sind die Seiten, die von AP-1.3 profitieren:

   ```sql
   SELECT COUNT(*) AS ohne_begriffe
   FROM wp_postmeta
   WHERE meta_key = '_glossar_term_candidates'
     AND meta_value IN ('a:0:{}', '');
   ```

4. Stichprobe: Drei zufällige Skriptenseiten im Frontend aufrufen und prüfen,
   dass die Glossarverlinkung dort weiterhin funktioniert.
5. In `Theme/CLAUDE.md` im Abschnitt zum Glossar-System einen kurzen Hinweis
   ergänzen: Nach einem Import von Seiten direkt in die Datenbank (also ohne
   `save_post`) muss der Bulk-Scan erneut laufen, sonst fallen diese Seiten in
   den teuren Fallback.

**Akzeptanzkriterien:**
- [ ] Die Zählabfrage aus Schritt 2 liefert `0`.
- [ ] Die Anzahl aus Schritt 3 ist im Testprotokoll festgehalten.
- [ ] Drei stichprobenartig geprüfte Skriptenseiten zeigen weiterhin verlinkte Glossarbegriffe.
- [ ] `Theme/CLAUDE.md` enthält den Hinweis zum erneuten Bulk-Scan nach direktem DB-Import.

**Tests:**
- Smoke-Test: Der Bulk-Scan läuft im WP-Admin ohne Fehlermeldung bis zum Ende durch.
- Prüfschritt: Beide SQL-Abfragen ausführen und die Ergebnisse ins Testprotokoll eintragen.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf während des Scans entstandene Fehler prüfen.

**Übergabenotiz:**

---

#### AP-1.6: Nachmessung und Phasenabschluss Phase 1

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-1.5

**Ziel & Kontext:**
Die Wirkung von Phase 1 wird gegen die in AP-1.2 erhobene Ausgangsmessung
belegt, und der Phasenstand wird sauber gebaut und gesichert. Dieses AP führt
zugleich den Integrationstest und den Regressionscheck der Phase durch.

**Betroffene Dateien:**
- keine Quelldatei; erzeugt werden Build-Artefakte und Protokolleinträge

**Vorgehen:**
1. Im Ordner `Theme/`: `php -l` über alle PHP-Dateien in der Wurzel und in
   `includes/`. Bei null Fehlern `npm run build` ausführen. Die Version
   erhöht sich dabei automatisch – **nicht von Hand ändern**.
2. Das erzeugte ZIP `dist/fos-online-schulbuch.zip` auf der Live-Site
   einspielen.
3. Messung wie in AP-1.2 wiederholen: dieselben drei URLs, je drei Aufrufe
   mit hartem Neuladen, mittleren Wert notieren. Ergebnisse als
   Vorher-/Nachher-Tabelle in die Übergabenotiz und ins Testprotokoll.
4. Regressionsliste R1 bis R8 aus Abschnitt 5.2 vollständig abarbeiten und
   jedes Ergebnis einzeln im Testprotokoll vermerken.
5. Commit und Push auf `phase-1-glossar-fallback`.

**Akzeptanzkriterien:**
- [ ] Vorher-/Nachher-Werte für alle drei URLs liegen im Testprotokoll vor.
- [ ] Die Queryzahl der Inhaltsverzeichnisseite ist gegenüber AP-1.2 nicht gestiegen.
- [ ] Alle acht Regressionsprüfungen R1–R8 sind mit Ergebnis dokumentiert und bestanden.
- [ ] `dist/fos-online-schulbuch.zip` existiert und ist auf der Live-Site eingespielt.
- [ ] Der Branch `phase-1-glossar-fallback` ist zum Remote gepusht.

**Tests:**
- Smoke-Test: Nach dem Einspielen laden Startseite, Skriptenseite mit Sidebar, Inhaltsverzeichnisseite und WP-Admin ohne weiße Seite.
- Integrationstest der Phase: Die Inhaltsverzeichnisseite lädt vollständig, zeigt weiterhin alle Seiten der `core/page-list`-Ausgabe und enthält im Quelltext weder `glossar-term`-Spans noch `glossarData`.
- Regressionscheck: R1 bis R8 aus Abschnitt 5.2.
- Prüfschritt: Fehlerlog im All-Inkl-KAS nach allen Tests auf neue Einträge prüfen.

**Übergabenotiz:**

---

#### AP-1.rev: Unabhängiges Review Phase 1

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus
**Abhängigkeiten:** AP-1.1, AP-1.2, AP-1.3, AP-1.4, AP-1.5, AP-1.6

**Ziel & Kontext:**
Unabhängige Qualitätsprüfung der Phase 1 durch einen Agenten, der an keiner
Implementierung beteiligt war. Nur lesend arbeiten (Dateien ansehen, suchen) –
KEINE Datei verändern, kein Build, kein Upload.

**Vorgehen:**
1. Für jedes Implementierungs-AP der Phase (AP-1.1 bis AP-1.6): den Code in
   `Theme/functions.php` gegen dessen Akzeptanzkriterien prüfen. Stichproben
   im Quelltext nehmen, nicht nur den Übergabenotizen glauben.
2. Besonders prüfen: Greift der Fallback in
   `simple_clean_glossar_auto_link_content_optimized()` wirklich nur noch bei
   fehlendem `_glossar_scan_version`? Gibt es einen Pfad, auf dem eine
   gescannte Seite mit leerer Kandidatenliste doch noch in den teuren Zweig
   läuft? Entscheiden `simple_clean_glossar_assets()` und der Autolinker auf
   derselben Seite garantiert gleich?
3. Phasen-Endzustand prüfen: Lädt die bestehende Inhaltsverzeichnisseite
   messbar schneller, und funktioniert das Glossar auf normalen Seiten
   unverändert? Belege aus dem Testprotokoll heranziehen.
4. Scope-Check: Wurde ausschließlich die Fallback-Bedingung geändert? Wurde
   `simple_clean_process_glossar_links_optimized()` tatsächlich nicht
   angefasst? Wurden Versionsnummern von Hand verändert (unerwünscht)?
5. Qualitäts-Check: Sind die neuen Kommentarblöcke verständlich? Ist
   `simple_clean_perf_footer()` wirklich doppelt abgesichert
   (`current_user_can` **und** Parameter)? Gibt PHP 7.4 nichts zu beanstanden?
6. Befunde als Review-Bericht in die Übergabenotiz: je Befund Schweregrad
   (kritisch / mittel / gering), betroffenes AP, Datei und Fundstelle.

**Akzeptanzkriterien:**
- [ ] Jedes Implementierungs-AP der Phase wurde gegen seine Akzeptanzkriterien geprüft.
- [ ] Alle Befunde mit Schweregrad, Datei und Fundstelle dokumentiert.
- [ ] Keine Datei wurde verändert (`git status` zeigt keinen neuen Diff).

**Tests:**
- entfällt (Review-AP; das Ergebnis ist der Bericht).

**Übergabenotiz:**

---

#### AP-1.doc: Dokumentation Phase 1 aktualisieren

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-1.rev

**Ziel & Kontext:**
`Theme/CLAUDE.md` auf den Stand nach Phase 1 bringen, damit das korrigierte
Glossar-Verhalten und die neue Messausgabe ohne Kenntnis dieses Plans
nachvollziehbar sind. Eine `reference_file_map.md` existiert für das Theme
noch nicht; sie entsteht in AP-5.3. Bis dahin werden Dateiänderungen in den
Übergabenotizen gesammelt.

**Betroffene Dateien:**
- `Theme/CLAUDE.md` (ändern)
- `Theme/docs/PLAN-Seitenindex.md` (ändern – Statustabelle, Testprotokoll, Datum im Kopf)

**Vorgehen:**
1. Alle Übergabenotizen der Phase 1 durchgehen.
2. In `Theme/CLAUDE.md` im Abschnitt „Glossar-System" ergänzen: Die
   Kandidatenliste ist nur in Verbindung mit `_glossar_scan_version`
   aussagekräftig; ein leeres Kandidaten-Array bedeutet „keine Begriffe auf
   dieser Seite" und führt bewusst **nicht** mehr in den Fallback über alle
   Begriffe. Warum das wichtig ist (textarme Seiten wie das
   Inhaltsverzeichnis) in zwei Sätzen erklären.
3. Einen neuen kurzen Abschnitt „Performance-Messung" aufnehmen: Aufruf einer
   beliebigen URL mit `?sc_perf=1` als Administrator gibt im Seitenquelltext
   eine Zeile `<!-- SC-PERF queries=… time=… peak=… -->` aus; implementiert
   in `simple_clean_perf_footer()`.
4. Statustabelle (Abschnitt 8) und Testprotokoll (Abschnitt 9) dieses Plans
   auf den Stand bringen, „Letzte Aktualisierung" im Dateikopf setzen.
5. Branch `phase-1-glossar-fallback` nach `main` mergen und `main` pushen.

**Akzeptanzkriterien:**
- [ ] `Theme/CLAUDE.md` beschreibt die Rolle von `_glossar_scan_version` und die Messausgabe `?sc_perf=1`.
- [ ] Kein Verweis in `Theme/CLAUDE.md` zeigt auf eine nicht existierende Funktion (Stichprobe: die drei zuletzt genannten Funktionsnamen im Quelltext suchen).
- [ ] Statustabelle und Testprotokoll dieses Plans sind für alle APs der Phase 1 gefüllt.
- [ ] `phase-1-glossar-fallback` ist nach `main` gemerged und `main` gepusht.

**Tests:**
- Stichprobe: Zwei in `Theme/CLAUDE.md` neu genannte Funktionsnamen im Quelltext von `Theme/functions.php` suchen – beide müssen existieren.

**Übergabenotiz:**

---

### Phase 2: Indexschicht

Diese Phase baut die Datengrundlage. Im Frontend ändert sich noch **nichts** –
die Inhaltsverzeichnisseite läuft weiterhin über `core/page-list`. Das ist
Absicht: Der Index kann in Ruhe auf Korrektheit geprüft werden, bevor
irgendetwas davon abhängt.

Gemeinsame Vereinbarungen für alle APs dieser Phase (verbindliche
Schnittstelle, auf die Phase 3 aufbaut):

**Datei:** `Theme/includes/page-index.php`, geladen aus `Theme/functions.php`
per `require_once get_template_directory() . '/includes/page-index.php';`
neben den bestehenden `require_once`-Zeilen für
`includes/admin/page-manager.php` und `includes/admin/clipboard-uploader.php`.

**Optionen:**
- `simple_clean_page_index` – der Index, gespeichert mit
  `update_option($name, $value, false)`, also **ohne** Autoload.
- `simple_clean_page_index_version` – Ganzzahl, wird bei jeder Invalidierung
  um 1 erhöht. Klein, Autoload erlaubt.

**Konstante:** `SIMPLE_CLEAN_PAGE_INDEX_SCHEMA` mit dem Wert `1`, definiert in
`page-index.php`. Sie wandert in den gespeicherten Index; stimmt sie beim Lesen
nicht überein, wird neu gebaut.

**Öffentliche Funktionen (Signaturen sind verbindlich):**

| Funktion | Rückgabe | Zweck |
|---|---|---|
| `simple_clean_get_page_index()` | `array` | Liefert den Index; baut ihn bei Bedarf neu (Read-Through) |
| `simple_clean_build_page_index()` | `array` | Baut den Index aus der Datenbank und speichert ihn |
| `simple_clean_invalidate_page_index()` | `void` | Erhöht den Versionszähler und löscht die Index-Option |
| `simple_clean_get_page_index_version()` | `int` | Aktueller Stand des Versionszählers |

**Struktur des Rückgabewerts von `simple_clean_get_page_index()`:**

```php
array(
    'schema'   => 1,          // int, Wert von SIMPLE_CLEAN_PAGE_INDEX_SCHEMA
    'version'  => 7,          // int, Zählerstand beim Bau
    'built_at' => 1754500000, // int, Unix-Zeit
    'count'    => 412,        // int, Anzahl Knoten
    'nodes'    => array(
        123 => array(
            'id'     => 123,
            'parent' => 12,
            'title'  => 'Aminosäuren',
            'slug'   => 'aminosaeuren',
            'uri'    => 'biochemie/proteine/aminosaeuren',
            'depth'  => 2,
        ),
        // …
    ),
    'children' => array(
        0  => array(12, 45),     // oberste Ebene
        12 => array(123, 124),   // bereits sortiert nach menu_order, dann Titel
    ),
)
```

`uri` ist der Pfad **ohne** führenden und abschließenden Schrägstrich und ohne
Domain. Die fertige URL entsteht erst beim Rendern (Phase 3).

---

#### AP-2.1: Indexaufbau und Speicherung

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus (Datenstruktur und URI-Berechnung sind der Kern der Erweiterung; Fehler hier pflanzen sich in alle Folgephasen fort)
**Abhängigkeiten:** AP-1.doc

**Ziel & Kontext:**
Der Core-Block `core/page-list` ruft `get_pages()` ohne Zwischenspeicherung
auf, lädt dabei alle Seiten als vollständige `WP_Post`-Objekte einschließlich
`post_content` und ruft für jede Seite einzeln `get_permalink()`. Dieses AP
schafft die schlanke Gegenvariante: eine einzige Abfrage über genau fünf
Spalten, eine gemeinsame Berechnung aller Pfade und ein gespeichertes Ergebnis.

Zuerst diesen Branch anlegen: `git checkout main`, `git pull`,
`git checkout -b phase-2-seitenindex`, und
`Theme/dist/fos-online-schulbuch.zip` nach
`Theme/backups/fos-online-schulbuch-rollback-phase2.zip` kopieren.

**Betroffene Dateien:**
- `Theme/includes/page-index.php` (neu)
- `Theme/functions.php` (ändern – eine `require_once`-Zeile)
- `Theme/backups/fos-online-schulbuch-rollback-phase2.zip` (neu, Kopie)

**Vorgehen:**
1. Datei `Theme/includes/page-index.php` anlegen, beginnend mit dem im Projekt
   üblichen Schutz `if (!defined('ABSPATH')) { exit; }` und der Definition
   `define('SIMPLE_CLEAN_PAGE_INDEX_SCHEMA', 1);`.
2. `simple_clean_build_page_index()` implementieren:
   - Eine Abfrage über `$wpdb`:
     `SELECT ID, post_parent, post_title, post_name, menu_order FROM {$wpdb->posts} WHERE post_type = 'page' AND post_status = 'publish' ORDER BY menu_order ASC, post_title ASC`
     Die Abfrage enthält keine Nutzereingabe; `$wpdb->posts` wird über die
     Eigenschaft eingesetzt, nicht als Zeichenkette zusammengebaut.
   - Eine zweite Abfrage für die Ausschlussliste:
     `SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_simple_clean_hide_from_index' AND meta_value = '1'`
     Dieses Meta wird erst in AP-4.4 in der Oberfläche gesetzt; die Abfrage
     liefert bis dahin ein leeres Ergebnis. Das ist beabsichtigt, damit
     AP-4.4 keine Änderung an dieser Datei mehr braucht.
   - Ausgeschlossene Seiten werden samt ihres **gesamten Unterbaums**
     weggelassen: Erst die Knoten aufbauen, dann von jeder ausgeschlossenen
     Seite abwärts alle Nachfahren entfernen.
   - `children`-Abbildung aus `post_parent` aufbauen. Die Reihenfolge aus der
     SQL-Sortierung bleibt dabei erhalten (Einträge in der Lesereihenfolge
     anhängen).
   - **Verwaiste Knoten behandeln:** Zeigt `post_parent` auf eine Seite, die
     nicht im Ergebnis ist (etwa ein nicht veröffentlichtes Elternteil), wird
     der Knoten und sein Unterbaum weggelassen. Das entspricht dem Verhalten
     von WordPress-Permalinks und verhindert unerreichbare Einträge.
   - **Zyklenschutz:** Beim Berechnen von `uri` und `depth` die Elternkette
     auf höchstens 20 Schritte begrenzen. Wird die Grenze erreicht, den Knoten
     weglassen und einmal per `error_log()` melden.
   - `uri` berechnen: Pfad des Elternteils + `/` + `post_name`. Auf oberster
     Ebene ist `uri` gleich `post_name`. Die Berechnung erfolgt einmal je
     Knoten von oben nach unten (Elternpfade sind dann bereits bekannt), nicht
     rekursiv je Knoten neu.
   - `depth` mitschreiben: oberste Ebene = 0.
   - Ergebnis-Array nach der oben festgelegten Struktur zusammensetzen,
     `version` aus `simple_clean_get_page_index_version()` übernehmen.
   - Speichern mit `update_option('simple_clean_page_index', $index, false)`.
     Der dritte Parameter `false` ist zwingend.
   - Index zurückgeben.
3. `simple_clean_get_page_index()` implementieren (Read-Through):
   - Statische Variable innerhalb der Funktion, damit der Index pro Request
     nur einmal geladen wird.
   - `get_option('simple_clean_page_index')` lesen. Ist das Ergebnis kein
     Array, fehlt `schema`, weicht `schema` von
     `SIMPLE_CLEAN_PAGE_INDEX_SCHEMA` ab oder weicht `version` vom aktuellen
     Zählerstand ab → `simple_clean_build_page_index()` aufrufen.
   - Sonst den gespeicherten Index zurückgeben.
4. `simple_clean_get_page_index_version()` implementieren:
   `(int) get_option('simple_clean_page_index_version', 1)`.
5. `simple_clean_invalidate_page_index()` implementieren: Zähler um 1 erhöhen
   (`update_option('simple_clean_page_index_version', $version + 1)`) und
   `delete_option('simple_clean_page_index')`. Die Funktion darf beliebig oft
   hintereinander aufgerufen werden, ohne Schaden anzurichten.
6. In `Theme/functions.php` die `require_once`-Zeile ergänzen, direkt neben
   den beiden bestehenden `require_once`-Zeilen für `includes/admin/`.
7. Kopf-Kommentar in `page-index.php`, der Zweck, Optionsnamen, Autoload-Regel
   und die Read-Through-Strategie in wenigen Sätzen erklärt.

**Akzeptanzkriterien:**
- [ ] `Theme/includes/page-index.php` existiert und wird von `functions.php` geladen.
- [ ] Die SQL-Abfrage für die Seiten enthält weder `post_content` noch `SELECT *`.
- [ ] `simple_clean_get_page_index()` liefert ein Array mit den Schlüsseln `schema`, `version`, `built_at`, `count`, `nodes`, `children`.
- [ ] Die Anzahl in `count` stimmt mit der Anzahl veröffentlichter Seiten im WP-Admin überein (abzüglich ausgeschlossener Unterbäume, die es zu diesem Zeitpunkt noch nicht gibt).
- [ ] SQL-Prüfung in phpMyAdmin: `SELECT autoload FROM wp_options WHERE option_name = 'simple_clean_page_index';` liefert `no`.
- [ ] Ein Knoten der dritten Ebene hat ein `uri` der Form `eltern/kind/enkel` ohne führenden oder abschließenden Schrägstrich und `depth` = 2.
- [ ] `php -l` über alle PHP-Dateien im Theme meldet keinen Fehler.

**Tests:**
- Smoke-Test: ZIP bauen und einspielen; Startseite, Skriptenseite und WP-Admin laden ohne weiße Seite. Da noch nichts den Index nutzt, darf sich im Frontend nichts ändern.
- Prüfschritt (Nachweis ohne eigenes Testskript): Die in AP-1.2 angelegte Messausgabe vorübergehend erweitern ist **nicht** erlaubt. Stattdessen den Index über phpMyAdmin nachweisen: `SELECT LENGTH(option_value), autoload FROM wp_options WHERE option_name = 'simple_clean_page_index';` – die Länge muss größer 0 sein. Vorher die Inhaltsverzeichnisseite einmal aufrufen, damit der Read-Through greift. **Hinweis:** Solange kein Aufrufer existiert, wird der Index nicht gebaut. Deshalb in diesem AP am Ende der Datei eine temporäre Zeile ergänzen, die den Index auf `wp_footer` für angemeldete Administratoren mit `?sc_index=1` als HTML-Kommentar mit `count` und `version` ausgibt – analog zu `simple_clean_perf_footer()`. Diese Ausgabe bleibt dauerhaft (sie ist das Diagnosewerkzeug für AP-2.2 bis AP-2.4) und heißt `simple_clean_page_index_debug_footer()`.
- Prüfschritt: Aufruf einer beliebigen Seite mit `?sc_index=1` als Administrator zeigt `<!-- SC-INDEX count=… version=… built_at=… -->`; ohne Parameter oder als abgemeldeter Besucher erscheint nichts.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.

**Übergabenotiz:**

---

#### AP-2.2: Invalidierung über WordPress-Hooks

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet
**Abhängigkeiten:** AP-2.1

**Ziel & Kontext:**
Der Index muss ungültig werden, sobald sich an der Seitenstruktur etwas ändert.
Dieses AP deckt alle Änderungen ab, die über die regulären WordPress-Abläufe
laufen. Änderungen, die der Seitenmanager per direktem SQL vornimmt, behandelt
AP-2.3 – die sind hier ausdrücklich **nicht** erfasst.

Der Index enthält fertig berechnete Pfade. Deshalb muss er auch dann neu
gebaut werden, wenn sich die Permalink-Struktur oder die Site-Adresse ändert –
das wird leicht übersehen.

**Betroffene Dateien:**
- `Theme/includes/page-index.php` (ändern – Hook-Registrierungen und eine Hilfsfunktion)

**Vorgehen:**
1. Hilfsfunktion `simple_clean_maybe_invalidate_page_index($post_id, $post = null)`
   anlegen. Sie ruft `simple_clean_invalidate_page_index()` nur auf, wenn:
   - kein Autosave läuft (`defined('DOING_AUTOSAVE') && DOING_AUTOSAVE` → return),
   - es sich nicht um eine Revision handelt (`wp_is_post_revision($post_id)` → return),
   - der Post-Type `page` ist (über `$post` falls vorhanden, sonst
     `get_post_type($post_id)`).
2. Folgende Hooks registrieren, jeweils auf die Hilfsfunktion:
   - `save_post_page` (Priorität 20, 2 Parameter)
   - `deleted_post` (2 Parameter, ab WP 5.5 wird das Post-Objekt mitgeliefert;
     defensiv auch mit einem Parameter lauffähig halten)
   - `trashed_post`
   - `untrashed_post`
3. Zusätzlich `transition_post_status` mit drei Parametern registrieren
   (`$new_status, $old_status, $post`): invalidieren, wenn
   `$post->post_type === 'page'` **und** `$new_status !== $old_status`. Damit
   sind Wechsel zwischen `publish`, `draft` und `private` abgedeckt, die über
   `save_post_page` allein nicht zuverlässig auffallen.
4. Auf `update_option_permalink_structure` und `update_option_home` je eine
   Invalidierung registrieren (die Pfade im Index hängen davon ab). Beide
   Hooks liefern Parameter, die nicht gebraucht werden – die Callbacks nehmen
   sie entgegen und ignorieren sie.
5. Über jedem Hook-Block einen Kommentar setzen, der erklärt, warum genau
   dieser Hook nötig ist. Insbesondere bei den beiden Options-Hooks, deren
   Zusammenhang nicht offensichtlich ist.
6. In den Kommentarkopf der Datei einen deutlichen Hinweis aufnehmen: Wer
   `post_parent` oder `menu_order` **direkt per SQL** ändert, muss
   `simple_clean_invalidate_page_index()` selbst aufrufen – `save_post` feuert
   dabei nicht. Verweis auf `includes/admin/page-manager.php`.

**Akzeptanzkriterien:**
- [ ] Eine Seite im WP-Admin bearbeiten und speichern erhöht `simple_clean_page_index_version` um mindestens 1 (per phpMyAdmin nachweisbar).
- [ ] Eine Seite in den Papierkorb legen erhöht den Zähler.
- [ ] Eine Seite aus dem Papierkorb zurückholen erhöht den Zähler.
- [ ] Eine Seite von „Veröffentlicht" auf „Entwurf" umstellen erhöht den Zähler.
- [ ] Ein Autosave im Editor erhöht den Zähler **nicht**.
- [ ] Das Speichern eines Beitrags (Post-Type `post`) oder eines Glossareintrags erhöht den Zähler **nicht**.
- [ ] Nach jedem der obigen Vorgänge zeigt ein Frontend-Aufruf mit `?sc_index=1` einen `count`, der die Änderung widerspiegelt.

**Tests:**
- Smoke-Test: ZIP bauen und einspielen; Startseite, Skriptenseite und WP-Admin laden ohne weiße Seite.
- Prüfschritt: Eine Testseite anlegen („Index-Test", oberste Ebene, veröffentlichen). Danach eine beliebige Frontend-Seite mit `?sc_index=1` aufrufen – `count` ist um 1 höher als vorher und `version` gestiegen.
- Prüfschritt: Dieselbe Testseite einer bestehenden Seite als Unterseite zuordnen und speichern. Erneut `?sc_index=1` – `count` bleibt gleich, `version` steigt.
- Prüfschritt: Testseite in den Papierkorb legen. Erneut `?sc_index=1` – `count` ist wieder auf dem Ausgangswert.
- Prüfschritt: Im Editor eines Beitrags (nicht Seite) etwas ändern und speichern – `version` bleibt unverändert.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.

**Übergabenotiz:**

---

#### AP-2.3: Invalidierung im Seitenmanager

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-2.2

**Ziel & Kontext:**
`Theme/includes/admin/page-manager.php` ändert die Seitenhierarchie an mehreren
Stellen **direkt in der Datenbank** – in der Sortier-Routine per
`$wpdb->update($wpdb->posts, ['post_parent' => …, 'menu_order' => …], ['ID' => …])`,
gefolgt von `clean_post_cache()`. Bei diesem Weg feuert `save_post` **nicht**.
Die in AP-2.2 registrierten Hooks greifen also nicht, und der Index bliebe nach
jeder Umsortierung veraltet. Das ist der wahrscheinlichste Weg, auf dem diese
Erweiterung im Alltag falsche Ergebnisse liefern würde.

**Betroffene Dateien:**
- `Theme/includes/admin/page-manager.php` (ändern)

**Vorgehen:**
1. Alle AJAX-Handler in der Datei identifizieren, die schreibend auf Seiten
   wirken. Das sind mindestens: die Sortier-/Verschiebe-Routine (mit der
   `$wpdb->update`-Schleife über `post_parent` und `menu_order`), das Anlegen
   einer Seite, das Löschen einer Seite und das Umschalten des
   Veröffentlichungsstatus. Die genaue Menge im Quelltext ermitteln und in der
   Übergabenotiz auflisten.
2. In **jedem** dieser Handler unmittelbar vor dem Senden der Erfolgsantwort
   (`wp_send_json_success(...)`) `simple_clean_invalidate_page_index()`
   aufrufen. Bei der Sortier-Routine genügt ein einziger Aufruf nach der
   Schleife – aber nur, wenn tatsächlich mindestens eine Zeile geändert wurde
   (die Routine führt bereits einen Zähler `$updated` mit).
3. Jeden Aufruf mit `function_exists('simple_clean_invalidate_page_index')`
   absichern, damit die Admin-Datei auch dann lädt, wenn
   `includes/page-index.php` einmal fehlen sollte.
4. Über jedem Aufruf einen einzeiligen Kommentar setzen, der erklärt, warum er
   nötig ist: direkter SQL-Schreibzugriff, `save_post` feuert nicht.
5. Die AJAX-Antworten selbst bleiben unverändert – kein zusätzliches Feld, kein
   geändertes Format. Das JavaScript in `Theme/src/js/page-manager.js` wird
   nicht angefasst.

**Akzeptanzkriterien:**
- [ ] Jeder schreibende AJAX-Handler in `page-manager.php` ruft `simple_clean_invalidate_page_index()` auf; die vollständige Liste steht in der Übergabenotiz.
- [ ] Eine Umsortierung per Drag & Drop erhöht `simple_clean_page_index_version`.
- [ ] Eine Umsortierung, bei der sich nichts ändert (Seite an dieselbe Stelle zurückgelegt), erhöht den Zähler **nicht**.
- [ ] Anlegen, Löschen und Status-Umschalten im Seitenmanager erhöhen den Zähler jeweils.
- [ ] `Theme/src/js/page-manager.js` ist unverändert (per `git diff` nachweisbar).
- [ ] `php -l Theme/includes/admin/page-manager.php` meldet keinen Fehler.

**Tests:**
- Smoke-Test: ZIP bauen und einspielen; den Seitenmanager im WP-Admin öffnen – die Seitenliste erscheint wie zuvor.
- Prüfschritt R3 (Regression): Eine Seite per Drag & Drop an eine andere Position ziehen. Erfolgsmeldung erscheint; nach dem Neuladen der Seite steht sie an der neuen Position.
- Prüfschritt: Direkt danach eine Frontend-Seite mit `?sc_index=1` aufrufen – `version` ist gestiegen, und die Reihenfolge in `children` entspricht der neuen Sortierung (stichprobenhaft an dem verschobenen Eintrag prüfen).
- Prüfschritt: Im Seitenmanager eine Testseite anlegen und wieder löschen; `count` steigt und fällt entsprechend.
- Prüfschritt: Browser-Konsole während der Drag-Aktionen frei von Fehlern.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.

**Übergabenotiz:**

---

#### AP-2.4: Manueller Neuaufbau und Statusanzeige

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet
**Abhängigkeiten:** AP-2.3

**Ziel & Kontext:**
Trotz vollständiger Invalidierung braucht ein zwischengespeicherter Index ein
Sicherheitsventil: einen Knopf, der ihn erzwungen neu aufbaut. Das deckt Fälle
ab, die kein Hook erwischt – etwa einen Import direkt in die Datenbank oder ein
Fremd-Plugin, das an Seiten schreibt. Zugleich macht eine kleine Statusanzeige
sichtbar, wie alt der Index ist und wie viele Seiten er enthält.

Der Knopf gehört auf die Seitenmanager-Seite im WP-Admin, weil dort ohnehin die
Seitenstruktur bearbeitet wird.

**Betroffene Dateien:**
- `Theme/includes/page-index.php` (ändern – AJAX-Handler und Aufräumfunktion)
- `Theme/includes/admin/page-manager.php` (ändern – Statusanzeige und Knopf in der Admin-Oberfläche)

**Vorgehen:**
1. In `page-index.php` eine Funktion
   `simple_clean_clear_page_index_fragments()` anlegen, die alle
   Fragment-Transients des Blocks löscht:
   `DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_sc\_pidx\_%' OR option_name LIKE '\_transient\_timeout\_sc\_pidx\_%'`
   Das Muster wird als Zeichenkette an `$wpdb->query()` mit
   `$wpdb->prepare()` und `%s`-Platzhaltern übergeben; die Unterstriche im
   Muster sind mit Backslash zu maskieren, damit sie nicht als
   Einzelzeichen-Joker wirken. Diese Transients entstehen erst in Phase 3 –
   die Funktion darf jetzt schon existieren und löscht dann eben nichts.
2. AJAX-Handler `simple_clean_rebuild_page_index_ajax()` anlegen, registriert
   auf `wp_ajax_simple_clean_rebuild_page_index`. Er prüft in dieser
   Reihenfolge:
   - `check_ajax_referer('simple_clean_rebuild_page_index', 'nonce')`
   - `current_user_can('manage_options')`, sonst `wp_send_json_error`
   Danach: `simple_clean_invalidate_page_index()`,
   `simple_clean_clear_page_index_fragments()`,
   `$index = simple_clean_build_page_index()` und
   `wp_send_json_success(array('count' => $index['count'], 'version' => $index['version']))`.
3. In `page-manager.php` oberhalb der Seitenliste einen schlichten
   Informationskasten einfügen (im Stil der dort bereits vorhandenen
   Hinweiskästen), der ausgibt: Anzahl Seiten im Index, Versionsnummer,
   Zeitpunkt des letzten Aufbaus (aus `built_at`, formatiert über
   `wp_date(get_option('date_format') . ' H:i', $built_at)`).
4. Darunter einen Knopf „Seitenindex neu aufbauen" mit einem kleinen
   Inline-Script, das den AJAX-Aufruf ausführt, den Knopf währenddessen
   deaktiviert und danach die Anzahl aus der Antwort im Kasten aktualisiert.
   Das Nonce über `wp_create_nonce('simple_clean_rebuild_page_index')`
   erzeugen und im `data-`-Attribut des Knopfes übergeben.
5. Der Knopf wird nur für Benutzer mit `manage_options` ausgegeben.

**Akzeptanzkriterien:**
- [ ] Der Seitenmanager zeigt Anzahl, Version und Aufbauzeitpunkt des Index an.
- [ ] Ein Klick auf „Seitenindex neu aufbauen" meldet Erfolg und aktualisiert die angezeigte Anzahl ohne Neuladen der Seite.
- [ ] Nach dem Klick ist `simple_clean_page_index_version` in der Datenbank gestiegen.
- [ ] Ein Aufruf der AJAX-Aktion ohne gültiges Nonce wird abgewiesen (nachweisbar, indem die Seite mit einem veralteten Nonce erneut abgeschickt wird, nachdem der Benutzer abgemeldet wurde).
- [ ] Ein Benutzer der Rolle „Block-Redakteur" bzw. Redakteur sieht den Knopf nicht.
- [ ] `php -l` über beide geänderten Dateien meldet keinen Fehler.

**Tests:**
- Smoke-Test: ZIP bauen und einspielen; Seitenmanager öffnen – Kasten und Knopf erscheinen, Seitenliste funktioniert wie zuvor.
- Prüfschritt: Knopf klicken. Erfolgsmeldung erscheint, Anzahl bleibt plausibel (entspricht der Zahl veröffentlichter Seiten). Browser-Konsole ohne Fehler.
- Prüfschritt R3 (Regression): Drag & Drop im Seitenmanager funktioniert nach dem Einbau des Kastens unverändert.
- Prüfschritt: Mit einem Redakteur-Konto den Seitenmanager öffnen – kein Knopf sichtbar.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.

**Übergabenotiz:**

---

#### AP-2.rev: Unabhängiges Review Phase 2

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus
**Abhängigkeiten:** AP-2.1, AP-2.2, AP-2.3, AP-2.4

**Ziel & Kontext:**
Unabhängige Qualitätsprüfung der Phase 2 durch einen Agenten, der an keiner
Implementierung beteiligt war. Nur lesend arbeiten – KEINE Datei verändern.

**Vorgehen:**
1. Für jedes Implementierungs-AP der Phase (AP-2.1 bis AP-2.4): den Code gegen
   dessen Akzeptanzkriterien prüfen, mit Stichproben im Quelltext.
2. Vollständigkeit der Invalidierung prüfen: `Theme/includes/admin/page-manager.php`
   nach **allen** Stellen durchsuchen, die schreibend auf `$wpdb->posts`,
   `wp_insert_post`, `wp_update_post` oder `wp_delete_post` wirken. Für jede
   gefundene Stelle prüfen, ob eine Invalidierung folgt. Fehlende Stellen sind
   ein kritischer Befund.
3. Autoload prüfen: Wird `update_option` für `simple_clean_page_index`
   tatsächlich überall mit `false` als drittem Parameter aufgerufen? Auch in
   `simple_clean_build_page_index()`?
4. SQL prüfen: Enthalten die Abfragen Nutzereingaben? Werden Platzhalter
   verwendet, wo nötig? Ist das `LIKE`-Muster in
   `simple_clean_clear_page_index_fragments()` korrekt maskiert, sodass es
   nicht versehentlich fremde Transients löscht?
5. Robustheit prüfen: Was passiert bei einer Seite ohne `post_name`? Bei einem
   Elternteil im Papierkorb? Greift der Zyklenschutz? Ist die statische
   Variable in `simple_clean_get_page_index()` so gesetzt, dass ein Neuaufbau
   innerhalb desselben Requests (etwa nach dem Rebuild-AJAX) nicht auf einen
   veralteten Wert trifft?
6. PHP 7.4 prüfen: keine Sprachmittel ab 8.0.
7. Scope-Check: Wurde `sidebar.php` unverändert gelassen? Wurde
   `src/js/page-manager.js` unverändert gelassen? Wurde im Frontend etwas
   verändert, obwohl die Phase das ausdrücklich nicht vorsieht?
8. Befunde als Review-Bericht in die Übergabenotiz: je Befund Schweregrad
   (kritisch / mittel / gering), betroffenes AP, Datei und Fundstelle.

**Akzeptanzkriterien:**
- [ ] Jedes Implementierungs-AP der Phase wurde gegen seine Akzeptanzkriterien geprüft.
- [ ] Die Suche nach schreibenden Zugriffen in `page-manager.php` ist dokumentiert (gefundene Stellen und jeweiliges Prüfergebnis).
- [ ] Alle Befunde mit Schweregrad, Datei und Fundstelle dokumentiert.
- [ ] Keine Datei wurde verändert.

**Tests:**
- entfällt (Review-AP; das Ergebnis ist der Bericht).

**Übergabenotiz:**

---

#### AP-2.doc: Dokumentation Phase 2 aktualisieren

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-2.rev

**Ziel & Kontext:**
`Theme/CLAUDE.md` um das neue Subsystem „Seitenindex" ergänzen, damit ein
späterer Bearbeiter Aufbau, Speicherort und – vor allem – die
Invalidierungspflicht bei direktem SQL-Zugriff kennt.

**Betroffene Dateien:**
- `Theme/CLAUDE.md` (ändern)
- `Theme/docs/PLAN-Seitenindex.md` (ändern – Statustabelle, Testprotokoll, Datum im Kopf)

**Vorgehen:**
1. Alle Übergabenotizen der Phase 2 durchgehen.
2. In `Theme/CLAUDE.md` in der Funktionsübersicht einen neuen Abschnitt
   „Seitenindex (includes/page-index.php)" anlegen mit: Zweck, den beiden
   Optionsnamen samt Autoload-Hinweis, den vier öffentlichen Funktionen mit
   Signatur, der Read-Through-Strategie und der Diagnoseausgabe `?sc_index=1`.
3. Deutlich hervorheben: Wer `post_parent` oder `menu_order` direkt per SQL
   ändert, muss `simple_clean_invalidate_page_index()` selbst aufrufen. Mit
   Verweis auf `includes/admin/page-manager.php` als bestehendes Beispiel.
4. Statustabelle und Testprotokoll dieses Plans auf den Stand bringen,
   „Letzte Aktualisierung" im Dateikopf setzen.
5. Branch `phase-2-seitenindex` nach `main` mergen und `main` pushen.

**Akzeptanzkriterien:**
- [ ] `Theme/CLAUDE.md` enthält den Abschnitt „Seitenindex" mit allen vier Funktionssignaturen.
- [ ] Der Hinweis zur Invalidierungspflicht bei direktem SQL-Zugriff ist enthalten.
- [ ] Statustabelle und Testprotokoll dieses Plans sind für alle APs der Phase 2 gefüllt.
- [ ] `phase-2-seitenindex` ist nach `main` gemerged und `main` gepusht.

**Tests:**
- Stichprobe: Zwei in `Theme/CLAUDE.md` genannte Funktionsnamen im Quelltext von `Theme/includes/page-index.php` suchen – beide müssen mit identischer Signatur existieren.

**Übergabenotiz:**

---

### Phase 3: Block und Rendering

Gemeinsame Vereinbarungen für alle APs dieser Phase:

**Blockname:** `fos/inhaltsverzeichnis`. Der Namensraum `fos/` kollidiert weder
mit `container-block-designer/*` (CDB-Designer-Plugin) noch mit
`modular-blocks/*` (Eigene WP Blocks).

**Attribute (verbindlich, identisch in `block.json`, im Renderer und im
Editor-Script):**

| Attribut | Typ | Standard | Wirkung |
|---|---|---|---|
| `rootPage` | number | 0 | ID der Startseite; 0 = alle Seiten der obersten Ebene |
| `maxDepth` | number | 2 | Maximale ausgegebene Tiefe, erlaubt 1 bis 5 |
| `layout` | string | `cards` | erlaubt: `cards`, `list`, `columns` |
| `columns` | number | 3 | Spalten des Kartenrasters, erlaubt 1 bis 4 |
| `collapsible` | boolean | true | Unterebenen in `<details>` verpacken |
| `openByDefault` | boolean | false | `<details>` beim Laden geöffnet |
| `showSearch` | boolean | true | Filterfeld über der Liste ausgeben |
| `showCounts` | boolean | false | Anzahl der Unterseiten je Kapitel anzeigen |

**Grundregel für den Renderer:** Die Ausgabe ist **request-unabhängig**. Sie
darf nicht davon abhängen, welche Seite gerade aufgerufen wird, wer angemeldet
ist oder welche Sprache eingestellt ist. Keine Hervorhebung der aktuellen
Seite. Nur unter dieser Bedingung ist der Fragment-Cache über alle Besucher
hinweg gültig.

**HTML-Grundgerüst der Ausgabe** (Klassennamen sind für Phase 4 verbindlich):

```html
<nav class="page-index page-index--cards page-index--cols-3" aria-label="Inhaltsverzeichnis">
  <div class="page-index__search-wrap">
    <input type="search" class="page-index__search" placeholder="Seite suchen …" aria-label="Inhaltsverzeichnis durchsuchen">
  </div>
  <ul class="page-index__chapters">
    <li class="page-index__chapter">
      <a class="page-index__chapter-link" href="…">Kapiteltitel</a>
      <details class="page-index__sub">
        <summary class="page-index__sub-toggle">Unterseiten (12)</summary>
        <ul class="page-index__pages">
          <li class="page-index__page"><a class="page-index__page-link" href="…">Titel</a></li>
        </ul>
      </details>
    </li>
  </ul>
</nav>
```

Bei `layout = list` entfällt die Kartenklasse, die Struktur bleibt gleich.
Bei `collapsible = false` entfallen `<details>` und `<summary>`; die
Unterlisten stehen dann direkt im `<li>`.

---

#### AP-3.1: Block registrieren und Attribute absichern

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet
**Abhängigkeiten:** AP-2.doc

**Ziel & Kontext:**
Der Block `fos/inhaltsverzeichnis` wird als dynamischer Block registriert – er
speichert keinen Inhalt im Beitrag, sondern wird bei jedem Aufruf serverseitig
erzeugt. Registriert wird über `block.json`, weil damit Attribute, Kategorie
und Rendering an einer Stelle beschrieben sind.

Zuerst diesen Branch anlegen: `git checkout main`, `git pull`,
`git checkout -b phase-3-block`, und `Theme/dist/fos-online-schulbuch.zip`
nach `Theme/backups/fos-online-schulbuch-rollback-phase3.zip` kopieren.

**Betroffene Dateien:**
- `Theme/blocks/inhaltsverzeichnis/block.json` (neu)
- `Theme/includes/page-index.php` (ändern – Registrierung und Attributprüfung)
- `Theme/backups/fos-online-schulbuch-rollback-phase3.zip` (neu, Kopie)

**Vorgehen:**
1. `Theme/blocks/inhaltsverzeichnis/block.json` anlegen mit:
   - `"$schema": "https://schemas.wp.org/trunk/block.json"`, `"apiVersion": 3`
   - `"name": "fos/inhaltsverzeichnis"`, `"title": "Inhaltsverzeichnis"`
   - `"category": "widgets"`, `"icon": "list-view"`
   - `"description"`: ein Satz auf Deutsch, der erklärt, dass der Block eine
     schnelle Seitenübersicht aus dem vorberechneten Seitenindex erzeugt.
   - `"textdomain": "fos-online-schulbuch"`
   - `"supports"`: `{ "html": false, "align": ["wide", "full"], "anchor": true }`
   - `"attributes"`: exakt die acht Attribute aus der Tabelle am Anfang dieser
     Phase, mit Typ und Standardwert.
   - **Kein** `"render"`-Eintrag. Die Eigenschaft `render` in der `block.json`
     wurde erst mit WordPress 6.1 eingeführt; auf älteren Versionen würde sie
     stillschweigend ignoriert und der Block gäbe nichts aus. Das Theme
     deklariert „Requires at least: 5.0". Gerendert wird deshalb über
     `render_callback` (Schritt 3) – das funktioniert ab WordPress 5.5
     einheitlich. Es gibt bewusst **keine** Datei `render.php`.
   - **Kein** `editorScript` in der `block.json`. Das Editor-Script wird in
     AP-3.3 über `enqueue_block_editor_assets` eingehängt, weil es aus
     `dist/js/` kommt und `block.json` nur Pfade relativ zum Blockordner
     auflösen kann.
2. Eine vorläufige Renderfunktion `simple_clean_render_page_index($attributes, $content = '', $block = null)`
   in `Theme/includes/page-index.php` anlegen, die vorerst eine leere
   Zeichenkette zurückgibt. AP-3.2 füllt sie vollständig aus. Die Signatur
   ist verbindlich – sie entspricht dem, was WordPress an einen
   `render_callback` übergibt.
3. In `Theme/includes/page-index.php` eine Funktion
   `simple_clean_register_page_index_block()` anlegen, eingehängt auf `init`:
   - Prüfen, ob `register_block_type_from_metadata()` existiert (WP 5.5+);
     sonst still zurückkehren.
   - `register_block_type_from_metadata(get_template_directory() . '/blocks/inhaltsverzeichnis', array('render_callback' => 'simple_clean_render_page_index'));`
4. Funktion `simple_clean_page_index_sanitize_attrs($attrs)` anlegen, die ein
   Attribut-Array entgegennimmt und ein bereinigtes Array **mit allen acht
   Schlüsseln** zurückgibt. Sie ist die einzige Stelle, an der Attributwerte
   normalisiert werden; der Renderer und die Cache-Schlüsselbildung nutzen
   ausschließlich ihr Ergebnis.
   - `rootPage`: `absint()`; zeigt die ID auf keinen Knoten im Index, auf 0
     zurückfallen.
   - `maxDepth`: `absint()`, dann auf den Bereich 1 bis 5 begrenzen.
   - `layout`: nur `cards`, `list`, `columns` zulassen, sonst `cards`.
   - `columns`: `absint()`, auf 1 bis 4 begrenzen.
   - `collapsible`, `openByDefault`, `showSearch`, `showCounts`: als `bool`
     casten.
   - Die Rückgabe hat eine **feste Schlüsselreihenfolge**, damit die
     Serialisierung für den Cache-Schlüssel in AP-3.2 stabil ist.
5. Kommentarblock über der Registrierung, der auf die Grundregel
   „request-unabhängige Ausgabe" hinweist und begründet, warum das für den
   Fragment-Cache wesentlich ist.

**Akzeptanzkriterien:**
- [ ] Im Block-Editor einer Seite lässt sich über die Blocksuche „Inhaltsverzeichnis" der Block `fos/inhaltsverzeichnis` einfügen.
- [ ] Der eingefügte Block lässt sich speichern; der Beitragsinhalt enthält danach `<!-- wp:fos/inhaltsverzeichnis ... /-->` ohne gespeichertes HTML.
- [ ] `simple_clean_page_index_sanitize_attrs(array())` liefert ein Array mit genau acht Schlüsseln und den Standardwerten aus der Tabelle.
- [ ] `simple_clean_page_index_sanitize_attrs(array('maxDepth' => 99, 'layout' => 'foo', 'columns' => 0))` liefert `maxDepth = 5`, `layout = 'cards'`, `columns = 1`.
- [ ] `Theme/blocks/inhaltsverzeichnis/block.json` ist gültiges JSON (per `node -e "JSON.parse(require('fs').readFileSync('blocks/inhaltsverzeichnis/block.json','utf8'))"` im Ordner `Theme/` nachweisbar).
- [ ] `php -l Theme/includes/page-index.php` meldet keinen Fehler.

**Tests:**
- Smoke-Test: ZIP bauen und einspielen; Startseite, Skriptenseite und WP-Admin laden ohne weiße Seite. **Achtung:** Ohne AP-3.4 ist der Ordner `blocks/` möglicherweise nicht im ZIP enthalten. Vor dem Hochladen den ZIP-Inhalt prüfen; fehlt der Ordner, in diesem AP die Dateien einmalig manuell per Datei-Manager des Hosters ins Theme-Verzeichnis legen und das in der Übergabenotiz vermerken – AP-3.4 behebt die Ursache.
- Prüfschritt: Eine neue Testseite anlegen, den Block einfügen, speichern, Frontend aufrufen. Es erscheint (noch) keine Liste, aber auch kein Blockfehler und kein PHP-Fehler.
- Prüfschritt: Im Editor die Seite erneut öffnen – der Block wird ohne Meldung „Dieser Block enthält unerwarteten oder ungültigen Inhalt" geladen.
- Prüfschritt: Browser-Konsole im Editor frei von Fehlern.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.

**Übergabenotiz:**

---

#### AP-3.2: Rendering aus dem Index mit Fragment-Cache

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus (Zusammenspiel von Baumausgabe, Zwischenspeicherung und Schlüsselbildung; Fehler hier führen zu veralteter Ausgabe, die im Betrieb schwer auffällt)
**Abhängigkeiten:** AP-3.1

**Ziel & Kontext:**
Der Renderer erzeugt das Inhaltsverzeichnis aus dem in Phase 2 gebauten Index
und legt das Ergebnis als Fragment ab. Auf einem warmen Cache kostet ein
Blockaufruf damit einen einzigen Lesevorgang. Die Datenquelle ist ausschließlich
`simple_clean_get_page_index()` – der Renderer stellt **keine** eigene
Datenbankabfrage und ruft **kein** `get_pages()`, `get_permalink()` oder
`get_post()` auf.

**Betroffene Dateien:**
- `Theme/includes/page-index.php` (ändern – Renderfunktion und Cache-Hilfsfunktionen)

**Vorgehen:**
1. Die in AP-3.1 als Rumpf angelegte Funktion
   `simple_clean_render_page_index($attributes, $content = '', $block = null)`
   in `page-index.php` vollständig ausarbeiten. Sie ist als `render_callback`
   des Blocks registriert und damit der einzige Einstiegspunkt. Ablauf:
   - `$attrs = simple_clean_page_index_sanitize_attrs($attributes);`
   - Cache-Schlüssel bilden:
     `'sc_pidx_' . simple_clean_get_page_index_version() . '_' . md5(serialize($attrs))`
     Die Indexversion steckt bewusst **im Schlüssel** – dadurch invalidiert
     sich das Fragment nach jeder Seitenänderung von selbst, ohne dass die
     Options-Tabelle durchsucht werden muss. Der Schlüssel darf 172 Zeichen
     nicht überschreiten (Grenze für Transient-Namen); das ist bei diesem
     Aufbau eingehalten.
   - `get_transient($key)` lesen. Ist das Ergebnis eine Zeichenkette,
     zurückgeben.
   - Sonst HTML erzeugen (Schritte 2 bis 5) und mit
     `set_transient($key, $html, WEEK_IN_SECONDS)` ablegen.
2. Startknoten bestimmen: Bei `rootPage = 0` sind es die Kinder von `0` aus
   `children`. Sonst die Kinder von `rootPage`. Ist die Liste leer, eine
   schlichte Meldung ausgeben (`<p class="page-index__empty">Keine Seiten
   vorhanden.</p>`) und diese ebenfalls zwischenspeichern.
3. Baumausgabe **iterativ oder rekursiv mit harter Tiefenbegrenzung**
   implementieren:
   - Ebene 0 sind die Kapitel: je Kapitel ein `<li class="page-index__chapter">`
     mit Link.
   - Ab Ebene 1 die Unterseiten, begrenzt durch `maxDepth` (Ebene 0 zählt als
     Tiefe 1). Bei `maxDepth = 2` werden also Kapitel und deren direkte
     Unterseiten ausgegeben, tiefere Ebenen nicht.
   - Bei `collapsible = true` und vorhandenen Unterseiten die Unterliste in
     `<details>` mit `<summary>` verpacken; `open`-Attribut nur bei
     `openByDefault = true`.
   - Bei `showCounts = true` im `<summary>` die Anzahl der direkten
     Unterseiten anzeigen, sonst nur die Beschriftung „Unterseiten".
   - Rekursionsschutz: eine Besuchsliste mitführen; jede Knoten-ID darf nur
     einmal ausgegeben werden.
4. URLs erzeugen: Steht eine Permalink-Struktur (`get_option('permalink_structure')`
   nicht leer), lautet die URL `home_url('/' . $node['uri'] . '/')`. Sonst
   `home_url('/?page_id=' . $node['id'])`. Alle URLs durch `esc_url()`, alle
   Titel durch `esc_html()`, alle Klassen- und Attributwerte durch
   `esc_attr()`.
5. Wrapper-Attribute: `get_block_wrapper_attributes()` verwenden, damit
   Ausrichtung, Anker und eigene Zusatzklassen aus dem Editor wirksam werden.
   Die eigenen Klassen `page-index`, `page-index--<layout>` und
   `page-index--cols-<columns>` werden als `class`-Eintrag übergeben. Bei
   `layout = list` wird `page-index--cols-…` weggelassen.
   Steht `get_block_wrapper_attributes()` nicht zur Verfügung, auf ein
   einfaches `<nav class="…">` zurückfallen.
6. `aria-label="Inhaltsverzeichnis"` am `<nav>` setzen.
7. Das Suchfeld nur ausgeben, wenn `showSearch = true`. Es enthält kein
   `name`-Attribut und steht in keinem `<form>` – gefiltert wird in Phase 4
   rein im Browser.
8. Die Funktion gibt die fertige Zeichenkette **zurück** (`return`), sie gibt
   nichts direkt aus. Ein `render_callback` muss zurückgeben, nicht `echo`en –
   sonst erscheint die Ausgabe an der falschen Stelle im Dokument.
9. Kommentarblock über `simple_clean_render_page_index()`, der die
   Grundregel der request-unabhängigen Ausgabe und die Rolle der Indexversion
   im Cache-Schlüssel erklärt.

**Akzeptanzkriterien:**
- [ ] Eine Seite mit dem Block zeigt im Frontend die Kapitel der obersten Ebene als Links; jeder Link führt auf die richtige Seite.
- [ ] Mit `maxDepth = 2` erscheinen Kapitel und deren direkte Unterseiten, aber keine dritte Ebene.
- [ ] Mit `collapsible = true` sind die Unterlisten in `<details>` verpackt und lassen sich per Klick auf- und zuklappen – auch mit deaktiviertem JavaScript.
- [ ] Mit `rootPage` auf eine bestimmte Seite gesetzt erscheinen ausschließlich deren Nachfahren.
- [ ] Der zweite Aufruf derselben Seite erzeugt keine zusätzliche Datenbanklast: die per `?sc_perf=1` gemeldete Queryzahl ist beim zweiten Aufruf nicht höher als beim ersten.
- [ ] Die Renderfunktion gibt ihre Ausgabe per `return` zurück; die Datei enthält innerhalb der Funktion kein `echo` und kein `print` (per Textsuche nachweisbar).
- [ ] Nach einer Seitenumbenennung im WP-Admin zeigt das Inhaltsverzeichnis beim nächsten Aufruf den neuen Titel (Fragment invalidiert sich über die Indexversion).
- [ ] Im Seitenquelltext taucht kein `get_pages`-typisches Verhalten auf: Die Queryzahl der Seite mit dem Block liegt deutlich unter der Queryzahl derselben Seite mit `core/page-list` (Vergleichswert aus AP-1.6).
- [ ] `php -l Theme/includes/page-index.php` meldet keinen Fehler.

**Tests:**
- Smoke-Test: ZIP bauen und einspielen; Testseite mit dem Block im Frontend aufrufen – die Liste erscheint, keine PHP-Meldung, Browser-Konsole ohne Fehler.
- Prüfschritt: Drei Links aus verschiedenen Ebenen anklicken – alle führen auf die erwartete Seite (keine 404).
- Prüfschritt: Messung mit `?sc_perf=1` auf der Testseite. Erster Aufruf (kalt) und zweiter Aufruf (warm) notieren; der warme Aufruf muss weniger oder gleich viele Queries melden.
- Prüfschritt: Eine im Verzeichnis gelistete Seite umbenennen, speichern, Testseite neu laden – der neue Titel erscheint.
- Prüfschritt: Eine Seite in den Papierkorb legen, Testseite neu laden – der Eintrag ist verschwunden. Seite wiederherstellen, erneut laden – der Eintrag ist zurück.
- Prüfschritt: Attribute im Editor durchprobieren (`maxDepth` 1 und 3, `collapsible` an/aus, `rootPage` gesetzt) und jeweils das Frontend prüfen. Da der Editor-Inspektor erst in AP-3.3 entsteht, die Attribute für diesen Test über die Codeansicht des Blocks (Editor → Block → Als HTML bearbeiten bzw. Codeansicht) setzen.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.

**Übergabenotiz:**

---

#### AP-3.3: Editor-Integration mit Inspektor und Vorschau

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet
**Abhängigkeiten:** AP-3.2

**Ziel & Kontext:**
Im Block-Editor soll der Block eine echte Vorschau zeigen und alle acht
Attribute über die rechte Seitenleiste einstellbar sein. Die Vorschau nutzt
`wp.serverSideRender`, ruft also dieselbe PHP-Renderfunktion wie das Frontend
auf – es gibt dadurch keine zweite Darstellungslogik, die auseinanderlaufen
könnte.

**Wichtige Falle:** Vite gibt ES-Module aus. Das Editor-Script wird als
klassisches Script eingehängt und darf deshalb **keine** `import`- oder
`export`-Anweisung enthalten. Der Zugriff auf WordPress-Bausteine erfolgt über
die globalen Objekte, genau wie in der bereits vorhandenen Datei
`Theme/src/js/glossar-editor.js`, die mit
`const { registerFormatType } = wp.richText;` beginnt. Dieses Muster ist
einzuhalten.

**Betroffene Dateien:**
- `Theme/src/js/page-index-editor.js` (neu)
- `Theme/includes/page-index.php` (ändern – Einhängen des Editor-Scripts)

**Vorgehen:**
1. `Theme/src/js/page-index-editor.js` anlegen. Am Dateianfang die benötigten
   Bausteine aus den Globalen holen, ohne `import`:
   `const { registerBlockType } = wp.blocks;`,
   `const { InspectorControls, useBlockProps } = wp.blockEditor;`,
   `const { PanelBody, RangeControl, SelectControl, ToggleControl } = wp.components;`,
   `const { createElement: el, Fragment } = wp.element;`,
   `const ServerSideRender = wp.serverSideRender;`
2. Den Block mit `registerBlockType('fos/inhaltsverzeichnis', { edit: … })`
   registrieren. Attribute und Standardwerte kommen aus der `block.json` und
   werden im JavaScript **nicht** erneut deklariert. `save` wird nicht
   angegeben (dynamischer Block).
3. Die `edit`-Funktion gibt zwei Dinge zurück:
   - `InspectorControls` mit einem `PanelBody` „Einstellungen":
     - `rootPage`: Auswahl der Startseite. Die Seitenliste über
       `wp.data.useSelect` aus `core` beziehen
       (`getEntityRecords('postType', 'page', { per_page: -1, orderby: 'menu_order', order: 'asc', _fields: 'id,title,parent' })`)
       und als `SelectControl` mit einem Eintrag „Alle Seiten (oberste Ebene)"
       für den Wert 0 anbieten. Solange die Liste lädt, das Steuerelement
       deaktiviert anzeigen.
     - `maxDepth`: `RangeControl`, 1 bis 5.
     - `layout`: `SelectControl` mit den drei Werten und deutschen
       Beschriftungen („Kapitelkarten", „Einfache Liste", „Mehrspaltig").
     - `columns`: `RangeControl`, 1 bis 4, nur sichtbar wenn `layout` nicht
       `list` ist.
     - `collapsible`, `openByDefault`, `showSearch`, `showCounts`: je ein
       `ToggleControl` mit deutscher Beschriftung. `openByDefault` nur
       sichtbar, wenn `collapsible` aktiv ist.
   - Die Vorschau: ein `div` mit `useBlockProps()`, darin
     `ServerSideRender` mit `block: 'fos/inhaltsverzeichnis'` und den
     aktuellen Attributen.
4. In `Theme/includes/page-index.php` eine Funktion
   `simple_clean_page_index_editor_assets()` anlegen, eingehängt auf
   `enqueue_block_editor_assets`. Sie folgt dem Muster von
   `simple_clean_glossar_editor_assets()` in `Theme/functions.php`:
   - Pfad `get_template_directory() . '/dist/js/page-index-editor.js'`
   - `file_exists()`-Guard
   - `wp_enqueue_script()` mit Handle `simple-clean-page-index-editor`,
     Abhängigkeiten
     `array('wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-data', 'wp-server-side-render')`,
     Version `filemtime($js_file)`, `true` für Laden im Footer.
5. Ein Kommentar im Kopf von `page-index-editor.js` weist auf die
   ESM-Falle hin: keine `import`/`export`-Anweisungen, Zugriff nur über
   `wp.*`-Globale.

**Akzeptanzkriterien:**
- [ ] Im Block-Editor zeigt der eingefügte Block eine gerenderte Vorschau des Inhaltsverzeichnisses, nicht nur einen Platzhalter.
- [ ] Die rechte Seitenleiste zeigt alle acht Einstellungen mit deutschen Beschriftungen.
- [ ] Eine Änderung an `maxDepth` aktualisiert die Vorschau ohne Neuladen des Editors.
- [ ] `columns` ist ausgeblendet, solange `layout` auf „Einfache Liste" steht.
- [ ] `openByDefault` ist ausgeblendet, solange `collapsible` aus ist.
- [ ] Die Auswahl „Startseite" listet vorhandene Seiten und enthält den Eintrag für „Alle Seiten (oberste Ebene)".
- [ ] `Theme/src/js/page-index-editor.js` enthält weder `import ` noch `export ` (per Textsuche nachweisbar).
- [ ] Nach `npm run build` existiert `Theme/dist/js/page-index-editor.js`.

**Tests:**
- Smoke-Test: Nach `npm run build` und Upload eine Seite im Block-Editor öffnen, den Block einfügen – Vorschau erscheint, Browser-Konsole ohne Fehler.
- Prüfschritt: Alle acht Einstellungen nacheinander verändern; nach jeder Änderung aktualisiert sich die Vorschau und es erscheint keine Konsolenmeldung.
- Prüfschritt: Seite speichern, Frontend aufrufen – die Ausgabe entspricht der Vorschau im Editor.
- Prüfschritt: Seite im Editor erneut öffnen – keine Meldung über ungültigen Blockinhalt.
- Prüfschritt R6 (Regression): Eine Seite mit einem CDB-Container-Block öffnen und speichern – der Container rendert unverändert, Konsole ohne Fehler.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.

**Übergabenotiz:**

---

#### AP-3.4: Build-Kette erweitern

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-3.3

**Ziel & Kontext:**
Zwei Stellen der Build-Kette kennen die neuen Dateien noch nicht.

Erstens `Theme/vite.config.js`: Dort sind sechs Einstiegspunkte eingetragen
(`main`, `glossar`, `glossar-editor`, `glossar-style`, `page-manager`,
`page-manager-style`). Die neuen Dateien fehlen und würden nicht gebaut.

Zweitens – und das ist die gefährlichere Stelle – `Theme/create-theme-zip.js`:
Die Funktion `shouldIncludeFile()` nimmt PHP-Dateien, `style.css`, `readme.md`,
`LICENSE`, alles unter `dist/js/`, `dist/css/`, die Vite-Manifestdatei sowie
PHP- und JS-Dateien unter `includes/` auf. **`.json`-Dateien sind nirgends
erfasst.** Die `block.json` fiele damit aus dem Verteilungspaket, der Block
wäre auf der Live-Site unbekannt und die Inhaltsverzeichnisseite zeigte einen
Blockfehler. Diese Fehlerklasse ist im Projekt bereits einmal aufgetreten
(fehlender Autoloader im Plugin-ZIP) und deshalb ausdrücklich abzusichern.

**Betroffene Dateien:**
- `Theme/vite.config.js` (ändern)
- `Theme/create-theme-zip.js` (ändern)

**Vorgehen:**
1. In `vite.config.js` unter `build.rollupOptions.input` drei Einträge
   ergänzen, in der Schreibweise der bestehenden Zeilen:
   - `'page-index-editor': resolve(__dirname, 'src/js/page-index-editor.js')`
   - `'page-index': resolve(__dirname, 'src/js/page-index.js')`
   - `'page-index-style': resolve(__dirname, 'src/css/page-index.css')`
   Die Dateien `src/js/page-index.js` und `src/css/page-index.css` entstehen
   erst in Phase 4. Damit der Build jetzt nicht scheitert, beide Dateien in
   diesem AP als **minimale Platzhalter** anlegen: die JS-Datei mit einem
   Kommentar und einem leeren `(function(){})();`, die CSS-Datei mit einem
   Kommentar und einer harmlosen Regel (etwa `.page-index { display: block; }`).
   In beiden Dateien vermerken, dass Phase 4 sie füllt.
2. In `create-theme-zip.js` die Funktion `shouldIncludeFile()` erweitern:
   Dateien unterhalb von `blocks/` mit den Endungen `.php` und `.json`
   aufnehmen. Die bestehende Ausschlussprüfung am Anfang der Funktion bleibt
   unverändert und wird weiterhin zuerst ausgewertet.
3. Zusätzlich in `INCLUDE_PATTERNS` (der beschreibenden Liste am Dateianfang)
   `blocks/**/*.{php,json}` ergänzen, damit die Liste den tatsächlichen
   Umfang wiedergibt.
4. Prüfen, dass keine der bestehenden Regeln versehentlich weiter oder enger
   wird. Insbesondere darf `.json` **nicht** pauschal für das ganze Theme
   freigegeben werden – `package.json` und `package-lock.json` sollen weiterhin
   draußen bleiben.

**Akzeptanzkriterien:**
- [ ] `npm run build` im Ordner `Theme/` läuft ohne Fehler durch.
- [ ] Nach dem Build existieren `dist/js/page-index-editor.js`, `dist/js/page-index.js` und `dist/css/page-index-style.css`.
- [ ] Alle sechs bisherigen Ausgabedateien existieren weiterhin (`main.js`, `glossar.js`, `glossar-editor.js`, `page-manager.js`, `glossar-style.css`, `page-manager-style.css`).
- [ ] Die Konsolenausgabe von `npm run zip` listet `blocks/inhaltsverzeichnis/block.json`.
- [ ] Das erzeugte ZIP enthält `blocks/inhaltsverzeichnis/block.json` (per Öffnen des Archivs nachweisbar).
- [ ] Das ZIP enthält **nicht** `package.json`, `package-lock.json` oder Dateien aus `src/`.

**Tests:**
- Smoke-Test: `npm run build` ausführen und die Konsolenausgabe vollständig prüfen.
- Prüfschritt: Das erzeugte `dist/fos-online-schulbuch.zip` öffnen und den Dateibaum sichten. Erwartet werden mindestens: `fos-online-schulbuch/style.css`, `.../functions.php`, `.../includes/page-index.php`, `.../blocks/inhaltsverzeichnis/block.json`, `.../dist/js/page-index-editor.js`, `.../dist/css/page-index-style.css`.
- Prüfschritt: ZIP auf der Live-Site einspielen. Danach die Testseite mit dem Block aufrufen – die Liste erscheint (nicht der Hinweis auf einen fehlenden Block). Falls in AP-3.1 Dateien von Hand hochgeladen wurden, diese vorher entfernen, damit der Test aussagekräftig ist.
- Prüfschritt R8 (Regression): Startseite, Skriptenseite mit Sidebar, Glossarbegriff anklicken, Bild-Lightbox öffnen – alles funktioniert.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.

**Übergabenotiz:**

---

#### AP-3.rev: Unabhängiges Review Phase 3

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus
**Abhängigkeiten:** AP-3.1, AP-3.2, AP-3.3, AP-3.4

**Ziel & Kontext:**
Unabhängige Qualitätsprüfung der Phase 3 durch einen Agenten, der an keiner
Implementierung beteiligt war. Nur lesend arbeiten – KEINE Datei verändern.

**Vorgehen:**
1. Für jedes Implementierungs-AP der Phase (AP-3.1 bis AP-3.4): den Code gegen
   dessen Akzeptanzkriterien prüfen, mit Stichproben im Quelltext.
2. Cache-Korrektheit prüfen: Enthält der Transient-Schlüssel wirklich die
   Indexversion? Gibt es einen Pfad, auf dem gerendert wird, ohne dass
   `simple_clean_page_index_sanitize_attrs()` gelaufen ist (dann wäre der
   Schlüssel instabil)? Ist die Schlüsselreihenfolge des bereinigten
   Attribut-Arrays fest, sodass `serialize()` bei gleichen Werten immer
   dieselbe Zeichenkette ergibt?
3. Request-Unabhängigkeit prüfen: Greift der Renderer irgendwo auf
   `get_the_ID()`, `is_page()`, `$post`, `get_queried_object()`,
   `is_user_logged_in()` oder Ähnliches zu? Jeder solche Zugriff ist ein
   kritischer Befund, weil er den Fragment-Cache falsch macht.
4. Ausgabesicherheit prüfen: Sind **alle** Titel durch `esc_html()`, alle URLs
   durch `esc_url()` und alle Attributwerte durch `esc_attr()` geführt? Ein
   Seitentitel kann Sonderzeichen enthalten.
5. Datenquelle prüfen: Ruft der Renderer wirklich nur
   `simple_clean_get_page_index()` auf – kein `get_pages()`, kein
   `get_permalink()`, kein `get_post()`, keine eigene SQL-Abfrage?
6. Robustheit prüfen: Was passiert bei leerem Index? Bei `rootPage` auf eine
   gelöschte Seite? Greift der Rekursionsschutz? Wird `maxDepth` wirklich
   eingehalten?
7. Build-Kette prüfen: Ist `.json` in `create-theme-zip.js` eng genug gefasst,
   sodass `package.json` weiterhin ausgeschlossen bleibt? Enthält
   `page-index-editor.js` tatsächlich keine `import`/`export`-Anweisung?
8. PHP 7.4 prüfen: keine Sprachmittel ab 8.0.
9. Scope-Check: Wurde etwas außerhalb der AP-Scopes geändert? Wurden die
   Nicht-Ziele eingehalten (keine Sidebar-Änderung, kein Nachladen per AJAX,
   keine Fremdbibliothek)?
10. Befunde als Review-Bericht in die Übergabenotiz: je Befund Schweregrad
    (kritisch / mittel / gering), betroffenes AP, Datei und Fundstelle.

**Akzeptanzkriterien:**
- [ ] Jedes Implementierungs-AP der Phase wurde gegen seine Akzeptanzkriterien geprüft.
- [ ] Die Prüfung auf request-abhängige Zugriffe im Renderer ist dokumentiert (gesuchte Funktionsnamen und Ergebnis).
- [ ] Alle Befunde mit Schweregrad, Datei und Fundstelle dokumentiert.
- [ ] Keine Datei wurde verändert.

**Tests:**
- entfällt (Review-AP; das Ergebnis ist der Bericht).

**Übergabenotiz:**

---

#### AP-3.doc: Dokumentation Phase 3 aktualisieren

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-3.rev

**Ziel & Kontext:**
`Theme/CLAUDE.md` um den neuen Block und die erweiterte Build-Kette ergänzen.

**Betroffene Dateien:**
- `Theme/CLAUDE.md` (ändern)
- `Theme/docs/PLAN-Seitenindex.md` (ändern – Statustabelle, Testprotokoll, Datum im Kopf)

**Vorgehen:**
1. Alle Übergabenotizen der Phase 3 durchgehen.
2. In `Theme/CLAUDE.md` einen Abschnitt „Inhaltsverzeichnis-Block
   (blocks/inhaltsverzeichnis)" ergänzen: Blockname, die acht Attribute mit
   Standardwerten, Fundort der `block.json`, die Registrierung über
   `render_callback` (samt Begründung: `"render"` in der `block.json` setzt
   WordPress 6.1 voraus und scheitert auf älteren Versionen stillschweigend),
   die Renderfunktion `simple_clean_render_page_index()` und das
   Cache-Verhalten (Indexversion im Transient-Schlüssel).
3. Die Grundregel „request-unabhängige Ausgabe" mit ihrer Begründung
   festhalten – sie ist beim Weiterentwickeln leicht zu verletzen.
4. Im Abschnitt zum Build-System ergänzen: Es gibt jetzt neun Vite-Einträge,
   und `create-theme-zip.js` nimmt zusätzlich `blocks/**/*.{php,json}` auf.
   Ausdrücklich warnen: Neue Dateitypen im Theme müssen in
   `create-theme-zip.js` freigegeben werden, sonst fehlen sie im ZIP und der
   Fehler zeigt sich erst auf der Live-Site.
5. Die ESM-Falle bei Vite-gebauten Editor-Scripts festhalten (keine
   `import`/`export`, Zugriff über `wp.*`-Globale).
6. Statustabelle und Testprotokoll dieses Plans auf den Stand bringen,
   „Letzte Aktualisierung" im Dateikopf setzen.
7. Branch `phase-3-block` nach `main` mergen und `main` pushen.

**Akzeptanzkriterien:**
- [ ] `Theme/CLAUDE.md` beschreibt den Block mit allen acht Attributen und Standardwerten.
- [ ] Der Warnhinweis zur ZIP-Whitelist und die ESM-Falle sind dokumentiert.
- [ ] Statustabelle und Testprotokoll dieses Plans sind für alle APs der Phase 3 gefüllt.
- [ ] `phase-3-block` ist nach `main` gemerged und `main` gepusht.

**Tests:**
- Stichprobe: Die in `Theme/CLAUDE.md` genannten Attributnamen mit `Theme/blocks/inhaltsverzeichnis/block.json` abgleichen – Namen und Standardwerte müssen übereinstimmen.

**Übergabenotiz:**

---

### Phase 4: Gestaltung

Gemeinsame Vereinbarungen für alle APs dieser Phase:

**Farben werden abgeleitet, nicht gesetzt.** Das Theme gibt Farben als
CSS-Variablen in `:root` aus, erzeugt in `simple_clean_customizer_css()`
(`Theme/functions.php`, eingehängt auf `wp_head`). Vorhandene Variablen, die
hier zu nutzen sind: `--color-ui-surface`, `--color-ui-surface-dark`,
`--color-ui-surface-light`, `--color-special-text`, `--color-sidebar-border`,
`--color-text-primary`, `--color-background`, `--color-background-light`.
Feste Hexwerte im neuen CSS sind nicht zulässig – sie würden die
Customizer-Einstellungen des Nutzers stillschweigend aushebeln.

**Der „plastische Look" bleibt außen vor.** Laut `Theme/CLAUDE.md` ist er
bewusst auf `.sidebar-toggle-btn` begrenzt. Die Kapitelkarten bleiben flach.
Nicht „der Einheitlichkeit wegen" ausweiten.

**Neue Variablen** tragen den Präfix `--pidx-` und werden in
`simple_clean_customizer_css()` ausgegeben, nicht in `style.css` fest verdrahtet.

---

#### AP-4.1: Gestaltung des Inhaltsverzeichnisses

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet
**Abhängigkeiten:** AP-3.doc

**Ziel & Kontext:**
Die in AP-3.2 erzeugte HTML-Struktur bekommt ihr Aussehen: die oberste Ebene
als Kapitelkarten in einem Raster, die Unterseiten als aufklappbare Liste
darunter. Ziel ist eine Übersicht, die auch bei mehreren hundert Seiten
überschaubar bleibt: sichtbar sind zunächst nur die Kapitel, alles Weitere
liegt hinter `<details>`.

Die Datei `Theme/src/css/page-index.css` existiert seit AP-3.4 als Platzhalter
und ist bereits als Vite-Eintrag `page-index-style` registriert; das Ergebnis
landet in `dist/css/page-index-style.css`.

Zuerst diesen Branch anlegen: `git checkout main`, `git pull`,
`git checkout -b phase-4-design`, und `Theme/dist/fos-online-schulbuch.zip`
nach `Theme/backups/fos-online-schulbuch-rollback-phase4.zip` kopieren.

**Betroffene Dateien:**
- `Theme/src/css/page-index.css` (ändern – Platzhalter durch die vollständige Gestaltung ersetzen)
- `Theme/backups/fos-online-schulbuch-rollback-phase4.zip` (neu, Kopie)

**Vorgehen:**
1. Am Dateianfang einen `:root`-Block mit Rückfallwerten für die
   `--pidx-`-Variablen anlegen, damit das CSS auch dann sinnvoll aussieht,
   wenn AP-4.3 noch nicht gelaufen ist. Jede Variable leitet sich aus einer
   bestehenden Theme-Variablen ab:
   - `--pidx-card-bg`: Rückfall `var(--color-background)`
   - `--pidx-card-border`: Rückfall `var(--color-sidebar-border)`
   - `--pidx-card-title`: Rückfall `var(--color-special-text)`
   - `--pidx-accent`: Rückfall `var(--color-ui-surface)`
   - `--pidx-radius`: Rückfall `8px`
   - `--pidx-gap`: Rückfall `1rem`
2. `.page-index__chapters` als CSS-Grid gestalten. Die Spaltenzahl kommt aus
   den Klassen `.page-index--cols-1` bis `.page-index--cols-4`. Verwende
   `repeat(auto-fit, minmax(…, 1fr))` in Kombination mit der jeweiligen
   Höchstspaltenzahl, damit das Raster von selbst umbricht.
3. `.page-index__chapter` als Karte: Hintergrund `var(--pidx-card-bg)`,
   Rahmen 1px `var(--pidx-card-border)`, Eckenradius `var(--pidx-radius)`,
   Innenabstand, dezenter Übergang beim Überfahren (Rahmenfarbe wechselt auf
   `var(--pidx-accent)`). **Kein** Verlauf, kein Glanz, kein Schlagschatten
   im Stil des plastischen Looks.
4. `.page-index__chapter-link`: als Überschrift gestalten (kräftiger,
   Farbe `var(--pidx-card-title)`, keine Unterstreichung, Unterstreichung
   beim Überfahren).
5. `.page-index__sub-toggle` (`<summary>`): als klickbare Zeile gestalten,
   `cursor: pointer`, `list-style: none` und `::-webkit-details-marker
   { display: none }`, stattdessen ein eigenes Dreieck über `::before`, das
   sich bei `details[open]` dreht. Sichtbarer Fokusrahmen für
   Tastaturbedienung (`:focus-visible`).
6. `.page-index__pages` als schlichte Liste ohne Aufzählungszeichen,
   `.page-index__page-link` in `var(--color-text-primary)` mit Akzentfarbe
   beim Überfahren.
7. `.page-index--list`: Kartenoptik abschalten (kein Rahmen, kein
   Hintergrund, einspaltig), damit dieses Layout wirklich schlicht ist.
8. `.page-index--columns`: die Kapitel in CSS-Mehrspaltensatz
   (`column-count`) statt Grid; `break-inside: avoid` auf den Einträgen.
9. `.page-index__search-wrap` und `.page-index__search`: Eingabefeld über die
   volle Breite, Rahmen in `var(--pidx-card-border)`, Fokusrahmen in
   `var(--pidx-accent)`.
10. Die vom Filter in AP-4.2 gesetzte Klasse `.page-index__chapter--hidden`
    und `.page-index__page--hidden` mit `display: none` versehen, sowie eine
    Klasse `.page-index__no-results` für die Meldung „Keine Treffer".
11. Reaktionsfähigkeit an den Theme-Haltepunkten: unter 768px höchstens zwei
    Spalten, unter 480px eine Spalte und geringere Innenabstände. Die
    bestehenden Haltepunkte des Themes (992px, 768px, 480px) verwenden.
12. `@media print`: alle `<details>` aufgeklappt darstellen
    (`details > *:not(summary) { display: block !important; }` bzw. über
    `details[open]`-unabhängige Regel), Suchfeld ausblenden. Eine
    Inhaltsverzeichnisseite wird erfahrungsgemäß ausgedruckt.
13. `@media (prefers-reduced-motion: reduce)`: Übergänge abschalten.

**Akzeptanzkriterien:**
- [ ] `Theme/src/css/page-index.css` enthält keinen einzigen festen Hexfarbwert (Textsuche nach `#` liefert nur Treffer in Kommentaren).
- [ ] Nach `npm run build` existiert `Theme/dist/css/page-index-style.css` und ist größer als 1 KB.
- [ ] Auf einem Bildschirm über 1200px erscheinen die Kapitel bei `columns = 3` dreispaltig.
- [ ] Unter 480px erscheinen sie einspaltig.
- [ ] Ein `<summary>` lässt sich mit der Tabulatortaste anspringen und mit der Leertaste öffnen; der Fokus ist sichtbar.
- [ ] Bei `layout = list` erscheint keine Kartenoptik (kein Rahmen, kein Hintergrund).
- [ ] In der Druckvorschau des Browsers sind alle Unterseiten sichtbar und das Suchfeld ausgeblendet.

**Tests:**
- Smoke-Test: `npm run build` läuft durch; ZIP einspielen; Testseite mit dem Block aufrufen – die Karten erscheinen gestaltet, Browser-Konsole ohne Fehler.
- Prüfschritt: Das Browserfenster von breit nach schmal ziehen und die drei Haltepunkte prüfen (über 992px, zwischen 768px und 992px, unter 480px).
- Prüfschritt: Im Customizer unter „Farbeinstellungen" die UI-Oberflächen-Farbe ändern und speichern. Die Akzentfarbe des Inhaltsverzeichnisses (Rahmen beim Überfahren, Fokusrahmen) muss mitgehen.
- Prüfschritt: Druckvorschau öffnen (Strg+P) und die Darstellung prüfen.
- Prüfschritt R1 (Regression): Sidebar auf einer Skriptenseite öffnen und schließen – Aussehen und Verhalten unverändert. Insbesondere darf der Navigationsstreifen `.sidebar-toggle-btn` seinen plastischen Look behalten.

**Übergabenotiz:**

---

#### AP-4.2: Filterfeld im Browser

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet
**Abhängigkeiten:** AP-4.1

**Ziel & Kontext:**
Bei mehreren hundert Seiten ist Blättern mühsam. Das in AP-3.2 ausgegebene
Suchfeld bekommt Funktion: Es filtert die bereits im HTML vorhandenen Einträge
im Browser. Es findet **keine** Netzwerkanfrage statt – die Liste ist ohnehin
vollständig vorhanden.

Die Datei `Theme/src/js/page-index.js` existiert seit AP-3.4 als Platzhalter
und ist als Vite-Eintrag `page-index` registriert; das Ergebnis landet in
`dist/js/page-index.js`.

Zusätzlich wird das Frontend-Bündel nur dort geladen, wo es gebraucht wird.

**Betroffene Dateien:**
- `Theme/src/js/page-index.js` (ändern – Platzhalter durch die Umsetzung ersetzen)
- `Theme/includes/page-index.php` (ändern – bedingtes Einhängen von CSS und JS)

**Vorgehen:**
1. `page-index.js` umsetzen. Wie bei den anderen Theme-Bündeln **ohne**
   `import`/`export`, gekapselt in einer sofort ausgeführten Funktion:
   - Auf `DOMContentLoaded` alle `.page-index` im Dokument durchgehen (es
     können mehrere Verzeichnisse auf einer Seite stehen).
   - Je Verzeichnis das `.page-index__search` suchen; fehlt es, nichts tun.
   - Beim Tippen (Ereignis `input`, entprellt um etwa 150 ms) den Suchtext in
     Kleinbuchstaben normalisieren und jeden `.page-index__page` prüfen:
     Enthält der Linktext den Suchtext, bleibt der Eintrag sichtbar, sonst
     bekommt er `.page-index__page--hidden`.
   - Ein `.page-index__chapter` wird ausgeblendet, wenn weder sein eigener
     Titel passt noch eine seiner Unterseiten sichtbar bleibt.
   - Passt ein Kapitel selbst, bleiben alle seine Unterseiten sichtbar.
   - Bei aktivem Suchtext alle `<details>` innerhalb sichtbarer Kapitel
     öffnen, damit Treffer nicht hinter zugeklappten Ebenen verborgen sind.
     Beim Leeren des Feldes den Ausgangszustand wiederherstellen: alle
     `--hidden`-Klassen entfernen und die `<details>` in den Zustand
     zurücksetzen, den sie beim Laden hatten (beim Start je `<details>`
     merken, ob es geöffnet war).
   - Bleibt kein Kapitel sichtbar, eine Meldung
     `<p class="page-index__no-results">Keine Treffer.</p>` einfügen bzw.
     wieder entfernen.
   - Die Anzahl der Treffer über ein Element mit `aria-live="polite"`
     bekanntgeben, damit Screenreader die Filterung mitbekommen.
2. In `Theme/includes/page-index.php` eine Funktion
   `simple_clean_page_index_frontend_assets()` anlegen, eingehängt auf
   `wp_enqueue_scripts`:
   - Sofort zurückkehren, wenn `is_admin()`.
   - `has_block('fos/inhaltsverzeichnis')` prüfen. Trifft das nicht zu,
     zusätzlich prüfen, ob die Seite Container-Blöcke des CDB-Plugins
     enthält (`has_block('container-block-designer/container')`), denn der
     Block kann darin verschachtelt sein. `has_block()` erkennt verschachtelte
     Blöcke im gespeicherten Inhalt zuverlässig, weil es den rohen
     Beitragsinhalt durchsucht – die zusätzliche Prüfung ist eine
     Absicherung für den Fall, dass ein Container seinen Inhalt anders
     ablegt. Trifft keine der Prüfungen zu, nichts einhängen.
   - CSS einhängen: Handle `simple-clean-page-index-style`, Pfad
     `dist/css/page-index-style.css`, mit `file_exists()`-Guard und
     `filemtime()` als Version.
   - JS einhängen: Handle `simple-clean-page-index`, Pfad
     `dist/js/page-index.js`, ebenso abgesichert, im Footer.
3. Kommentar über der Funktion, der die bedingte Ladelogik und den Grund für
   die zusätzliche Container-Prüfung erklärt.

**Akzeptanzkriterien:**
- [ ] Auf einer Seite **ohne** den Block sind weder `page-index-style.css` noch `page-index.js` im Seitenquelltext eingebunden.
- [ ] Auf der Testseite **mit** dem Block sind beide eingebunden.
- [ ] Eingabe von drei Buchstaben ins Suchfeld blendet nicht passende Einträge aus; passende bleiben sichtbar.
- [ ] Kapitel ohne sichtbare Unterseiten und ohne eigenen Treffer verschwinden.
- [ ] Leeren des Suchfelds stellt exakt den Ausgangszustand wieder her, einschließlich der ursprünglich zugeklappten `<details>`.
- [ ] Eine Sucheingabe ohne Treffer zeigt „Keine Treffer."
- [ ] `Theme/src/js/page-index.js` enthält weder `import ` noch `export `.
- [ ] Bei deaktiviertem JavaScript ist die Liste weiterhin vollständig nutzbar (Aufklappen funktioniert über `<details>`), nur das Filtern entfällt.

**Tests:**
- Smoke-Test: `npm run build`, ZIP einspielen, Testseite aufrufen – Suchfeld erscheint, Browser-Konsole ohne Fehler.
- Prüfschritt: Nacheinander eingeben: ein Wort, das in mehreren Titeln vorkommt · ein Wort, das nur in einem Kapiteltitel vorkommt · eine Zeichenfolge ohne Treffer. Jeweils das Ergebnis prüfen.
- Prüfschritt: Suchfeld leeren und prüfen, ob zugeklappte Ebenen wieder zugeklappt sind.
- Prüfschritt: Eine normale Skriptenseite aufrufen und im Seitenquelltext nach `page-index` suchen – kein Treffer.
- Prüfschritt: Den Block innerhalb eines CDB-Container-Blocks einfügen, speichern, Frontend aufrufen – Gestaltung und Filter funktionieren dort ebenfalls.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.

**Übergabenotiz:**

---

#### AP-4.3: Customizer-Sektion „Inhaltsverzeichnis"

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet
**Abhängigkeiten:** AP-4.2

**Ziel & Kontext:**
Die Block-Optionen aus AP-3.3 regeln Aufbau und Umfang je Einfügung. Das
Aussehen soll dagegen global einstellbar sein, an derselben Stelle wie die
übrigen Theme-Farben. Dafür wird die bestehende Customizer-Registrierung
erweitert – keine zweite Struktur aufbauen.

Die einschlägigen Funktionen stehen in `Theme/functions.php`:
`simple_clean_customize_register($wp_customize)` legt Sektionen, Einstellungen
und Steuerelemente an; `simple_clean_customizer_css()` gibt die Werte als
CSS-Variablen in `:root` aus, eingehängt auf `wp_head`.

**Betroffene Dateien:**
- `Theme/functions.php` (ändern – beide genannten Funktionen)

**Vorgehen:**
1. In `simple_clean_customize_register()` eine neue Sektion
   `simple_clean_page_index` mit dem Titel „Inhaltsverzeichnis" und einer
   Priorität knapp hinter `simple_clean_colors` anlegen.
2. Folgende Einstellungen registrieren, jeweils mit `sanitize_callback` und
   `'transport' => 'refresh'` wie bei den bestehenden Farbeinstellungen:

   | Einstellung | Typ | Standard | Beschriftung | Bereinigung |
   |---|---|---|---|---|
   | `pidx_card_bg` | Farbe | `#ffffff` | Kartenhintergrund | `sanitize_hex_color` |
   | `pidx_card_border` | Farbe | `#e0e0e0` | Kartenrahmen | `sanitize_hex_color` |
   | `pidx_card_title` | Farbe | `#71230a` | Farbe der Kapitelüberschriften | `sanitize_hex_color` |
   | `pidx_radius` | Zahl | `8` | Eckenradius in Pixel (0–24) | `absint`, auf 0–24 begrenzen |
   | `pidx_density` | Auswahl | `normal` | Dichte: `kompakt`, `normal`, `luftig` | nur die drei Werte zulassen, sonst `normal` |

   Für `pidx_radius` ein `WP_Customize_Control` vom Typ `number` mit
   `input_attrs` (`min` 0, `max` 24, `step` 1) verwenden, für `pidx_density`
   ein `select`. Für die drei Farben `WP_Customize_Color_Control` wie bei den
   bestehenden Farbreglern.
3. Jede Einstellung bekommt eine kurze deutsche `description`, die erklärt,
   worauf sie wirkt.
4. In `simple_clean_customizer_css()` die Werte auslesen und im bestehenden
   `:root`-Block ergänzen:
   - `--pidx-card-bg`, `--pidx-card-border`, `--pidx-card-title` direkt.
   - `--pidx-accent` wird **nicht** als eigener Regler angeboten, sondern auf
     `var(--color-ui-surface)` gesetzt – die Akzentfarbe soll bewusst der
     übrigen Oberfläche folgen.
   - `--pidx-radius` als `<wert>px`.
   - `--pidx-gap` aus `pidx_density` ableiten: `kompakt` = `0.5rem`,
     `normal` = `1rem`, `luftig` = `1.75rem`.
   Alle Werte vor der Ausgabe durch `esc_attr()` bzw. bei Zahlen durch
   `absint()` führen.
5. Die Rückfallwerte in `Theme/src/css/page-index.css` (aus AP-4.1) bleiben
   bestehen – sie greifen, solange der Nutzer nichts eingestellt hat.

**Akzeptanzkriterien:**
- [ ] Im Customizer erscheint die Sektion „Inhaltsverzeichnis" mit fünf Einstellungen.
- [ ] Eine Änderung an „Kartenhintergrund" wirkt nach dem Speichern im Frontend.
- [ ] Eine Änderung an „Dichte" verändert sichtbar den Abstand zwischen den Karten.
- [ ] Der Eckenradius 0 erzeugt eckige Karten, der Wert 24 deutlich abgerundete.
- [ ] Im Seitenquelltext enthält der `:root`-Block alle sechs `--pidx-`-Variablen.
- [ ] Die acht bestehenden Farbregler funktionieren unverändert.
- [ ] `php -l Theme/functions.php` meldet keinen Fehler.

**Tests:**
- Smoke-Test: ZIP einspielen, Customizer öffnen – er lädt, die neue Sektion erscheint, Vorschau zeigt die Seite.
- Prüfschritt: Jede der fünf Einstellungen einmal verändern, speichern und im Frontend prüfen.
- Prüfschritt R5 (Regression): Unter „Farbeinstellungen" die UI-Oberflächen-Farbe ändern und speichern – Kopfleiste, Sidebar-Streifen und Akzentfarbe des Inhaltsverzeichnisses gehen gemeinsam mit.
- Prüfschritt: Einen ungültigen Wert erzwingen, indem die Einstellung `pidx_density` über die Browser-Entwicklerwerkzeuge auf einen unbekannten Wert gesetzt und gespeichert wird – die Ausgabe muss auf `normal` zurückfallen und darf keinen PHP-Fehler erzeugen.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.

**Übergabenotiz:**

---

#### AP-4.4: Einzelne Seiten vom Inhaltsverzeichnis ausnehmen

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-4.3

**Ziel & Kontext:**
Nicht jede veröffentlichte Seite gehört ins Inhaltsverzeichnis – etwa
Hilfsseiten, das Impressum oder die Inhaltsverzeichnisseite selbst. Dafür
bekommt die bereits vorhandene Meta-Box eine zweite Auswahlmöglichkeit.

Die Meta-Box ist in `Theme/functions.php` registriert
(`simple_clean_add_navigation_meta_box()`, Titel „Seitenleiste (Sidebar)
Einstellungen", Post-Type `page`, Position `side`). Ausgegeben wird sie in
`simple_clean_navigation_meta_box_callback($post)`, gespeichert in
`simple_clean_save_navigation_meta($post_id)` – dort werden Nonce, Autosave,
Berechtigung und Post-Type bereits geprüft.

Der Index berücksichtigt das Meta `_simple_clean_hide_from_index` seit AP-2.1
bereits, samt Ausschluss des gesamten Unterbaums. In diesem AP entsteht nur
die Bedienoberfläche.

**Betroffene Dateien:**
- `Theme/functions.php` (ändern – Meta-Box-Ausgabe, Speicherung und Titel der Box)

**Vorgehen:**
1. Den Titel der Meta-Box von „Seitenleiste (Sidebar) Einstellungen" auf
   „Navigation & Inhaltsverzeichnis" ändern, weil sie nun zwei Dinge regelt.
   Die Meta-Box-ID `simple_clean_hide_navigation` **nicht** ändern – daran
   hängen gespeicherte Bildschirmeinstellungen der Benutzer.
2. In `simple_clean_navigation_meta_box_callback()` unterhalb des bestehenden
   Kastens einen zweiten Kasten im gleichen Stil ergänzen mit einer Checkbox
   `simple_clean_hide_from_index` und der Beschriftung „Diese Seite nicht im
   Inhaltsverzeichnis anzeigen". In der Beschreibung deutlich darauf
   hinweisen, dass damit auch **alle Unterseiten** aus dem Inhaltsverzeichnis
   verschwinden, und dass die Seite selbst weiterhin erreichbar bleibt.
3. Das bestehende Nonce-Feld wird mitgenutzt – kein zweites Nonce anlegen.
4. In `simple_clean_save_navigation_meta()` die zweite Checkbox mitspeichern:
   bei gesetztem Häkchen `update_post_meta($post_id, '_simple_clean_hide_from_index', '1')`,
   sonst `delete_post_meta($post_id, '_simple_clean_hide_from_index')`.
   Der bestehende Umgang mit `_simple_clean_hide_navigation` bleibt
   unverändert.
5. Nach dem Speichern die Index-Invalidierung auslösen: am Ende der Funktion
   `simple_clean_invalidate_page_index()` aufrufen, abgesichert mit
   `function_exists()`. Zwar feuert `save_post_page` ohnehin und AP-2.2 hängt
   dort, aber die Reihenfolge der Rückrufe ist nicht garantiert – der
   Meta-Wert muss gespeichert sein, bevor invalidiert wird. Ein zusätzlicher
   Aufruf schadet nicht, da die Invalidierung nur einen Zähler erhöht.
6. Kommentar an der neuen Checkbox, der auf das Zusammenspiel mit
   `includes/page-index.php` verweist.

**Akzeptanzkriterien:**
- [ ] Die Meta-Box heißt „Navigation & Inhaltsverzeichnis" und zeigt beide Auswahlmöglichkeiten.
- [ ] Häkchen an einer Seite setzen und speichern: Die Seite verschwindet beim nächsten Aufruf aus dem Inhaltsverzeichnis.
- [ ] Auch die Unterseiten dieser Seite verschwinden aus dem Inhaltsverzeichnis.
- [ ] Die Seite selbst bleibt über ihre URL erreichbar und erscheint weiterhin in der Sidebar.
- [ ] Häkchen wieder entfernen und speichern: Seite und Unterseiten erscheinen wieder.
- [ ] Die bestehende Auswahl „Seitenleiste ausblenden" funktioniert unverändert.
- [ ] `php -l Theme/functions.php` meldet keinen Fehler.

**Tests:**
- Smoke-Test: ZIP einspielen, eine Seite im Editor öffnen – die Meta-Box erscheint mit beiden Auswahlmöglichkeiten.
- Prüfschritt: An einer Seite mit mindestens einer Unterseite das Häkchen setzen, speichern, Testseite mit dem Block aufrufen – Seite und Unterseite fehlen.
- Prüfschritt: Dieselbe Seite direkt über ihre URL aufrufen – sie lädt normal und zeigt die Sidebar.
- Prüfschritt R4 (Regression): An einer anderen Seite „Seitenleiste ausblenden" setzen und speichern – die Sidebar verschwindet dort, das Inhaltsverzeichnis bleibt unverändert.
- Prüfschritt: Häkchen zurücknehmen und prüfen, dass alles wieder erscheint.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.

**Übergabenotiz:**

---

#### AP-4.rev: Unabhängiges Review Phase 4

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus
**Abhängigkeiten:** AP-4.1, AP-4.2, AP-4.3, AP-4.4

**Ziel & Kontext:**
Unabhängige Qualitätsprüfung der Phase 4 durch einen Agenten, der an keiner
Implementierung beteiligt war. Nur lesend arbeiten – KEINE Datei verändern.

**Vorgehen:**
1. Für jedes Implementierungs-AP der Phase (AP-4.1 bis AP-4.4): den Code gegen
   dessen Akzeptanzkriterien prüfen, mit Stichproben im Quelltext.
2. Farbregel prüfen: Enthält `Theme/src/css/page-index.css` feste Farbwerte
   außerhalb von Kommentaren (Hexwerte, `rgb(`, benannte Farben)? Jeder Fund
   ist ein Befund, weil er die Customizer-Einstellung aushebelt.
3. Abgrenzung zum plastischen Look prüfen: Wurden Verläufe, Glanzflächen oder
   die `--plastic-`-Variablen im neuen CSS verwendet? Das wäre ein Verstoß
   gegen die in `Theme/CLAUDE.md` festgehaltene Entscheidung.
4. Bedingtes Laden prüfen: Kann `simple_clean_page_index_frontend_assets()`
   Assets auf Seiten einhängen, die den Block gar nicht enthalten? Kann es
   umgekehrt Seiten geben, die den Block enthalten, aber keine Assets
   bekommen (etwa bei Verschachtelung in einem Container-Block)?
5. Barrierefreiheit prüfen: Hat das `<nav>` ein `aria-label`? Ist der
   Fokusrahmen des `<summary>` sichtbar? Gibt es einen `aria-live`-Bereich
   für die Trefferanzahl? Funktioniert die Liste ohne JavaScript?
6. Wiederherstellung nach dem Filtern prüfen: Stellt das Leeren des Suchfelds
   den Ausgangszustand der `<details>` wirklich wieder her, oder bleiben
   Ebenen offen, die vorher zu waren?
7. Bereinigung im Customizer prüfen: Werden alle fünf Einstellungen bereinigt?
   Kann ein unerwarteter Wert für `pidx_density` ungeprüft in das CSS
   gelangen?
8. Scope-Check: Wurde `sidebar.php` unverändert gelassen? Wurde `style.css`
   angefasst (sollte in dieser Phase nicht nötig sein außer der automatischen
   Versionszeile)? Wurden die Nicht-Ziele eingehalten?
9. PHP 7.4 prüfen: keine Sprachmittel ab 8.0.
10. Befunde als Review-Bericht in die Übergabenotiz: je Befund Schweregrad
    (kritisch / mittel / gering), betroffenes AP, Datei und Fundstelle.

**Akzeptanzkriterien:**
- [ ] Jedes Implementierungs-AP der Phase wurde gegen seine Akzeptanzkriterien geprüft.
- [ ] Die Suche nach festen Farbwerten im neuen CSS ist dokumentiert (Suchmuster und Ergebnis).
- [ ] Alle Befunde mit Schweregrad, Datei und Fundstelle dokumentiert.
- [ ] Keine Datei wurde verändert.

**Tests:**
- entfällt (Review-AP; das Ergebnis ist der Bericht).

**Übergabenotiz:**

---

#### AP-4.doc: Dokumentation Phase 4 aktualisieren

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-4.rev

**Ziel & Kontext:**
`Theme/CLAUDE.md` und die Wurzel-`CLAUDE.md` um die Gestaltungsmöglichkeiten
ergänzen, damit spätere Bearbeiter wissen, welche Regler es gibt und welche
Regeln beim Erweitern gelten.

**Betroffene Dateien:**
- `Theme/CLAUDE.md` (ändern)
- `CLAUDE.md` im Website-Wurzelverzeichnis (ändern – Abschnitt „Color Scheme")
- `Theme/docs/PLAN-Seitenindex.md` (ändern – Statustabelle, Testprotokoll, Datum im Kopf)

**Vorgehen:**
1. Alle Übergabenotizen der Phase 4 durchgehen.
2. In `Theme/CLAUDE.md` den Abschnitt zum Inhaltsverzeichnis-Block erweitern:
   Klassennamen der Ausgabe, die sechs `--pidx-`-Variablen mit ihrer
   Herkunft, die fünf Customizer-Einstellungen, das bedingte Laden der Assets
   über `has_block()` und die Meta-Box-Erweiterung
   `_simple_clean_hide_from_index` (mit dem Hinweis, dass der gesamte
   Unterbaum entfällt).
3. Ausdrücklich festhalten: Im CSS des Inhaltsverzeichnisses stehen keine
   festen Farbwerte, und der plastische Look bleibt auf
   `.sidebar-toggle-btn` beschränkt.
4. In der Wurzel-`CLAUDE.md` im Abschnitt „Color Scheme" die neue
   Customizer-Sektion „Inhaltsverzeichnis" mit ihren fünf Einstellungen
   ergänzen, damit die dortige Aufstellung vollständig bleibt.
5. Statustabelle und Testprotokoll dieses Plans auf den Stand bringen,
   „Letzte Aktualisierung" im Dateikopf setzen.
6. Branch `phase-4-design` nach `main` mergen und `main` pushen.

**Akzeptanzkriterien:**
- [ ] `Theme/CLAUDE.md` beschreibt Klassennamen, `--pidx-`-Variablen, Customizer-Einstellungen und die Meta-Box-Erweiterung.
- [ ] Die Wurzel-`CLAUDE.md` listet die neue Customizer-Sektion.
- [ ] Statustabelle und Testprotokoll dieses Plans sind für alle APs der Phase 4 gefüllt.
- [ ] `phase-4-design` ist nach `main` gemerged und `main` gepusht.

**Tests:**
- Stichprobe: Zwei in der Dokumentation genannte CSS-Klassen im Quelltext von `Theme/src/css/page-index.css` suchen – beide müssen existieren.
- Stichprobe: Die fünf dokumentierten Customizer-Einstellungsnamen in `Theme/functions.php` suchen – alle fünf müssen registriert sein.

**Übergabenotiz:**

---

### Phase 5: Umstellung, Absicherung und Dokumentation

---

#### AP-5.1: Vollständiger Regressionsdurchlauf

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus (die Bewertung, ob ein abweichendes Verhalten ein Fehler oder eine gewollte Änderung ist, verlangt Urteilsvermögen)
**Abhängigkeiten:** AP-4.doc

**Ziel & Kontext:**
Vor der Umstellung der echten Inhaltsverzeichnisseite wird die gesamte Website
systematisch geprüft. Alle vier Phasen haben an `Theme/functions.php`
gearbeitet, dazu am Seitenmanager, an der Meta-Box und am Customizer – das ist
eine breite Fläche.

Zuerst diesen Branch anlegen: `git checkout main`, `git pull`,
`git checkout -b phase-5-umstellung`, und
`Theme/dist/fos-online-schulbuch.zip` nach
`Theme/backups/fos-online-schulbuch-rollback-phase5.zip` kopieren.

**Betroffene Dateien:**
- keine Codeänderung; Ergebnis ist das Prüfprotokoll. Werden Fehler gefunden, entstehen daraus Korrektur-APs (`AP-5.fix1`, …), die dieses AP als blockiert markieren

**Vorgehen:**
1. Aktuellen Stand von `main` bauen (`php -l` über alle PHP-Dateien, dann
   `npm run build`) und auf der Live-Site einspielen.
2. Die vollständige Regressionsliste R1 bis R8 aus Abschnitt 5.2 abarbeiten
   und jedes Ergebnis einzeln im Testprotokoll festhalten.
3. Zusätzlich diese Prüfungen durchführen:
   - **P1 Seitenmanager:** Seite anlegen, verschieben, Status umschalten,
     löschen. Nach jedem Schritt eine Frontend-Seite mit `?sc_index=1`
     aufrufen und prüfen, dass `version` gestiegen und `count` plausibel ist.
   - **P2 Rebuild-Knopf:** „Seitenindex neu aufbauen" klicken; danach die
     Testseite mit dem Block aufrufen und prüfen, dass die Liste stimmt.
   - **P3 Rollen:** Mit einem Konto der Rolle „Block-Redakteur" anmelden
     (falls vorhanden) bzw. Redakteur: Seiten bearbeiten funktioniert, der
     Rebuild-Knopf ist nicht sichtbar, der Block lässt sich einfügen.
   - **P4 Passwortschutz:** Ist der Website-Passwortschutz aktiv, in einem
     privaten Browserfenster prüfen, dass die Anmeldemaske erscheint und nach
     Eingabe des Passworts die Inhaltsverzeichnisseite lädt.
   - **P5 Plugins:** Eine Seite mit CDB-Container-Block und eine Seite mit
     einem Block aus „Eigene WP Blocks" (etwa Multiple-Choice) aufrufen –
     beide rendern und funktionieren, Konsole ohne Fehler.
   - **P6 Mobil:** Die Testseite mit dem Block auf einem Mobilgerät oder in
     der Geräteansicht der Entwicklerwerkzeuge prüfen: Karten einspaltig,
     Aufklappen funktioniert, Suchfeld bedienbar.
4. Jede Abweichung bewerten: Ist sie eine beabsichtigte Folge dieses Plans
   (etwa: Glossarbegriffe erscheinen auf textarmen Seiten nicht mehr) oder
   ein Fehler? Beabsichtigte Änderungen in die Übergabenotiz, Fehler als
   Korrektur-AP anlegen.

**Akzeptanzkriterien:**
- [ ] Alle acht Regressionsprüfungen R1–R8 sind mit Ergebnis dokumentiert.
- [ ] Alle sechs Zusatzprüfungen P1–P6 sind mit Ergebnis dokumentiert.
- [ ] Jede Abweichung ist als „beabsichtigt" oder „Fehler" eingeordnet und begründet.
- [ ] Für jeden Fehler existiert ein Korrektur-AP in Statustabelle und Testprotokoll.
- [ ] Das Fehlerlog im All-Inkl-KAS enthält nach dem gesamten Durchlauf keine neuen Warnings oder Notices aus Theme-Dateien.

**Tests:**
- Der Durchlauf selbst ist der Test. Ergebnis ist das ausgefüllte Testprotokoll.

**Übergabenotiz:**

---

#### AP-5.2: Verzeichnisseiten umstellen und Wirkung belegen

> **Zugeschnitten am 2026-08-08:** Es sind **drei** Seiten umzustellen, nicht
> eine — `/organische-chemie-und-biochemie/`, `/analytische-chemie/` und
> `/laborsicherheit/`. Jede bekommt ihr eigenes `rootPage`. Die Schritte
> unten gelten sinngemäß je Seite; gemessen wird vorher und nachher auf
> allen dreien.

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-5.1

**Ziel & Kontext:**
Jetzt wird die eigentliche Umstellung vollzogen: Auf der echten
Inhaltsverzeichnisseite wird der Core-Block „Seitenliste" (`core/page-list`)
durch `fos/inhaltsverzeichnis` ersetzt. Anschließend wird die Wirkung gegen
die Ausgangsmessung aus AP-1.2 belegt.

Der Umstieg ist ein Blocktausch auf einer einzigen Seite und jederzeit
umkehrbar – der Core-Block bleibt verfügbar.

**Betroffene Dateien:**
- keine Codeänderung; geändert wird der Inhalt einer WordPress-Seite

**Vorgehen:**
1. Vorher-Messung: Die Inhaltsverzeichnisseite in ihrem jetzigen Zustand
   (noch mit `core/page-list`) als angemeldeter Administrator dreimal mit
   `?sc_perf=1` aufrufen und den mittleren Wert notieren. Zusätzlich die
   Dokumentgröße aus dem Netzwerk-Tab notieren.
2. Die Seite im Block-Editor öffnen. **Vor der Änderung** den bisherigen
   Inhalt sichern: über die Codeansicht des Blocks den Block-Kommentar
   `<!-- wp:page-list ... /-->` in die Übergabenotiz kopieren, damit der
   Ausgangszustand jederzeit wiederherstellbar ist.
3. Den Block „Seitenliste" entfernen und `fos/inhaltsverzeichnis` an
   derselben Stelle einfügen. Einstellungen: `layout` = Kapitelkarten,
   `columns` = 3, `maxDepth` = 2, `collapsible` = ein,
   `openByDefault` = aus, `showSearch` = ein, `showCounts` = ein,
   `rootPage` = 0. Weichen die Wünsche des Nutzers davon ab, seine Vorgaben
   verwenden und die gewählten Werte in der Übergabenotiz festhalten.
4. Seite speichern und im Frontend aufrufen.
5. Inhaltliche Vollständigkeit prüfen: Stichprobenartig fünf Kapitel und je
   drei Unterseiten mit dem Seitenmanager abgleichen – Titel, Reihenfolge und
   Ziel-URL müssen übereinstimmen. Besonders auf Kapitel achten, deren
   Reihenfolge über `menu_order` gesteuert ist.
6. Nachher-Messung: dieselbe Seite dreimal mit `?sc_perf=1` aufrufen,
   mittleren Wert und Dokumentgröße notieren.
7. Vorher/Nachher als Tabelle in die Übergabenotiz und ins Testprotokoll:
   Queryzahl, Zeit, Speicher, Dokumentgröße – jeweils die Werte aus AP-1.2
   (Ausgangszustand vor Phase 1), aus AP-1.6 (nach dem Glossar-Fix) und aus
   diesem AP (nach der Umstellung).
8. Die Inhaltsverzeichnisseite selbst über die Meta-Box aus AP-4.4 aus dem
   Index nehmen, damit sie sich nicht selbst auflistet – sofern sie zuvor in
   der Liste auftauchte.

**Akzeptanzkriterien:**
- [ ] Die Inhaltsverzeichnisseite enthält im Beitragsinhalt keinen `core/page-list`-Block mehr.
- [ ] Der ursprüngliche Block-Kommentar ist in der Übergabenotiz gesichert.
- [ ] Alle stichprobenartig geprüften Kapitel und Unterseiten stimmen in Titel, Reihenfolge und Ziel-URL mit dem Seitenmanager überein.
- [ ] Die Queryzahl der Seite ist gegenüber der Vorher-Messung aus Schritt 1 gesunken.
- [ ] Die Ladezeit der Seite ist gegenüber der Ausgangsmessung aus AP-1.2 gesunken.
- [ ] Die dreistufige Vorher/Nachher-Tabelle steht im Testprotokoll.

**Tests:**
- Smoke-Test: Die Inhaltsverzeichnisseite lädt im Frontend ohne PHP-Meldung; Browser-Konsole ohne Fehler.
- Prüfschritt: Fünf zufällige Links aus dem Verzeichnis anklicken – alle führen auf die erwartete Seite, keine 404.
- Prüfschritt: Suchfeld mit einem bekannten Seitentitel füllen – der Eintrag wird gefunden und ist sichtbar.
- Prüfschritt: Eine im Verzeichnis gelistete Seite umbenennen, speichern, Inhaltsverzeichnisseite neu laden – der neue Titel erscheint sofort.
- Prüfschritt: Als abgemeldeter Besucher (privates Browserfenster) die Seite aufrufen – sie lädt vollständig und enthält keinen `SC-PERF`- oder `SC-INDEX`-Kommentar.
- Prüfschritt: Fehlerlog im All-Inkl-KAS auf neue Warnings/Notices prüfen.

**Übergabenotiz:**

---

#### AP-5.3: Datei-Map für das Theme anlegen

**Status:** ☐ offen
**Umfang:** M
**Modell:** sonnet
**Abhängigkeiten:** AP-5.2

**Ziel & Kontext:**
Für das Theme existiert bisher keine Datei-Map – anders als für das Plugin
„Eigene WP Blocks", das eine `reference_file_map.md` besitzt. Die
Funktionsübersicht in `Theme/CLAUDE.md` ist ein guter Ersatz, aber keine
Navigationshilfe auf Dateiebene. Dieses AP schließt die Lücke und erfasst
dabei sowohl den Bestand als auch die neuen Dateien dieses Vorhabens.

**Betroffene Dateien:**
- `Theme/reference_file_map.md` (neu)
- `DOKUMENTATION.md` im Website-Wurzelverzeichnis (ändern – Verweis ergänzen)

**Vorgehen:**
1. `Theme/reference_file_map.md` anlegen, im Format der bestehenden Datei-Map
   des Plugins „Eigene WP Blocks" (`Plugins/Eigene WP Blocks/reference_file_map.md`):
   Kopfzeile mit Stand-Datum, dann eine Tabelle mit den Spalten
   `Datei | Zweck | Wichtige Funktionen/Inhalte | Hängt ab von`.
2. Alle projektrelevanten Theme-Dateien erfassen:
   - Vorlagen im Wurzelverzeichnis: `functions.php`, `header.php`,
     `footer.php`, `index.php`, `single.php`, `page.php`, `sidebar.php`,
     `archive-glossar.php`, `single-glossar.php`, `style.css`.
   - `includes/page-index.php`, `includes/admin/page-manager.php`,
     `includes/admin/clipboard-uploader.php`,
     `includes/admin/image-lightbox-editor.js`.
   - `blocks/inhaltsverzeichnis/block.json`.
   - Quelldateien: `src/js/main.js`, `src/js/glossar.js`,
     `src/js/glossar-editor.js`, `src/js/page-manager.js`,
     `src/js/page-index.js`, `src/js/page-index-editor.js`,
     `src/css/glossar.css`, `src/css/page-manager.css`,
     `src/css/page-index.css`.
   - Build-Dateien: `vite.config.js`, `create-theme-zip.js`,
     `backup-and-build.js`, `package.json`.
   - Sammelzeilen statt Einzelauflistung für `node_modules/`, `dist/` und
     `backups/`.
3. Bei `functions.php` wegen ihrer Größe nicht alle Funktionen auflisten,
   sondern die Subsysteme nennen (Setup, Assets, Customizer, Meta-Box,
   Glossar, Passwortschutz, AI-Blocker, SVG-Pipeline, Lightbox,
   Performance-Messung) und auf die Funktionsübersicht in `Theme/CLAUDE.md`
   verweisen.
4. Bei den in diesem Vorhaben entstandenen Dateien die Übergabenotizen aller
   Phasen heranziehen, damit Zweck und Funktionen genau stimmen.
5. In `DOKUMENTATION.md` im Website-Wurzelverzeichnis zwei Zeilen ergänzen:
   einen Verweis auf `Theme/reference_file_map.md` und einen auf
   `Theme/docs/PLAN-Seitenindex.md` samt
   `Theme/docs/ERWEITERUNGSANALYSE-Seitenindex.md`.

**Akzeptanzkriterien:**
- [ ] `Theme/reference_file_map.md` existiert und listet mindestens alle oben genannten Dateien.
- [ ] Jede in diesem Vorhaben neu angelegte Datei hat eine Zeile mit Zweck und wichtigsten Funktionen.
- [ ] Die Spalte „Hängt ab von" ist bei den neuen Dateien gefüllt (etwa: `blocks/inhaltsverzeichnis/block.json` hängt an `includes/page-index.php`, das die Registrierung und den `render_callback` bereitstellt).
- [ ] `DOKUMENTATION.md` verweist auf die Datei-Map und auf beide Dokumente in `Theme/docs/`.
- [ ] Kein Eintrag verweist auf eine Datei, die nicht existiert.

**Tests:**
- Stichprobe: Fünf zufällige Zeilen der Datei-Map gegen den echten Dateiinhalt prüfen – Zweck und genannte Funktionen müssen stimmen.
- Prüfschritt: Jeden in der Map genannten Dateipfad auf Existenz prüfen.

**Übergabenotiz:**

---

#### AP-5.4: Abschluss, Auslieferung und Zusammenführung

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-5.3

**Ziel & Kontext:**
Der letzte Stand wird gebaut, geprüft, ausgeliefert und im Repository
zusammengeführt. Zusätzlich wird der Zustand der Diagnosewerkzeuge festgelegt,
die während des Vorhabens entstanden sind.

**Betroffene Dateien:**
- `Theme/CLAUDE.md` (ändern – Abschnitt zu den Diagnoseparametern vervollständigen)
- Build-Artefakte in `Theme/dist/`

**Vorgehen:**
1. Entscheiden und dokumentieren, was mit den beiden Diagnoseausgaben
   geschieht: `simple_clean_perf_footer()` (`?sc_perf=1`) und
   `simple_clean_page_index_debug_footer()` (`?sc_index=1`). **Vorgabe: beide
   bleiben erhalten.** Sie sind je etwa zehn Zeilen groß, doppelt abgesichert
   (Administratorrecht **und** ausdrücklicher Parameter) und geben bei jeder
   künftigen Performance-Frage sofort Auskunft. In `Theme/CLAUDE.md` beide
   in einem gemeinsamen Abschnitt „Diagnose" festhalten, mit Aufrufbeispiel
   und dem Hinweis, dass sie für nicht angemeldete Besucher nichts ausgeben.
2. `php -l` über **alle** PHP-Dateien im Theme laufen lassen: Wurzel,
   `includes/`, `includes/admin/`, `blocks/inhaltsverzeichnis/`. Bei null
   Fehlern weiter.
3. `npm run build` ausführen. Die Version erhöht sich automatisch – nicht von
   Hand ändern. Die neue Versionsnummer notieren.
4. Den ZIP-Inhalt prüfen: Das Archiv muss `blocks/inhaltsverzeichnis/block.json`,
   `includes/page-index.php`, `includes/admin/page-manager.php`,
   `dist/js/page-index.js`, `dist/js/page-index-editor.js` und
   `dist/css/page-index-style.css` enthalten – und weiterhin alle bisherigen
   Dateien.
5. ZIP auf der Live-Site einspielen.
6. Abschließende Sichtprüfung: Startseite · Inhaltsverzeichnisseite ·
   Skriptenseite mit Sidebar und Glossarbegriffen · WP-Admin ·
   Seitenmanager · Block-Editor mit dem Inhaltsverzeichnis-Block.
7. Alle Änderungen committen und `phase-5-umstellung` nach `main` mergen,
   `main` pushen. Anschließend prüfen, dass `git status` sauber ist.
8. Statustabelle und Testprotokoll dieses Plans vollständig füllen,
   „Letzte Aktualisierung" im Dateikopf setzen.
9. In der Übergabenotiz festhalten: die ausgelieferte Versionsnummer, die
   dreistufige Messtabelle aus AP-5.2 als Ergebnis des Vorhabens und alle
   offenen Punkte aus den Review-APs, die als „gering" eingestuft und nicht
   behoben wurden.

**Akzeptanzkriterien:**
- [ ] `php -l` meldet für jede PHP-Datei im Theme „No syntax errors detected".
- [ ] Das erzeugte ZIP enthält alle sechs in Schritt 4 genannten Dateien.
- [ ] Alle sechs Sichtprüfungen aus Schritt 6 sind bestanden und im Testprotokoll vermerkt.
- [ ] `Theme/CLAUDE.md` enthält den Abschnitt „Diagnose" mit beiden Parametern.
- [ ] `main` enthält alle fünf Phasen-Branches und ist gepusht; `git status` ist sauber.
- [ ] Statustabelle und Testprotokoll dieses Plans sind vollständig ausgefüllt.

**Tests:**
- Smoke-Test: Nach dem Einspielen laden alle sechs in Schritt 6 genannten Ansichten ohne weiße Seite und ohne Konsolenfehler.
- Prüfschritt: `?sc_perf=1` und `?sc_index=1` als Administrator aufrufen – beide liefern ihre Zeile. Als abgemeldeter Besucher – beide liefern nichts.
- Prüfschritt: Fehlerlog im All-Inkl-KAS ein letztes Mal prüfen.

**Übergabenotiz:**

---

#### AP-5.rev: Unabhängiges Review Phase 5 und Gesamtabnahme

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus
**Abhängigkeiten:** AP-5.1, AP-5.2, AP-5.3, AP-5.4

**Ziel & Kontext:**
Abschließende unabhängige Prüfung durch einen Agenten, der an keiner
Implementierung beteiligt war. Geprüft wird nicht nur Phase 5, sondern das
Vorhaben als Ganzes gegen Projektziel und Nicht-Ziele. Nur lesend arbeiten –
KEINE Datei verändern.

**Vorgehen:**
1. Für jedes Implementierungs-AP der Phase 5 (AP-5.1 bis AP-5.4): gegen dessen
   Akzeptanzkriterien prüfen.
2. Gesamtziel prüfen (Abschnitt 1 dieses Plans): Belegen die Messwerte im
   Testprotokoll, dass die Inhaltsverzeichnisseite schneller lädt? Sind die
   drei Messzeitpunkte (AP-1.2, AP-1.6, AP-5.2) vergleichbar erhoben?
3. Nicht-Ziele prüfen (Abschnitt 2): Wurde `sidebar.php` verändert? Wurde
   `simple_clean_fallback_menu()` verändert? Gibt es Nachladen per AJAX oder
   REST im Renderer? Wurde eine Fremdbibliothek oder eine CDN-Einbindung
   hinzugefügt? Wurde der plastische Look ausgeweitet? Wurde eine
   Datenbanktabelle angelegt?
4. Vollständigkeit der Dokumentation prüfen: Deckt `Theme/reference_file_map.md`
   alle neuen Dateien ab? Beschreibt `Theme/CLAUDE.md` alle vier neuen
   Subsysteme (Seitenindex, Block, Gestaltung, Diagnose)? Verweist
   `DOKUMENTATION.md` auf die neuen Dokumente?
5. Offene Punkte sammeln: alle in den Reviews der Phasen 1 bis 4 als „mittel"
   oder „gering" eingestuften und nicht behobenen Befunde zusammentragen und
   als Liste ausgeben – sie sind der Ausgangspunkt für spätere Arbeiten.
6. Befunde als Review-Bericht in die Übergabenotiz: je Befund Schweregrad
   (kritisch / mittel / gering), betroffenes AP, Datei und Fundstelle.
   Abschließend eine ausdrückliche Gesamtbewertung: abgenommen oder nicht.

**Akzeptanzkriterien:**
- [ ] Jedes Implementierungs-AP der Phase 5 wurde gegen seine Akzeptanzkriterien geprüft.
- [ ] Jedes der acht Nicht-Ziele ist einzeln geprüft und das Ergebnis dokumentiert.
- [ ] Die Zielerreichung ist anhand der Messwerte aus dem Testprotokoll bewertet.
- [ ] Die Liste offener Punkte aus allen Phasen liegt vor.
- [ ] Eine ausdrückliche Gesamtbewertung ist ausgesprochen.
- [ ] Keine Datei wurde verändert.

**Tests:**
- entfällt (Review-AP; das Ergebnis ist der Bericht).

**Übergabenotiz:**

---

#### AP-5.doc: Abschlussdokumentation

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-5.rev

**Ziel & Kontext:**
Letzter Schliff an der Dokumentation, damit das Vorhaben ohne Kenntnis dieses
Plans nachvollziehbar bleibt und die offenen Punkte nicht verloren gehen.

**Betroffene Dateien:**
- `Theme/reference_file_map.md` (ändern – Stand nach allen Phasen)
- `Theme/CLAUDE.md` (ändern – Abschnitt „Bekannte Einschränkungen")
- `Theme/docs/PLAN-Seitenindex.md` (ändern – Abschluss)

**Vorgehen:**
1. Alle Übergabenotizen der Phase 5 und den Bericht aus AP-5.rev durchgehen.
2. `Theme/reference_file_map.md` gegen den tatsächlichen Dateibestand
   abgleichen und das Stand-Datum aktualisieren.
3. In `Theme/CLAUDE.md` einen Abschnitt „Bekannte Einschränkungen
   Seitenindex" anlegen mit den bewusst nicht umgesetzten Punkten aus
   Abschnitt 2 dieses Plans (nur Post-Type `page`, nur veröffentlichte
   Seiten, kein Nachladen, Sidebar unverändert) sowie den offenen Befunden
   aus AP-5.rev.
4. In diesem Plan alle Statuszeilen auf ihren Endstand setzen, das
   Testprotokoll abschließen und „Letzte Aktualisierung" setzen.
5. Abschließend committen und `main` pushen.

**Akzeptanzkriterien:**
- [ ] `Theme/reference_file_map.md` bildet den tatsächlichen Dateibestand ab (Stichprobe von fünf Zeilen).
- [ ] `Theme/CLAUDE.md` enthält den Abschnitt „Bekannte Einschränkungen Seitenindex".
- [ ] Alle Zeilen der Statustabelle in Abschnitt 8 stehen auf ☑ oder tragen eine Begründung.
- [ ] Das Testprotokoll enthält für jedes AP mindestens eine Zeile.
- [ ] `main` ist gepusht und `git status` ist sauber.

**Tests:**
- Stichprobe: Fünf zufällige Zeilen der Datei-Map gegen den echten Dateiinhalt prüfen.
- Stichprobe: Drei in `Theme/CLAUDE.md` genannte Funktionsnamen im Quelltext suchen – alle drei müssen existieren.

**Übergabenotiz:**

## 8. Status

Wird während der Ausführung gepflegt.
Legende: ☐ offen · ◐ in Arbeit · ☑ erledigt · ✗ blockiert

| AP | Titel | Modell | Status | Abhängig von | Notiz |
|---|---|---|---|---|---|
| AP-1.1 | Ausgangszustand sichern und Phasen-Branch anlegen | sonnet | ☑ | – | Commit `c9322e2` auf `main`; Branch `phase-1-glossar-fallback` aktiv. **Kein PHP 7.4 lokal verfügbar** – siehe Übergabenotiz |
| AP-1.2 | Messbasis für die Inhaltsverzeichnisseite schaffen | sonnet | ◐ | AP-1.1 | Code fertig (`36ad832`, v1.5.69). Offen: ZIP einspielen + Messung durch den Nutzer |
| AP-1.3 | Kandidaten-Fallback im Autolinker an `_glossar_scan_version` koppeln | opus | ◐ | AP-1.2 | Code fertig, Harness 6/6. Build wartet auf die Ausgangsmessung aus AP-1.2 |
| AP-1.4 | Gleiche Korrektur für die Auslieferung von `glossarData` | opus | ☐ | AP-1.3 | |
| AP-1.5 | Flächendeckenden Scan-Stand herstellen und nachweisen | sonnet | ☐ | AP-1.4 | |
| AP-1.6 | Nachmessung und Phasenabschluss Phase 1 | sonnet | ☐ | AP-1.5 | |
| AP-1.rev | Unabhängiges Review Phase 1 | opus | ☐ | AP-1.1 … AP-1.6 | frischer Agent, nur lesend |
| AP-1.doc | Dokumentation Phase 1 aktualisieren | sonnet | ☐ | AP-1.rev | |
| AP-2.1 | Indexaufbau und Speicherung | opus | ☐ | AP-1.doc | legt Branch `phase-2-seitenindex` an |
| AP-2.2 | Invalidierung über WordPress-Hooks | sonnet | ☐ | AP-2.1 | |
| AP-2.3 | Invalidierung im Seitenmanager | sonnet | ☐ | AP-2.2 | kritisch: `$wpdb->update` umgeht `save_post` |
| AP-2.4 | Manueller Neuaufbau und Statusanzeige | sonnet | ☐ | AP-2.3 | |
| AP-2.rev | Unabhängiges Review Phase 2 | opus | ☐ | AP-2.1 … AP-2.4 | frischer Agent, nur lesend |
| AP-2.doc | Dokumentation Phase 2 aktualisieren | sonnet | ☐ | AP-2.rev | |
| AP-3.1 | Block registrieren und Attribute absichern | sonnet | ☐ | AP-2.doc | legt Branch `phase-3-block` an |
| AP-3.2 | Rendering aus dem Index mit Fragment-Cache | opus | ☐ | AP-3.1 | |
| AP-3.3 | Editor-Integration mit Inspektor und Vorschau | sonnet | ☐ | AP-3.2 | |
| AP-3.4 | Build-Kette erweitern | sonnet | ☐ | AP-3.3 | ohne dieses AP fehlt `block.json` im ZIP |
| AP-3.rev | Unabhängiges Review Phase 3 | opus | ☐ | AP-3.1 … AP-3.4 | frischer Agent, nur lesend |
| AP-3.doc | Dokumentation Phase 3 aktualisieren | sonnet | ☐ | AP-3.rev | |
| AP-4.1 | Gestaltung des Inhaltsverzeichnisses | sonnet | ☐ | AP-3.doc | legt Branch `phase-4-design` an |
| AP-4.2 | Filterfeld im Browser | sonnet | ☐ | AP-4.1 | |
| AP-4.3 | Customizer-Sektion „Inhaltsverzeichnis" | sonnet | ☐ | AP-4.2 | |
| AP-4.4 | Einzelne Seiten vom Inhaltsverzeichnis ausnehmen | sonnet | ☐ | AP-4.3 | |
| AP-4.rev | Unabhängiges Review Phase 4 | opus | ☐ | AP-4.1 … AP-4.4 | frischer Agent, nur lesend |
| AP-4.doc | Dokumentation Phase 4 aktualisieren | sonnet | ☐ | AP-4.rev | |
| AP-5.1 | Vollständiger Regressionsdurchlauf | opus | ☐ | AP-4.doc | legt Branch `phase-5-umstellung` an |
| AP-5.2 | Inhaltsverzeichnisseite umstellen und Wirkung belegen | sonnet | ☐ | AP-5.1 | |
| AP-5.3 | Datei-Map für das Theme anlegen | sonnet | ☐ | AP-5.2 | |
| AP-5.4 | Abschluss, Auslieferung und Zusammenführung | sonnet | ☐ | AP-5.3 | |
| AP-5.rev | Unabhängiges Review Phase 5 und Gesamtabnahme | opus | ☐ | AP-5.1 … AP-5.4 | frischer Agent, nur lesend |
| AP-5.doc | Abschlussdokumentation | sonnet | ☐ | AP-5.rev | |

**Hinweis zur Parallelisierung:** Die APs sind bewusst sequenziell verkettet.
Grund: Fast alle greifen auf `Theme/functions.php` oder
`Theme/includes/page-index.php` zu, und getestet wird auf der Live-Site –
zwei gleichzeitig eingespielte Stände wären nicht auseinanderzuhalten.
Innerhalb einer Phase dürfen ausschließlich AP-4.1 (nur CSS) und AP-4.3 (nur
`functions.php`) parallel laufen, sofern sie in getrennten Worktrees
entwickelt und **nacheinander** eingespielt werden.

## 9. Testprotokoll

Wird während der Ausführung gepflegt. Ein Eintrag pro abgeschlossenem AP und
pro Phasenabschluss.

**Messtabelle (wird in AP-1.2, AP-1.6 und AP-5.2 gefüllt):**

| Zeitpunkt | URL | Queries | Zeit (s) | Speicher | Dokumentgröße |
|---|---|---|---|---|---|
| Ausgangszustand (AP-1.2) | Inhaltsverzeichnis | | | | |
| Ausgangszustand (AP-1.2) | Skriptenseite (Vergleich) | | | | |
| Ausgangszustand (AP-1.2) | Startseite (Vergleich) | | | | |
| Nach Glossar-Fix (AP-1.6) | Inhaltsverzeichnis | | | | |
| Nach Umstellung (AP-5.2) | Inhaltsverzeichnis | | | | |

**Prüfprotokoll:**

| Datum | AP / Phase | Getestet | Ergebnis | Getestet von |
|---|---|---|---|---|
| 2026-08-08 | AP-1.1 | Alle 5 Akzeptanzkriterien; `php -l` über 11 PHP-Dateien; ZIP-Inhalt und Version geprüft | **Bestanden.** 11/11 Dateien „No syntax errors detected". Arbeitsbaum sauber, `main` und `phase-1-glossar-fallback` beide auf `c9322e2` und gepusht. Rollback-ZIP 79 145 Bytes, 22 Einträge, `style.css` = v1.5.68 | Claude (AP-Ausführung) |
| | AP-1.2 | | | |
| | AP-1.3 | | | |
| | AP-1.4 | | | |
| | AP-1.5 | | | |
| | AP-1.6 | | | |
| | Phase 1 Integration + Regression R1–R8 | | | |
| | AP-1.rev | | | |
| | AP-1.doc | | | |
| | AP-2.1 | | | |
| | AP-2.2 | | | |
| | AP-2.3 | | | |
| | AP-2.4 | | | |
| | Phase 2 Integration + Regression R1–R8 | | | |
| | AP-2.rev | | | |
| | AP-2.doc | | | |
| | AP-3.1 | | | |
| | AP-3.2 | | | |
| | AP-3.3 | | | |
| | AP-3.4 | | | |
| | Phase 3 Integration + Regression R1–R8 | | | |
| | AP-3.rev | | | |
| | AP-3.doc | | | |
| | AP-4.1 | | | |
| | AP-4.2 | | | |
| | AP-4.3 | | | |
| | AP-4.4 | | | |
| | Phase 4 Integration + Regression R1–R8 | | | |
| | AP-4.rev | | | |
| | AP-4.doc | | | |
| | AP-5.1 (R1–R8 und P1–P6) | | | |
| | AP-5.2 | | | |
| | AP-5.3 | | | |
| | AP-5.4 | | | |
| | Phase 5 Integration + Gesamtabnahme | | | |
| | AP-5.rev | | | |
| | AP-5.doc | | | |

## 10. Dokumentation

- **Arbeits- und Architekturdoku Theme:** `Theme/CLAUDE.md` – bestehende
  Datei, wird je Phase im `AP-<N>.doc` erweitert. Enthält am Ende die
  Abschnitte „Seitenindex", „Inhaltsverzeichnis-Block", „Diagnose" und
  „Bekannte Einschränkungen Seitenindex".
- **Datei-Map:** `Theme/reference_file_map.md` – wird in AP-5.3 erstmals
  angelegt (Bestand plus neue Dateien) und in AP-5.doc abschließend
  abgeglichen. Bis dahin sammeln die APs ihre Dateiänderungen in den
  Übergabenotizen.
- **Ursachenanalyse und Einordnung:** `Theme/docs/ERWEITERUNGSANALYSE-Seitenindex.md`
  – Hintergrunddokument zu diesem Plan, unverändert lassen.
- **Wegweiser im Projekt:** `DOKUMENTATION.md` im Website-Wurzelverzeichnis –
  wird in AP-5.3 um Verweise auf die Datei-Map und die beiden Dokumente in
  `Theme/docs/` ergänzt.
- **Projektübergreifende Farbdoku:** `CLAUDE.md` im Website-Wurzelverzeichnis,
  Abschnitt „Color Scheme" – wird in AP-4.doc um die neue Customizer-Sektion
  ergänzt.
