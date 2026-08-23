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
sichtbar; dort ist der Streifen wieder eine kompakte runde Pille unten links.

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

**Vorgemerkt für ein künftiges Darkmode-Vorhaben** (Befund aus AP-1.rev,
Schweregrad gering, nicht blockierend): `--color-background` wird an fünf
Stellen zweckentfremdet als **Textfarbe** statt als Flächenhintergrund
verwendet — `style.css` Z. 912 sowie `glossar.css` Z. 91, 416, 678, 725.
Wertlich korrekt und aktuell optisch unauffällig, aber bei einem künftigen
Darkmode mit dunklem `--color-background` würden diese Textstellen ungewollt
mitkippen. Kein Fehler von AP-1.3/1.4 — im AP-1.1-Vokabular gab es keine
passendere Variable dafür; vor einer echten Darkmode-Umsetzung an diesen
fünf Stellen prüfen, ob eine eigene semantische Variable nötig ist.

**To customize colors:** WordPress Admin → Design → Customizer →
„Farbeinstellungen" (Details Root-`CLAUDE.md`). Für die acht
Ergänzungsvariablen oben: Wert an beiden Stellen ändern —
`style.css` `:root` **und** `simple_clean_customizer_css()` in
`functions.php` —, sonst driften sie auseinander.

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

4. **Dark Mode**
   - CSS variables for theming
   - JavaScript toggle with localStorage
   - Respect system preference

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
- **Einstellungen:** Optionen `glossar_modal_type` (tooltip|sidebar),
  `glossar_auto_link`, `glossar_first_only`, `glossar_case_sensitive`,
  `glossar_auto_rebuild`; Admin-Seite `simple_clean_glossar_settings_page()`
  (Untermenü des Glossar-CPT) mit CSV-Import/-Export und Bulk-Scan
  (AJAX `glossar_bulk_scan` / `glossar_bulk_scan_batch`).
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
  (Nonce `page_manager_nonce`, wie die vier bestehenden). Acht Aktionen als
  **Whitelist** in `bulk_aktionen()`: `status_publish`, `status_draft`,
  `set_parent`, `hide_index`, `show_index`, `hide_nav`, `show_nav`, `trash`.
  Der Wert aus `$_POST` wird nur gegen diese Liste geprüft und nie in einen
  Methodennamen übersetzt.

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
| `fallback` | wie oft auf **alle** Glossarbegriffe zurückgefallen wurde |
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
