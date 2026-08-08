# Erweiterungs-Analyse: Seitenindex & Inhaltsverzeichnis-Block

Stand: 2026-08-07 · Komponente: Theme „FOS Online Schulbuch" (v1.5.68)
Grundlage: `Theme/CLAUDE.md`, `CLAUDE.md` (Root), `DOKUMENTATION.md`, Quellcode

---

## 1. Kurzbeschreibung der Erweiterung

Das Theme bekommt einen **vorberechneten Seitenindex** (schlanker Seitenbaum im
Options-Table, der bei jeder Seitenänderung ungültig gemacht und beim nächsten
Lesen neu aufgebaut wird) und einen darauf aufsetzenden, serverseitig gerenderten
Gutenberg-Block **`fos/inhaltsverzeichnis`**. Er ersetzt den Core-Block
„Seitenliste" (`core/page-list`) auf der Inhaltsverzeichnisseite. Die gerenderte
HTML-Ausgabe wird zusätzlich als Fragment zwischengespeichert, sodass ein
Seitenaufruf im Normalfall nur noch zwei Cache-Lesevorgänge kostet statt einer
Volltabellen-Abfrage plus Baumaufbau.

Zweiter, gleichrangiger Teil: Die Inhaltsverzeichnisseite wird **zusätzlich vom
Glossar-Autolinker ausgebremst** (Begründung unter 7). Ohne diesen Fix bringt der
Index nur einen Teil der erhofften Beschleunigung.

---

## 2. Verständnis des Ist-Projekts

**Projektzweck:** WordPress-Website „FOS Online Schulbuch" — Unterrichtsskripten
als tief verschachtelte Seitenhierarchie, ergänzt um zwei eigene Plugins
(Container Block Designer, Eigene WP Blocks) für interaktive Lerninhalte.

**Relevante Module/Schichten:**

- `Theme/functions.php` (~3850 Zeilen) — Sammelstelle für alle Theme-Subsysteme:
  Setup, Assets, Customizer-Farben, Sidebar-Meta-Box, Glossar (größtes
  Subsystem), Passwortschutz, AI-Blocker, SVG-Pipeline, Lightbox.
- `Theme/sidebar.php` — hierarchischer Seitenbaum, bereits auf **eine**
  `get_pages()`-Query plus Parent-Children-Map optimiert (v1.5.x-Optimierung).
- `Theme/includes/admin/page-manager.php` — Admin-Seitenübersicht mit
  Drag-Sortierung; ändert `post_parent`/`menu_order` **direkt per `$wpdb->update`**.
- `Theme/vite.config.js` — Multi-Entry-Build (main, glossar, glossar-editor,
  glossar-style, page-manager, page-manager-style) → `dist/js/`, `dist/css/`.
- `Theme/create-theme-zip.js` — Distributions-ZIP; whitelistet `*.php`,
  `style.css`, `readme.md`, `LICENSE`, `dist/js/**`, `dist/css/**`,
  `dist/.vite/manifest.json` sowie `includes/**/*.{php,js}`.

**Geltende Konventionen, die eingehalten werden müssen:**

| Konvention | Quelle | Konsequenz für diese Erweiterung |
|---|---|---|
| Präfix `simple_clean_` für alle Theme-Funktionen | `functions.php` durchgängig | Neue Funktionen heißen `simple_clean_page_index_*` |
| PHP 7.4 als Mindestversion | `style.css` Header, `Theme/CLAUDE.md` | Keine Union Types, kein `match`, keine Enums, keine Constructor Property Promotion |
| Assets mit `filemtime()` als Version einhängen | `functions.php:100-118`, Lehre aus v1.5.64 | Neue CSS/JS-Handles genauso, **nie** feste Versionsnummer |
| Farben nur über CSS-Variablen aus dem Customizer | Root-`CLAUDE.md` „Color Scheme", `functions.php:241-294` | Keine festen Hexwerte im neuen CSS |
| „Plastischer Look" **nur** auf `.sidebar-toggle-btn` | `Theme/CLAUDE.md` (ausdrückliche Nutzerentscheidung) | Die neuen Kapitelkarten bleiben schlicht — nicht „der Konsistenz wegen" ausweiten |
| Keine CDN-Einbindungen (DSGVO) | Projektentscheidung AP8/AP23 | Lösung kommt ohne Fremdbibliothek aus (natives `<details>`, Vanilla JS) |
| Vor jedem ZIP: `php -l` über alle PHP-Dateien | `Theme/CLAUDE.md` „Syntax Check (MANDATORY)" | Teil jeder AP-Testanweisung |
| Kein Hoisting bei `function_exists`-Guards | `Theme/CLAUDE.md`, Fatal v1.5.57→58 | In Template-Dateien Funktionsdefinitionen an den Dateianfang |
| Plan-Dokumente AP-nummeriert, `DOKUMENTATION.md` verweist darauf | Projektkonvention seit 2026-07-04 | Plan als `Theme/docs/PLAN-Seitenindex.md`, Verweis in `DOKUMENTATION.md` |

---

## 3. Einordnung in die Architektur

### Ursachenanalyse (belegt, nicht vermutet)

**Ursache A — `core/page-list` rechnet bei jedem Aufruf komplett neu.**
Der Core-Render (`wp-includes/blocks/page-list.php`) ruft

```php
$all_pages = get_pages( array( 'sort_column' => 'menu_order,post_title', 'order' => 'asc' ) );
```

**ohne jede Zwischenspeicherung** auf, lädt also alle Seiten als vollständige
`WP_Post`-Objekte inklusive `post_content`, ruft je Seite `get_permalink()` und
verschachtelt anschließend rekursiv **ohne Tiefenbegrenzung**. Ein
Fragment-Cache existiert nicht. Bei mehreren hundert Skriptenseiten wächst das
linear in Datenbanklast, PHP-Speicher **und** DOM-Größe im Browser.

**Ursache B — der Glossar-Autolinker fällt auf dieser Seite in den teuersten
Pfad.** `simple_clean_glossar_auto_link_content_optimized()`
(`functions.php:1421`) prüft in Zeile 1447:

```php
if (empty($candidates) || !is_array($candidates)) {
    // Fallback: Verwende alle Glossar-Begriffe
```

`empty()` kann **„noch nie gescannt"** und **„gescannt, keine Treffer"** nicht
unterscheiden. Der `post_content` der Inhaltsverzeichnisseite besteht praktisch
nur aus dem Block-Kommentar `<!-- wp:page-list /-->`, enthält also keinen
Fließtext → `simple_clean_scan_glossar_candidates()` liefert korrekt ein leeres
Array → der Fallback lädt **alle** Glossarbegriffe, expandiert jeden über
`simple_clean_get_glossar_term_variants()` in mehrere Wortvarianten, baut daraus
ein einziges Alternations-Pattern (`functions.php:1268`) und schickt es über den
**gesamten**, gerade erst erzeugten Seitenlisten-HTML — inklusive `preg_split`
über jeden Tag (`functions.php:1326`).

Derselbe Fallback in `simple_clean_glossar_assets()` (`functions.php:929-934`)
lädt zusätzlich alle Begriffe **samt Definitionen** und liefert sie als
`glossarData` an den Browser aus.

Damit trägt jede zusätzliche Seite doppelt zur Ladezeit bei: einmal über die
Seitenliste, einmal über die Größe des HTML, das der Autolinker durchkämmt.

### Andockpunkte

| # | Andockpunkt | Ort |
|---|---|---|
| 1 | Index-Speicher & Aufbau | neue Datei `includes/page-index.php`, eingebunden wie `includes/admin/page-manager.php` (`functions.php:3565`) |
| 2 | Invalidierung | `save_post_page`, `transition_post_status`, `deleted_post`, `trashed_post`, `untrashed_post`, `update_option_permalink_structure`, `update_option_home` — plus **explizite Aufrufe** im Seitenmanager |
| 3 | Blockregistrierung | `init`-Hook, `register_block_type_from_metadata( get_template_directory() . '/blocks/inhaltsverzeichnis' )` |
| 4 | Editor-Script | `enqueue_block_editor_assets`, Muster von `simple_clean_glossar_editor_assets()` (`functions.php:1962`) |
| 5 | Frontend-Assets | `wp_enqueue_scripts`, bedingt über `has_block('fos/inhaltsverzeichnis')` |
| 6 | Design-Regler global | neue Customizer-Sektion in `simple_clean_customize_register()`, Variablen-Ausgabe in `simple_clean_customizer_css()` |
| 7 | Ausschluss einzelner Seiten | zweite Checkbox in der bestehenden Meta-Box `simple_clean_navigation_meta_box_callback()` (`functions.php:353`) |
| 8 | Glossar-Fix | `functions.php:1447` und `functions.php:929` |

**Begründung der Stelle:** Die Erweiterung hängt sich bewusst **nicht** in
`core/page-list` ein (kein `render_block`-Filter auf einen Core-Block). Ein
eigener Block ist explizit, im Editor sichtbar, versionsfest gegenüber
Core-Änderungen und lässt sich pro Einfügung konfigurieren. Der Index landet in
einer eigenen `includes/`-Datei statt in der ohnehin 3850 Zeilen langen
`functions.php` — dem Muster von `page-manager.php` folgend.

---

## 4. Betroffene Dateien

| Datei | Rolle heute | Änderung |
|---|---|---|
| `Theme/includes/page-index.php` | – | **neu** — Aufbau, Speicherung, Invalidierung, Render des Index; Block-Registrierung |
| `Theme/blocks/inhaltsverzeichnis/block.json` | – | **neu** — Blockdefinition, Attribute, `render`-Verweis |
| _(kein `render.php`)_ | – | Das Rendering liegt als `render_callback` in `includes/page-index.php`. Die `block.json`-Eigenschaft `"render"` gibt es erst ab WordPress 6.1 und würde auf älteren Versionen stillschweigend ignoriert. |
| `Theme/src/js/page-index-editor.js` | – | **neu** — Editor-Integration (InspectorControls + ServerSideRender) |
| `Theme/src/js/page-index.js` | – | **neu** — optionales Suchfeld/Filter im Frontend |
| `Theme/src/css/page-index.css` | – | **neu** — Kartenraster, Aufklapp-Ebenen, responsive |
| `Theme/functions.php` | zentrale Theme-Logik | ändern — `require_once` der neuen Datei; Customizer-Sektion + CSS-Variablen; zweite Meta-Box-Checkbox + Speicherung; **Glossar-Fallback-Fix an zwei Stellen** |
| `Theme/includes/admin/page-manager.php` | Drag-Sortierung, Anlegen/Löschen/Status per AJAX | ändern — Index-Invalidierung nach jedem schreibenden AJAX-Handler |
| `Theme/vite.config.js` | Multi-Entry-Build | ändern — drei neue Entries |
| `Theme/create-theme-zip.js` | ZIP-Whitelist | ändern — `blocks/**/*.{json,php}` aufnehmen |
| `Theme/style.css` | Haupt-Stylesheet | ändern — nur Versionsnummer im Theme-Header |
| `Theme/package.json` | Version | ändern — Versionsnummer |
| `Theme/CLAUDE.md` | Arbeitsdoku Theme | ändern — neues Subsystem dokumentieren |
| `DOKUMENTATION.md` | Wegweiser | ändern — Verweis auf den neuen Plan |
| `Theme/sidebar.php` | Seitenbaum links | **nur lesen** (Referenz für Baumaufbau) — bleibt unangetastet |
| `Theme/page.php` | Seiten-Template | **nur lesen** — keine Änderung nötig |

---

## 5. Wiederverwendung statt Neubau

- **`simple_clean_customize_register()` / `simple_clean_customizer_css()`**
  (`functions.php:125` / `:241`) → neue Sektion „Inhaltsverzeichnis" wird
  angehängt; die Ausgabe der CSS-Variablen nutzt denselben `wp_head`-Block.
  Kein zweiter Mechanismus.
- **`simple_clean_hex_to_rgb()`** (`functions.php:299`) → für `rgba()`-Schatten
  der Kapitelkarten, falls benötigt.
- **Bestehende CSS-Variablen** `--color-ui-surface`, `--color-ui-surface-light`,
  `--color-special-text`, `--color-sidebar-border`, `--color-background-light`
  → Basis der Kartenoptik; neue `--pidx-*`-Variablen nur, wo wirklich ein
  eigener Regler dazukommt.
- **Baumaufbau-Muster aus `sidebar.php:132-145`** (eine Query → `children_map`
  über `post_parent`) → dieselbe Idee, nur auf der schlanken SQL-Zeile statt auf
  `WP_Post`-Objekten. Wird kopiert, nicht importiert (sidebar.php bleibt
  unberührt).
- **Meta-Box `simple_clean_hide_navigation`** (`functions.php:340-420`) → wird um
  eine zweite Checkbox erweitert, statt eine neue Box zu registrieren.
- **Asset-Enqueue-Muster** aus `simple_clean_glossar_assets()` /
  `simple_clean_glossar_editor_assets()` → identisch übernommen (`filemtime`,
  `file_exists`-Guard, Footer-Laden).
- **Bestehender Bulk-Rescan** (`wp_ajax_glossar_bulk_scan_batch`,
  `functions.php:3517`) → dient als Migrationswerkzeug nach dem Glossar-Fix,
  muss nicht neu gebaut werden.

---

## 6. Integrationspunkte & Schnittstellen

### 6.1 Datenschicht — der Index

**Aufbau (`simple_clean_build_page_index()`):** eine einzige schlanke Abfrage,
bewusst ohne `post_content`:

```sql
SELECT ID, post_parent, post_title, post_name, menu_order
FROM {$wpdb->posts}
WHERE post_type = 'page' AND post_status = 'publish'
ORDER BY menu_order ASC, post_title ASC
```

plus **eine** Sammelabfrage der Ausschluss-Flags:

```sql
SELECT post_id FROM {$wpdb->postmeta}
WHERE meta_key = '_simple_clean_hide_from_index' AND meta_value = '1'
```

Daraus entsteht die Struktur:

```php
array(
  'version'   => 7,                       // Zählerstand beim Bau
  'built_at'  => 1754500000,
  'count'     => 412,
  'nodes'     => array( ID => array('id','parent','title','slug','uri','depth') ),
  'children'  => array( parentID => array(childID, …) ),  // bereits sortiert
)
```

Die `uri` wird **selbst** aus der Elternkette gebaut (`elternpfad/slug`) —
exakt die Logik von `get_page_uri()`, aber einmal für alle Knoten statt
`get_permalink()` je Seite. Zur Ausgabezeit wird daraus `home_url('/' . uri . '/')`
bzw. bei abgeschalteten Permalinks `?page_id=ID`.

**Speicherung:** Option `simple_clean_page_index`, **zwingend mit
`autoload = false`** (`update_option($key, $value, false)`). Eine autoloadende
Option dieser Größe würde auf *jedem* Request der ganzen Site geladen — das wäre
schlimmer als das Ausgangsproblem.

**Versionszähler:** Option `simple_clean_page_index_version` (kleine Ganzzahl,
autoload erlaubt). Invalidierung = Zähler erhöhen und Index-Option löschen.

**Aufbaustrategie: Read-Through, nicht eager.** Beim Speichern wird nur der
Zähler erhöht; neu gebaut wird beim nächsten Lesezugriff. Grund: bei einer
Drag-Sortierung über 50 Seiten oder einem Import würde ein eager Rebuild 50-mal
laufen. Der Neuaufbau kostet eine indizierte Abfrage — der erste Besucher danach
zahlt vernachlässigbar wenig.

### 6.2 Render-Schicht — Fragment-Cache

Der gerenderte HTML-Block landet in einem Transient:

```
sc_pidx_{indexVersion}_{md5(serialisierte Attribute + Locale)}   TTL 1 Woche
```

Weil die Indexversion **im Schlüssel steckt**, ist keine gezielte Löschung nötig
— nach einer Änderung greifen die alten Schlüssel schlicht nicht mehr und laufen
per TTL aus. Auf einem warmen Cache kostet ein Blockaufruf: 1 Transient-Lesung.

Aufräumen verwaister Transients: einmalig beim Versionssprung per gezieltem
`DELETE` auf `_transient_sc_pidx_%` — bewusst **nicht** bei jedem Save, sondern
gedrosselt (z. B. höchstens stündlich), damit die Options-Tabelle nicht bei jedem
Seitenspeichern durchsucht wird.

### 6.3 Invalidierungspunkte — vollständige Liste

| Auslöser | Hook / Ort |
|---|---|
| Seite gespeichert | `save_post_page` (Autosave/Revision überspringen) |
| Status geändert (publish ↔ draft ↔ privat) | `transition_post_status` |
| Seite gelöscht / in den Papierkorb / zurückgeholt | `deleted_post`, `trashed_post`, `untrashed_post` (jeweils `post_type === 'page'` prüfen) |
| **Drag-Sortierung im Seitenmanager** | **direkter Aufruf** nach der `$wpdb->update`-Schleife in `page-manager.php:354-372` |
| Seite im Seitenmanager angelegt / gelöscht / Status umgeschaltet | direkte Aufrufe in den jeweiligen AJAX-Handlern |
| Checkbox „Aus Inhaltsverzeichnis ausblenden" | `simple_clean_save_navigation_meta()` |
| Permalink-Struktur oder Site-URL geändert | `update_option_permalink_structure`, `update_option_home` |
| Schema der Indexstruktur geändert (Theme-Update) | Konstante `SIMPLE_CLEAN_PAGE_INDEX_SCHEMA` beim Lesen vergleichen |
| Manuell | Button „Seitenindex neu aufbauen" im Seitenmanager |

> **Kritisch:** Der Seitenmanager schreibt mit `$wpdb->update` direkt in
> `wp_posts` (`page-manager.php:354`). `save_post` feuert dabei **nicht**. Ohne
> die expliziten Aufrufe bliebe der Index nach jeder Umsortierung veraltet —
> das ist der wahrscheinlichste Weg, wie diese Erweiterung im Alltag falsch
> aussehen würde.

### 6.4 Block-Schnittstelle

`fos/inhaltsverzeichnis`, `apiVersion` 3, dynamisch (kein `save`-Output).

| Attribut | Typ | Standard | Wirkung |
|---|---|---|---|
| `rootPage` | number | 0 | Startseite; 0 = alle obersten Seiten |
| `maxDepth` | number | 2 | Maximale Tiefe (1–5) |
| `layout` | string | `cards` | `cards` \| `list` \| `columns` |
| `columns` | number | 3 | Spalten des Kartenrasters (1–4) |
| `collapsible` | boolean | true | Unterebenen in `<details>` |
| `openByDefault` | boolean | false | `<details open>` |
| `showSearch` | boolean | true | Client-seitiges Filterfeld |
| `showCounts` | boolean | false | Anzahl Unterseiten je Kapitel |
| `className` | string | – | Standard-Zusatzklasse für eigenes CSS |

**Editor-Script:** `src/js/page-index-editor.js`, gebaut über Vite wie
`glossar-editor.js`. **Wichtig:** dieselbe Schreibweise verwenden — Zugriff über
die `wp.*`-Globals (`const { registerBlockType } = wp.blocks;`), **keine
`import`/`export`-Anweisungen**. Vite gibt ES-Module aus; eine Datei mit
`export` würde als klassisches Script (so wird sie eingehängt) brechen.
`glossar-editor.js` macht es bereits genau so.

**Vorschau im Editor:** `wp.serverSideRender` (Abhängigkeit
`wp-server-side-render`) — die Vorschau nutzt dieselbe PHP-Renderfunktion und
denselben Cache, es gibt also keine zweite Darstellungslogik.

### 6.5 Ausgabe & Design

```
<nav class="page-index page-index--cards page-index--cols-3">
  <input class="page-index__search">                  (optional)
  <ul class="page-index__chapters">
    <li class="page-index__chapter">
      <a class="page-index__chapter-link">Kapiteltitel</a>
      <details class="page-index__sub">
        <summary>Unterseiten (12)</summary>
        <ul class="page-index__pages"> … </ul>
      </details>
    </li>
  </ul>
</nav>
```

Aufklappen über natives `<details>/<summary>`: barrierefrei, tastaturbedienbar,
funktioniert ohne JavaScript und ohne Fremdbibliothek (DSGVO-Linie). Das
JavaScript wird **nur** für das optionale Suchfeld geladen.

**Neue Customizer-Sektion „Inhaltsverzeichnis"** (globale Vorgaben):
Kartenhintergrund, Kartenrahmen, Titelfarbe, Dichte (kompakt/normal/luftig),
Eckenradius. Ausgabe als `--pidx-*`-Variablen im bestehenden `wp_head`-Block.
Was schon über `--color-ui-surface` & Co. abgedeckt ist, bekommt **keinen**
zweiten Regler.

**Kein „plastischer Look".** Laut `Theme/CLAUDE.md` ist der bewusst auf
`.sidebar-toggle-btn` beschränkt; die Karten bleiben flach.

### 6.6 Kein DB-Schema, keine Migration

Es entstehen keine neuen Tabellen. Alles liegt in `wp_options` (zwei Einträge +
kurzlebige Transients) und einem neuen Post-Meta-Schlüssel
`_simple_clean_hide_from_index`. Deinstallation = drei Zeilen Aufräumcode.

---

## 7. Regressionsfläche (kritisch)

| Betroffen | Warum gefährdet | Muss nachweislich noch laufen |
|---|---|---|
| **Glossar-Verlinkung auf allen Seiten** | Der Fix an `functions.php:1447` ändert das Verhalten für *jede* Seite mit leerer Kandidatenliste: bisher „alle Begriffe verlinken", künftig „keine verlinken". Bei nie gescannten Altseiten wäre das ein sichtbarer Verlust. | Fix darf nur greifen, wenn `_glossar_scan_version` gesetzt ist. **Vor dem Ausrollen Bulk-Rescan laufen lassen.** Stichprobe: Skriptenseite mit bekannten Begriffen → Begriffe weiterhin verlinkt und anklickbar |
| **`glossarData`-Auslieferung** (`functions.php:929`) | Gleiche Fallback-Logik; ändert, welche Begriffe im Modal verfügbar sind | Tooltip/Sidebar-Modal auf einer normalen Skriptenseite öffnet weiterhin mit Definition |
| **Sidebar-Navigation** | Bleibt zwar unangetastet, teilt sich aber Datenquelle und Cache-Invalidierung; `sidebar.php` hat die dokumentierte Hoisting-Falle | Sidebar auf Skriptenseite, Unterseite und tiefer Unterseite rendert; kein Fatal |
| **Seitenmanager (Drag-Sortierung)** | Wird um Invalidierungsaufrufe erweitert; die AJAX-Antworten dürfen sich nicht ändern | Umsortieren speichert weiterhin, Erfolgsmeldung erscheint, **und** das Inhaltsverzeichnis zeigt die neue Reihenfolge |
| **Meta-Box „Seitenleiste (Sidebar) Einstellungen"** | Bekommt eine zweite Checkbox im selben Nonce-/Speicherpfad | Bestehende Checkbox „Sidebar ausblenden" speichert weiterhin korrekt |
| **Theme-ZIP** | `blocks/`-Verzeichnis und `.json`-Dateien fallen durch die aktuelle Whitelist in `create-theme-zip.js` | ZIP-Inhalt nach dem Build prüfen: `blocks/inhaltsverzeichnis/block.json` enthalten |
| **Customizer** | Neue Sektion und neue CSS-Variablen im gemeinsamen `wp_head`-Ausgabeblock | Bestehende acht Farbregler wirken unverändert; Vorschau lädt |
| **Vite-Build** | Drei neue Entries | `npm run build` erzeugt alle sechs alten **und** die drei neuen Dateien |
| **CDB-Container-Blöcke** | Der neue Block kann in einem Container liegen (InnerBlocks ohne Einschränkung) | Block innerhalb eines Container-Blocks rendert und lädt sein CSS |

---

## 8. Konventions-Konformität

- **Namensschema:** `simple_clean_page_index_*` für PHP, `page-index` /
  `page-index-style` als Vite-Entry und Asset-Handle (analog `page-manager`),
  CSS-Klassen im BEM-Stil unter `.page-index`.
- **Blocknamespace:** `fos/` — kollidiert weder mit
  `container-block-designer/*` noch mit `modular-blocks/*`.
- **PHP 7.4:** keine Sprachmittel ab 8.0. Vor jedem ZIP `php -l` über alle
  PHP-Dateien, wie in `Theme/CLAUDE.md` vorgeschrieben.
- **Sicherheit:** alle Ausgaben durch `esc_html()` / `esc_url()` / `esc_attr()`;
  die SQL-Abfragen enthalten keine Nutzereingaben (feste Spalten, `$wpdb->posts`
  über Property, kein Interpolieren von Nutzerdaten); Blockattribute werden über
  das `block.json`-Schema typisiert **und** im Renderer noch einmal begrenzt
  (`absint`, Whitelist für `layout`).
- **Assets:** `file_exists()`-Guard plus `filemtime()`-Version, Laden im Footer,
  konditional über `has_block()`.
- **i18n:** `__()` mit Textdomain `fos-online-schulbuch` (die in `style.css`
  eingetragene Domain — `functions.php` nutzt an manchen Stellen noch die alte
  `simple-clean-theme`; neuer Code verwendet die aktuelle).
- **Doku:** neues Subsystem in `Theme/CLAUDE.md` unter der Funktionsübersicht
  ergänzen, `DOKUMENTATION.md` um den Planverweis erweitern.

---

## 9. Risiken & offene Fragen

| Risiko | Gegenmaßnahme / Rollback |
|---|---|
| **Index veraltet nach Drag-Sortierung** (wahrscheinlichster Alltagsfehler) | Explizite Invalidierung in allen vier schreibenden AJAX-Handlern des Seitenmanagers + manueller „Neu aufbauen"-Button als Sicherheitsventil |
| **Option `simple_clean_page_index` autoloadet** und bremst die ganze Site | `update_option(..., false)` verbindlich; als Akzeptanzkriterium prüfen: `SELECT autoload FROM wp_options WHERE option_name='simple_clean_page_index'` liefert `no` |
| **Glossar-Fix nimmt Altseiten die Verlinkung** | Fix hängt an `_glossar_scan_version`; Bulk-Rescan als Pflichtschritt **vor** dem Ausrollen; Rollback = die eine `if`-Bedingung zurücknehmen |
| **`blocks/`-Ordner fehlt im ZIP** → Block auf der Live-Site unbekannt, Seite zeigt „Ihr Block hat einen Fehler" | Eigenes AP für `create-theme-zip.js`; ZIP-Inhalt als Akzeptanzkriterium auflisten. (Dieselbe Klasse Fehler wie die dokumentierte CDB-ZIP-Autoloader-Falle) |
| **Editor-Script bricht, weil Vite ESM ausgibt** | Keine `import`/`export` in `page-index-editor.js`; Muster von `glossar-editor.js` einhalten |
| **Umstellung bricht die bestehende Seite** | Der Core-Block bleibt installiert; Umstieg ist ein manueller Blocktausch auf **einer** Seite und jederzeit rückgängig zu machen |
| **Kein persistenter Object-Cache auf dem Hosting** (all-inkl) | Deshalb Options-/Transient-basiert (Datenbank) statt `wp_cache_*` — funktioniert ohne Redis/Memcached. Mit persistentem Cache wird es automatisch nochmals schneller |
| **Sehr großes DOM bleibt großes DOM** | `maxDepth`-Standard 2 + `<details>` sorgen dafür, dass tiefe Ebenen zwar im HTML stehen, aber nicht gerendert/umbrochen werden. Falls das bei sehr vielen Seiten nicht reicht: Nachlade-Variante als späteres AP, nicht in dieser Runde |

**Doku-Lücke aus Schritt 1:** Für das Theme existiert **keine**
`reference_file_map.md` (nur `Plugins/Eigene WP Blocks/` hat eine). Die
Funktionsübersicht in `Theme/CLAUDE.md` ist ein guter Ersatz, aber keine
Datei-Map. → Vorschlag: ein kleines AP legt `Theme/reference_file_map.md` an
(Bestand + neue Dateien) und `DOKUMENTATION.md` verweist darauf. Das ist die
Grundlage für alle künftigen Theme-Erweiterungen.

**Offene Punkte, die im Plan als Annahme laufen (Korrektur jederzeit möglich):**

1. Der Index umfasst nur **veröffentlichte** Seiten (`post_status = 'publish'`).
   Entwürfe erscheinen nicht im Inhaltsverzeichnis — auch nicht für Redakteure.
2. Nur Post-Type `page`. Glossareinträge und Beiträge bleiben außen vor.
3. Kein neuer Regler für Schriftgrößen — das regelt weiterhin `style.css`.

---

## 10. Grobzuschnitt für den projektplan-skill

**Mehrphasig** (deutlich über 5 Arbeitspakete, und Phase 1 hat eigenständigen
Nutzen, den man vor dem Rest ausliefern kann).

| Phase | Inhalt | Lauffähiges Zwischenergebnis |
|---|---|---|
| **1 — Sofortbremse lösen** | Glossar-Fallback an `functions.php:1447` und `:929` an `_glossar_scan_version` koppeln; Bulk-Rescan durchführen; Vorher-/Nachher-Messung | Bestehende Inhaltsverzeichnisseite mit `core/page-list` ist bereits spürbar schneller — **ohne** neuen Block |
| **2 — Indexschicht** | `includes/page-index.php`: Aufbau, Speicherung (autoload `no`), Versionszähler, alle Invalidierungshooks, Aufrufe im Seitenmanager, manueller Rebuild-Button | Index existiert und bleibt korrekt; noch keine sichtbare Änderung im Frontend |
| **3 — Block & Rendering** | `block.json`, Registrierung per `render_callback`, Fragment-Cache, Editor-Script, Vite-Entries, `create-theme-zip.js` | Block einsetzbar, ersetzt `core/page-list` auf der Inhaltsverzeichnisseite |
| **4 — Design** | CSS (Kapitelkarten, `<details>`, responsiv), Suchfeld-JS, Customizer-Sektion, Meta-Box-Checkbox „aus Index ausblenden" | Gestaltung im Editor und im Customizer steuerbar |
| **5 — Absicherung & Doku** | Regressionsdurchlauf über die Tabelle in Abschnitt 7, `php -l` + Build + ZIP-Prüfung, `Theme/CLAUDE.md`, `Theme/reference_file_map.md`, `DOKUMENTATION.md`, Versionssprung | Auslieferbares ZIP, Doku aktuell |

Je Phase gehören ein Review-AP (`AP-<N>.rev`) und ein Dokumentations-AP
(`AP-<N>.doc`) dazu; jedes AP, das Dateien anlegt oder wesentlich ändert, trägt
das Akzeptanzkriterium „`Theme/reference_file_map.md` aktualisiert".
