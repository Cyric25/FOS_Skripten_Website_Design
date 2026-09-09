# Simple Clean Theme - Documentation

This file provides guidance to Claude Code when working with this WordPress theme.

## Theme Overview

**Name:** FOS Online Schulbuch (ehemals "Simple Clean Theme")
**Version:** siehe `package.json` / `style.css` (aktuell 1.5.x)

**Hinweis Menü-Toggle:** Der Mobile-Menü-Toggle ist ausschließlich als Inline-Script
in `header.php` implementiert (inkl. ARIA, ESC, Click-outside). `src/js/main.js`
enthält nur noch die Custom-Lightbox.
**Description:** Ein einfaches, responsives WordPress-Theme ohne Widgets, fokussiert auf Inhalt und Lesbarkeit
**WordPress Compatibility:** 5.0+
**PHP Compatibility:** 7.4+
**Repository:** https://github.com/Cyric25/FOS_Skripten_Website_Design

## Architecture

This theme uses a **modern build system with Vite** for JavaScript bundling while maintaining a **classic PHP-based WordPress theme structure** for maximum compatibility.

### Key Design Principles

1. **Minimalist & Clean** - No widgets, no sidebars, focus on content
2. **Responsive-First** - Mobile-optimized with breakpoints at 768px and 480px
3. **Modern Build Pipeline** - Vite for JavaScript bundling and optimization
4. **WordPress Standards** - Follows WordPress coding standards and best practices
5. **Performance-Focused** - File modification timestamps for cache busting

## Project Structure

```
Theme/
├── src/                          # Source files (development)
│   └── js/
│       └── main.js              # Main JavaScript entry point
├── dist/                         # Build output (auto-generated, not in Git)
│   ├── .vite/
│   │   └── manifest.json        # Vite manifest for asset mapping
│   ├── js/
│   │   └── main.js              # Bundled & minified JavaScript
│   └── simple-clean-theme-v1.0.0.zip  # Distributable theme ZIP
├── *.php                        # WordPress template files
│   ├── functions.php            # Theme setup & configuration
│   ├── header.php               # Header with navigation
│   ├── footer.php               # Footer with login link
│   ├── index.php                # Blog posts listing
│   ├── single.php               # Single post template
│   ├── page.php                 # Static page template
│   └── sidebar.php              # Hierarchical page navigation
├── style.css                    # Main stylesheet (includes theme header)
├── vite.config.js               # Vite build configuration
├── create-theme-zip.js          # ZIP creation script
├── package.json                 # NPM dependencies & scripts
├── .gitignore                   # Git ignore rules
├── readme.md                    # Theme installation guide (for users)
└── CLAUDE.md                    # This file (for Claude Code)
```

## Build System

### Overview

The theme uses **Vite 5** for JavaScript bundling, providing:
- Fast development server with HMR (Hot Module Replacement)
- Optimized production builds with tree-shaking
- Automatic code splitting
- Modern ES module support

### Build Configuration

**File:** `vite.config.js`

```javascript
// Entry point: src/js/main.js
// Output: dist/js/main.js
// Manifest: dist/.vite/manifest.json (for WordPress integration)
```

**Key settings:**
- Output directory: `dist/`
- Entry file: `src/js/main.js`
- Manifest generation: Enabled (for WordPress asset loading)
- Development server: `localhost:3000`

### Build Commands

**CRITICAL: Always run syntax check before creating ZIP!**

```bash
# Install dependencies
npm install

# Development mode (with dev server)
npm run dev

# Production build (minified, optimized) + create ZIP
# IMPORTANT: Always run syntax check first!
for file in *.php; do php -l "$file" || exit 1; done && npm run build

# Build JavaScript only (no ZIP)
npm run build:js

# Watch mode (auto-rebuild on changes)
npm run watch

# Preview production build
npm run preview

# Create theme ZIP only (requires existing build)
npm run zip

# Force rebuild and ZIP creation
npm run zip:force
```

**IMPORTANT:** `npm run build` now automatically creates a distributable ZIP file in `dist/` after building JavaScript.

### Syntax Check (MANDATORY before ZIP creation)

**Always run before creating distribution ZIP:**

```bash
# Check all PHP files for syntax errors
for file in *.php; do echo "Checking $file..."; php -l "$file" || exit 1; done
```

**Complete workflow (recommended):**

```bash
# 1. Syntax check all PHP files
for file in *.php; do php -l "$file" || exit 1; done

# 2. If no errors: Build and create ZIP
npm run build

# 3. Commit and push
git add .
git commit -m "Your commit message"
git push origin main
```

**Why this matters:**
- Prevents distributing broken PHP code
- Catches syntax errors early
- Ensures WordPress won't show fatal errors
- Required before every ZIP creation

**What gets checked:**
- All `*.php` files in theme root
- Syntax validation via `php -l`
- Exit immediately on first error (`|| exit 1`)

**If syntax error found:**
- Fix the error
- Re-run syntax check
- Only then create ZIP

### Build Output

**Development (`npm run dev`):**
- Starts Vite dev server on `localhost:3000`
- Hot Module Replacement enabled
- Source maps included

**Production (`npm run build`):**
- Minified JavaScript output to `dist/js/main.js`
- Manifest file generated at `dist/.vite/manifest.json`
- Optimized for performance (tree-shaking, code splitting)
- **Automatically creates ZIP:** `dist/simple-clean-theme-v{version}.zip`

### Theme ZIP Distribution

**File:** `create-theme-zip.js`

The theme includes an automated ZIP creation system that packages the theme for WordPress installation.

**What's included in the ZIP:**
- All PHP template files (`*.php`)
- Main stylesheet (`style.css`)
- Built JavaScript (`dist/js/main.js`)
- Vite manifest (`dist/.vite/manifest.json`)
- Documentation (`readme.md`)
- License file (`LICENSE`)

**What's excluded from the ZIP:**
- Source files (`src/`)
- Node modules (`node_modules/`)
- Build configuration (`vite.config.js`, `package.json`)
- Git files (`.git/`, `.gitignore`)
- Development files (`create-theme-zip.js`, `CLAUDE.md`)

**ZIP file location:** `dist/simple-clean-theme-v{version}.zip`

**Usage:**
```bash
# Automatic (recommended) - builds JS + creates ZIP
npm run build

# Manual ZIP creation (after build)
npm run zip

# Force rebuild everything
npm run zip:force
```

**Installation:** The generated ZIP can be uploaded directly to WordPress via Design → Themes → Theme hochladen.

## WordPress Integration

### Theme Setup

**File:** `functions.php`

**Key features:**
- Theme support: `post-thumbnails`, `title-tag`, `custom-logo`, HTML5
- Navigation menu: Single primary menu (`'primary'`)
- Custom excerpt length: 30 words
- Custom excerpt "Read more" link

**Location:** `functions.php:8-25`

### Asset Enqueuing

**File:** `functions.php:28-45`

```php
function simple_clean_theme_assets() {
    // Enqueue stylesheet
    wp_enqueue_style('simple-clean-style', get_stylesheet_uri(), array(), '1.0');

    // Enqueue bundled JavaScript (if exists)
    $js_file = get_template_directory() . '/dist/js/main.js';
    if (file_exists($js_file)) {
        wp_enqueue_script(
            'simple-clean-script',
            get_template_directory_uri() . '/dist/js/main.js',
            array(),
            filemtime($js_file),  // Cache busting via file modification time
            true                   // Load in footer
        );
    }
}
```

**How it works:**
1. Checks if build output exists (`dist/js/main.js`)
2. Uses `filemtime()` for automatic cache busting
3. Loads script in footer for better performance
4. No dependencies required (standalone bundle)

### Template Hierarchy

**Blog Posts Listing:** `index.php`
- Shows post excerpts (30 words)
- Displays post meta (date, author)
- Pagination with previous/next links

**Static Pages:** `page.php`
- Clean layout with just title and content
- No post meta displayed
- Full content rendering with `the_content()`

**Single Blog Post:** `single.php`
- Full post content
- Post meta (date, author)
- Categories and tags in footer
- Previous/next post navigation

**Header:** `header.php`
- Sticky navigation with hamburger menu
- Site title linked to homepage
- Primary menu (fallback to page list)
- **IMPORTANT:** Contains inline script for menu toggle (lines 45-50)

**Footer:** `footer.php`
- Copyright notice (dynamic year)
- Login link for admin access

## JavaScript Architecture

### Main Entry Point

**File:** `src/js/main.js`

**Features:**
1. **Mobile Menu Toggle**
   - Toggles `.active` class on navigation
   - Updates ARIA attributes for accessibility
   - Click-outside detection to close menu
   - ESC key closes menu

2. **Event Listeners:**
   - `DOMContentLoaded` - Ensures DOM is ready
   - Click events - Menu toggle, click-outside
   - Keyboard events - ESC key handling

**Note:** The theme has **two menu toggle implementations**:
- **Inline script in header.php:45-50** (basic toggle)
- **Bundled script in src/js/main.js** (enhanced with accessibility)

**Recommendation:** Consider removing the inline script in `header.php` and relying solely on the bundled version for consistency.

## Styling

### Main Stylesheet

**File:** `style.css`

**Theme header (lines 1-12):**
```css
/*
Theme Name: Simple Clean Theme
Description: Ein einfaches, responsives WordPress-Theme ohne Widgets
Version: 1.0
Author: Ihr Name
Text Domain: simple-clean-theme
...
*/
```

**Important:** The theme header in `style.css` is required for WordPress theme recognition.

### CSS Architecture

**Reset & Base:** `style.css:14-26`
- CSS box-sizing reset
- System font stack
- Base typography settings

**Layout Components:**
- `.container` - Max-width 1200px, centered
- `.site-header` - Sticky header with shadow
- `.site-main` - Min-height calc, 2rem padding
- `.site-footer` - Light background, top border

**Navigation:** `style.css:58-74`
- Horizontal flex menu on desktop
- Hover effects with color transition
- Mobile toggle button (hidden on desktop)

**Content Styles:** `style.css:127-248`
- Typography: H1-H6, paragraphs, lists
- Blockquotes with left border accent
- Code blocks with syntax highlighting background
- Tables with borders and header styling

**Sidebar Navigation:** `style.css:350-602`
- Hierarchical page tree layout
- Expand/collapse animations
- Sticky positioning on desktop
- Fixed slide-in on mobile
- Current page and ancestor highlighting

**Responsive Breakpoints:**
- **Desktop:** Default (1200px max container)
- **Tablet/Mobile:** `@media (max-width: 992px)` - Sidebar becomes mobile slide-in
- **Tablet/Mobile (Header):** `@media (max-width: 768px)` - Lines 279-334
  - Hamburger menu appears
  - Vertical navigation
  - Stacked footer layout
- **Small Mobile:** `@media (max-width: 480px)` - Lines 336-348, 583-602
  - Reduced font sizes
  - Tighter spacing
  - Smaller sidebar width

### Plastischer Look (seit v1.5.62, Umfang festgelegt in v1.5.65)

**Im Theme betrifft das ausschließlich den Navigations-Streifen der
Seitenleiste** (`.sidebar-toggle-btn`). Kopfleiste, Menü und Mobilmenü bleiben
schlicht weiß — das war eine bewusste Entscheidung des Nutzers, nachdem eine
Fassung mit orangem Kopfband und Menüpunkten als Kacheln verworfen wurde.
**Nicht erneut auf den Header ausweiten**, auch nicht „der Konsistenz wegen".

Im CDB-Plugin nutzen zusätzlich der PDF-Button und die PDF-Werkzeugleiste
denselben Look (siehe `Plugins/CDB-Designer/CLAUDE.md`).

**Quelle der Rezeptur ist nicht das CSS, sondern die SVG-Erzeugung** in
`Website/Icons/generate_iconset_local.py` (Ergebnis z. B.
`Plugins/CDB-Designer/assets/icons/kategorien/*.svg`):

| Element | Wert im SVG |
|---|---|
| Verlauf | linear 135°, Basisfarbe → `darken(base, 0.20)` |
| Glanz | radial `30% 22%`, r 75 %, Weiß 0.35 → 0.08 (45 %) → 0 |
| Innenkante oben | dunkel, Deckkraft 0.75 |
| Innenkante unten | weiß, Deckkraft 0.5 |
| Innenkante rechts | dunkel, Deckkraft 0.25 (nur bei Knöpfen, nicht bei Bändern) |
| Schlagschatten | `darken(base, 0.55)`, Deckkraft 0.55 |

**Fundstellen:**

- `style.css` `:root` — `--plastic-dark`, `--plastic-shadow`,
  `--plastic-edge-dark`, `--plastic-drop-shadow`
- `style.css` `.site-header` — das Band selbst
- `style.css` `@media (max-width: 768px) .main-navigation` — aufgeklapptes
  Mobilmenü im selben Look
- `style.css` `.sidebar-toggle-btn` (+ `:hover`) — der senkrechte
  „Navigation"-Streifen am linken Rand, **die einzige Stelle im Theme**. Sein
  Schatten war vorher fest auf `rgba(232, 70, 20, …)` verdrahtet und blieb
  orange, auch wenn im Customizer eine andere UI-Farbe eingestellt war; jetzt
  läuft er über `--plastic-drop-shadow` mit.

**Streifen am Desktop (v1.5.66):** durchgehend von Fensterkante zu Fensterkante
(`top: 0; bottom: 0`, `border-radius: 0`), `z-index: 1001` — **über** der
Kopfleiste (1000), sonst verschwände der obere Teil hinter ihr. Die drei
Striche (`.toggle-icon`) sind am Desktop ausgeblendet und nur unter 992px
sichtbar; dort ist der Streifen eine kompakte quadratische Kachel unten
links (bis v1.5.94 eine breite Pille, siehe nächster Absatz).

**Gemeinsame Geometrie mit dem PDF-Knopf des Plugins (seit v1.5.95):** Unter
992px sitzen zwei schwebende Knöpfe gleichzeitig am unteren Rand — der
Navigationsknopf des Themes links, der PDF-Knopf des CDB-Designers
(`#cbd-pdf-export-fab`) rechts. Sie standen auf unterschiedlicher Höhe
(20px bzw. 15px gegen 30px) und hatten unterschiedliche Form (Pille gegen
Kachel); das Paar wirkte auf dem Handy unabsichtlich schief. Seit v1.5.95
teilen sie vier Werte:

| Wert | |
|---|---|
| Größe | 52px × 52px |
| Eckenradius | 12px |
| Abstand unten | 20px |
| Abstand zum seitlichen Rand | 20px |

**Diese vier Werte stehen doppelt** — in `style.css` im Block
`@media (max-width: 992px)` und in
`Plugins/CDB-Designer/assets/js/floating-pdf-button.js`. Wer einen davon
ändert, muss die andere Stelle nachziehen; an beiden Stellen steht ein
Kommentar mit demselben Hinweis. Eine gemeinsame CSS-Variable ist nicht
möglich: der PDF-Knopf wird per jQuery `.css()` **inline** gestylt (inline
schlägt jede Stylesheet-Regel ohne `!important`), und Theme und Plugin sind
getrennt versionierte Pakete — eines kann ohne das andere installiert sein.

Folge für den Schriftzug: „Navigation“ (`.toggle-text`) ist unter 992px
**ausgeblendet** — er passt nicht in eine 52px-Kachel und machte den Knopf
sonst breiter als den PDF-Knopf. Das Zeichen ☰ trägt die Bedeutung allein;
der Knopf hat in `sidebar.php` ein `aria-label`, Screenreader verlieren also
nichts. Am Desktop bleibt der Schriftzug sichtbar.

Ebenfalls entfallen: die frühere Verkleinerung unter 480px (`bottom`/`left`
15px, kleineres `padding`). Sie hätte den Navigationsknopf auf schmalen
Geräten gegenüber dem PDF-Knopf verschoben, der über alle Breiten dieselbe
Größe behält.

**Schriftschärfe — Falle:** `.sidebar-toggle-btn` hatte `transform:
translateX(0)` und `opacity: 0.95`. Beides erzeugt dauerhaft eine eigene
Compositing-Ebene, und darin schaltet der Browser die Subpixel-Glättung ab —
bei gedrehter Schrift (`writing-mode: vertical-rl`) sichtbar matschig. Jetzt
`transform: none` (die Einblend-Animation läuft weiter, `none` und
`translateX(-100%)` sind interpolierbar), `opacity: 1`, kein `text-shadow`,
Schriftgröße 1rem statt 0.75rem. **Keine Teiltransparenz und kein
Dauer-Transform hier wieder einbauen** — das holt die unscharfe Schrift zurück.
- `Plugins/CDB-Designer/assets/js/floating-pdf-button.js` — FAB und
  Werkzeugleiste, dort als JS-Strings (die Datei stylt inline, nicht per CSS-Datei)

**Immer `background-image`, nie die Kurzschreibweise `background`.** Wird der
Verlauf ungültig — etwa in einem Browser ohne `color-mix()` —, setzt die
Kurzschreibweise auch `background-color` mit zurück; die Fläche wäre dann
**durchsichtig** statt einfarbig. Ein `background-color` davor ist also nur
dann ein echter Rückfall, wenn die Verlaufsschichten über `background-image`
kommen. Gilt genauso im JS des PDF-Buttons (`backgroundImage`, nicht
`background`).

**Farben werden abgeleitet, nicht gesetzt.** Die Stufen entstehen per
`color-mix()` aus `--color-ui-surface`, damit die Customizer-Farbeinstellung
weiterwirkt — eine feste Hexfarbe hätte sie für Kopfleiste und PDF-Button
stillschweigend ausgehebelt. Vor jeder `background`-Zeile mit `color-mix()`
steht ein einfarbiges `background-color` als Rückfall für Browser ohne
`color-mix()`.

**Der Hover in der Navigation musste sich ändern:** Er färbte den Link vorher
orange — auf der jetzt orangen Leiste wäre er unsichtbar. Stattdessen hellt er
die Fläche unter dem Link auf.

### CSS erreicht den Browser nur mit Cache-Busting (Fix v1.5.64)

`functions.php` hängte das Stylesheet mit fester Version `'1.0'` ein. Die URL
lautete damit dauerhaft `style.css?ver=1.0` — Browser, Caching-Plugins und CDNs
lieferten nach einem Theme-Update weiter die **alte** Datei aus. CSS-Änderungen
kamen schlicht nicht an, obwohl das ZIP korrekt war. Jetzt steht dort
`filemtime()`, wie beim JavaScript von Anfang an.

**Bei „die Änderung ist nicht zu sehen" zuerst hier prüfen**, nicht am CSS
zweifeln: Seitenquelltext ansehen, ob `style.css?ver=` eine große Zahl
(Unix-Zeitstempel) trägt. Steht dort `1.0`, läuft eine alte functions.php.

### Behobener Altfehler in style.css (v1.5.64)

Zwischen dem 992px- und dem 480px-Block **fehlte die öffnende
`@media (max-width: 480px) {`-Zeile**. Folgen:

- `.sidebar-toggle-btn`, `.toggle-text` und `.page-link` galten auf **allen**
  Bildschirmgrößen statt nur unter 480px.
- Die abschließende Klammer verwarfen Browser als verirrt.
- Sichtbarster Effekt: Der Navigations-Streifen bekam auf dem Desktop
  zusätzlich zu seinem `top: 100px` ein `bottom: 15px` und wurde dadurch über
  die **gesamte Fensterhöhe** gezogen, statt eine kompakte Pille zu bleiben.

Der Fehler war vorbestehend (auch in HEAD). Diagnose damals über `postcss`:
`node -e "postcss.parse(fs.readFileSync('style.css','utf8'))"` meldet die Zeile
der ersten unbalancierten Klammer — schneller als Durchzählen.

### Color Scheme

**Stand: 2026-08-23 (PLAN-CSS-Variablen-Darkmode.md, Phase 1 abgeschlossen).**
Die früher hier genannten Werte (`#0073aa` als angebliche Akzentfarbe, `#333`/
`#fff` als „aktuelle" Primär-/Hintergrundfarbe) waren veraltet und kamen im
Code so nicht mehr vor — entfernt.

**Quelle der Wahrheit ist die Root-`CLAUDE.md`, Abschnitt „Color Scheme".**
Dort stehen die acht Customizer-gekoppelten Grundvariablen
(`--color-special-text`, `--color-ui-surface` + zwei Abstufungen,
`--color-sidebar-border`, `--color-text-primary`, `--color-background`,
`--color-background-light`) mit ihrer Anbindung an
`simple_clean_customize_register()` / `simple_clean_customizer_css()` in
`functions.php`. Diese Datei dupliziert das nicht.

**Acht Ergänzungsvariablen** (AP-1.1, `style.css` `:root`; seit AP-1.2 auch
als Fallback in `simple_clean_customizer_css()` ausgegeben) — nicht im
Customizer einstellbar, aber ebenso zentral definiert:

| Variable | Wert | Zweck |
|---|---|---|
| `--color-text-muted` | `#666666` | gedämpfter Fließtext (Meta-Angaben, Sekundärtext) |
| `--color-border` | `#dddddd` | Rahmen, kräftigere Abstufung (z. B. Tabellenrahmen) |
| `--color-border-light` | `#eeeeee` | Rahmen, hellere Abstufung (z. B. Trennlinien) |
| `--color-code-bg` | `#f1f1f1` | Hintergrund von `<code>`/`<pre>`-Blöcken |
| `--color-success` | `#2ecc40` | Erfolgsfarbe (u. a. `floating-pdf-button.js`) |
| `--color-danger` | `#cc3333` | Fehler-/Warnfarbe (u. a. `floating-pdf-button.js`) |
| `--font-family-base` | System-Sans-Serif-Stack (`-apple-system, …, sans-serif`) | Basis-Schriftfamilie |
| `--font-family-mono` | `'Courier New', monospace` | Code-/Monospace-Schriftfamilie |

**Seit AP-1.3/1.4 durchgehend variablenbasiert:** `style.css` (außerhalb
`:root`) und `src/css/glossar.css` verwenden keine freistehenden Hex-Werte
mehr, nur noch `var(--x, #bisheriger-wert)` mit dem jeweils bisherigen Wert
als Fallback — am Erscheinungsbild ändert sich dadurch nichts. Zwei
dokumentierte Ausnahmen in `style.css`: der `.sidebar-toggle-btn`-Block
(„Plastischer Look", siehe oben) und `#clb-overlay { background: #f2f2f2; }`
(Lightbox-Overlay, keine passende Variable im aktuellen Vokabular).

**Behoben durch `PLAN-Darkmode-Umschaltung.md`, AP-1.3** (vormals hier als
„vorgemerkt für ein künftiges Darkmode-Vorhaben" geführt, Befund aus dem
AP-1.rev des Vorgänger-Plans `PLAN-CSS-Variablen-Darkmode.md`):
`--color-background` wurde an fünf Stellen zweckentfremdet als **Textfarbe**
statt als Flächenhintergrund verwendet — `style.css` Z. 912 sowie
`glossar.css` Z. 91, 416, 678, 725 — plus eine sechste, während AP-1.1
zusätzlich gefundene Stelle (`style.css`, `.sc-lehrerhinweis__anmelden`).
Alle sechs wurden in `PLAN-Darkmode-Umschaltung.md`, AP-1.3, einzeln geprüft:
fünf echte Fehlnutzungen sind jetzt auf die neue Variable
`--color-text-on-accent` (`#ffffff` in beiden Modi, siehe Abschnitt
„Darkmode" unten) umgestellt; `glossar.css:91` (Sprechblasenspitze) blieb
bewusst unverändert, da der umgebende Container denselben Hintergrund trägt
und beide gemeinsam mit dem Darkmode mitziehen.

**To customize colors:** WordPress Admin → Design → Customizer →
„Farbeinstellungen" (Details Root-`CLAUDE.md`). Für die acht
Ergänzungsvariablen oben: Wert an beiden Stellen ändern —
`style.css` `:root` **und** `simple_clean_customizer_css()` in
`functions.php` —, sonst driften sie auseinander.

**Content-Links folgen den Themefarben (seit v1.5.96, AP-2.1 aus
`PLAN-Summary-PDF-und-Content-Links.md`, Vorhaben „Linkfarben allgemein").**
Ein vom Redakteur im Editor eingefügter Link (`core/link`, ohne eigene
Klasse) lief vorher auf Browser-Standardblau — `.entry-content` hatte keine
`a`-Regel. `style.css` trägt seither im `.entry-content`-Bereich:

```css
:where(.entry-content) a {
    color: var(--color-special-text, #71230a);
    text-decoration: underline;
    text-decoration-color: var(--color-ui-surface, #e24614);
    text-decoration-thickness: 1px;
    text-underline-offset: 2px;
}
:where(.entry-content) a:where(:hover, :focus-visible) {
    color: var(--color-ui-surface-dark, #c93d12);
}
```

Dunkelmodus kommt ohne Zusatzregel mit, da `--color-special-text` im
`:root[data-theme="dark"]`-Block bereits einen eigenen Wert trägt.

**Abweichung vom ursprünglichen Plan-Text — `:where(.entry-content) a`
statt `.entry-content a`, und der Grund ist tragend, keine Stilfrage.**
`.entry-content a` hat Spezifität (0,1,1) und schlägt damit jede
einklassige Komponentenregel im selben Inhaltsbereich — gemessen kippten
damit alle 74 `.page-index__page-link` des Blocks `fos/inhaltsverzeichnis`
von `rgb(51,51,51)`/ohne Unterstreichung auf themefarben/unterstrichen.
`:where()` senkt die Spezifität auf (0,0,1); Komponentenklassen
(`.page-index__page-link`, `.page-index__chapter-link`, `.admin-link`,
`.page-link` u. a.) gewinnen dadurch weiterhin, nur der klassenlose
Redakteurslink wird eingefärbt. **Wer die Regel auf `.entry-content a`
„vereinfacht", holt genau diesen Rückschritt zurück.**

**Zwei Präzisierungen aus dem unabhängigen Review (AP-2.rev), die die
Beschreibung oben ursprünglich zu knapp fasste:**
- Die Faustregel „Komponentenklasse gewinnt gegen `:where(.entry-content) a`"
  gilt **nicht uneingeschränkt** — sie setzt voraus, dass die
  Komponentenregel selbst außerhalb von `:where()` steht (reguläre
  Spezifität ≥ 0,1,0). WordPress' eigenes Blockstylesheet deklariert
  `.wp-block-button__link { text-decoration: none }` selbst als
  `:where(.wp-block-button__link)` (Spezifität 0,0,0) — dort gewinnt in
  Wahrheit nicht die Komponentenklasse gegen die Theme-Regel, sondern
  zusätzlich ausgegebene Global-Styles-Regeln mit 0,1,0. Betroffen ist
  aktuell nur dieser eine Kernblock-Fall (gemessen, Knopf bleibt
  unverändert weiß/ohne Unterstreichung); die Warnung gilt aber für jede
  künftige `:where()`-Komponentenregel, egal welchen Ursprungs.
- `:where()` senkt nur das **Gewicht**, es schränkt die **Treffermenge**
  nicht ein: Ein `<a class="irgendeine-ungestylte-klasse">` in
  `.entry-content` wird ebenso themefarben wie ein klassenloser Link — das
  ist beabsichtigt (ein Link, den keine andere Regel gestaltet, soll dem
  Theme folgen), ist aber **kein Wächter** wie das `a:not([class])` in
  `Plugins/CDB-Designer/assets/css/cbd-frontend-clean.css` (siehe unten).

**Container-Block-Inhalte des CDB-Designers folgen unabhängig einer eigenen
Regel** (`Plugins/CDB-Designer/CLAUDE.md`, Abschnitt „Links in
Container-Block-Inhalten") — Container-Blöcke liegen zwar meist innerhalb
von `.entry-content`, die dortige Regel nutzt aber bewusst `a:not([class])`
statt `a`, um `.cbd-block-reference-link`, `.cbd-block-reference-inline` und
`.wp-block-button__link` nicht zu überfahren. Beide Regeln greifen
unabhängig und liefern für den klassenlosen Link dasselbe Ergebnis — mit
**einer** bekannten, kosmetischen Ausnahme: `text-decoration-thickness`
fällt innerhalb eines Container-Blocks `auto` statt `1px` aus, weil die
Plugin-Regel die Kurzschreibweise `text-decoration: underline` benutzt, die
`-thickness`/`-style` zurücksetzt, und dabei mit höherer Spezifität
(0,2,1 gegen 0,0,1) gewinnt. Bei Fließtextgröße praktisch nicht
wahrnehmbar; bewusst nicht behoben (Befund B-2 aus AP-2.rev).

## Darkmode (seit v1.5.83, `PLAN-Darkmode-Umschaltung.md`, Phase 1 abgeschlossen)

Manueller, rein nutzergesteuerter Umschalter zwischen Hell- und Dunkelmodus.
**Bewusst KEIN `prefers-color-scheme`** — die Systemeinstellung von
Betriebssystem oder Browser wird an keiner Stelle abgefragt oder befolgt.
Das ist ein explizites Nicht-Ziel des zugrundeliegenden Plans, kein Versehen
und keine offene Baustelle (siehe dazu auch die Korrektur unter „Future
Enhancements" unten).

**Mechanismus:**
- Der gesamte Zustand steckt in einem einzigen Attribut: `data-theme="dark"`
  auf `<html>`. Fehlt das Attribut, gilt Lightmode — der Standardzustand.
- Persistiert wird in `localStorage` unter dem Schlüssel
  `fos-color-scheme` (Werte `'dark'` oder `'light'`).
- Die eigentlichen Farbwerte stehen in einem `:root[data-theme="dark"]`-Block
  direkt nach dem lichten `:root`-Block in `style.css` (14 Farbvariablen),
  identisch gespiegelt als Fallback in `simple_clean_customizer_css()` in
  `functions.php` — konkrete Werte siehe Abschnitt „Color Scheme" oben.

**Wo im Code:**
- **Toggle-Button:** `header.php`, Element `#fos-theme-toggle`
  (`.theme-toggle-btn`), im Markup vor `.menu-toggle` (siehe `reference_file_map.md`
  für die genaue Begründung der Platzierung). Eigenes Klick-Handler-Script
  am Dateiende von `header.php`, nach dem bestehenden Menü-Toggle-Script.
- **FOUC-Vermeidung:** ein blockierendes Inline-Script als erstes Element im
  `<head>` von `header.php`, **vor** `wp_head()` — liest `localStorage` aus
  und setzt `data-theme="dark"` auf `<html>`, bevor irgendein Stylesheet
  geladen ist. Kein `defer`/`async`, kein `matchMedia`-Aufruf.

**Pflicht-Konvention für neuen CSS-Code:** Neuer CSS-Code in diesem Theme
verwendet ausschließlich `var(--x, #fallback)` mit den in der Root-`CLAUDE.md`
(Abschnitt „Color Scheme") gelisteten Variablen — nie hartcodierte Hex-Werte.
Nur so bleibt neuer Code automatisch darkmode-fähig, ohne dass der
`:root[data-theme="dark"]`-Block nachgezogen werden muss.

**Stolperstein `<button>` (Fund 2026-08-30, behoben in v1.5.94):** Die
Konvention oben reicht bei Schaltflächen NICHT aus, wenn man die Farbe gar
nicht erst setzt. Ein `<button>` erbt `color` nicht vom `body` — das
Browser-Stylesheet weist ihm `color: buttontext` (schwarz) zu. `.menu-toggle`
(der Hamburger in `header.php`) hatte als einzige Schaltfläche in
`style.css` keine eigene `color`-Angabe und blieb deshalb im Darkmode
dunkel auf dunklem Header — auf dem Handy praktisch unsichtbar. Behoben
durch `color: var(--color-text-primary, #333)` samt Hover/Focus-Zustand,
wie ihn `.theme-toggle-btn`, `.sidebar-toggle-close` und `.page-toggle`
von Anfang an hatten. **Merksatz:** Jede neue Schaltfläche mit
`background: none` braucht zusätzlich eine explizite `color`-Angabe.

**Automatische Invertierung hintergrundloser Bilder (seit
`PLAN-Darkmode-Bildinvertierung.md`, 2026-09, abgeschlossen):**
Selbstgezeichnete Strukturbilder (z. B. Formelzeichnungen) haben oft einen
transparenten Hintergrund und dunkle Linien — im Darkmode auf dem dunklen
Seitenhintergrund unsichtbar. Ein neues Script erkennt solche Bilder
automatisch und invertiert nur sie, ohne dass Redakteure sie manuell
kennzeichnen müssen.

*Mechanismus:* `src/js/darkmode-image-invert.js` durchläuft nach `window.load`
(verzögert per `requestIdleCallback`, Rückfall `setTimeout` — nicht
render-blockierend) alle `<img>` in `.entry-content` (bewusst NICHT
site-weit). Für jedes Bild wird `analyzeImage(img)` aufgerufen: Das Bild
zeichnet sich auf einen unsichtbaren, auf max. 200px Kantenlänge
herunterskalierten `<canvas>` (Performance), `getImageData()` liefert die
Pixel. Zwei Bedingungen müssen BEIDE zutreffen, sonst bleibt das Bild
unangetastet:
1. Mindestens 5 % der Pixel sind nennenswert transparent (Alpha < 250) —
   Näherung für „hat keinen deckenden Hintergrund".
2. Die mittlere Luminanz (Rec.-709-Gewichtung) der undurchsichtigen Pixel
   liegt unter 100 (0–255-Skala) — Näherung für „ist überwiegend dunkel".

Treffen beide zu, bekommt das `<img>` die Klasse `fos-darkmode-invert`
(`img.dataset.fosDarkmodeChecked` verhindert Doppelanalyse). Die eigentliche
Invertierung passiert ausschließlich per CSS in `style.css`:
`:root[data-theme="dark"] img.fos-darkmode-invert { filter: invert(1); }` —
reine Invertierung ohne `hue-rotate`, bewusst konsistent mit dem
Tafelbild-Feature im CDB-Designer-Plugin (siehe dort, Abschnitt
„Tafelmodus im Darkmode"), das denselben Trade-off (Farbtöne kippen in ihr
Komplement) bereits nutzt. Damit läuft das Umschalten zwischen Hell- und
Dunkelmodus ohne erneute Bildanalyse — nur die CSS-Regel greift oder nicht.
`try/catch` um die Canvas-Analyse fängt `SecurityError` bei
fremdgehosteten Bildern ohne CORS-Freigabe ab (Rückfall: keine
Invertierung, kein Konsolenfehler). Enqueue in
`simple_clean_theme_assets()` (`functions.php`) folgt exakt dem Muster von
`'simple-clean-script'`.

*Finale Schwellenwerte (nach Live-Kalibrierung, unverändert gegenüber dem
ersten Entwurf):* `TRANSPARENT_ALPHA_THRESHOLD = 250`,
`MIN_TRANSPARENT_RATIO = 0.05`, `DARK_LUMINANCE_THRESHOLD = 100`,
`MAX_SAMPLE_DIMENSION = 200`. Auf der realen Content-Seite „Die wichtigsten
Organischen Grundlagen" (30 Bilder) erkennt die Funktion reproduzierbar
genau 2 Bilder (`Alkane.png`, `Alkene-1.png` — echte Strukturformeln mit
transparentem Hintergrund) als invertierungswürdig, 0 falsch-positive unter
den restlichen 28.

*Lightbox-Sonderfall:* Die Custom-Lightbox in `src/js/main.js` zeigt die
vergrößerte Ansicht NICHT über einen Klon des Original-`<img>`, sondern über
ein einziges, wiederverwendetes `<img id="clb-img">`, dessen `src` bei jedem
Öffnen neu gesetzt wird (`openLightbox(src, triggerEl)`). Die Markerklasse
würde deshalb ohne Zusatzcode nicht auf `#clb-img` landen. Seit diesem
Vorhaben überträgt `openLightbox()` sie per
`clbImg.classList.toggle('fos-darkmode-invert', !!(triggerEl &&
triggerEl.classList && triggerEl.classList.contains('fos-darkmode-invert')))`
— `toggle()` statt `add()`, damit die Klasse beim nächsten Öffnen eines
NICHT-invertierten Bildes auch wieder verschwindet (kein „Kleben" zwischen
zwei Bildern in derselben Sitzung).

*Bekannte, bewusst akzeptierte Einschränkung (aus dem unabhängigen Review
`AP-1.rev`, geringer Befund, kein Korrekturbedarf):* `scanImages()` läuft nur
einmalig nach `window.load`. Bilder, die erst nachträglich per AJAX in
`.entry-content` eingefügt werden (z. B. dynamisch nachgeladene
Container-Block-Inhalte), werden nicht erfasst. Kein Bestandteil dieses
Vorhabens; Kandidat für ein mögliches Folgevorhaben.

**Hintergrund/Historie:** `PLAN-CSS-Variablen-Darkmode.md` (Root-Verzeichnis)
legte in einer Vorstufe die heutigen CSS-Variablen und ihre
Customizer-Kopplung an, ohne selbst einen Umschalter zu bauen.
`PLAN-Darkmode-Umschaltung.md` (Root-Verzeichnis), Phase 1 „Theme", baut
darauf den eigentlichen manuellen Umschalter: dunklen Variablensatz
definieren, `--color-background`-Fehlnutzungen als Textfarbe bereinigen
(siehe oben), Toggle-Button samt FOUC-Script ergänzen und den plastischen
Look gegenprüfen. Ein unabhängiges Review (`AP-1.rev`) hat Phase 1
bestätigt.

## Navigation System

### Menu Registration

**Location:** `functions.php:22-24`

```php
register_nav_menus(array(
    'primary' => __('Hauptmenü', 'simple-clean-theme'),
));
```

### Menu Rendering

**Location:** `header.php:21-40`

**Behavior:**
- If menu is assigned: Displays custom menu
- If no menu: Shows homepage + all pages as fallback

**Setup Instructions (for users):**
1. WordPress Admin → Design → Menüs
2. Create or edit menu
3. Add pages/links
4. Assign to "Hauptmenü" location

### Mobile Menu Behavior

**Desktop (> 768px):**
- Horizontal navigation
- Menu toggle button hidden
- Always visible

**Mobile (≤ 768px):**
- Hamburger icon (☰) visible
- Navigation hidden by default
- Click toggle to reveal (adds `.active` class)
- Vertical stacked menu items
- Absolute positioning below header

## Development Workflow

### Initial Setup

```bash
cd Theme
npm install          # Install Vite and dependencies
npm run build       # Create initial build + ZIP
```

### Standard Development Workflow

**IMPORTANT:** After every significant change, follow this workflow:

```bash
# 1. Make your changes (edit PHP, CSS, or JS files)
# 2. Build and create ZIP
npm run build

# 3. Stage changes
git add .

# 4. Commit with descriptive message
git commit -m "Description of changes

🤖 Generated with [Claude Code](https://claude.com/claude-code)

Co-Authored-By: Claude <noreply@anthropic.com>"

# 5. Push to GitHub
git push origin main
```

**Automated by Claude Code:** When making theme changes, Claude should automatically:
1. Run `npm run build` to create JavaScript bundle + ZIP
2. Stage all changes
3. Create commit with clear description
4. Push to GitHub

### Daily Development Options

**Option 1: Watch mode** (for iterative JS development)
```bash
npm run watch       # Auto-rebuild JS on file changes
# Edit src/js/main.js
# Refresh browser to see changes
# When done, run: npm run zip && git add . && git commit -m "..." && git push
```

**Option 2: Manual builds** (recommended for theme changes)
```bash
# Edit PHP, CSS, or JS files
npm run build       # Build JS + create ZIP
git add . && git commit -m "..." && git push
```

**Option 3: Dev server** (for advanced JS development)
```bash
npm run dev         # Starts dev server with HMR
# Requires additional WordPress integration for HMR
# When done, run: npm run build && git add . && git commit -m "..." && git push
```

### Adding New JavaScript

**Steps:**
1. Edit `src/js/main.js` or create new modules
2. Import modules in `main.js` if needed
3. Run `npm run build`
4. Test in WordPress

**Example - Adding a new module:**

```javascript
// src/js/modules/scroll-effects.js
export function initScrollEffects() {
    window.addEventListener('scroll', () => {
        // Your scroll logic
    });
}

// src/js/main.js
import { initScrollEffects } from './modules/scroll-effects.js';

document.addEventListener('DOMContentLoaded', () => {
    // Existing code...
    initScrollEffects();
});
```

### CSS Modifications

**Current approach:** Direct editing of `styles.css`

**To add Sass/SCSS support:**
1. Update `vite.config.js` to include CSS entry points
2. Install `sass` package: `npm install -D sass`
3. Create `src/scss/main.scss`
4. Import in Vite config or JavaScript
5. Update `functions.php` to enqueue compiled CSS

## Git Workflow

### Repository Information

- **Remote:** https://github.com/Cyric25/FOS_Skripten_Website_Design
- **Branch:** `main`
- **Git initialized:** Yes
- **.gitignore configured:** Yes

### Ignored Files

The following are NOT tracked by Git (see `.gitignore`):
- `node_modules/` - NPM dependencies
- `dist/` - Build output (auto-generated)
- IDE files (`.vscode/`, `.idea/`)
- OS files (`.DS_Store`, `Thumbs.db`)
- `*.zip` files

### Common Git Commands

```bash
# Check status
git status

# Stage changes
git add .

# Commit changes
git commit -m "Description of changes"

# Push to GitHub
git push origin main

# Pull latest changes
git pull origin main
```

### Deployment Workflow

**For production deployment:**
1. Make changes to source files
2. Run `npm run build` to generate production assets
3. Commit source files only (not `dist/`)
4. Push to GitHub
5. On production server:
   - Pull latest code
   - Run `npm install` (if dependencies changed)
   - Run `npm run build`
   - Upload theme to WordPress

**Alternative:** Use CI/CD to auto-build on push.

## Theme Features

### Core Features

✅ **Responsive Design**
- Mobile-first approach
- Breakpoints at 768px (tablet) and 480px (mobile)
- Hamburger menu on mobile

✅ **Sticky Navigation**
- Header stays at top on scroll
- `position: sticky` with fallback

✅ **No Widgets/Sidebars**
- Clean, distraction-free reading
- Full-width content area

✅ **Accessibility**
- Semantic HTML5 structure
- ARIA labels on interactive elements
- Keyboard navigation support (ESC to close menu)

✅ **SEO-Friendly**
- Title tag support
- Semantic heading hierarchy
- Clean URL structure

✅ **Custom Logo Support**
- WordPress Customizer integration
- Can be added via Design → Customizer

### Interactive Features

**Mobile Menu Toggle:**
- Implemented in `src/js/main.js`
- Accessibility features (ARIA attributes)
- Click-outside to close
- ESC key to close

**Post Navigation:**
- Previous/next links on single posts
- Pagination on blog index

## Customization Guide

### Changing Colors

**Recommended: WordPress Customizer** (no code changes needed)
Design → Customizer → Farbeinstellungen. Changes there update the CSS
variables described in the „Color Scheme" section above at runtime via
`simple_clean_customizer_css()`.

**For a new variable not yet covered by the Customizer:** add it to the
`:root` block in `style.css` (see „Color Scheme" above for the current
variable set) with the existing literal value as its initial value, then
reference it as `var(--your-variable, #fallback)` wherever needed — never
hardcode a hex value directly in a selector.

### Changing Fonts

**Location:** `style.css:22`

```css
/* Current: System font stack */
font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, ...;

/* Example: Google Fonts */
/* 1. Add to header.php <head> */
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

/* 2. Update style.css */
font-family: 'Inter', -apple-system, BlinkMacSystemFont, ...;
```

### Adding New Templates

**Example: Custom template for landing pages**

1. Create `template-landing.php`:
```php
<?php
/*
Template Name: Landing Page
*/
get_header();
?>
<main class="landing-page">
    <!-- Custom layout -->
</main>
<?php get_footer(); ?>
```

2. Available in page editor dropdown

### Adding Sidebar Support

**If you need sidebars** (contrary to theme philosophy):

1. Register sidebar in `functions.php`
2. Create `sidebar.php`
3. Update templates to call `get_sidebar()`
4. Add sidebar styles to `style.css`

## Compatibility

### WordPress Blocks (Gutenberg)

**Current support:** Basic
- Theme supports title-tag, post-thumbnails, HTML5
- No block-specific styles (uses WordPress defaults)
- Works with Container Block Designer plugin
- Works with Eigene WP Blocks plugin

**To improve block support:**
- Add `add_theme_support('align-wide')` for wide/full alignment
- Add `add_theme_support('editor-styles')` for editor styling
- Create `editor-style.css` for backend editor matching

### Plugin Compatibility

**Tested with:**
- ✅ Container Block Designer (CDB-Designer)
- ✅ Eigene WP Blocks (Modular Blocks)

**Expected to work:**
- WordPress SEO plugins (Yoast, Rank Math)
- Contact forms (Contact Form 7, Gravity Forms)
- Page builders (may override theme styles)

### Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ⚠️ IE11+ (limited testing, may need polyfills)

## Performance Optimization

### Current Optimizations

1. **JavaScript:**
   - Loaded in footer (non-blocking)
   - File modification timestamps for cache busting
   - Production build is minified

2. **CSS:**
   - Single stylesheet (minimal HTTP requests)
   - No external dependencies

3. **Images:**
   - Responsive images via WordPress default behavior
   - No image optimization built-in (use plugin like Smush)

### Recommendations

**For better performance:**
1. **Lazy Loading:** Add to images in templates
2. **Critical CSS:** Inline above-the-fold CSS
3. **Font Loading:** Use `font-display: swap`
4. **Image Optimization:** Use image optimization plugin
5. **Caching:** Use WordPress caching plugin (W3 Total Cache, WP Rocket)

## Troubleshooting

### JavaScript not working

**Symptoms:** Menu doesn't toggle, no console logs

**Checks:**
1. Verify build exists: `dist/js/main.js` should exist
2. Run `npm run build` if missing
3. Check browser console for JavaScript errors
4. Verify script is enqueued: View source → search for `simple-clean-script`
5. Clear WordPress cache if using caching plugin

**Conflicts:**
- If other plugins load conflicting JavaScript, use `wp_dequeue_script()` in `functions.php`

### Menu not showing

**Symptoms:** Navigation is empty or shows "Home" only

**Checks:**
1. WordPress Admin → Design → Menüs
2. Verify menu is created
3. Verify menu is assigned to "Hauptmenü" location
4. If no menu exists, theme shows fallback (homepage + all pages)

### Styles not applied

**Symptoms:** Unstyled content, broken layout

**Checks:**
1. Verify `style.css` exists in theme root
2. Check theme header in `style.css` (required for WordPress)
3. Clear browser cache (Ctrl+Shift+R)
4. Check for CSS conflicts with plugins
5. Verify file permissions (should be readable by web server)

### Build errors

**Symptoms:** `npm run build` fails

**Common causes:**
1. **Syntax error in `src/js/main.js`:** Check error message, fix JavaScript syntax
2. **Missing dependencies:** Run `npm install`
3. **Node.js version:** Ensure Node 16+ (`node --version`)
4. **Path issues:** The `#` character in path may cause issues (Vite warning)

**Path issue with `#` character:**
- Warning appears during build: "The project root contains the "#" character"
- Doesn't break functionality but may cause issues with some tools
- Solution: Rename parent directory to remove `#` (optional)

### Mobile menu stuck open

**Symptoms:** Navigation stays visible on mobile

**Checks:**
1. Check if `active` class is stuck on `.main-navigation`
2. Clear browser cache
3. Test in incognito mode
4. Check for JavaScript errors in console

### Git push rejected

**Symptoms:** `git push` fails with "rejected" error

**Solution:**
```bash
git pull origin main          # Pull latest changes
# Resolve any conflicts if they appear
git push origin main          # Push again
```

## Important Code Locations

### Theme Setup
- Theme registration: `functions.php:8-25`
- Menu registration: `functions.php:22-24`
- Asset enqueuing: `functions.php:28-45`

### Templates
- Blog listing: `index.php:6-22` (post loop)
- Single post: `single.php:6-32` (with meta)
- Static page: `page.php:6-15` (minimal)
- Header: `header.php:10-43` (navigation)
- Footer: `footer.php:1-16`

### Styles
- Theme header: `style.css:1-12`
- Layout: `style.css:28-88`
- Navigation: `style.css:58-74`
- Content: `style.css:127-248`
- Sidebar: `style.css:350-602`
- Responsive: `style.css:279-348, 532-602`

### JavaScript
- Main entry: `src/js/main.js:7-33` (menu toggle)
- Inline toggle: `header.php:45-50` (basic version)

### Build Configuration
- Vite config: `vite.config.js:4-23`
- NPM scripts: `package.json:6-11`

## Known Issues

### Path Character Warning

**Issue:** Vite warns about `#` character in project path
```
The project root contains the "#" character (C:/Users/.../OneDrive...//#Unterricht/Website/Theme)
```

**Impact:** Build works, but may cause issues with some tools.

**Solution:** Rename `#Unterricht` to `Unterricht` (optional, requires updating OneDrive sync).

### No Block Editor Styles

**Issue:** Gutenberg editor doesn't match frontend styles.

**Impact:** WYSIWYG experience is limited.

**Solution:** Add editor styles:
1. `add_theme_support('editor-styles')` in `functions.php`
2. Create `editor-style.css` matching frontend
3. Enqueue with `add_editor_style()`

## Future Enhancements

### Potential Improvements

1. **Sass/SCSS Support**
   - Better CSS organization with variables, mixins, nesting
   - Compile via Vite

2. **CSS Bundling**
   - Move CSS to `src/css/main.css`
   - Import in JavaScript or Vite config
   - Auto-prefix for browser compatibility

3. **Block Editor Integration**
   - Custom block styles
   - Editor stylesheet matching frontend
   - Block patterns for common layouts

4. **Dark Mode** — **umgesetzt seit `PLAN-Darkmode-Umschaltung.md`, Phase 1**
   (siehe Abschnitt „Darkmode" oben): CSS-Variablen per
   `:root[data-theme="dark"]`, manueller Toggle-Button mit
   `localStorage`-Persistenz. **Bewusst KEINE Systempräferenz** — die
   tatsächliche Umsetzung fragt `prefers-color-scheme` an keiner Stelle ab;
   das ist ein explizites Nicht-Ziel des Plans, nicht eine noch offene
   Erweiterung dieser Liste.

5. **Animation Library**
   - Intersection Observer for scroll animations
   - Smooth transitions

6. **Advanced Typography**
   - Fluid typography (clamp())
   - Better vertical rhythm
   - Improved mobile readability

7. **Webpack Alternative**
   - Current Vite setup is modern and fast
   - No need to switch unless specific requirements

## Testing Checklist

Before committing changes:

- [ ] Run `npm run build` successfully
- [ ] Verify ZIP created: `dist/simple-clean-theme-v{version}.zip`
- [ ] Test ZIP contents (optional): `unzip -l dist/simple-clean-theme-v*.zip`
- [ ] Test on desktop (>1200px)
- [ ] Test on tablet (768px-1199px)
- [ ] Test on mobile (<768px)
- [ ] Verify hamburger menu works
- [ ] Check menu toggle on click-outside
- [ ] Test ESC key closes menu
- [ ] Verify page/post content renders correctly
- [ ] Check footer login link works
- [ ] Test navigation menu (primary menu assigned)
- [ ] Verify no JavaScript console errors
- [ ] Check Git status before commit
- [ ] Ensure `dist/` folder not committed (in .gitignore)
- [ ] Stage, commit, and push changes to GitHub

## Funktionsübersicht functions.php (WICHTIG — Wegweiser für künftige Arbeiten)

Die functions.php (~3850 Zeilen) enthält weit mehr als Theme-Setup. Die großen
Subsysteme, mit Suchankern (Funktionsnamen sind stabiler als Zeilennummern):

### Glossar-System (größtes Subsystem)
- **CPT + Taxonomie:** `simple_clean_register_glossar_cpt()` (Slug `glossar`),
  `simple_clean_register_glossar_taxonomy()` (Kategorie `glossar_category`)
- **Automatische Verlinkung:** `the_content`-Filter (Priorität 10000)
  `simple_clean_glossar_auto_link_content_optimized()` — Kandidaten-basiert:
  beim Speichern scannt `simple_clean_scan_glossar_candidates()` den Inhalt und
  legt Term-IDs in Post-Meta `_glossar_term_candidates` ab; beim Rendern werden
  nur diese Terms geladen (Object-Cache `glossar_terms`/`simple_clean_glossar`).
  Überspringt korrekt `<a>`, `<script>`, `<style>`, `<code>`, `<pre>`.

  **`_glossar_scan_version` entscheidet, nicht die Kandidatenliste allein
  (Fix v1.5.70) — bitte nicht „vereinfachen":**
  Ein **leeres** Kandidaten-Array ist ein gültiges Scan-Ergebnis und bedeutet
  „auf dieser Seite kommt kein Begriff vor". Eine Prüfung mit `empty()` kann
  das nicht von „noch nie gescannt" unterscheiden — beides sieht gleich aus.
  Genau daran hing ein teurer Fehler: Textarme Seiten (etwa eine Übersicht,
  deren Inhalt praktisch nur aus einem Block-Kommentar besteht) fielen in den
  Fallback, luden **alle** Glossarbegriffe, expandierten sie über
  `simple_clean_get_glossar_term_variants()` in Wortvarianten und schickten
  einen einzigen Alternations-Regex über das gesamte gerenderte HTML.
  Gemessen: **1,998 s statt 0,058 s** bei 1049 Begriffen — Faktor 34, bei
  identischer Query-Zahl.
  Maßgeblich ist deshalb das Meta `_glossar_scan_version` (gesetzt von
  `simple_clean_update_glossar_candidates()` und vom Bulk-Scan). Ist es
  vorhanden, gilt die Kandidatenliste; ist sie leer, wird der Inhalt
  unverändert zurückgegeben. Der Fallback greift nur noch ohne dieses Meta.
  Dieselbe Entscheidung trifft `simple_clean_glossar_assets()` für
  `glossarData` — beide müssen übereinstimmen, sonst liefert die eine Seite
  1049 Begriffe samt Definitionen an den Browser, während die andere nichts
  verlinkt.

  **Folge, die man kennen muss:** Seiten, die nie gescannt wurden, sind
  langsam. Nach einem Import direkt in die Datenbank (also ohne `save_post`)
  gehört der Bulk-Scan auf der Glossar-Einstellungsseite ausgeführt.

  **Datenverlust-Fund und Behebung (2026-08-29).** Live gemessen auf einer
  programmatisch ohne angemeldeten Benutzer angelegten Seite (1155
  veröffentlichte Glossarbegriffe): Der Rückfall lud ALLE Begriffe, expandierte
  sie über `simple_clean_get_glossar_term_variants()` in Wortvarianten und
  baute daraus in `simple_clean_build_glossar_pattern_for_terms()` ein rund
  800 kB großes Alternations-Pattern. `preg_replace_callback()` in
  `simple_clean_process_glossar_links_optimized()` scheiterte damit an
  „Compilation failed: regular expression is too large" und lieferte `null`
  zurück — der Aufrufer hängte diesen `null`-Wert **ungeprüft** an das Ergebnis
  an (`$result .= $processed_part`), wodurch der komplette Textabschnitt
  ersatzlos verschwand. Konkret: Inhaltslänge nach `the_content` fiel von 6246
  auf 2347 Zeichen, jede Beschriftung (`<h1></h1>`, `<label></label>`, …) war
  leer, die Seite brauchte 30 s und endete in HTTP 500 („Maximum execution
  time exceeded"). Nach einem einmaligen Scan derselben Seite lud sie in
  0,6 s vollständig. Erstbeschrieben als Nebenbefund in
  `Plugins/CDB-Designer/CLAUDE.md`, Abschnitt „Nebenbefund am Theme: nie
  gescannte Seiten verlieren ihren Text".

  Behoben an zwei Stellen, beide notwendig:

  1. **Datenverlust-Sicherung** (`simple_clean_process_glossar_links_optimized()`):
     Der Rückgabewert von `preg_replace_callback()` wird jetzt vor der
     Übernahme auf `null` geprüft. Schlägt die Kompilierung fehl, bleibt der
     betroffene Textabschnitt unverändert (unverlinkt, aber vollständig)
     statt zu verschwinden. Das greift unabhängig von der Ursache — auch bei
     einem künftigen, genuin sehr großen *echten* Kandidaten-Set.
  2. **Root Cause behoben** (`simple_clean_ensure_glossar_scanned()`, neue
     Funktion): Ersetzt den Rückfall über ALLE Begriffe vollständig. Fehlt
     `_glossar_scan_version`, holt diese Funktion den Scan (dieselbe schnelle
     `mb_stripos()`-Suche wie `simple_clean_scan_glossar_candidates()`) SOFORT
     nach und persistiert ihn (`_glossar_term_candidates`,
     `_glossar_scan_version`, `_glossar_last_scanned`) — genau wie der
     `save_post`-Hook, nur ohne dessen `current_user_can()`-Gate, da das
     Scannen selbst keine sicherheitsrelevante Aktion ist und der Aufrufer
     hier typischerweise ein nicht angemeldeter Frontend-Besucher ist. Jede
     nie gescannte Seite kostet dadurch nur noch EINMAL den (schnellen)
     Scan statt bei jedem Aufruf den teuren Alternations-Rückfall.
     Aufgerufen von **beiden** Stellen, die bislang unabhängig auf „alle
     Begriffe" zurückfielen: `simple_clean_glossar_auto_link_content_optimized()`
     (der Autolinker selbst) und `simple_clean_glossar_assets()` (liefert
     `glossarData` fürs Frontend-JS) — die beiden Stellen bleiben damit
     weiterhin zwangsläufig konsistent (siehe Kommentar dort), jetzt aber
     ohne die teure/gefährliche gemeinsame Grundlage.

  Der `?sc_perf=1`-Zähler `fallback` (siehe Abschnitt „Diagnose: Wo geht die
  Zeit hin?" unten) zählt seitdem nicht mehr „Rückfall über ALLE Begriffe",
  sondern „Seite musste in diesem Aufruf zum ersten Mal gescannt werden" —
  ein einmaliges, günstiges Ereignis statt eines wiederkehrenden teuren.

  Regressionstest ohne WordPress: `php tools/test-glossar-scan-fallback.php`
  — extrahiert die betroffenen Funktionen aus der echten `functions.php` und
  prüft sowohl die Scan-Nachholung (inkl. „kein zweiter Scan bei erneutem
  Aufruf") als auch die Datenverlust-Sicherung anhand eines echten,
  reproduzierten PCRE-Kompilierungsfehlers.
- **Einstellungen:** Optionen `glossar_modal_type` (tooltip|sidebar),
  `glossar_auto_link`, `glossar_first_only`, `glossar_case_sensitive`,
  `glossar_auto_rebuild`; Admin-Seite `simple_clean_glossar_settings_page()`
  (Untermenü des Glossar-CPT) mit CSV-Import/-Export und Bulk-Scan
  (AJAX `glossar_bulk_scan` / `glossar_bulk_scan_batch`).

  **CSV-Import: mehrere Dateien gleichzeitig (seit
  `PLAN-Glossar-Mehrfachimport-und-Seitenmanager-Ergaenzungen.md`,
  Phase 1).** Das Datei-Input trägt `multiple` **und** den Feldnamen
  `glossar_csv[]` (mit `[]` — ohne die Klammern liefert PHP bei einer
  echten Formular-Absendung nie ein Array in `$_FILES`, das war ein
  kritischer, im Review gefundener und in `AP-1.fix1` behobener Fehler:
  der Import schlug dadurch selbst im Einzeldatei-Fall fehl). Neue
  Funktion `simple_clean_handle_glossar_import_multi()` ruft die
  bestehende `simple_clean_handle_glossar_import($file, &$existing_posts)`
  je Datei mit einem aus dem PHP-Mehrfach-Upload-Array rekonstruierten
  „virtuellen" `$_FILES`-Element auf; `$existing_posts` wird dabei per
  Referenz über alle Dateien einer Sitzung hinweg weitergereicht, damit
  Duplikate auch DATEIÜBERGREIFEND erkannt werden. Eine fehlerhafte
  Einzeldatei (falsches Format, Lesefehler) wird übersprungen und im
  `admin_notices`-Bericht je Datei vermerkt, die übrigen Dateien werden
  trotzdem importiert.

  **Auto-Scan nach Mehrfach-Import:** Liefert der Gesamtimport
  `sollScannen === true` (mindestens ein Begriff über alle Dateien hinweg
  neu importiert oder aktualisiert, `imported_gesamt + updated_gesamt > 0`),
  startet der bestehende Bulk-Scan-AJAX-Ablauf beim Neuladen der
  Einstellungsseite automatisch — ohne den `confirm()`-Dialog des
  manuellen Buttons „Alle Seiten jetzt scannen", der unverändert und
  zusätzlich nutzbar bleibt. Die Kopplung läuft über die JS-Variable
  `glossarAutoScan` (aus `$glossar_import_ergebnis['sollScannen']`
  abgeleitet), die denselben `startBulkScan()`-Ablauf aufruft wie der
  Klick-Handler.
- **Duplikat-Erkennung:** `simple_clean_glossar_term_exists_or_similar()`
  (Normalisierung, Singular/Plural-Heuristik, Levenshtein ≤ 2).
- **REST:** `POST simple-clean/v1/glossar` (Permission: `edit_posts`) zum
  programmatischen Anlegen von Begriffen.
- **Frontend-Assets:** `simple_clean_glossar_assets()` lädt glossar.js nur,
  wenn die Seite Kandidaten hat; Terms werden als `glossarData` lokalisiert.

### Website-Passwortschutz (kompletter Site-Lock)
- `simple_clean_password_protection_check()` auf `template_redirect`;
  Admin-Seite unter `simple_clean_password_protection_menu()`.
- Passwort gehasht (`wp_check_password`), Brute-Force-Lockout
  (10 Versuche / 15 min / IP via Transient), Zugriffs-Cookie
  `simple_clean_password_granted` = abgeleiteter Token (30 Tage, httponly).
- Eingeloggte Nutzer und wp-login sind ausgenommen. Formular-HTML inline in
  `simple_clean_show_password_form()`.

### AI-Crawler-Blocker
- `simple_clean_block_ai_user_agents()` auf `template_redirect` (Priorität 1,
  läuft VOR dem Passwortschutz): 403 für bekannte AI-User-Agents
  (Musterliste im Code); Logging nur bei WP_DEBUG.
- Ergänzend `simple_clean_generate_robots_txt()` (Filter `robots_txt`).
- Wichtig zu wissen: blockt nur WordPress-gerenderte Seiten — statische
  Dateien unter /wp-content/ liefert der Webserver direkt aus.

### SVG-Upload-Pipeline
- `simple_clean_allow_svg_upload()` (MIME), `simple_clean_fix_svg_mime()`,
  Sanitizing bei Upload via `simple_clean_sanitize_svg_upload()` →
  `simple_clean_sanitize_svg_string()`: DOM-basiert, entfernt script/
  foreignObject/SMIL-Elemente und on*-Attribute, href-Whitelist
  (`simple_clean_svg_href_is_safe()`: nur #fragment, relativ, http(s),
  data:image/* außer SVG). Abmessungen für den Editor:
  `simple_clean_svg_dimensions()`.

### Custom Lightbox (CLB)
- Ersetzt die WP-Core-Lightbox komplett: `simple_clean_disable_wp_lightbox()`
  (render_block_data) + `simple_clean_custom_lightbox()` (render_block) hängen
  `data-clb-src` (Full-Size-URL) an core/image-Blöcke; das Frontend-JS mit
  FLIP-Zoom-Animation liegt in `src/js/main.js` (einziger Inhalt des Bundles).

### Sidebar-Navigation
- `sidebar.php`: kompletter Seitenbaum mit EINER `get_pages()`-Query +
  Parent-Children-Map; Swipe/ESC/Click-outside im Inline-Script.
- **ACHTUNG Hoisting-Falle:** Die Template-Funktionen (`get_root_page_id`,
  `display_page_tree_item`) stehen bewusst AM DATEIANFANG in
  function_exists-Guards — bedingt deklarierte Funktionen werden von PHP
  nicht gehoistet; standen sie am Dateiende, gab es einen Fatal (v1.5.57→58).
- Pro Seite abschaltbar über Meta `_simple_clean_hide_navigation`
  (Meta-Box „Seitenleiste (Sidebar) Einstellungen").

### Admin-Werkzeuge (includes/admin/)
- `page-manager.php`: Seiten-Übersicht mit Drag-Sortierung, Anlegen/Löschen/
  Status-Toggle per AJAX (`page_manager_*`), Rechteprüfung pro Einzelseite.

  **Sammelaktionen (seit v1.5.76).** Auswahlkästchen je Zeile plus Leiste
  `.page-bulk-bar`; ein zusätzlicher Endpunkt `page_manager_bulk_action`
  (Nonce `page_manager_nonce`, wie die vier bestehenden). **Zwölf** Aktionen
  als **Whitelist** in `bulk_aktionen()`: `status_publish`, `status_draft`,
  `set_parent`, `hide_index`, `show_index`, `hide_nav`, `show_nav`,
  `lock_teacher`, `unlock_teacher`, `lock_nav`, `unlock_nav`, `trash`.
  Der Wert aus `$_POST` wird nur gegen diese Liste geprüft und nie in einen
  Methodennamen übersetzt.

  **`lock_nav`/`unlock_nav` (seit
  `PLAN-Glossar-Mehrfachimport-und-Seitenmanager-Ergaenzungen.md`,
  Phase 2) togglen `_simple_clean_nav_gesperrt`** — dieselbe Sperre, die in
  `functions.php` als Einzelseiten-Checkbox „Für Navigation sperren"
  existiert (macht eine Seite in Inhaltsverzeichnis/Seitenleiste nicht mehr
  anklickbar, Unterbaum bleibt bedienbar, **kein Zugriffsschutz** — die
  Seite bleibt über ihre Adresse erreichbar). **Nicht zu verwechseln** mit
  `hide_nav`/`show_nav` zwei Zeilen darüber: Die togglen das ANDERE Meta
  `_simple_clean_hide_navigation` (ob eine Seite selbst eine eigene
  Sidebar anzeigt) und mit der ebenfalls vorhandenen, aber weiterhin ohne
  eigene Bulk-Aktion gebliebenen fünften Checkbox
  `_simple_clean_hide_from_sidebar` (nimmt eine Seite ganz aus dem
  Seitenbaum).

  **Neue Seiten landen immer am Ende ihrer Geschwister (seit demselben
  Plan, Phase 2).** `ajax_create_page()` ermittelte den `menu_order` einer
  neuen Seite vorher hartcodiert als `0`; seither wird vor dem
  `wp_insert_post()`-Aufruf der höchste vorhandene `menu_order`-Wert unter
  den Geschwistern des Ziel-`post_parent` per `$wpdb`-Abfrage ermittelt und
  die neue Seite mit diesem Wert + 1 angelegt — einheitlich für Unterseiten
  und Seiten auf oberster Ebene. Ohne Geschwister bleibt `menu_order` weiterhin
  `0`.

  Rechte werden **je Einzelseite** geprüft (`edit_page`), beim Veröffentlichen
  zusätzlich `publish_pages`, beim Papierkorb `delete_page`. Fehler werden
  gesammelt statt beim ersten Problem abzubrechen — dasselbe Muster wie in
  `ajax_update_order()`. `set_parent` nutzt die vorhandene
  `would_create_circular_reference()`.

  **Zwei Schreibwege, bewusst unterschiedlich — nicht vereinheitlichen:**

  | Aktion | Weg | Grund |
  |---|---|---|
  | Status | `wp_update_post()` | feuert `save_post`, dadurch läuft `simple_clean_update_glossar_candidates()` mit und die Seite bekommt `_glossar_scan_version`. Ohne dieses Meta fällt sie beim Rendern auf **alle** Glossarbegriffe zurück (gemessen 1,998 s statt 0,058 s bei 1049 Begriffen) |
  | Elternseite | `$wpdb->update()` + `clean_post_cache()` | wie `ajax_update_order()`; der Inhalt ändert sich nicht, ein Glossar-Scan wäre unnötig |
  | Meta-Aktionen | `update_post_meta()` / `delete_post_meta()` mit dem String `'1'` | identisch zur Meta-Box in `functions.php` (Zeilen 604–615); eine abweichende Schreibweise würde von `includes/page-index.php` und `sidebar.php` nicht erkannt |

  Die Antwort enthält `reload`: wahr bei Aktionen, die den Baum sichtbar
  verändern (Status, Papierkorb, Elternseite), falsch bei den Meta-Aktionen —
  dort genügt eine Statusmeldung. Vor dem Neuladen sichert das JavaScript den
  Aufklapp-Zustand, wie `createPage()` es tut.

  **Zur Drag-Sortierung:** Das Sortable ist mit `handle: '.drag-handle'`
  initialisiert. Ein Klick auf das Auswahlkästchen kann deshalb kein Ziehen
  auslösen — eine `cancel`-Option ist **nicht** nötig und wurde bewusst nicht
  ergänzt. Wer `handle` entfernt, muss sie nachrüsten.
- `clipboard-uploader.php`: Bilder aus der Zwischenablage in die Mediathek
  (Capability `upload_files`).

**Der Menü-Slug `page-manager` ist eine öffentliche Schnittstelle.** Das
Plugin CDB-Designer hängt dort per `add_submenu_page()` den Eintrag „Seiten
importieren" ein (siehe `Plugins/CDB-Designer/CLAUDE.md`, Abschnitt
„Seitenimport"). Wird der Slug hier geändert oder der Seitenmanager entfernt,
verschwindet der Eintrag aus diesem Menü und landet im Rückfall unter
„Container Designer". Beim Umbenennen also das Plugin mitziehen.

### Sonstiges
- Customizer-Farben: `simple_clean_customize_register()` /
  `simple_clean_customizer_css()` — CSS-Variablen in :root (Details siehe
  Root-CLAUDE.md „Color Scheme").
- Menü-Auto-Zuweisung: `simple_clean_auto_assign_menu()` (sucht Menü
  „Skripten Übersicht").

## Inhaltsverzeichnis-Block (`fos/inhaltsverzeichnis`)

Ersetzt den Core-Block „Seitenliste" (`core/page-list`) auf den
Kapitelübersichten. Code in `includes/page-index.php`, Metadaten in
`blocks/inhaltsverzeichnis/block.json`.

**Attribute** (Standardwerte in Klammern): `rootPage` (0 = oberste Ebene),
`maxDepth` (2), `layout` (`cards` | `list` | `columns`), `columns` (3),
`collapsible` (true), `openByDefault` (false), `showSearch` (true),
`showCounts` (false). Bereinigt werden sie ausschließlich in
`simple_clean_page_index_sanitize_attrs()` — bewusst ohne Datenbankzugriff
und dadurch rein prüfbar.

**Datenbeschaffung:** `simple_clean_page_index_daten()` stellt zwei schlanke
Abfragen (fünf Spalten, kein `post_content`) und berechnet alle Pfade in
einem Durchlauf per Breitensuche ab der Wurzel. Der Durchlauf erledigt
nebenbei zweierlei ohne Sonderbehandlung: Verwaiste Knoten (Elternteil nicht
veröffentlicht) und Zyklen sind von der Wurzel aus nicht erreichbar und
fallen samt Unterbaum heraus. Das Ergebnis liegt in einer statischen
Variablen — mehrere Blöcke auf einer Seite teilen die Abfragen.

**Kein Zwischenspeicher, und das ist Absicht.** Der ursprüngliche Plan sah
einen vorberechneten Index in `wp_options` mit Versionszähler,
Invalidierungshooks und Fragment-Cache vor. Eine Messung am 2026-08-08 hat
das widerlegt: Der Seitenbaum kostet bei 258 Seiten rund 0,03 s. Ein
Zwischenspeicher würde kein gemessenes Problem lösen, aber Fehlerquellen
einführen — allen voran veraltete Ausgabe nach Sortierungen im
Seitenmanager, der `post_parent` und `menu_order` an `save_post` vorbei
schreibt. Belege in `docs/PLAN-Seitenindex.md`, Abschnitt 11.

**Zwei Registrierungen, beide nötig:**

| Ort | Was sie leistet |
|---|---|
| `register_block_type_from_metadata()` in `includes/page-index.php` | Rendering, Block-Supports, Metadaten |
| `registerBlockType()` in `src/js/page-index-editor.js` | **Sichtbarkeit im Einfügen-Menü** |

Eine rein serverseitige Registrierung genügt **nicht** — der Block wäre im
Editor schlicht nicht auffindbar. Das hat beim Bauen einen Umweg gekostet.

Ebenfalls bewusst: **kein `"render"` in der `block.json`.** Diese Eigenschaft
gibt es erst ab WordPress 6.1 und würde auf älteren Versionen stillschweigend
ignoriert — der Block gäbe nichts aus, ohne Fehlermeldung. Das Theme
deklariert „Requires at least: 5.0", deshalb `render_callback`.

**Gestaltung:** `src/css/page-index.css`. Klassen: `.page-index`,
`--cards|--list|--columns`, `--cols-1..4` (**nur bei `--columns`**, siehe
unten), `__search`, `__chapters`,
`__chapter`, `__chapter-link`, `__sub`, `__sub-toggle`, `__pages`, `__page`,
`__page-link`, `__empty`, `__no-results`, `__status` sowie
`__chapter--hidden` / `__page--hidden` für den Filter.
Aufgeklappt wird über natives `<details>` — barrierefrei, tastaturbedienbar,
funktioniert ohne JavaScript.

**Farben:** sechs `--pidx-*`-Variablen, ausgegeben in
`simple_clean_customizer_css()`. Fünf davon sind im Customizer unter
„Inhaltsverzeichnis" einstellbar (Kartenhintergrund, Kartenrahmen,
Titelfarbe, Eckenradius, Dichte). `--pidx-accent` hat bewusst **keinen**
eigenen Regler und folgt `--color-ui-surface`.
Im CSS stehen **keine freistehenden Farbwerte**; Hexwerte nur als Rückfall in
`var(--x, #wert)`, wo die Customizer-Farbe weiterhin gewinnt.
Der „plastische Look" bleibt außen vor — er ist dem Navigations-Streifen
vorbehalten.

**Kapitelkarten stehen immer untereinander** (seit v1.5.75, auf Wunsch des
Nutzers). Bei Kapiteln unterschiedlicher Länge entstehen im Raster ungleich
hohe Karten und ausgefranste Reihen, und die Lesereihenfolge wird mehrdeutig.
Wer mehrere Spalten will, wählt die Darstellung „Mehrspaltig" — dort ist der
Spaltensatz der Zweck. **Folge:** Die Spalteneinstellung wirkt nur noch auf
`--columns`; der Renderer gibt `--cols-N` auch nur dort aus, und der Editor
blendet den Regler bei den anderen Darstellungen aus. Wer das Raster für
Karten zurückholen will, braucht drei Änderungen — CSS, Klassenausgabe in
`simple_clean_render_page_index()` und die Bedingung im Editor-Script.

**Adressen baut der Block selbst** — `simple_clean_page_index_url()`, nicht
`get_permalink()`. Das ist Absicht: `get_permalink()` löst je Seite die
Elternkette erneut auf, der fertige Pfad steht im Knoten aber schon.
**Die Hülle darum muss trotzdem von WordPress kommen.** Bis 2026-08-21 stand
dort `home_url('/' . $uri . '/')`, was unterstellt, sprechende Adressen lägen
unmittelbar unter der Startadresse. Für die PATHINFO-Struktur
`/index.php/%postname%/` — eingestellt, wo mod_rewrite oder `.htaccess`
fehlen, etwa auf dem Testserver — stimmt das nicht: **jeder Link im
Verzeichnis endete auf 404**, während die Seitenleiste richtig verlinkte.
Auffällig spät bemerkt, weil die verbreitete Struktur `/%postname%/` zufällig
dasselbe Ergebnis liefert. Jetzt liefert `WP_Rewrite::get_page_permastruct()`
die Hülle, dieselbe Quelle wie `_get_page_link()` im Kern. **Wer hier wieder
selbst zusammensetzt, baut den Fehler nach.**

**Seiten ausnehmen:** Meta `_simple_clean_hide_from_index`, gesetzt über die
zweite Checkbox der Meta-Box „Navigation, Verzeichnis & Zugriff". Die Seite
entfällt **samt ihrem gesamten Unterbaum**, bleibt aber erreichbar und in der
Seitenleiste sichtbar.

**Gegenstück seit 2026-08-21:** `_simple_clean_hide_from_sidebar` (fünftes
Kästchen, „Nicht in der Seitenleiste anzeigen") nimmt eine Seite samt Unterbaum
aus dem Seitenbaum links, lässt sie im Verzeichnis aber stehen. Nachgeschlagen
wird sie über `simple_clean_seitenleiste_versteckte_seiten()` in
`includes/page-index.php` — dort, weil sie die gleiche Art Frage beantwortet
wie `simple_clean_nav_gesperrte_seiten()` daneben, obwohl nur `sidebar.php` sie
liest. **Bewusst zwei getrennte Metas:** Das Verzeichnis ist die kuratierte
Übersicht, die Seitenleiste der vollständige Arbeitsbaum. Das
Verzeichnis-Häkchen um die Seitenleiste zu erweitern hätte jede Seite, die es
heute schon trägt, still aus der Navigation genommen. **Beides ist kein
Zugriffsschutz** — dafür gibt es „Nur für Lehrpersonen sichtbar".

### Lehrpersonen-Toggle (Phase 2 von `PLAN-Inhaltsverzeichnisse.md`, seit 2026-08-26, v1.5.90)

Angemeldete Lehrpersonen sehen im Block standardmäßig **dieselbe Ansicht wie
ein nicht angemeldeter Besucher** — als „Nur für Lehrpersonen sichtbar"
gesperrte Seiten sind ausgeblendet. Ein Button blendet sie bei Bedarf
zusätzlich ein, ohne Neuladen der Seite.

**Markup-Vertrag** (verbindlich festgelegt in `PLAN-Inhaltsverzeichnisse.md`,
Abschnitt 4):

| Element | Bedeutung |
|---|---|
| Klasse `page-index__chapter--lehrer-only` / `page-index__page--lehrer-only` | zusätzlich zur bestehenden `page-index__chapter`/`page-index__page`-Klasse am `<li>` eines gesperrten Knotens, nur für angemeldete Lehrpersonen ausgegeben |
| Button `<button type="button" class="page-index__lehrer-toggle" aria-pressed="false">Lehrpersonen-Seiten anzeigen</button>` | erscheint unmittelbar vor der Liste (nach dem Such-Wrapper, falls vorhanden), nur wenn `simple_clean_ist_lehrperson()` wahr ist UND mindestens ein gesperrter Knoten im Baum steht (`simple_clean_render_page_index()`) |
| CSS | `.page-index:not(.page-index--zeige-lehreransicht) .page-index__chapter--lehrer-only, .page-index:not(.page-index--zeige-lehreransicht) .page-index__page--lehrer-only { display: none; }` (`src/css/page-index.css`) — keine explizite „display wiederherstellen"-Regel nötig, ohne die `:not()`-Bedingung gilt automatisch das normale Layout der jeweiligen Darstellung |
| JavaScript | Klick auf `.page-index__lehrer-toggle` togglet `page-index--zeige-lehreransicht` am `.page-index`-Wrapper, `aria-pressed` und den Button-Text (`richteEin()` in `src/js/page-index.js`, unabhängig vom Suchfeld-Frühausstieg platziert) |
| Berechnung der gesperrten IDs | `simple_clean_page_index_daten()` in `includes/page-index.php` — `simple_clean_gesperrte_seiten_mit_unterbaum()` (mit `function_exists()`-Rückfall auf `simple_clean_gesperrte_seiten()`), damit auch NUR geerbt gesperrte Nachfahren die Klasse tragen (Korrektur aus AP-2.fix1, siehe unten) |

**Der Toggle-Zustand wird bewusst NICHT gespeichert** — jeder Seitenaufruf
beginnt wieder in der Schüleransicht. Anders als der `localStorage`-Vertrag
der Klassenmodus-Inhaltsverzeichnisse im CDB-Plugin (Phase 1 desselben
Plans, siehe `Plugins/CDB-Designer/CLAUDE.md`, Abschnitt „Klassenmodus:
Klappbare Inhaltsverzeichnisse") ist Persistenz hier ausdrücklich kein Ziel.

**Bewusste Ausnahme von der „Grundregel für den Renderer".** Der
Kopfkommentar von `includes/page-index.php` hielt bislang fest, die Ausgabe
hänge „allein von den Blockattributen ab — nicht davon, welche Seite gerade
aufgerufen wird oder wer angemeldet ist". Seit diesem Vorhaben steht dort
ergänzend:

> AUSNAHME seit dem Vorhaben „Inhaltsverzeichnisse" (2026-08): Für angemeldete
> Lehrpersonen kennzeichnet der Renderer zusätzlich gesperrte Knoten
> (`_simple_clean_nur_lehrpersonen`) mit den Klassen
> `page-index__chapter--lehrer-only` / `page-index__page--lehrer-only` und
> gibt einen Button `page-index__lehrer-toggle` aus, damit sie sich ein- und
> ausblenden lassen … Für nicht angemeldete Besucher gilt die Grundregel
> unverändert: Sie erhalten gesperrte Knoten weiterhin gar nicht erst im HTML
> … Diese Ausschlussgrenze ist die Sicherheitsgrenze dieses Vorhabens und darf
> nicht aufgeweicht werden.

Der bestehende Ausschluss-Zweig für nicht angemeldete Besucher in
`simple_clean_page_index_daten()` (`!simple_clean_ist_lehrperson()`) wurde
dabei nicht verändert — die Kennzeichnung für Lehrpersonen ist ein rein
additiver, zweiter Zweig. Unabhängig geprüft in AP-2.rev (Byte-Vergleich
gegen den Ausgangszustand) und im dedizierten Sicherheits-Testpaket AP-2.3
(Inkognito-Vergleich, Direktaufruf, REST-Einzelabruf).

### Bekannte, bewusst akzeptierte Einschränkungen

Aus dem unabhängigen Review AP-2.rev (`PLAN-Inhaltsverzeichnisse.md`,
Abschnitt 7 — ein Befund mit Schweregrad „mittel", behoben in AP-2.fix1
(siehe unten), plus vier geringe, kein weiteres Korrektur-AP nötig):

1. **G1** (`includes/page-index.php`, `simple_clean_render_page_index()`,
   rund um Zeile 692) — der Toggle-Button erscheint seitenweit statt
   blockbezogen; zeigt eine `rootPage`-Ansicht ganz ohne markierte Knoten im
   sichtbaren Ausschnitt, ist er wirkungslos vorhanden.
2. **G2** (`src/js/page-index.js:73-82`, `zaehleSichtbareSeiten()`) — zählt
   versteckte `--lehrer-only`-Knoten mit; die Trefferanzeige der Suche
   überzählt dadurch für angemeldete Lehrpersonen.
3. **G3** (`src/js/page-index.js:95-96`) — die Button-Texte
   („Lehrpersonen-Seiten anzeigen" / „Nur Schüleransicht zeigen") sind
   deutsche JavaScript-Literale statt über PHP `esc_html__()` übersetzt — so
   im Markup-Vertrag oben vorgegeben, keine Abweichung.
4. **G4, vorbestehend, außerhalb des Scopes** (`includes/page-index.php`,
   `simple_clean_render_page_index()`, rund um Zeile 644) — zeigt `rootPage`
   auf eine für den aktuellen Betrachter ausgeschlossene Seite, fällt der
   Block auf die oberste Ebene zurück statt eine leere Liste zu zeigen;
   existierte bereits vor diesem Vorhaben.

**Korrigiert durch AP-2.fix1 (kein offener Befund mehr):** AP-2.rev fand
einen Befund mit Schweregrad „mittel" — die Lehrpersonen-Markierung lief
ursprünglich über `simple_clean_gesperrte_seiten()` (nur direkt gesperrte
Seiten), nicht über `simple_clean_gesperrte_seiten_mit_unterbaum()`. Dadurch
erschienen nur *geerbt* gesperrte Nachfahren (z. B. eine ungesperrte
Unterseite unter einer gesperrten Elternseite) fälschlich OHNE
`--lehrer-only`-Klasse und damit standardmäßig sichtbar — kein
Sicherheitsleck (Gäste erhielten sie ohnehin nie, da deren Ausschluss
weiterhin über `simple_clean_gesperrte_seiten()` läuft), aber eine
Zielverfehlung gegenüber „Lehrperson sieht standardmäßig dieselbe Ansicht wie
ein Gast". AP-2.fix1 hat die eine betroffene Zeile in
`simple_clean_page_index_daten()` auf `simple_clean_gesperrte_seiten_mit_unterbaum()`
umgestellt (mit `function_exists()`-Rückfall) — bereits oben in der
Vertrags-Tabelle als aktueller Stand dokumentiert.

Details je Befund und die vollständigen Übergabenotizen der Phase-2-APs:
`PLAN-Inhaltsverzeichnisse.md`, Abschnitt 7. Datei-Referenzen:
`reference_file_map.md`, Zeilen zu `includes/page-index.php`,
`src/css/page-index.css` und `src/js/page-index.js`.

### Kapitellinks (`PLAN-Summary-PDF-und-Content-Links.md`, Phase 3, seit v1.5.97)

Ein Redakteur kann im Editor markierten Text (oder die freie
Einfügeposition) in einen Link verwandeln, der beim Klick zur Seite mit dem
Inhaltsverzeichnis-Block springt und dort automatisch zur gewählten
Kapitelkarte scrollt und sie kurz hervorhebt. Vier Bausteine greifen
ineinander:

**1. Anker-ID-Schema (AP-3.1, `includes/page-index.php`,
`simple_clean_page_index_liste()`).** Jedes Kapitel-`<li>` — nur Ebene 0
relativ zu `rootPage`, also genau die Knoten, die auch die Kapitelkarten
sind — trägt zusätzlich `id="page-index-kapitel-<post_id>"`
(Architekturentscheidung A6: Post-ID statt Slug, weil sie Titel- und
Adressänderungen übersteht und kollisionsfrei ist):

```php
$id_attr = '';
if ($ist_kapitel) {
    $id_attr = ' id="' . esc_attr('page-index-kapitel-' . $node['id']) . '"';
}
$html .= '<li class="' . esc_attr($eintrag_klasse_knoten) . '"' . $id_attr . '>';
```

Rein additiv — Klassen, `<details>`-Struktur und die
`--lehrer-only`-Kennzeichnung sind unverändert. Die ID sitzt bewusst am
`<li>` (der eigentlichen Kapitel**karte** mit Rahmen/Hintergrund), nicht am
`<details>` darin: Das `<li>` existiert immer, das `<details>` nur bei
`collapsible === true` UND vorhandenen Unterseiten — Kapitel ohne
Unterseiten hätten am `<details>` gar keine Sprungmarke bekommen.
Unterseiten bekommen bewusst keine ID (Ziel ist die Kapitelkarte, nicht
jede Ebene). Ein als „nur für Lehrpersonen" gesperrtes Kapitel bekommt
seine ID nur, wenn der Betrachter angemeldet ist — für Gäste steht der
Knoten gar nicht im Baum, der Sprung bleibt für sie folgenlos, die Sperre
bleibt gewahrt.

**Bekannte, akzeptierte Einschränkung:** Mehrere `fos/inhaltsverzeichnis`-
Blöcke mit überlappenden Kapiteln auf **derselben** Seite erzeugen doppelte
IDs. `document.getElementById()` (Punkt 2) nimmt dann das erste Vorkommen
im Dokument — kein Blocker, der Sprung landet im richtigen Kapitel, nur
ggf. in der falschen Blockinstanz. Dieselbe Wahl trifft die Blocksuche in
Punkt 3 („der erste Block gewinnt").

**2. Hash-Scroll-Mechanismus (AP-3.2, `src/js/page-index.js`,
`behandleHashNavigation()`, plus `src/css/page-index.css`).** Beim Laden
der Seite (`start()`) und bei jedem `hashchange`-Ereignis (ein Kapitellink
auf die bereits geöffnete Seite ändert nur den Hash, ohne ein
`load`-Ereignis auszulösen):

1. `window.location.hash` gegen `/^#page-index-kapitel-\d+$/` prüfen — ein
   schemafremder Hash (`#kommentar-12`) führt gar nicht erst zu einer Suche.
2. Ziel per `document.getElementById(hash.slice(1))` suchen — bewusst
   NICHT `querySelector(hash)`: Das würde den Hash als Selektor auswerten
   und bei einem unerwarteten Wert eine Ausnahme werfen, während
   `getElementById` einen reinen String nimmt und schlicht `null` liefert.
3. Alle `<details>`-**Vorfahren** des Ziels öffnen — nicht das `<details>`
   der Kapitelkarte selbst (dessen Aufklappzustand bleibt Sache von
   `openByDefault` bzw. des Lesers; im heutigen Markup ohnehin nie
   erreicht, da Kapitel immer Ebene 0 sind und nie selbst in einem
   `<details>` liegen).
4. `ziel.scrollIntoView({behavior: 'smooth', block: 'start'})`. **Seit
   AP-2.1 (`PLAN-Summary-Punktesystem-Buttons-und-Kapitellink-
   Feinschliff.md`) nicht mehr so** — ersetzt durch eine manuelle
   `window.scrollTo()`-Berechnung auf ca. 20 % der Bildschirmhöhe, Details
   im Nachtrag „20-%-Scroll-Positionierung und neuer Tab" weiter unten.
5. Laufenden Hervorhebungs-Timer löschen, alle vorhandenen
   `--highlight`-Klassen im Dokument entfernen, per erzwungenem Reflow
   (`void ziel.offsetWidth`) sicherstellen, dass die Animation auch bei
   einem zweiten Sprung auf dieselbe Karte neu anläuft (sonst fasst der
   Browser Entfernen und Hinzufügen derselben Klasse innerhalb eines
   Frames zusammen und die Animation bliebe aus), dann
   `page-index__chapter--highlight` setzen und nach 2000 ms per
   `setTimeout` wieder entfernen.

Fehlt der Hash, passt er nicht zum Schema oder existiert kein passendes
Element: Die Funktion bricht still ab, kein Fehler.

**CSS-Feinheit — Endwert `var(--pidx-card-bg)` statt `transparent`
(Abweichung vom ursprünglichen Plan-Text):**

```css
.page-index__chapter--highlight {
    animation: page-index-highlight-pulse 2s ease-out;
}
@keyframes page-index-highlight-pulse {
    0% {
        background-color: var(--color-ui-surface-light, #f5ede9);
        border-color: var(--pidx-accent);
    }
    100% {
        background-color: var(--pidx-card-bg);
        border-color: var(--pidx-card-border);
    }
}
```

Die Kapitelkarte hat eine eigene Flächenfarbe
(`.page-index__chapter { background-color: var(--pidx-card-bg) }`). Ein
Auslaufen nach `transparent`, wie ursprünglich im Plan-Text vorgeschlagen,
ließe am Ende die Seitenfarbe durchscheinen und spränge beim Entfernen der
Klasse sichtbar auf die Kartenfarbe zurück — ein Blitzer statt eines
Ausklingens. Zusätzlich läuft der Rahmen über `--pidx-accent` mit, weil der
Flächenunterschied bei hellem Kartenhintergrund allein sehr zart ist. Beide
Werte kommen aus dem bestehenden `--pidx-*`-Vokabular, folgen damit dem
Customizer und ziehen im Darkmode automatisch mit (Pflicht-Konvention:
nur `var(--x, #fallback)`, nie ein freistehender Hexwert). Dazu ein
`@media (prefers-reduced-motion: reduce)`-Zweig, der die Hervorhebung als
ruhigen Zustand statt als Verlauf zeigt (Fläche/Rahmen sofort auf dem
Endwert, `animation: none`) — das Weichscrollen selbst regelt der Browser
bei dieser Einstellung von sich aus.

**3. REST-Endpunkte für die zweistufige Zielauswahl (AP-3.3,
`includes/kapitellink-api.php`, neu).** Zwei `GET`-Routen im Namespace
`simple-clean/v1` (dieselbe Konvention wie der bestehende
Glossar-Endpunkt), registriert auf `rest_api_init`:

| Route | Antwort | Zweck |
|---|---|---|
| `/inhaltsverzeichnis-seiten` | `[{id, title}]` | alle veröffentlichten Seiten mit einem `fos/inhaltsverzeichnis`-Block, ermittelt per `has_block()` (findet ihn auch verschachtelt, z. B. in einem Container-Block des CDB-Designers) |
| `/inhaltsverzeichnis-kapitel?seite=<id>` | `[{id, title}]` | die Kapitel dieses Blocks auf der gewählten Seite; leeres Array, wenn kein Block gefunden wird oder die Seite keine Kapitel zeigt (bewusst kein Fehlerstatus — für das Auswahlfeld im Editor ist „nichts zu wählen" die brauchbarere Antwort) |

Beide Routen teilen sich **einen** `permission_callback`
(`current_user_can('edit_posts')`), damit sie nicht auseinanderlaufen
können. Ausschließlich WordPress-APIs (`get_pages()`, `has_block()`,
`get_post()`, `parse_blocks()`, dazu die vorhandenen
`simple_clean_page_index_sanitize_attrs()` und
`simple_clean_page_index_daten()`) — **keine rohe `$wpdb`-Abfrage** in
dieser Datei (siehe „Falle: rohe SQL-Abfragen greifen die Filter nicht ab"
oben). **Präzisierung aus dem Review (Befund G3):**
`simple_clean_page_index_daten()` selbst arbeitet mit rohem `$wpdb`
(`includes/page-index.php`, Z. 163–176); die Kapitel-Route erreicht rohes
SQL also *mittelbar*. Unbedenklich, weil genau diese Funktion ihre eigene
Sichtbarkeitsprüfung mitbringt (siehe Sicherheitsabschnitt unten) — die
Aussage „keine rohen `$wpdb`-Abfragen" im Kopfkommentar der Datei gilt nur
für die neue Datei selbst, nicht für den gesamten Weg. Kein Zwischenspeicher
(kein Transient, keine Option) — wie beim übrigen
Inhaltsverzeichnis-Code.

`simple_clean_kapitellink_finde_block()` durchsucht `parse_blocks()`-Bäume
**rekursiv** (der Block kann in einem Container-Block stecken); „der erste"
Treffer in Dokumentreihenfolge gewinnt bei mehreren Verzeichnisblöcken auf
einer Seite — dieselbe Wahl trifft `getElementById()` bei doppelten
Anker-IDs (Punkt 1). Die Konstante `SIMPLE_CLEAN_PAGE_INDEX_BLOCK`
(`'fos/inhaltsverzeichnis'`) hält den Blocknamen an einer Stelle fest.

**Abweichung vom Plan-Text — `$daten['children'][$rootPage]` statt
`depth === 0`, mit Begründung.** Der ursprüngliche Plan-Text schlug vor,
„alle Knoten mit `depth === 0`" als Kapitel zurückzugeben. Das wäre falsch
gewesen: Das Feld `depth` in `simple_clean_page_index_daten()` zählt die
Tiefe **ab der Wurzel der gesamten Website**, nicht ab `rootPage`. Bei
einem Block mit `rootPage != 0` (auf dem Testserver der Regelfall, z. B.
`rootPage = 17` auf `/chemie/`) hätte der Plan-Vorschlag die obersten
Seiten der ganzen Website geliefert statt der Kapitel dieses Blocks.
Verwendet wird deshalb `$daten['children'][$wurzel]` — dieselbe Quelle, aus
der `simple_clean_render_page_index()` seine `$start_ids` zieht, inklusive
desselben Rückfalls auf Ebene 0, wenn `rootPage` auf einen im Baum nicht
(mehr) vorhandenen Knoten zeigt. Damit stimmt die Auswahlliste im Editor
zwangsläufig mit dem überein, was auf der Seite tatsächlich steht. Live
verifiziert: Ein Block mit `rootPage = 5613` lieferte über die neue Route
2 Kapitel — mit dem `depth === 0`-Vorschlag wären es die obersten Seiten
der Website gewesen.

**Sicherheit — Ergebnis der Review-Prüfung (Risiko R3, kein Leck):** Beide
Routen erben die Lehrpersonen-Sichtbarkeit **transitiv** von der
projektweit einzigen Definition `simple_clean_ist_lehrperson()`
(`includes/sichtbarkeit.php`), auf zwei unterschiedlichen, aber beide
tragfähigen Wegen:
- Route `/inhaltsverzeichnis-kapitel` über `simple_clean_page_index_daten()`,
  die selbst `simple_clean_ist_lehrperson()` befragt.
- Route `/inhaltsverzeichnis-seiten` über `get_pages()` → `WP_Query` →
  `pre_get_posts` → `simple_clean_query_ausschluss()`
  (`includes/sichtbarkeit.php`), das dank
  `simple_clean_gesperrte_ids_liste()` für Lehrpersonen leer bleibt und
  sonst greift.

Live mit einer gesperrten Testseite und vier Rollen nachgewiesen
(AP-3.rev): Anonym und Rollen ohne `edit_posts` erhalten HTTP 401/403 ohne
Datenkörper — kein gesperrter Titel sickert durch. Wird
`simple_clean_ist_lehrperson()` künftig verschärft (das Theme warnt weiter
oben, dass sie heute nur „angemeldet" bedeutet), ziehen **beide** Routen
automatisch mit, ohne dass diese Datei angefasst werden müsste.

**4. Editor-Werkzeug „Kapitellink einfügen" (AP-3.4,
`src/js/kapitellink-format.js`, neu; Enqueue in
`simple_clean_kapitellink_editor_assets()` in
`includes/kapitellink-api.php` — dort, nicht in `page-index.php`, weil das
Script ausschließlich die beiden Routen dieser Datei bedient und den Block
selbst nur als Datenquelle liest).** Markierten Text (oder die
Einfügeposition ohne Auswahl) über einen neuen Knopf „Kapitellink" in der
Inline-Werkzeugleiste (unter „Mehr", Dashicon `admin-links`) in einen Link
verwandeln. Ein `Popover` (`@wordpress/components`) führt zweistufig: erst
`SelectControl` „Seite" (Optionen aus
`/simple-clean/v1/inhaltsverzeichnis-seiten`), nach Auswahl ein zweites
`SelectControl` „Kapitel" (Optionen aus
`/simple-clean/v1/inhaltsverzeichnis-kapitel?seite=<id>`). „Einfügen" holt
den Permalink über `/wp/v2/pages/<id>` (Feld `link` — **nicht** selbst
zusammengebaut, siehe die Warnung im Kopfkommentar von `page-index.php`
gegen eigene Adress-Konstruktion) und baut
`href = Permalink + '#page-index-kapitel-' + Kapitel-ID`.

**Abweichung vom Plan-Text — `className: 'fos-kapitellink-werkzeug'` statt
`null`, angewendet wird `core/link`, nicht das eigene Format.** Der
Plan-Text schlug ein RichText-Format mit `tagName: 'a'` und
`className: null` vor, das per `applyFormat()` direkt angewendet wird.
Grund für die Abweichung: Ein Format mit `tagName: 'a'` UND
`className: null` beansprucht in Gutenberg das **nackte** `<a>`-Element
für sich — genau das, was der Kern-Formattyp `core/link` bereits tut.
`getFormatTypeForBareElement()` nimmt den **ersten** passenden Typ; zwei
Bewerber um dasselbe Element sind eine unnötige Fehlerquelle, und der
eingefügte Link ließe sich dann nicht mehr mit der gewohnten
Link-Oberfläche bearbeiten oder entfernen. Der registrierte Formattyp
`fos/kapitellink` ist deshalb reiner **Träger des Werkzeugknopfes** und
trägt die Klasse `fos-kapitellink-werkzeug`, die nie in einem Inhalt
vorkommt, weil das Format nie angewendet wird — angewendet wird
stattdessen `core/link` mit der berechneten URL. Das Ergebnis ist ein
gewöhnlicher `<a href>`-Link **ohne Klasse und ohne Inline-Style**, mit
der Standard-Link-Oberfläche weiter bearbeitbar, und folgt damit derselben
Linkfarbregel wie jeder andere Content-Link (`:where(.entry-content) a`,
siehe Abschnitt „Color Scheme" oben, Absatz „Content-Links folgen den
Themefarben"). Nachgemessen aus der Datenbank: beide erzeugten Test-Links
ohne `class`/`style`, berechnete `color` = `--color-special-text`,
unterstrichen — identisch zu jedem anderen Inhalts-Link.

Weitere Entscheidungen: Der Auswahlbereich wird beim Öffnen des Popovers in
`gemerkterWert` festgehalten (sobald der Fokus in die Auswahlfelder
wandert, ist auf den laufend hereingereichten `props.value` kein Verlass
mehr — ohne diese Sicherung ist der Fehler sporadisch und schwer zu
finden). Ohne Textauswahl wird der Kapiteltitel als neuer, bereits
verlinkter Text eingefügt. Die Seitenliste wird erst beim Öffnen des
Popovers geholt, nicht beim Laden des Editors — sonst liefe bei jeder
Bearbeitung eine Anfrage, die die meisten nie brauchen. Fehlerfälle
(Liste/Permalink nicht ladbar, keine passende Seite, Seite ohne Kapitel)
zeigen eine sichtbare Meldung im Popover statt eines stillen Abbruchs. Ein
„Kapitellink bearbeiten" mit vorbelegter Auswahl gibt es bewusst nicht —
der eingefügte Link ist danach ein ganz normaler Link, änderbar über den
Standard-Link-Dialog oder durch erneutes Aufrufen des Werkzeugs.

**Nachtrag: Fehlerdiagnose und Kapitel-Filter**
(`PLAN-Nachtraege-Summary-PDF-und-Kapitellinks.md`, Phase 2, seit v1.5.100).
Nach dem ersten Live-Einsatz durch den Betreiber meldete er zwei Punkte am
Kapitellink-Werkzeug — eine vorab durchgeführte Diagnose klärte, dass ein
gemeldeter Filtervorschlag auf der falschen Auswahlebene angesetzt hätte,
zwei Nachbesserungen wurden umgesetzt:

- **Diagnostizierbare Fehlermeldungen (AP-2.1, `src/js/kapitellink-format.js`).**
  Die beiden `catch`-Blöcke um die Seitenliste (`holeSeiten()`) und die
  Kapitelliste (`holeKapitel()`) nahmen zuvor kein Funktionsargument
  entgegen — der tatsächliche Fehlergrund ging verloren, die Oberfläche
  zeigte in jedem Fehlerfall dieselbe nicht diagnostizierbare
  Pauschalmeldung. Zwei neue Hilfsfunktionen: `istSitzungsFehler(fehler)`
  erkennt `fehler.code === 'rest_cookie_invalid_nonce'` oder
  `'rest_forbidden'`, ersatzweise HTTP 401/403 über `fehler.data.status`
  (regulärer `wp.apiFetch()`-REST-Fehler) oder `fehler.status` (roher
  Netzwerk-/Fetch-Fehler ohne `data`-Feld); `formatiereFehlermeldung(fehler,
  basisText)` liefert bei einer erkannten Sitzungs-/Berechtigungsfrage die
  feste Meldung „Die Sitzung ist abgelaufen. Bitte die Seite neu laden und
  erneut versuchen.", sonst `basisText + ' (Fehlercode: ' + (fehler.code ||
  fehler.message || 'unbekannt') + ').'`. Beide `catch`-Blöcke rufen
  zusätzlich `console.error(fehler)` auf, damit das vollständige
  Fehlerobjekt für die Fehlersuche über die Browser-Konsole erhalten
  bleibt. Der Erfolgsfall mit leerer Liste (z. B. „Diese Seite zeigt keine
  Kapitel an.") ist kein Fehlerfall und blieb unverändert.

- **Kapitel-Filter auf navigationsgesperrte Kapitel (AP-2.2,
  `includes/kapitellink-api.php`, `simple_clean_kapitellink_kapitel()`).**
  Ein Kapitellink ist nur für Kapitel sinnvoll, die über die normale
  Navigation NICHT erreichbar sind, weil sie „Für Navigation sperren" aktiv
  haben (Meta `_simple_clean_nav_gesperrt`) — genau dann rendert
  `page-index.php` die Kapitelkarte als `<span>` statt `<a>`. Für alle
  anderen Kapitel kann der Redakteur einfach einen gewöhnlichen Link
  setzen. Die Funktion ruft dafür **einmalig**, außerhalb der Schleife über
  die Kapitel, die bestehende `simple_clean_nav_gesperrte_seiten()`
  (`includes/page-index.php`) auf und nimmt nur Kapitel auf, deren ID per
  `isset()` in deren Ergebnis (`ID => true`) vorkommt — kein
  Datenbankzugriff je Kapitel.

  **Warum NICHT auf der Seiten-Liste gefiltert wird
  (`simple_clean_kapitellink_seiten()`, unverändert) — wichtig für
  künftige Bearbeiter.** Die Sperre sitzt nicht auf den Übersichtsseiten
  (erster Auswahlschritt des Werkzeugs), sondern eine Ebene tiefer, auf den
  einzelnen Kapiteln (zweiter Auswahlschritt). Ein Filter auf der
  Seiten-Liste hätte auf dem geprüften Datenbestand 0 Treffer geliefert:
  Bei keiner der sechs Seiten mit `fos/inhaltsverzeichnis`-Block war die
  Sperre je auf der Seite selbst gesetzt, sondern ausschließlich auf ihren
  Kindern. Ein Filter dort hätte das Werkzeug also insgesamt unbenutzbar
  gemacht, nicht nur in Randfällen (Architekturentscheidung B5,
  `PLAN-Nachtraege-Summary-PDF-und-Kapitellinks.md`, Abschnitt 4). Wer das
  später „vereinfachen" möchte, indem der Filter auf beide Auswahlschritte
  ausgeweitet wird, tappt in dieselbe Falle, die die ursprüngliche Anfrage
  des Betreibers auslöste.

  **Betriebshinweis (Befund G4 aus AP-2.rev).** Damit eine Seite im
  Kapitellink-Werkzeug überhaupt nutzbare Kapitel anbietet, müssen die
  betreffenden Unterseiten „Für Navigation sperren" aktiviert haben
  (Seitenmanager bzw. Meta-Box „Navigation, Verzeichnis & Zugriff"). Ist
  bei keinem Kapitel einer Seite die Sperre gesetzt, liefert die
  Kapitel-Route eine leere Liste und das Werkzeug zeigt „Diese Seite zeigt
  keine Kapitel an." — das ist kein Fehler, sondern das erwartete Verhalten
  des Filters. Auf dem Testserver betraf das (Stand 2026-09-08) zusätzlich
  zur bereits vorher leeren Seite 5614 auch Seite 5613 „Organische Chemie
  und Biochemie Neu": beide Kapitel dieser Seite existieren, sind aber
  keines davon navigationsgesperrt.

Details, Messwerte und die vollständigen Übergabenotizen:
`PLAN-Nachtraege-Summary-PDF-und-Kapitellinks.md`, Abschnitt 7 (AP-2.1,
AP-2.2, AP-2.rev).

**Bekannte Einschränkungen aus dem unabhängigen Review** (AP-3.rev,
`PLAN-Summary-PDF-und-Content-Links.md`, Abschnitt 7 — kein kritischer
Befund, Merge freigegeben, kein `AP-3.fix1` nötig):

- **M1 (mittel) — `edit_posts` ist für ein reines Seiten-Werkzeug die
  eigentlich falsche Berechtigung.** Beide REST-Routen und
  `simple_clean_kapitellink_editor_assets()` prüfen `edit_posts`; das
  Werkzeug bearbeitet aber ausschließlich **Seiten**. Die auf dieser
  Installation tatsächlich genutzten Rollen `administrator` und
  `block_redakteur` haben beide `edit_posts`, ein hypothetischer `editor`
  ohne diese Capability (aber mit `edit_pages`) sähe den Knopf
  „Kapitellink" dennoch — die Editor-Enqueue-Funktion hat keine eigene
  Berechtigungsprüfung und hängt sich pauschal an
  `enqueue_block_editor_assets` — bekäme beim Klick aber nur „Die
  Seitenliste konnte nicht geladen werden."; die eigentliche Ursache (403)
  ist von der Oberfläche aus nicht erkennbar. Der Befund geht auf eine
  wörtliche Vorgabe im Plan-Text zurück, kein Implementierungsfehler. Kein
  Blocker für den Merge (kein `editor`-Konto auf dieser Installation).
  **Kandidat für ein Folgevorhaben:** Beide Prüfungen auf `edit_pages`
  statt `edit_posts` umstellen.
- **G1 (gering) — die beiden Routen bilden ihre Titel unterschiedlich.**
  `/inhaltsverzeichnis-seiten` nutzt `get_the_title()` (durchläuft
  `the_title`-Filter, u. a. `wptexturize`), `/inhaltsverzeichnis-kapitel`
  reicht den rohen `post_title` aus `simple_clean_page_index_daten()`
  durch. Bei Sonderzeichen (`&`, Anführungszeichen, Auslassungspunkte)
  können die beiden Auswahlfelder deshalb leicht unterschiedlich
  beschriftet sein. Kein Sicherheitsproblem — React und
  `wp.richText.create()` behandeln den Wert als Text, nicht als Markup.
- **G2 (gering) — ein Kapitellink auf ein gesperrtes Kapitel bleibt für die
  Lehrperson sichtbar wirkungslos.** Die Kapitelliste bietet Redakteuren
  auch gesperrte Kapitel an (ein Redakteur soll auch auf ein
  Lösungskapitel verlinken können). Ruft eine Lehrperson den Link auf, ohne
  vorher den Lehrpersonen-Toggle einzuschalten, steht das Ziel zwar im DOM,
  ist aber `display: none` — der Sprung bleibt sichtbar folgenlos
  (`scrollY` bleibt 0). Kein Sicherheitsproblem (Gäste erhalten den Knoten
  ohnehin nicht). Im echten Seitenbestand bisher nicht aufgefallen, weil
  alle gesperrten Seiten tiefe Unterseiten sind, kein Kapitel auf Ebene 0.
- **G3 (gering) — Kopfkommentar-Formulierung „keine rohen `$wpdb`-Abfragen"
  zu kurz gegriffen.** Siehe die Präzisierung oben im REST-Abschnitt.
- **G4 (gering) — ein Verzeichnisblock in einem synchronisierten Muster
  (`core/block`) wird von beiden Routen übersehen.** `has_block()`
  durchsucht den serialisierten Inhalt nicht innerhalb eines
  `core/block`-Verweises, `parse_blocks()` liefert dafür leere
  `innerBlocks`. Theoretisch — im aktuellen Seitenbestand nicht
  eingetreten.

**Bekannte Einschränkungen — Nachtrag Phase 2** (aus dem unabhängigen
Review AP-2.rev, `PLAN-Nachtraege-Summary-PDF-und-Kapitellinks.md` —
kein kritischer, kein mittlerer Befund, Merge freigegeben, kein
`AP-2.fix1` nötig; die folgenden zwei Punkte sind offene Kandidaten für
einen möglichen Folgeplan, kein Blocker):

- **G1 (gering) — ein dritter, noch ungesicherter `catch`-Block.** Der
  `catch` um `holePermalink()` in `einfuegen()`
  (`src/js/kapitellink-format.js`) nimmt weiterhin kein Fehlerargument
  entgegen und ruft kein `console.error()` auf; die Meldung „Die Adresse
  der Zielseite konnte nicht ermittelt werden." bleibt undiagnostizierbar.
  Bewusst außerhalb des Scopes von AP-2.1 gelassen (der Plan-Text nannte
  dort ausdrücklich nur die beiden Listen-`catch`-Blöcke). Trifft real
  z. B. zu, wenn die Sitzung erst zwischen dem Öffnen des Popovers und dem
  Klick auf „Einfügen" abläuft. Die beiden Hilfsfunktionen
  `istSitzungsFehler()`/`formatiereFehlermeldung()` existieren bereits —
  eine Behebung wäre eine kleine, gezielte Änderung.
- **G2 (gering) — „Sitzung abgelaufen" ist bei einer reinen
  Berechtigungsfrage irreführend.** Jeder 401/403 führt zur Meldung „Die
  Sitzung ist abgelaufen. Bitte die Seite neu laden und erneut
  versuchen." — auch ein `rest_forbidden` OHNE abgelaufene Sitzung. Das
  ist plan-konform (der AP-2.1-Text schreibt genau diese Meldung für „eine
  Sitzungs-/Berechtigungsfrage" vor) und auf dieser Installation
  folgenlos, weil beide genutzten Rollen (`administrator`,
  `block_redakteur`) `edit_posts` besitzen. Hängt inhaltlich mit dem
  bereits oben dokumentierten Befund **M1** aus dem Vorgängerplan zusammen
  (Kapitellink-Routen und -Enqueue prüfen `edit_posts` statt des
  eigentlich passenderen `edit_pages`): Eine Rolle mit `edit_pages`, aber
  ohne `edit_posts`, sähe den Werkzeugknopf, bekäme beim Öffnen aber einen
  403 — und „Seite neu laden" wäre die falsche Handlungsanweisung, weil
  keine Sitzung abgelaufen ist, sondern die Berechtigung fehlt.
  `console.error` zeigt in jedem Fall den echten Fehlercode, Support kann
  also unterscheiden. **Kandidat für einen Folgeplan:** Wortlaut auf „Die
  Sitzung ist abgelaufen oder die Berechtigung fehlt…" erweitern oder 401
  und 403 getrennt behandeln — sinnvollerweise zusammen mit M1
  (`edit_pages` statt `edit_posts`).

Beide Punkte sind reine Beobachtungen ohne Sicherheitsrelevanz (kein
Datenleck, keine falsche Berechtigungsprüfung) und blockieren den Merge
nicht. Details und Nachweise:
`PLAN-Nachtraege-Summary-PDF-und-Kapitellinks.md`, AP-2.rev, Befunde
G1/G2.

**Nachtrag: 20-%-Scroll-Positionierung und neuer Tab**
(`PLAN-Summary-Punktesystem-Buttons-und-Kapitellink-Feinschliff.md`, Phase 2,
seit v1.5.105). Auf Betreiberwunsch zwei weitere Feinschliffe am
Kapitellink-Sprungziel, unabhängig review-geprüft (AP-2.rev — kein
kritischer Befund, Phase merge-fähig, kein `AP-2.fix1` nötig):

- **Sprungziel jetzt bei ca. 20 % der Bildschirmhöhe statt 0 % (AP-2.1,
  `src/js/page-index.js`, `behandleHashNavigation()`).** Schritt 4 im
  Ablauf oben (`ziel.scrollIntoView({behavior:'smooth', block:'start'})`)
  legte die Kapitelkarte auf 0 % der Bildschirmhöhe — dort verschwand sie
  teilweise unter der sticky Kopfleiste (`.site-header`,
  `position: sticky; top: 0; z-index: 1000`, 70 px hoch = 11,7 % von
  599 px Fensterhöhe im Testfall). Ersetzt durch eine manuelle Berechnung
  (Architekturentscheidung C5 des Plans — `scroll-margin-top` in Prozent
  wird von keinem Browser unterstützt, live gemessen:
  `CSS.supports('scroll-margin-top', '20%') === false`):

  ```js
  var y = ziel.getBoundingClientRect().top + window.scrollY - window.innerHeight * 0.2;
  window.scrollTo({ top: Math.max(0, y), behavior: 'smooth' });
  ```

  `Math.max(0, y)` verhindert einen negativen Scroll-Wert bei Kapiteln nahe
  dem Seitenanfang; bei einem Kapitel nahe dem Seitenende klemmt der
  Browser den Sollwert ohnehin automatisch auf die maximale
  Scroll-Position (live verifiziert, kein Sonderfall im Code nötig).

  **Zusätzlich nötig — 500-ms-Verzögerung nur beim initialen
  Seitenaufruf, mit gegenüber der ursprünglichen Übergabenotiz korrigierter
  Ursachenbeschreibung (Review-Befund G1).** Ohne Verzögerung landete die
  Karte beim ersten Aufruf einer Seite mit Hash in der URL trotz der neuen
  Berechnung wieder auf 0 % statt 20 %. Die ursprüngliche Übergabenotiz von
  AP-2.1 führte das auf „nachladende Bilder oberhalb des Ziels" zurück —
  das unabhängige Review (AP-2.rev) hat diese Beschreibung präzisiert: Die
  tatsächlich tragende Ursache ist, dass der Browser nach
  `DOMContentLoaded` selbst einen nativen, wegen
  `html { scroll-behavior: smooth }` (`src/css/glossar.css:339`) weichen
  Fragment-Sprung auf das `#`-Ziel auslöst — und dieser native Sprung
  konkurriert mit dem eigenen, kurz zuvor synchron berechneten
  `scrollTo()`-Aufruf um die zuletzt wirksame Scroll-Position. Welche
  Bewegung zuletzt „gewinnt", entscheidet reine Zeitfolge, nicht ein
  erneutes Layout-Verschieben durch nachladende Bilder — auf der geprüften
  Testseite trägt das einzige Bild oberhalb des Verzeichnisses ohnehin
  feste `width`/`height`-Maße. Behoben durch
  `window.setTimeout(behandleHashNavigation, 500)` **nur** im
  `start()`-Pfad (Vorbild `handleAutoScroll()` in `src/js/glossar.js:97`)
  — beim `hashchange`-Pfad (Klick auf einen Kapitellink zur bereits
  offenen Seite) tritt der native Sprung nicht auf, dort bleibt der Aufruf
  unverzögert.

- **Kapitellinks öffnen jetzt in einem neuen Tab (AP-2.2,
  `src/js/kapitellink-format.js`, Architekturentscheidung C6).** Das beim
  Einfügen angewendete `linkFormat`-Objekt
  (`{ type: 'core/link', attributes: { url, target, rel } }`) trägt seit
  dieser Ergänzung zusätzlich `target: '_blank', rel: 'noopener'` —
  dieselben Werte, die WordPress' eigener „In neuem Tab öffnen"-Schalter im
  Standard-Link-Werkzeug setzt (`createLinkFormat()` in
  `format-library.js`, vom Review am echten, installierten WordPress-Core
  nachgewiesen). Beide Attribute sind reguläre `core/link`-Attribute,
  werden von `wp_kses_post()` nicht entfernt (`$allowedposttags['a']`
  erlaubt beide) und bleiben über die Standard-Link-Oberfläche weiter
  bearbeitbar — kein Filter, kein clientseitiges Nachrüsten nötig. Rein
  additive Änderung am Neueinfüge-Pfad: bereits vorher eingefügte
  Kapitellinks bleiben unverändert ohne `target`/`rel`.

  **Zusammenspiel mit der 500-ms-Verzögerung, vom Review nachgemessen
  (korrigiert gegenüber der ursprünglichen AP-2.2-Übergabenotiz, Befund
  G5).** Ein `target="_blank"`-Link öffnet die Zielseite in einem neuen,
  zunächst häufig im Hintergrund liegenden Tab (`document.hidden ===
  true`). Die AP-2.2-Übergabenotiz hatte dazu ursprünglich vermerkt, ein
  nicht gefronteter Tab liefere `scrollY: 0` selbst nach einem manuellen
  Scroll-Aufruf — das war eine Eigenart der in dieser Sitzung verwendeten
  Automatisierungsumgebung, keine zutreffende Aussage über reales
  Browser-Verhalten, und wurde vom Review widerlegt: Ein Kapitel-Ziel in
  einem echten, nie gefronteten Hintergrund-Tab (`document.hidden ===
  true`) erreichte trotzdem zuverlässig **pct ≈ 19,97** — der
  500-ms-Timer und der weiche Scroll laufen also auch dann korrekt, wenn
  die Zielseite (wie bei einem echten neuen Tab typisch) zunächst im
  Hintergrund lädt.

**Bekannte Einschränkung — Kandidat für ein Folgevorhaben (Review-Befund
M1 + G2/G3/G4/G9, kein Merge-Blocker).** Die 500-ms-Verzögerung oben ist
eine reine Heuristik ohne Abschlussbedingung: Sie geht davon aus, dass der
native Fragment-Sprung des Browsers innerhalb von 500 ms abgeschlossen ist,
prüft das aber nicht (kein `load`-Nachschlag, kein Abgleich der erreichten
Position, kein Abbruch bei eigenem Scrollen des Lesers während der
Wartezeit). Auf dem Testserver funktioniert das durchgängig, weil oberhalb
des geprüften Inhaltsverzeichnisses nur ein einziges, fest bemaßtes Bild
liegt (kein Layoutsprung beim Nachladen) — auf einer Produktivseite mit
unbemaßten, `loading="lazy"`- oder spät ersetzten Bildern bzw. spät
geladenen Schriften oberhalb des Verzeichnisses ist ein Layoutsprung nach
500 ms plausibel und bisher **ungetestet**; im schlechtesten Fall landet
die Karte dann wieder auf 0 % — also auf dem Zustand vor AP-2.1, keine
Verschlechterung gegenüber dem vorherigen Stand.

Das Review hat dafür live eine robustere, im aktuellen Plan nicht geprüfte
Alternative gemessen (Architekturentscheidung C5 hatte nur
`scroll-margin-top` verworfen, das keine Prozentwerte akzeptiert — nicht
`scroll-padding-top`, das sie akzeptiert und gegen die Fensterhöhe
auflöst): Eine einzelne CSS-Zeile `scroll-padding-top: 20vh` (oder `20%`)
neben dem bestehenden `html { scroll-behavior: smooth }` in
`src/css/glossar.css` lieferte beim nativen Fragment-Sprung bereits
**19,96 %** (`CSS.supports('scroll-padding-top', '20%') === true`) — ohne
JavaScript-Timer, ohne die zweistufige Sprungbewegung (erst Richtung 0 %,
dann nach 500 ms die Korrektur auf 20 %, Befund G3), ohne das Risiko, einen
währenddessen selbst scrollenden Leser wegzureißen (Befund G4). Ein
möglicher Folgeplan sollte diese eine CSS-Zeile auf einer Produktivseite
mit unbemaßten Bildern verifizieren und, falls sie sich bestätigt, den
`setTimeout`-Mechanismus ersatzlos entfernen. Unberührt davon bleibt der
vorbestehende, nicht durch dieses Vorhaben verursachte Befund G9: Ein
explizites `behavior: 'smooth'` im `scrollTo()`-Aufruf schlägt
`prefers-reduced-motion: reduce` — derselbe Punkt bestand bereits beim
vorherigen `scrollIntoView({behavior:'smooth', …})` und wäre bei einer
künftigen Umstellung auf `scroll-padding-top` (das die native,
CSS-gesteuerte Bewegung nutzt und `prefers-reduced-motion` damit
automatisch respektieren würde) mit erledigt.

Details, Messwerte und die vollständigen Übergabenotizen:
`PLAN-Summary-Punktesystem-Buttons-und-Kapitellink-Feinschliff.md`,
Abschnitt 7 (AP-2.1, AP-2.2, AP-2.rev, AP-2.doc).

**Ankerschema, kein Bruch:** Alle vier Bausteine verwenden exakt dasselbe
Muster `page-index-kapitel-<post_id>` — geprüft per `grep` über `includes/`
und `src/` (AP-3.rev) und live durch Abgleich der von
`/inhaltsverzeichnis-kapitel` gelieferten IDs gegen die tatsächlich
gerenderten `id`-Attribute (deckungsgleich).

Details, Messwerte und die vollständigen Übergabenotizen der Phase-3-APs:
`PLAN-Summary-PDF-und-Content-Links.md`, Abschnitt 7. Datei-Referenzen:
`reference_file_map.md`, Zeilen zu `includes/kapitellink-api.php`,
`src/js/kapitellink-format.js`, `includes/page-index.php`,
`src/js/page-index.js` und `src/css/page-index.css`.

## Seiten nur für Lehrpersonen (seit v1.5.78)

Einzelne Seiten lassen sich sperren: Für nicht angemeldete Besucher
verschwinden sie aus Seitenleiste, Inhaltsverzeichnis, Menü, Suche, REST und
Sitemap; der direkte Aufruf endet mit **HTTP 403** auf einer Hinweisseite.
Gedacht für Lösungsseiten. Code in `includes/sichtbarkeit.php`, Plan und
Analyse in `docs/PLAN-Lehrerseiten.md` bzw.
`docs/ERWEITERUNGSANALYSE-Lehrerseiten.md`.

**Gesetzt wird das Meta `_simple_clean_nur_lehrpersonen`** (String `'1'`, sonst
gelöscht) an zwei Stellen: dem dritten Häkchen der Meta-Box „Navigation,
Verzeichnis & Zugriff" (`functions.php`) und den Sammelaktionen
`lock_teacher` / `unlock_teacher` im Seitenmanager.

### Die fünf Funktionen

| Funktion | Aufgabe |
|---|---|
| `simple_clean_ist_lehrperson()` | **Die einzige Definition von „Lehrperson".** Filter `simple_clean_ist_lehrperson` |
| `simple_clean_gesperrte_seiten()` | IDs mit gesetztem Meta, `array(ID => true)`, eine Abfrage, statisch gehalten |
| `simple_clean_gesperrte_seiten_mit_unterbaum()` | dieselben plus **alle Nachfahren**. Ohne gesperrte Seite: kein Baumaufbau, keine zweite Abfrage |
| `simple_clean_seite_nur_lehrpersonen($id)` | Seite selbst oder ein Vorfahre gesperrt? |
| `simple_clean_seite_sichtbar($id)` | die Gesamtentscheidung inkl. Freigabe-Filter |

Dazu `simple_clean_sichtbarkeit_cache_leeren()` — verwirft die statisch
gehaltenen Listen (Tests, WP-CLI, Importe).

### „Lehrperson" heißt derzeit nur „angemeldet" — Warnung

Das trägt, solange es ausschließlich Lehrer-Konten gibt; Schülerinnen und
Schüler melden sich nie an, sie kommen über das Klassenpasswort des
CDB-Plugins. **Sobald ein Konto ohne Lehrauftrag existiert** — ein Abonnent,
ein Testkonto, ein späterer Schülerzugang —, öffnet sich die Sperre still.
Verschärft wird an **einer** Stelle: `simple_clean_ist_lehrperson()` bzw. dem
gleichnamigen Filter, etwa auf `current_user_can('cbd_edit_blocks')`. Alle
übrigen Fundstellen fragen nur diese Funktion.

### Die Sperre vererbt sich auf den Unterbaum

Wie `_simple_clean_hide_from_index`. **In den Baumdarstellungen kommt das
gratis:** Seitenleiste und Inhaltsverzeichnis laufen von der Wurzel abwärts,
ein entfernter Knoten nimmt seine Nachfahren mit. Dort genügt
`simple_clean_gesperrte_seiten()`.

**In flachen Listen nicht** — Menü, Suche, REST, Sitemap. Eine Unterseite steht
dort für sich; deshalb `simple_clean_gesperrte_seiten_mit_unterbaum()`.

**Ausnahme seit `PLAN-Inhaltsverzeichnisse.md`, Phase 2 (2026-08-26):** Der
Block `fos/inhaltsverzeichnis` zeigt einer angemeldeten Lehrperson gesperrte
Seiten nicht mehr automatisch — standardmäßig sieht sie dieselbe Ansicht wie
ein nicht angemeldeter Besucher, ein Button blendet gesperrte Seiten (samt
Unterbaum) erst nach Klick zusätzlich ein. Der serverseitige Ausschluss für
nicht angemeldete Besucher (oben) ist davon unberührt. Details: Abschnitt
„Inhaltsverzeichnis-Block (`fos/inhaltsverzeichnis`)", Unterabschnitt
„Lehrpersonen-Toggle".

### Reihenfolge auf `template_redirect` — die zählt

| Priorität | Funktion |
|---|---|
| 1 | `simple_clean_block_ai_user_agents()` |
| 10 | `simple_clean_password_protection_check()` |
| **20** | `simple_clean_lehrerseite_pruefen()` |

Die Lehrersperre kommt zuletzt. Sonst käme ein Besucher, der das
Website-Passwort nicht kennt, über die Hinweisseite an der Passwortabfrage
vorbei — und wüsste, dass es die Seite gibt.

### Zwei Dinge, die nicht verändert werden dürfen

**Der Filter `simple_clean_lehrerseite_freigeben` hat den Standardwert
`false`.** Er ist die Naht, an der sich das CDB-Plugin einhängt, um gesperrte
Seiten in der Klassenansicht freizugeben. Fehlt das Plugin oder greift der
Filter nicht, bleibt die Seite gesperrt — ein Fehler in der Naht zeigt zu
wenig, nie zu viel.

**Kein persistenter Zwischenspeicher für die Seitenbäume** (Transient,
Option). Er würde Titel gesperrter Seiten an Nichtberechtigte ausliefern,
sobald ein Aufruf einer Lehrperson ihn füllt. Die statischen Variablen gelten
nur für die Dauer eines Aufrufs und sind unbedenklich. (Gegen einen
Zwischenspeicher für den Seitenindex sprach ohnehin schon eine Messung, siehe
Abschnitt „Inhaltsverzeichnis-Block".)

### Falle: rohe SQL-Abfragen greifen die Filter nicht ab

Die Ausblend-Filter hängen an WordPress-APIs (`pre_get_posts`,
`wp_get_nav_menu_items`, `rest_page_query`, …). **Wo das Theme mit rohem
`$wpdb` arbeitet, wirkt keiner davon.** Genau daran hing ein Leck:
`simple_clean_get_term_usage()` speist die Liste „Dieser Begriff wird verwendet
in:" auf jeder Glossarseite — mit dem **Titel** der Fundstelle. Eine gesperrte
Lösungsseite stand damit namentlich im Netz, obwohl die Hinweisseite ihren
Titel verbirgt. Die Funktion filtert jetzt selbst.

**Wer eine neue Stelle baut, die Seiten auflistet oder verlinkt, muss prüfen,
ob sie über eine WordPress-API läuft.** Wenn nicht: `simple_clean_seite_sichtbar()`
bzw. `simple_clean_gesperrte_seiten_mit_unterbaum()` von Hand einsetzen. Die
vollständige Liste der geprüften Fundstellen steht in
`docs/PLAN-Lehrerseiten.md`, AP-1.rev.

### REST: Sammlung UND Einzelabruf

`rest_page_query` filtert nur Sammlungen. Der Abruf einer einzelnen Seite
(`/wp-json/wp/v2/pages/<id>`) geht daran vorbei und lieferte sonst Titel und
vollständigen Inhalt an jeden — die Sperre wäre mit einer URL auszuhebeln.
Dafür gibt es zusätzlich einen Filter auf `rest_pre_dispatch`.

Angemeldete sind nicht betroffen; nachgewiesen mit `X-WP-Nonce`. **Beim
Prüfen daran denken:** Cookie-Anmeldung allein genügt der REST-Schnittstelle
nicht, ohne Nonce gilt die Anfrage als anonym.

### `pre_get_posts` gilt für alle Abfragen, nicht nur die Hauptabfrage

REST und der Suchendpunkt bauen eigene Abfragen. **Ausgenommen sind
`is_singular()`-Abfragen** — sonst fände die Abfrage die gesperrte Seite nicht
mehr, und statt der Hinweisseite mit 403 käme ein gewöhnliches 404 ohne
Erklärung und ohne Anmelde-Link. Ebenfalls ausgenommen: Abfragen fremder
Inhaltstypen.

### Prüfharnisch

`php tools/test-sichtbarkeit.php` — 17 Prüfungen ohne WordPress, mit Stubs.
Der `$wpdb`-Doppel zählt Abfragen mit, damit nachweisbar bleibt, dass ohne
gesperrte Seiten keine zweite Abfrage läuft. **`tools/` ist von
`create-theme-zip.js` ausgeschlossen** — nötig, weil die Einschlussregel
`filePath.match(/\.php$/)` jede PHP-Datei in jedem Unterverzeichnis trifft.

Kosten: **+1 Datenbankabfrage** je Aufruf für nicht angemeldete Besucher, auch
wenn nichts gesperrt ist. Für Angemeldete keine.
**Zum Nachmessen:** nicht einfach „als Administrator" — dort wird der Pfad
übersprungen. Ein mu-Plugin mit
`add_filter('simple_clean_ist_lehrperson', '__return_false');` erzwingt die
Besuchersicht, während `?sc_perf=1` weiter ausgibt.

## Klassenansicht (kommt aus dem CDB-Plugin)

**Sie greift tief ins Theme, steht aber nicht in dessen Code.** Wer nur diese
Datei liest, hält Kopfleiste und Seitenleiste für unangetastet — sie sind es
im Klassenmodus nicht.

Das Plugin „Container Block Designer" bringt ein Klassensystem mit: Schüler
melden sich über den Shortcode `[cbd_classroom]` mit einem Klassenpasswort an
und rufen danach normale Seiten mit `?classroom=<id>&token=<token>` auf. In
diesem Modus tut `assets/js/classroom-page-filter.js` im Browser Folgendes:

- blendet `.site-header` aus und setzt **eine eigene Kopfleiste** davor
  (`#cbd-classroom-nav-header`),
- **ersetzt den Inhalt von `#sidebar`** durch die Klassen-Navigation — dabei
  werden die Theme-Klassen `page-tree`, `page-item`, `page-link` usw.
  wiederverwendet, damit die Gestaltung passt,
- hängt an jeden internen Link die Klassenparameter an,
- versteckt Container-Blöcke, die für die Klasse nicht als „behandelt"
  markiert sind.

**Folgen für Arbeiten am Theme:** Wer diese CSS-Klassen oder die Struktur von
`#sidebar` umbenennt, bricht die Klassenansicht — der Fehler zeigt sich nur im
Klassenmodus und fällt beim normalen Testen nicht auf. Wer am Aufbau der
Seitenleiste arbeitet, sollte einmal mit Klassenparametern gegenprüfen.

Auf Seiten, die **nur für Lehrpersonen** sichtbar sind (Abschnitt oben),
filtert das Plugin zusätzlich **serverseitig** — dort steht nur noch im HTML,
was freigegeben ist. Verbunden sind beide Seiten über den Filter
`simple_clean_lehrerseite_freigeben`.

Details: `Plugins/CDB-Designer/CLAUDE.md`, Abschnitt „Klassen-Durchlass für
gesperrte Seiten".

## Diagnose: Wo geht die Zeit hin?

Auf einem Shared Hosting ohne SSH und ohne WP-CLI steht kein Profiler zur
Verfügung. Das Theme bringt deshalb eine kleine eigene Messausgabe mit —
`simple_clean_perf_footer()`, eingehängt auf `wp_footer` mit Priorität 9999.

**Aufruf:** beliebige URL mit `?sc_perf=1` als angemeldeter Administrator.
Im Seitenquelltext stehen dann zwei Zeilen:

```
<!-- SC-PERF queries=42 time=0.058s peak=52428800 -->
<!-- SC-GLOSSAR aufrufe=1 kandidaten=0 fallback=0 begriffe=0 zeit=0.000s -->
```

| Wert | Bedeutung |
|---|---|
| `queries` | Datenbankabfragen des Seitenaufbaus |
| `time` | Sekunden seit `$timestart` |
| `peak` | Spitzenspeicher in Bytes |
| `kandidaten` | Einträge in `_glossar_term_candidates` (`-1` = kein Array) |
| `fallback` | wie oft die Seite in diesem Aufruf zum ersten Mal gescannt werden musste (seit dem Fix des Datenverlust-Funds oben kein Rückfall auf ALLE Begriffe mehr, sondern ein einmaliger, persistierter Nachhol-Scan) |
| `begriffe` | Begriffe, mit denen tatsächlich gearbeitet wurde |
| `zeit` | Sekunden allein in `simple_clean_process_glossar_links_optimized()` |

Die zweite Zeile trennt die häufigste Ursache von allen anderen: Ist `zeit`
groß oder `fallback` größer als 0, liegt es am Glossar. Ist beides klein und
die Seite trotzdem langsam, liegt es woanders.

**Doppelt abgesichert:** Ohne die Berechtigung `manage_options` **und** ohne
den ausdrücklichen Parameter wird nichts ausgegeben. Für nicht angemeldete
Besucher ist die Ausgabe unsichtbar.

**Zahlen unterwegs:** Die Werte werden bewusst mit
`number_format($wert, 3, '.', '')` und als reine Bytezahl ausgegeben, **nicht**
über `timer_stop()` und `size_format()`. Beide formatieren über
`number_format_i18n()` und liefern in einer deutschen Installation ein Komma
als Dezimaltrennzeichen (`time=1,873s`) — für Menschen richtig, für
maschinelles Auswerten unbrauchbar. Das hat schon einmal eine halbe Stunde
Fehlersuche gekostet, weil das Auswerteskript die Zeile nicht fand, obwohl sie
dastand.

**Auswertung:** `docs/messung.js` in die Browser-Konsole einfügen (als
Administrator, auf der Website). Das Skript prüft zuerst die Voraussetzungen
und nennt die Ursache, wenn etwas fehlt; danach misst es alle Seiten mit dem
Block „Seitenliste" plus zwei Vergleichsseiten, je drei Aufrufe, und gibt einen
fertigen Textblock aus. Es fällt **nicht** ins Verteilungs-ZIP (`docs/` ist in
`create-theme-zip.js` nicht freigegeben).

## Additional Documentation

- **Installation guide for users:** `readme.md`
- **GitHub repository:** https://github.com/Cyric25/FOS_Skripten_Website_Design
- **Main project documentation:** `../CLAUDE.md` (parent directory)
- **Plugin documentation:**
  - CDB-Designer: `../Plugins/CDB-Designer/CLAUDE.md`
  - Eigene WP Blocks: `../Plugins/Eigene WP Blocks/CLAUDE.md`

## Support & Contribution

This theme is part of the FOS Skripten educational website project.

**For issues or questions:**
1. Check this documentation first
2. Review `readme.md` for installation/setup issues
3. Check Git commit history for recent changes
4. Test in clean WordPress installation to isolate issues

**When reporting issues, include:**
- WordPress version
- PHP version
- Browser and version
- Console errors (if applicable)
- Steps to reproduce
