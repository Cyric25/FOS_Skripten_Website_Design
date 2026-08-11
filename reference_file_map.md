# Datei-Map: Theme „FOS Online Schulbuch"

_Stand: 2026-08-10 · Theme-Version 1.5.76_

Navigationshilfe auf Dateiebene. Details zu den Subsystemen stehen in
`CLAUDE.md`, insbesondere in der Funktionsübersicht der `functions.php`.

## Vorlagen (Theme-Wurzel)

| Datei | Zweck | Wichtige Funktionen/Inhalte | Hängt ab von |
|---|---|---|---|
| `functions.php` | Sammelstelle aller Theme-Subsysteme, ~3900 Zeilen | Setup, Asset-Einbindung, Customizer (Farben + Inhaltsverzeichnis), Meta-Box „Navigation & Inhaltsverzeichnis", Glossar-System, Passwortschutz, AI-Blocker, SVG-Pipeline, Lightbox, Diagnoseausgabe `simple_clean_perf_footer()`. Gliederung siehe `CLAUDE.md`. Bindet `includes/sichtbarkeit.php` ein (außerhalb des `is_admin()`-Blocks — die Sperre wirkt im Frontend) | `includes/*`, `dist/*` |
| `header.php` | Kopfbereich, Hauptmenü | Inline-Script für den Mobil-Menü-Umschalter (inkl. ARIA, ESC, Klick daneben) | – |
| `footer.php` | Fußbereich | Copyright, Anmelde-Link, `wp_footer()` | – |
| `index.php` | Beitragsliste | Auszüge, Datum/Autor, Blätternavigation | `header.php`, `footer.php` |
| `single.php` | Einzelner Beitrag | Inhalt, Meta, Kategorien, Vor/Zurück | `header.php`, `footer.php` |
| `page.php` | Statische Seite | Bindet die Seitenleiste ein, sofern nicht per Meta abgeschaltet | `sidebar.php` |
| `sidebar.php` | Hierarchischer Seitenbaum links | `get_root_page_id()`, `display_page_tree_item()` — **beide bewusst am Dateianfang** in `function_exists`-Guards (kein Hoisting, sonst Fatal); eine `get_pages()`-Abfrage plus Parent-Kind-Map; Swipe/ESC/Klick-daneben im Inline-Script | `functions.php` (Meta `_simple_clean_hide_navigation`) |
| `archive-glossar.php` | Glossar-Übersicht | Liste aller Begriffe | `functions.php` |
| `single-glossar.php` | Einzelner Glossarbegriff | Definition, Verwendungsnachweise | `functions.php` |
| `style.css` | Haupt-Stylesheet **und** Theme-Header | Theme-Name und Version (von `backup-and-build.js` gepflegt), `:root`-Variablen, Layout, Navigation, Inhalt, Seitenleiste, Haltepunkte 992/768/480 | – |

## Eingebundene Module

| Datei | Zweck | Wichtige Funktionen/Inhalte | Hängt ab von |
|---|---|---|---|
| `includes/page-index.php` | Block „Inhaltsverzeichnis" (`fos/inhaltsverzeichnis`) | `simple_clean_page_index_sanitize_attrs()`, `simple_clean_page_index_daten()` (zwei schlanke Abfragen, Breitensuche, statisch zwischengehalten), `simple_clean_page_index_url()`, `simple_clean_page_index_liste()`, `simple_clean_render_page_index()`, Registrierung per `render_callback`, Asset-Einbindung für Editor und Frontend | `blocks/inhaltsverzeichnis/block.json`, `dist/js/page-index*.js`, `dist/css/page-index-style.css`, Meta `_simple_clean_hide_from_index` |
| `includes/admin/page-manager.php` | Seitenübersicht im Admin mit Drag-Sortierung und **Sammelaktionen** | Anlegen/Löschen/Status-Umschalten per AJAX; `ajax_bulk_action()` mit acht Aktionen als Whitelist (`bulk_aktionen()`), Rechteprüfung je Einzelseite, `render_parent_options()` für die Elternauswahl. **Status läuft über `wp_update_post()` (wegen `save_post` und dem Glossar-Scan), `post_parent` und `menu_order` dagegen direkt per `$wpdb->update`, also an `save_post` vorbei** | `dist/js/page-manager.js` |
| `includes/sichtbarkeit.php` | Seiten **nur für Lehrpersonen** — die zentrale Sichtbarkeitslogik | `simple_clean_ist_lehrperson()` (**einzige** Definition von „Lehrperson"), `simple_clean_gesperrte_seiten()`, `simple_clean_gesperrte_seiten_mit_unterbaum()` (Unterbaum, nur für flache Listen nötig), `simple_clean_seite_nur_lehrpersonen()`, `simple_clean_seite_sichtbar()`, `simple_clean_sichtbarkeit_cache_leeren()`. Bietet die Filter `simple_clean_ist_lehrperson` und `simple_clean_lehrerseite_freigeben` — **Standardwert des zweiten ist `false` und muss es bleiben** (das CDB-Plugin hängt sich dort ein) | Meta `_simple_clean_nur_lehrpersonen` |
| `includes/admin/clipboard-uploader.php` | Bilder aus der Zwischenablage in die Mediathek | `Simple_Clean_Clipboard_Uploader::init()`, Capability `upload_files` | – |
| `includes/admin/image-lightbox-editor.js` | Lightbox-Schalter in der Bild-Blockleiste | wird direkt eingebunden, nicht über Vite gebaut | – |

## Block-Definitionen

| Datei | Zweck | Wichtige Funktionen/Inhalte | Hängt ab von |
|---|---|---|---|
| `blocks/inhaltsverzeichnis/block.json` | Metadaten des Blocks | Name `fos/inhaltsverzeichnis`, `apiVersion` 3, acht Attribute. **Bewusst ohne `render`-Eigenschaft** (gibt es erst ab WordPress 6.1 und scheitert auf älteren Versionen stillschweigend) und ohne `editorScript` (dort sind nur blockrelative Pfade auflösbar) | `includes/page-index.php` liefert Registrierung und `render_callback` |

## Quelldateien (werden von Vite gebaut)

| Datei | Zweck | Wichtige Funktionen/Inhalte | Hängt ab von |
|---|---|---|---|
| `src/js/main.js` | Haupt-Bündel | ausschließlich die eigene Lightbox mit FLIP-Zoom | – |
| `src/js/glossar.js` | Glossar-Frontend | Tooltip- bzw. Seitenleisten-Modal; erhält die Begriffe als `glossarData` | `functions.php` |
| `src/js/glossar-editor.js` | Glossar im Block-Editor | Werkzeugleisten-Schaltfläche zum Anlegen von Begriffen. **Muster für alle Editor-Scripts: Zugriff über `wp.*`-Globale, keine `import`/`export`** | – |
| `src/js/page-manager.js` | Drag-Sortierung und Mehrfachauswahl im Seitenmanager | `aktualisiereAuswahl()`, `fuehreBulkAus()`, Bereichsauswahl mit Umschalttaste; Sortable zieht nur am `.drag-handle`, deshalb keine `cancel`-Option nötig | `includes/admin/page-manager.php` |
| `src/js/page-index-editor.js` | Block im Editor registrieren und einstellen | `registerBlockType`, InspectorControls mit acht Einstellungen, Vorschau über `wp.serverSideRender`. **Ohne diese Datei erscheint der Block nicht im Einfügen-Menü** — serverseitige Registrierung allein genügt dafür nicht | `includes/page-index.php` |
| `src/js/page-index.js` | Suchfeld-Filter im Frontend | rekursives Filtern, merkt sich den Ausgangszustand der Aufklappebenen, `aria-live`-Meldung | – |
| `src/css/glossar.css` | Gestaltung des Glossars | – | – |
| `src/css/page-manager.css` | Gestaltung des Seitenmanagers | Baum, Werkzeugleiste, Modal und die Leiste `.page-bulk-bar` samt Auswahlkästchen | – |
| `src/css/page-index.css` | Gestaltung des Inhaltsverzeichnisses | Kapitelkarten, Aufklappebenen, drei Darstellungen, Druckansicht. **Keine freistehenden Farbwerte** — alles über `--pidx-*` und die Theme-Variablen | `functions.php` (gibt die Variablen aus) |

## Build und Verteilung

| Datei | Zweck | Wichtige Funktionen/Inhalte | Hängt ab von |
|---|---|---|---|
| `vite.config.js` | Build-Konfiguration | neun Einstiegspunkte → `dist/js/` und `dist/css/` | `src/*` |
| `backup-and-build.js` | Einstiegspunkt von `npm run build` | **erhöht die Patch-Version selbstständig** in `package.json` und `style.css`, sichert das vorherige ZIP (nur EINE Generation), baut dann | `vite.config.js`, `create-theme-zip.js` |
| `create-theme-zip.js` | Verteilungspaket schnüren | Whitelist: `*.php`, `style.css`, `readme.md`, `LICENSE`, `dist/js/**`, `dist/css/**`, Vite-Manifest, `includes/**/*.{php,js}`, `blocks/**/*.{php,json}`. **Neue Dateitypen müssen hier freigegeben werden**, sonst fehlen sie im ZIP und der Fehler zeigt sich erst auf der Live-Site | – |
| `package.json` | Abhängigkeiten und Skripte | Version (von `backup-and-build.js` gepflegt) | – |

## Dokumentation und Werkzeuge

| Datei | Zweck |
|---|---|
| `CLAUDE.md` | Arbeits- und Architekturdoku, inklusive Funktionsübersicht der `functions.php`, Diagnose und bekannte Fallen |
| `reference_file_map.md` | diese Datei |
| `readme.md` | Installationsanleitung für Nutzer |
| `docs/PLAN-Seitenindex.md` | Projektplan zum Inhaltsverzeichnis-Block mit Statustabelle, Testprotokoll und Messwerten |
| `docs/ERWEITERUNGSANALYSE-Seitenindex.md` | Ursachenanalyse, die zum Plan geführt hat (teils durch Messung überholt — siehe Abschnitt 11 des Plans) |
| `docs/messung.js` | Konsolenskript zum Messen von Ladezeit, Queries und Glossaranteil. Fällt nicht ins Verteilungs-ZIP |
| `docs/PLAN-Lehrerseiten.md` | Projektplan „Seiten nur für Lehrpersonen" mit Statustabelle, Testprotokoll und Rückblick |
| `docs/ERWEITERUNGSANALYSE-Lehrerseiten.md` | Analyse, die zu diesem Plan führte |
| `tools/test-sichtbarkeit.php` | Prüfharnisch der Sichtbarkeitslogik, 17 Prüfungen **ohne WordPress** (Stubs für `is_user_logged_in`, `get_post_meta`, `get_post_ancestors`, Filter und `$wpdb`; der `$wpdb`-Doppel zählt Abfragen mit). Aufruf: `php tools/test-sichtbarkeit.php`. **`tools/` steht bewusst nicht in der ZIP-Whitelist** von `create-theme-zip.js` |

## Sammelzeilen

| Pfad | Anmerkung |
|---|---|
| `node_modules/` | NPM-Abhängigkeiten, nicht versioniert, nicht anfassen |
| `dist/` | Build-Ergebnis, nicht versioniert. Wird von `npm run build` erzeugt |
| `backups/` | Rollback-ZIPs je Phase plus die eine automatische Generation von `backup-and-build.js`. Nicht versioniert (`*.zip` ist ignoriert) |
