# Simple Clean Theme - Documentation

This file provides guidance to Claude Code when working with this WordPress theme.

## Theme Overview

**Name:** Simple Clean Theme
**Version:** 1.0
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

### Color Scheme

**Primary Colors:**
- Accent/Links: `#0073aa` (WordPress blue)
- Text: `#333` (near black)
- Background: `#fff` (white)
- Light background: `#f8f9fa`
- Muted text: `#666`
- Borders: `#eee`, `#ddd`

**To customize colors:** Search and replace hex values in `style.css` or add CSS variables.

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

**Method 1: Direct replacement** (quick)
```bash
# Search and replace in styles.css
#0073aa → #your-color  (accent color)
#333 → #your-dark       (text color)
```

**Method 2: CSS Variables** (recommended)
```css
/* Add to top of style.css after theme header */
:root {
    --color-primary: #0073aa;
    --color-text: #333;
    --color-background: #fff;
    --color-border: #eee;
}

/* Then replace hardcoded colors with var(--color-primary) etc. */
```

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

### Duplicate Menu Toggle Code

**Issue:** Menu toggle is implemented twice:
1. Inline script in `header.php:45-50` (basic)
2. Bundled script in `src/js/main.js` (enhanced)

**Impact:** Both work, but inline version loads first and lacks accessibility features.

**Recommendation:** Remove inline script from `header.php` and rely on bundled version.

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
