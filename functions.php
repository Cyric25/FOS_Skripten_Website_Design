<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Theme Setup
function simple_clean_theme_setup() {
    // Theme Support
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));

    // Navigation Menus
    register_nav_menus(array(
        'primary' => __('Hauptmenü', 'simple-clean-theme'),
    ));
}
add_action('after_setup_theme', 'simple_clean_theme_setup');

// Enqueue Styles and Scripts
function simple_clean_theme_assets() {
    // Enqueue main stylesheet
    wp_enqueue_style('simple-clean-style', get_stylesheet_uri(), array(), '1.0');

    // Enqueue main JavaScript (from Vite build)
    $js_file = get_template_directory() . '/dist/js/main.js';
    if (file_exists($js_file)) {
        wp_enqueue_script(
            'simple-clean-script',
            get_template_directory_uri() . '/dist/js/main.js',
            array(),
            filemtime($js_file),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'simple_clean_theme_assets');

/**
 * Theme Customizer - Color Settings
 */
function simple_clean_customize_register($wp_customize) {
    // Add Color Settings Section
    $wp_customize->add_section('simple_clean_colors', array(
        'title'    => __('Farbeinstellungen', 'simple-clean-theme'),
        'priority' => 30,
    ));

    // Special Text Color (Dark reddish-brown for emphasis)
    $wp_customize->add_setting('color_special_text', array(
        'default'           => '#71230a',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'color_special_text', array(
        'label'    => __('Spezialtext-Farbe', 'simple-clean-theme'),
        'description' => __('Dunkelrotbraun für besonderen Text und Hervorhebungen', 'simple-clean-theme'),
        'section'  => 'simple_clean_colors',
        'settings' => 'color_special_text',
    )));

    // UI Surface Color (Orange for buttons, highlights)
    $wp_customize->add_setting('color_ui_surface', array(
        'default'           => '#e24614',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'color_ui_surface', array(
        'label'    => __('UI-Oberflächen-Farbe', 'simple-clean-theme'),
        'description' => __('Orange für Buttons, aktive Elemente, Sidebar-Toggle', 'simple-clean-theme'),
        'section'  => 'simple_clean_colors',
        'settings' => 'color_ui_surface',
    )));

    // UI Surface Dark Color (Darker orange for hover states)
    $wp_customize->add_setting('color_ui_surface_dark', array(
        'default'           => '#c93d12',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'color_ui_surface_dark', array(
        'label'    => __('UI-Oberflächen-Farbe (Dunkel)', 'simple-clean-theme'),
        'description' => __('Dunkleres Orange für Hover-Zustände', 'simple-clean-theme'),
        'section'  => 'simple_clean_colors',
        'settings' => 'color_ui_surface_dark',
    )));

    // UI Surface Light Color (Light orange for backgrounds)
    $wp_customize->add_setting('color_ui_surface_light', array(
        'default'           => '#f5ede9',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'color_ui_surface_light', array(
        'label'    => __('UI-Oberflächen-Farbe (Hell)', 'simple-clean-theme'),
        'description' => __('Helles Orange für Hintergründe und Sidebar', 'simple-clean-theme'),
        'section'  => 'simple_clean_colors',
        'settings' => 'color_ui_surface_light',
    )));

    // Sidebar Border Color
    $wp_customize->add_setting('color_sidebar_border', array(
        'default'           => '#e0e0e0',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'color_sidebar_border', array(
        'label'    => __('Sidebar-Rahmen-Farbe', 'simple-clean-theme'),
        'description' => __('Farbe für Sidebar-Rahmen und Trennlinien', 'simple-clean-theme'),
        'section'  => 'simple_clean_colors',
        'settings' => 'color_sidebar_border',
    )));

    // Primary Text Color
    $wp_customize->add_setting('color_text_primary', array(
        'default'           => '#333333',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'color_text_primary', array(
        'label'    => __('Primäre Textfarbe', 'simple-clean-theme'),
        'description' => __('Hauptfarbe für Text im Theme', 'simple-clean-theme'),
        'section'  => 'simple_clean_colors',
        'settings' => 'color_text_primary',
    )));

    // Background Color
    $wp_customize->add_setting('color_background', array(
        'default'           => '#ffffff',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'color_background', array(
        'label'    => __('Hintergrundfarbe', 'simple-clean-theme'),
        'description' => __('Haupthintergrundfarbe des Themes', 'simple-clean-theme'),
        'section'  => 'simple_clean_colors',
        'settings' => 'color_background',
    )));

    // Light Background Color (for subtle backgrounds)
    $wp_customize->add_setting('color_background_light', array(
        'default'           => '#f8f9fa',
        'sanitize_callback' => 'sanitize_hex_color',
        'transport'         => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'color_background_light', array(
        'label'    => __('Heller Hintergrund', 'simple-clean-theme'),
        'description' => __('Helle Hintergrundfarbe für Bereiche wie Footer', 'simple-clean-theme'),
        'section'  => 'simple_clean_colors',
        'settings' => 'color_background_light',
    )));
}
add_action('customize_register', 'simple_clean_customize_register');

/**
 * Generate Custom CSS from Customizer Settings
 */
function simple_clean_customizer_css() {
    // Get color values from customizer
    $color_special_text = get_theme_mod('color_special_text', '#71230a');
    $color_ui_surface = get_theme_mod('color_ui_surface', '#e24614');
    $color_ui_surface_dark = get_theme_mod('color_ui_surface_dark', '#c93d12');
    $color_ui_surface_light = get_theme_mod('color_ui_surface_light', '#f5ede9');
    $color_sidebar_border = get_theme_mod('color_sidebar_border', '#e0e0e0');
    $color_text_primary = get_theme_mod('color_text_primary', '#333333');
    $color_background = get_theme_mod('color_background', '#ffffff');
    $color_background_light = get_theme_mod('color_background_light', '#f8f9fa');

    // Calculate RGB values for rgba() usage
    $ui_surface_rgb = simple_clean_hex_to_rgb($color_ui_surface);

    // Generate custom CSS
    $css = "
    <style type='text/css'>
        :root {
            --color-special-text: {$color_special_text};
            --color-ui-surface: {$color_ui_surface};
            --color-ui-surface-dark: {$color_ui_surface_dark};
            --color-ui-surface-light: {$color_ui_surface_light};
            --color-sidebar-bg: {$color_ui_surface_light};
            --color-sidebar-border: {$color_sidebar_border};
            --color-text-primary: {$color_text_primary};
            --color-background: {$color_background};
            --color-background-light: {$color_background_light};
        }

        /* Apply colors to elements */
        body {
            color: {$color_text_primary};
            background-color: {$color_background};
        }

        /* Sidebar toggle button shadow with dynamic color */
        .sidebar-toggle-btn {
            box-shadow: 0 4px 12px rgba({$ui_surface_rgb}, 0.3);
        }

        .sidebar-toggle-btn:hover {
            box-shadow: 0 2px 8px rgba({$ui_surface_rgb}, 0.4);
        }

        /* Footer */
        .site-footer {
            background-color: {$color_background_light};
        }
    </style>
    ";

    echo $css;
}
add_action('wp_head', 'simple_clean_customizer_css');

/**
 * Helper function to convert hex color to RGB
 */
function simple_clean_hex_to_rgb($hex) {
    // Remove # if present
    $hex = ltrim($hex, '#');

    // Convert to RGB
    if (strlen($hex) == 6) {
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "$r, $g, $b";
    }

    // Default to black if invalid
    return "0, 0, 0";
}

// Custom Excerpt Length
function simple_clean_excerpt_length($length) {
    return 30;
}
add_filter('excerpt_length', 'simple_clean_excerpt_length');

// Custom Excerpt More
function simple_clean_excerpt_more($more) {
    global $post;
    return '... <a href="' . get_permalink($post->ID) . '">' . __('Weiterlesen', 'simple-clean-theme') . '</a>';
}
add_filter('excerpt_more', 'simple_clean_excerpt_more');

// Add custom body classes
function simple_clean_body_classes($classes) {
    $classes[] = 'simple-clean-theme';
    return $classes;
}
add_filter('body_class', 'simple_clean_body_classes');

// ===================================================================
// CUSTOM FIELD: HIDE NAVIGATION
// ===================================================================

// Add meta box for hiding sidebar navigation
function simple_clean_add_navigation_meta_box() {
    add_meta_box(
        'simple_clean_hide_navigation',
        'Seitenleiste (Sidebar) Einstellungen',
        'simple_clean_navigation_meta_box_callback',
        'page',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'simple_clean_add_navigation_meta_box');

// Meta box callback
function simple_clean_navigation_meta_box_callback($post) {
    // Add nonce for security
    wp_nonce_field('simple_clean_save_navigation_meta', 'simple_clean_navigation_nonce');

    // Get current value
    $hide_navigation = get_post_meta($post->ID, '_simple_clean_hide_navigation', true);

    ?>
    <div style="padding: 10px; background: #f0f7fb; border-left: 4px solid #0073aa; margin-bottom: 10px;">
        <label for="simple_clean_hide_navigation" style="display: block; margin-bottom: 8px;">
            <input type="checkbox"
                   id="simple_clean_hide_navigation"
                   name="simple_clean_hide_navigation"
                   value="1"
                   <?php checked($hide_navigation, '1'); ?>
                   style="margin-right: 5px;">
            <strong>Seitenleiste am linken Rand ausblenden</strong>
        </label>
        <p class="description" style="margin: 5px 0 0 0; color: #666;">
            ✓ Aktiviert = Keine Sidebar (hierarchische Navigation) am linken Rand<br>
            ✗ Deaktiviert = Sidebar wird angezeigt<br>
            <strong>Hinweis:</strong> Der Header oben bleibt immer sichtbar!
        </p>
    </div>

    <?php if ($hide_navigation === '1'): ?>
        <div style="padding: 8px; background: #d4edda; border-left: 4px solid #28a745; color: #155724;">
            ✅ <strong>Status:</strong> Sidebar ist für diese Seite ausgeblendet
        </div>
    <?php else: ?>
        <div style="padding: 8px; background: #fff3cd; border-left: 4px solid #ffc107; color: #856404;">
            ℹ️ <strong>Status:</strong> Sidebar wird auf dieser Seite angezeigt
        </div>
    <?php endif; ?>
    <?php
}

// Save meta box data
function simple_clean_save_navigation_meta($post_id) {
    // Verify nonce
    if (!isset($_POST['simple_clean_navigation_nonce']) ||
        !wp_verify_nonce($_POST['simple_clean_navigation_nonce'], 'simple_clean_save_navigation_meta')) {
        return;
    }

    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Only process for pages
    if (get_post_type($post_id) !== 'page') {
        return;
    }

    // Save or delete meta
    if (isset($_POST['simple_clean_hide_navigation']) && $_POST['simple_clean_hide_navigation'] === '1') {
        $result = update_post_meta($post_id, '_simple_clean_hide_navigation', '1');
        // Add admin notice
        add_action('admin_notices', function() {
            echo '<div class="notice notice-success is-dismissible"><p><strong>✅ Sidebar ausgeblendet:</strong> Die Seitenleiste am linken Rand wird auf dieser Seite nicht angezeigt.</p></div>';
        });
    } else {
        delete_post_meta($post_id, '_simple_clean_hide_navigation');
        // Add admin notice
        add_action('admin_notices', function() {
            echo '<div class="notice notice-info is-dismissible"><p><strong>ℹ️ Sidebar aktiviert:</strong> Die Seitenleiste wird auf dieser Seite angezeigt.</p></div>';
        });
    }
}
add_action('save_post', 'simple_clean_save_navigation_meta');

// Helper function to check if navigation should be hidden
function simple_clean_should_hide_navigation() {
    // Check if we're on a page (not in admin, not a post, not archive, etc.)
    if (is_page() && !is_admin()) {
        $page_id = get_the_ID();
        if ($page_id) {
            $hide = get_post_meta($page_id, '_simple_clean_hide_navigation', true);

            // Debug: uncomment next line to see what's being checked
            // error_log("Page ID: $page_id | Hide Navigation Meta: " . var_export($hide, true));

            return $hide === '1' || $hide === 1;
        }
    }
    return false;
}

// ===================================================================
// GLOSSAR SYSTEM
// ===================================================================

// Register Custom Post Type: Glossar
function simple_clean_register_glossar_cpt() {
    $labels = array(
        'name'               => 'Glossar',
        'singular_name'      => 'Glossar-Begriff',
        'menu_name'          => 'Glossar',
        'add_new'            => 'Begriff hinzufügen',
        'add_new_item'       => 'Neuen Begriff hinzufügen',
        'edit_item'          => 'Begriff bearbeiten',
        'new_item'           => 'Neuer Begriff',
        'view_item'          => 'Begriff anzeigen',
        'search_items'       => 'Begriffe durchsuchen',
        'not_found'          => 'Keine Begriffe gefunden',
        'not_found_in_trash' => 'Keine Begriffe im Papierkorb',
        'all_items'          => 'Glossar',
    );

    $args = array(
        'labels'              => $labels,
        'public'              => true,
        'publicly_queryable'  => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => true, // Enable Gutenberg
        'menu_icon'           => 'dashicons-book-alt',
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => array('title', 'editor', 'thumbnail'),
        'has_archive'         => true,
        'rewrite'             => array('slug' => 'glossar'),
    );

    register_post_type('glossar', $args);
}
add_action('init', 'simple_clean_register_glossar_cpt');

// Glossar Settings Page
function simple_clean_glossar_settings_init() {
    register_setting('simple_clean_glossar', 'glossar_modal_type');
    register_setting('simple_clean_glossar', 'glossar_auto_link');
    register_setting('simple_clean_glossar', 'glossar_first_only');
    register_setting('simple_clean_glossar', 'glossar_case_sensitive');
    register_setting('simple_clean_glossar', 'glossar_auto_rebuild');

    add_settings_section(
        'glossar_settings_section',
        'Glossar Einstellungen',
        'simple_clean_glossar_settings_section_cb',
        'simple_clean_glossar'
    );

    add_settings_field(
        'glossar_modal_type',
        'Modal-Typ',
        'simple_clean_glossar_modal_type_cb',
        'simple_clean_glossar',
        'glossar_settings_section'
    );

    add_settings_field(
        'glossar_auto_link',
        'Automatische Verlinkung',
        'simple_clean_glossar_auto_link_cb',
        'simple_clean_glossar',
        'glossar_settings_section'
    );

    add_settings_field(
        'glossar_first_only',
        'Nur erste Erwähnung',
        'simple_clean_glossar_first_only_cb',
        'simple_clean_glossar',
        'glossar_settings_section'
    );

    add_settings_field(
        'glossar_case_sensitive',
        'Groß-/Kleinschreibung',
        'simple_clean_glossar_case_sensitive_cb',
        'simple_clean_glossar',
        'glossar_settings_section'
    );

    add_settings_field(
        'glossar_auto_rebuild',
        'Automatische Seitenerkennung',
        'simple_clean_glossar_auto_rebuild_cb',
        'simple_clean_glossar',
        'glossar_settings_section'
    );
}
add_action('admin_init', 'simple_clean_glossar_settings_init');

function simple_clean_glossar_settings_section_cb() {
    echo '<p>Konfiguriere das Verhalten des Glossar-Systems.</p>';
}

function simple_clean_glossar_modal_type_cb() {
    $value = get_option('glossar_modal_type', 'tooltip');
    ?>
    <select name="glossar_modal_type">
        <option value="tooltip" <?php selected($value, 'tooltip'); ?>>Tooltip (Kompakt)</option>
        <option value="sidebar" <?php selected($value, 'sidebar'); ?>>Sidebar-Panel</option>
    </select>
    <p class="description">Wie sollen Glossar-Erklärungen angezeigt werden?</p>
    <?php
}

function simple_clean_glossar_auto_link_cb() {
    $value = get_option('glossar_auto_link', '1');
    ?>
    <label>
        <input type="checkbox" name="glossar_auto_link" value="1" <?php checked($value, '1'); ?>>
        Begriffe automatisch im Content verlinken
    </label>
    <p class="description">Wenn aktiviert, werden Glossar-Begriffe automatisch erkannt und verlinkt.</p>
    <?php
}

function simple_clean_glossar_first_only_cb() {
    $value = get_option('glossar_first_only', '1');
    ?>
    <label>
        <input type="checkbox" name="glossar_first_only" value="1" <?php checked($value, '1'); ?>>
        Nur erste Erwähnung pro Seite verlinken
    </label>
    <p class="description">Vermeidet zu viele Links auf einer Seite.</p>
    <?php
}

function simple_clean_glossar_case_sensitive_cb() {
    $value = get_option('glossar_case_sensitive', '0');
    ?>
    <label>
        <input type="checkbox" name="glossar_case_sensitive" value="1" <?php checked($value, '1'); ?>>
        Groß-/Kleinschreibung beachten
    </label>
    <p class="description">Wenn deaktiviert, wird "Atom" und "atom" als gleich erkannt.</p>
    <?php
}

function simple_clean_glossar_auto_rebuild_cb() {
    $value = get_option('glossar_auto_rebuild', '0');
    ?>
    <label>
        <input type="checkbox" name="glossar_auto_rebuild" value="1" <?php checked($value, '1'); ?>>
        Seiten automatisch analysieren bei neuen Begriffen
    </label>
    <p class="description">
        <strong>Aktiviert:</strong> Alle Seiten werden sofort analysiert, wenn Sie einen neuen Glossarbegriff veröffentlichen (kann 5-10 Sekunden dauern).<br>
        <strong>Deaktiviert:</strong> Seiten werden beim nächsten Aufruf automatisch analysiert, oder Sie nutzen den manuellen Button "Alle Seiten jetzt analysieren".
    </p>
    <?php
}

// Add Settings Menu
function simple_clean_glossar_settings_menu() {
    add_submenu_page(
        'edit.php?post_type=glossar',
        'Glossar Einstellungen',
        'Einstellungen',
        'manage_options',
        'glossar-settings',
        'simple_clean_glossar_settings_page'
    );
}
add_action('admin_menu', 'simple_clean_glossar_settings_menu');

function simple_clean_glossar_settings_page() {
    // Handle delete actions
    simple_clean_handle_glossar_delete_actions();

    // Note: Export is handled in admin_init hook (before any output)

    // Handle cache clear
    if (isset($_POST['clear_glossar_cache']) && check_admin_referer('glossar_cache_clear', 'glossar_cache_nonce')) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'glossar_content_cache';
        $deleted = $wpdb->query("DELETE FROM $table_name");
        add_action('admin_notices', function() use ($deleted) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>✓ Cache geleert!</strong></p>';
            echo '<p>' . $deleted . ' Cache-Einträge wurden gelöscht.</p>';
            echo '</div>';
        });
    }

    // Handle cache rebuild
    if (isset($_POST['rebuild_glossar_cache']) && check_admin_referer('glossar_cache_rebuild', 'glossar_cache_rebuild_nonce')) {
        simple_clean_rebuild_all_content_caches();
    }

    // Handle import
    if (isset($_POST['glossar_import']) && check_admin_referer('glossar_import', 'glossar_import_nonce')) {
        $result = simple_clean_handle_glossar_import();
        if ($result['success']) {
            add_action('admin_notices', function() use ($result) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p><strong>✓ Import erfolgreich!</strong></p>';
                echo '<p>' . $result['imported'] . ' Begriffe importiert, ' . $result['updated'] . ' aktualisiert, ' . $result['skipped'] . ' übersprungen.</p>';
                if (!empty($result['errors'])) {
                    echo '<p><strong>Fehler:</strong></p><ul>';
                    foreach ($result['errors'] as $error) {
                        echo '<li>' . esc_html($error) . '</li>';
                    }
                    echo '</ul>';
                }
                echo '</div>';
            });
        } else {
            add_action('admin_notices', function() use ($result) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p><strong>❌ Import fehlgeschlagen!</strong></p>';
                echo '<p>' . esc_html($result['message']) . '</p>';
                echo '</div>';
            });
        }
    }

    // Handle usage tracking rebuild
    if (isset($_POST['rebuild_usage_tracking']) && check_admin_referer('glossar_rebuild_usage', 'glossar_rebuild_nonce')) {
        $rebuilt_count = simple_clean_rebuild_usage_tracking();
        add_action('admin_notices', function() use ($rebuilt_count) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>✓ Usage-Tracking aktualisiert!</strong></p>';
            echo '<p>' . $rebuilt_count . ' Seiten/Beiträge wurden analysiert und aktualisiert.</p>';
            echo '</div>';
        });
    }

    // Get all glossar terms
    $glossar_posts = get_posts(array(
        'post_type' => 'glossar',
        'posts_per_page' => -1,
        'post_status' => array('publish', 'draft', 'pending'),
        'orderby' => 'title',
        'order' => 'ASC',
    ));

    $total_terms = count($glossar_posts);
    ?>
    <div class="wrap">
        <h1>Glossar Einstellungen</h1>

        <!-- Settings Form -->
        <h2 class="nav-tab-wrapper">
            <a href="#settings" class="nav-tab nav-tab-active" onclick="switchTab(event, 'settings')">Einstellungen</a>
            <a href="#manage" class="nav-tab" onclick="switchTab(event, 'manage')">Begriffe verwalten (<?php echo $total_terms; ?>)</a>
            <a href="#importexport" class="nav-tab" onclick="switchTab(event, 'importexport')">📥 Import/Export</a>
        </h2>

        <!-- Settings Tab -->
        <div id="settings-tab" class="glossar-tab-content">
            <form method="post" action="options.php">
                <?php
                settings_fields('simple_clean_glossar');
                do_settings_sections('simple_clean_glossar');
                submit_button();
                ?>
            </form>

            <!-- Usage Tracking Rebuild -->
            <div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #2271b1; padding: 15px; margin: 20px 0;">
                <h3 style="margin-top: 0;">🔄 Usage-Tracking aktualisieren</h3>
                <p>Analysiert alle veröffentlichten Seiten und Beiträge und aktualisiert die Information, wo welche Glossarbegriffe verwendet werden.</p>
                <p><strong>Wann verwenden?</strong></p>
                <ul style="margin-left: 20px;">
                    <li>Nach dem ersten Import von Glossarbegriffen</li>
                    <li>Nach Bulk-Änderungen an Seiten/Beiträgen</li>
                    <li>Wenn die Backlinks nicht korrekt angezeigt werden</li>
                </ul>
                <form method="post">
                    <?php wp_nonce_field('glossar_rebuild_usage', 'glossar_rebuild_nonce'); ?>
                    <button type="submit" name="rebuild_usage_tracking" class="button button-primary" value="1">
                        ♻️ Alle Seiten jetzt analysieren
                    </button>
                </form>
            </div>

            <!-- Cache Management -->
            <div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #00a32a; padding: 15px; margin: 20px 0;">
                <h3 style="margin-top: 0;">🚀 Performance: Cache-Verwaltung</h3>
                <p>Der Glossar-Cache speichert vorab verarbeitete Seiten für schnellere Ladezeiten.</p>

                <?php
                global $wpdb;
                $table_name = $wpdb->prefix . 'glossar_content_cache';
                $cache_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                $glossar_version = get_option('_glossar_version', 1);
                ?>

                <p><strong>Cache-Statistiken:</strong></p>
                <ul style="margin-left: 20px;">
                    <li>Gecachte Seiten: <?php echo $cache_count; ?></li>
                    <li>Glossar-Version: <?php echo $glossar_version; ?></li>
                    <li>Automatische Seitenerkennung: <?php echo get_option('glossar_auto_rebuild', '0') === '1' ? '<span style="color: #00a32a;">✓ Aktiviert</span>' : '<span style="color: #666;">○ Deaktiviert (Lazy)</span>'; ?></li>
                </ul>

                <p><strong>Cache-Aktionen:</strong></p>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <form method="post" style="display: inline-block;">
                        <?php wp_nonce_field('glossar_cache_clear', 'glossar_cache_nonce'); ?>
                        <button type="submit" name="clear_glossar_cache" class="button" value="1">
                            🗑️ Cache leeren
                        </button>
                    </form>

                    <form method="post" style="display: inline-block;">
                        <?php wp_nonce_field('glossar_cache_rebuild', 'glossar_cache_rebuild_nonce'); ?>
                        <button type="submit" name="rebuild_glossar_cache" class="button" value="1">
                            ♻️ Cache leeren (Lazy Rebuild)
                        </button>
                    </form>
                </div>

                <p style="margin-top: 15px;"><strong>Wann Cache leeren?</strong></p>
                <ul style="margin-left: 20px;">
                    <li>Bei Problemen mit veralteten Glossar-Links</li>
                    <li>Nach manuellen Änderungen an der Datenbank</li>
                    <li>Nach Theme-Updates</li>
                </ul>

                <p><strong>Wie funktioniert der Cache? (Lazy Frontend Caching)</strong></p>
                <ul style="margin-left: 20px;">
                    <li>✅ <strong>Container-Block-kompatibel:</strong> Glossar-Filter läuft bei Priorität 10000 (NACH CDB LaTeX Parser bei 999)</li>
                    <li>Bei jedem Seitenaufruf wird zuerst der Cache geprüft (sehr schnell)</li>
                    <li>Bei Cache-HIT: Seite wird sofort angezeigt (&lt;5ms)</li>
                    <li>Bei Cache-MISS: Container-Blöcke werden gerendert → LaTeX verarbeitet → Glossar-Links hinzugefügt → Gecacht</li>
                    <li>Cache wird automatisch ungültig bei Änderungen an Glossar-Begriffen oder Seiten</li>
                    <li>Keine Pre-Generation: Cache entsteht nur bei echten Seitenaufrufen</li>
                </ul>
            </div>
        </div>

        <!-- Manage Terms Tab -->
        <div id="manage-tab" class="glossar-tab-content" style="display: none;">
            <h2>Glossar-Begriffe verwalten</h2>

            <?php if ($total_terms > 0): ?>
                <!-- Bulk Delete Section -->
                <div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #d63638; padding: 15px; margin: 20px 0;">
                    <h3 style="margin-top: 0;">⚠️ Gefahr: Alle Begriffe löschen</h3>
                    <p>Diese Aktion löscht <strong>alle <?php echo $total_terms; ?> Glossar-Begriffe</strong> dauerhaft aus der Datenbank.</p>
                    <p><strong>Diese Aktion kann nicht rückgängig gemacht werden!</strong></p>
                    <form method="post" onsubmit="return confirm('ACHTUNG: Möchten Sie wirklich ALLE <?php echo $total_terms; ?> Glossar-Begriffe unwiderruflich löschen? Diese Aktion kann nicht rückgängig gemacht werden!');">
                        <?php wp_nonce_field('glossar_bulk_delete', 'glossar_bulk_delete_nonce'); ?>
                        <input type="hidden" name="glossar_action" value="bulk_delete">
                        <button type="submit" class="button button-danger" style="background: #d63638; color: white; border-color: #d63638;">
                            🗑️ Alle <?php echo $total_terms; ?> Begriffe unwiderruflich löschen
                        </button>
                    </form>
                </div>

                <!-- Terms List -->
                <div style="background: #fff; border: 1px solid #c3c4c7; padding: 15px; margin: 20px 0;">
                    <h3>Einzelne Begriffe verwalten</h3>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th style="width: 40%;">Begriff</th>
                                <th style="width: 15%;">Status</th>
                                <th style="width: 20%;">Erstellt</th>
                                <th style="width: 25%;">Aktionen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($glossar_posts as $post): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($post->post_title); ?></strong>
                                        <?php if (!empty($post->post_content)): ?>
                                            <br><small style="color: #666;"><?php echo esc_html(wp_trim_words($post->post_content, 15)); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_labels = array(
                                            'publish' => '<span style="color: #00a32a;">✓ Veröffentlicht</span>',
                                            'draft' => '<span style="color: #dba617;">📝 Entwurf</span>',
                                            'pending' => '<span style="color: #996800;">⏳ Ausstehend</span>',
                                        );
                                        echo $status_labels[$post->post_status] ?? esc_html($post->post_status);
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo date_i18n('d.m.Y H:i', strtotime($post->post_date)); ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo get_edit_post_link($post->ID); ?>" class="button button-small">
                                            ✏️ Bearbeiten
                                        </a>
                                        <form method="post" style="display: inline-block; margin-left: 5px;" onsubmit="return confirm('Begriff \'<?php echo esc_js($post->post_title); ?>\' wirklich löschen?');">
                                            <?php wp_nonce_field('glossar_delete_' . $post->ID, 'glossar_delete_nonce'); ?>
                                            <input type="hidden" name="glossar_action" value="delete_single">
                                            <input type="hidden" name="glossar_post_id" value="<?php echo $post->ID; ?>">
                                            <button type="submit" class="button button-small" style="color: #d63638;">
                                                🗑️ Löschen
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php else: ?>
                <div class="notice notice-info" style="margin: 20px 0;">
                    <p><strong>ℹ️ Keine Glossar-Begriffe vorhanden</strong></p>
                    <p>Es wurden noch keine Glossar-Begriffe erstellt. Erstellen Sie neue Begriffe unter <a href="<?php echo admin_url('post-new.php?post_type=glossar'); ?>">Glossar → Begriff hinzufügen</a>.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Import/Export Tab -->
        <div id="importexport-tab" class="glossar-tab-content" style="display: none;">
            <h2>Glossar Import & Export</h2>

            <!-- Export Section -->
            <div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #00a32a; padding: 15px; margin: 20px 0;">
                <h3 style="margin-top: 0;">📤 Export</h3>
                <p>Exportiere alle Glossar-Begriffe als CSV-Datei. Diese Datei kann in Excel, Google Sheets oder von KI-Tools bearbeitet werden.</p>
                <p><strong>CSV-Format:</strong> Begriff, Definition, Slug, Status</p>
                <form method="post">
                    <?php wp_nonce_field('glossar_export', 'glossar_export_nonce'); ?>
                    <button type="submit" name="glossar_export" class="button button-primary" value="1">
                        📥 Als CSV herunterladen (<?php echo $total_terms; ?> Begriffe)
                    </button>
                </form>
            </div>

            <!-- Import Section -->
            <div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #2271b1; padding: 15px; margin: 20px 0;">
                <h3 style="margin-top: 0;">📥 Import</h3>
                <p>Importiere Glossar-Begriffe aus einer CSV-Datei. Die CSV-Datei muss folgende Spalten enthalten:</p>
                <ul style="margin-left: 20px;">
                    <li><strong>Begriff</strong> (Pflichtfeld) - Der Glossarbegriff</li>
                    <li><strong>Definition</strong> (Pflichtfeld) - Die Erklärung des Begriffs</li>
                    <li><strong>Slug</strong> (optional) - URL-freundlicher Name (wird automatisch generiert, wenn leer)</li>
                    <li><strong>Status</strong> (optional) - "publish", "draft" oder "pending" (Standard: publish)</li>
                </ul>

                <p><strong>📋 CSV-Template für KI:</strong></p>
                <code style="display: block; background: #f6f7f7; padding: 10px; margin: 10px 0; font-size: 12px;">
Begriff,Definition,Slug,Status<br>
Atom,"Ein Atom ist das kleinste Teilchen eines chemischen Elements.",atom,publish<br>
Molekül,"Ein Molekül besteht aus zwei oder mehr miteinander verbundenen Atomen.",molekuel,publish
                </code>

                <form method="post" enctype="multipart/form-data">
                    <?php wp_nonce_field('glossar_import', 'glossar_import_nonce'); ?>
                    <p>
                        <input type="file" name="glossar_csv" accept=".csv" required style="margin-right: 10px;">
                        <label>
                            <input type="checkbox" name="glossar_import_overwrite" value="1">
                            Bestehende Begriffe überschreiben (gleicher Slug)
                        </label>
                    </p>
                    <button type="submit" name="glossar_import" class="button button-primary" value="1">
                        📤 CSV-Datei importieren
                    </button>
                </form>
            </div>

            <!-- Info Section -->
            <div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #72aee6; padding: 15px; margin: 20px 0;">
                <h3 style="margin-top: 0;">ℹ️ Hinweise</h3>
                <ul style="margin-left: 20px;">
                    <li><strong>CSV-Encoding:</strong> Verwende UTF-8 für Umlaute (ä, ö, ü)</li>
                    <li><strong>Trennzeichen:</strong> Komma (,) - Excel/Google Sheets kompatibel</li>
                    <li><strong>KI-Unterstützung:</strong> Das CSV-Format kann von ChatGPT, Claude oder anderen KI-Tools ausgefüllt werden</li>
                    <li><strong>Bulk-Bearbeitung:</strong> Exportiere, bearbeite in Excel/Sheets, importiere zurück</li>
                    <li><strong>Nach dem Import:</strong> Verwende den Button "Usage-Tracking aktualisieren" im Tab "Einstellungen"</li>
                </ul>
            </div>
        </div>

        <!-- Tab Switching Script -->
        <script>
        function switchTab(event, tabName) {
            event.preventDefault();

            // Hide all tabs
            document.querySelectorAll('.glossar-tab-content').forEach(function(tab) {
                tab.style.display = 'none';
            });

            // Remove active class from all nav tabs
            document.querySelectorAll('.nav-tab').forEach(function(navTab) {
                navTab.classList.remove('nav-tab-active');
            });

            // Show selected tab
            document.getElementById(tabName + '-tab').style.display = 'block';
            event.target.classList.add('nav-tab-active');
        }
        </script>

        <!-- Custom Styles -->
        <style>
        .glossar-tab-content {
            background: white;
            padding: 20px;
            border: 1px solid #c3c4c7;
            border-top: none;
            margin-bottom: 20px;
        }
        .button-danger:hover {
            background: #b32d2e !important;
            border-color: #b32d2e !important;
        }
        </style>
    </div>
    <?php
}

// Handle glossar delete actions
function simple_clean_handle_glossar_delete_actions() {
    // Only process if we have an action
    if (!isset($_POST['glossar_action'])) {
        return;
    }

    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die('Sie haben keine Berechtigung, diese Aktion auszuführen.');
    }

    $action = sanitize_text_field($_POST['glossar_action']);

    // Handle bulk delete
    if ($action === 'bulk_delete') {
        // Verify nonce
        if (!isset($_POST['glossar_bulk_delete_nonce']) ||
            !wp_verify_nonce($_POST['glossar_bulk_delete_nonce'], 'glossar_bulk_delete')) {
            wp_die('Sicherheitsüberprüfung fehlgeschlagen.');
        }

        // Get all glossar posts
        $glossar_posts = get_posts(array(
            'post_type' => 'glossar',
            'posts_per_page' => -1,
            'post_status' => 'any',
        ));

        $deleted_count = 0;
        foreach ($glossar_posts as $post) {
            if (wp_delete_post($post->ID, true)) {
                $deleted_count++;
            }
        }

        // Add admin notice
        add_action('admin_notices', function() use ($deleted_count) {
            echo '<div class="notice notice-success is-dismissible">';
            echo '<p><strong>✓ Erfolgreich gelöscht!</strong></p>';
            echo '<p>' . $deleted_count . ' Glossar-Begriffe wurden dauerhaft aus der Datenbank entfernt.</p>';
            echo '</div>';
        });

        return;
    }

    // Handle single delete
    if ($action === 'delete_single') {
        // Get post ID
        if (!isset($_POST['glossar_post_id'])) {
            wp_die('Keine Post-ID angegeben.');
        }

        $post_id = intval($_POST['glossar_post_id']);

        // Verify nonce
        if (!isset($_POST['glossar_delete_nonce']) ||
            !wp_verify_nonce($_POST['glossar_delete_nonce'], 'glossar_delete_' . $post_id)) {
            wp_die('Sicherheitsüberprüfung fehlgeschlagen.');
        }

        // Verify post type
        if (get_post_type($post_id) !== 'glossar') {
            wp_die('Ungültiger Post-Typ.');
        }

        // Get post title before deletion
        $post_title = get_the_title($post_id);

        // Delete post
        if (wp_delete_post($post_id, true)) {
            add_action('admin_notices', function() use ($post_title) {
                echo '<div class="notice notice-success is-dismissible">';
                echo '<p><strong>✓ Begriff gelöscht!</strong></p>';
                echo '<p>Der Begriff "' . esc_html($post_title) . '" wurde dauerhaft gelöscht.</p>';
                echo '</div>';
            });
        } else {
            add_action('admin_notices', function() use ($post_title) {
                echo '<div class="notice notice-error is-dismissible">';
                echo '<p><strong>❌ Fehler beim Löschen!</strong></p>';
                echo '<p>Der Begriff "' . esc_html($post_title) . '" konnte nicht gelöscht werden.</p>';
                echo '</div>';
            });
        }
    }
}

// Early hook to handle export before any output
function simple_clean_handle_glossar_export_early() {
    // Only on admin pages
    if (!is_admin()) {
        return;
    }

    // Check if this is an export request
    if (!isset($_POST['glossar_export']) || $_POST['glossar_export'] !== '1') {
        return;
    }

    // Verify nonce
    if (!isset($_POST['glossar_export_nonce']) || !wp_verify_nonce($_POST['glossar_export_nonce'], 'glossar_export')) {
        return;
    }

    // Check permissions
    if (!current_user_can('manage_options')) {
        wp_die('Sie haben keine Berechtigung, diese Aktion auszuführen.');
    }

    // Call the actual export function
    simple_clean_handle_glossar_export();
    exit; // Important: Stop all further execution
}
add_action('admin_init', 'simple_clean_handle_glossar_export_early');

// Handle Glossar CSV Export
function simple_clean_handle_glossar_export() {
    // Get all glossar posts
    $glossar_posts = get_posts(array(
        'post_type' => 'glossar',
        'posts_per_page' => -1,
        'post_status' => array('publish', 'draft', 'pending'),
        'orderby' => 'title',
        'order' => 'ASC',
    ));

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="glossar-export-' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Create output stream
    $output = fopen('php://output', 'w');

    // Add BOM for Excel UTF-8 compatibility
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Write CSV header
    fputcsv($output, array('Begriff', 'Definition', 'Slug', 'Status'));

    // Write data
    foreach ($glossar_posts as $post) {
        $row = array(
            $post->post_title,
            wp_strip_all_tags($post->post_content), // Remove HTML tags from definition
            $post->post_name,
            $post->post_status
        );
        fputcsv($output, $row);
    }

    fclose($output);
}

// Helper function: Check if term or variation already exists
function simple_clean_glossar_term_exists_or_similar($term) {
    // Normalize the input term
    $normalized_term = simple_clean_normalize_glossar_term($term);

    // Get all existing glossar terms
    $existing_posts = get_posts(array(
        'post_type' => 'glossar',
        'posts_per_page' => -1,
        'post_status' => array('publish', 'draft', 'pending'),
    ));

    foreach ($existing_posts as $post) {
        $existing_normalized = simple_clean_normalize_glossar_term($post->post_title);

        // Check for exact match (normalized)
        if ($normalized_term === $existing_normalized) {
            return array(
                'exists' => true,
                'post' => $post,
                'reason' => 'Exakte Übereinstimmung'
            );
        }

        // Check for variations (one contains the other)
        if (strlen($normalized_term) > 3 && strlen($existing_normalized) > 3) {
            if (strpos($existing_normalized, $normalized_term) !== false) {
                return array(
                    'exists' => true,
                    'post' => $post,
                    'reason' => "Variation gefunden: '{$post->post_title}'"
                );
            }
            if (strpos($normalized_term, $existing_normalized) !== false) {
                return array(
                    'exists' => true,
                    'post' => $post,
                    'reason' => "Variation gefunden: '{$post->post_title}'"
                );
            }
        }

        // Check for singular/plural variations
        if (simple_clean_glossar_terms_are_similar($normalized_term, $existing_normalized)) {
            return array(
                'exists' => true,
                'post' => $post,
                'reason' => "Ähnliche Form gefunden: '{$post->post_title}'"
            );
        }
    }

    return array('exists' => false);
}

// Helper function: Normalize term for comparison
function simple_clean_normalize_glossar_term($term) {
    // Convert to lowercase
    $term = mb_strtolower(trim($term), 'UTF-8');

    // Remove common articles (German)
    $articles = array('der ', 'die ', 'das ', 'ein ', 'eine ', 'eines ', 'einem ', 'einen ');
    foreach ($articles as $article) {
        if (strpos($term, $article) === 0) {
            $term = substr($term, strlen($article));
        }
    }

    // Remove special characters and extra spaces
    $term = preg_replace('/[^\p{L}\p{N}\s]/u', '', $term);
    $term = preg_replace('/\s+/', ' ', $term);

    return trim($term);
}

// Helper function: Check if terms are similar (singular/plural)
function simple_clean_glossar_terms_are_similar($term1, $term2) {
    // If terms are very short, don't check similarity
    if (strlen($term1) < 4 || strlen($term2) < 4) {
        return false;
    }

    // Check if one is just plural of the other (simple heuristic)
    // German plural often: -e, -en, -er, -s
    $endings = array('e', 'en', 'er', 's', 'n');

    foreach ($endings as $ending) {
        // Check if term1 = term2 + ending
        if ($term1 === $term2 . $ending || $term2 === $term1 . $ending) {
            return true;
        }
    }

    // Check Levenshtein distance (max 2 character difference for similar terms)
    if (strlen($term1) > 5 && strlen($term2) > 5) {
        $distance = levenshtein($term1, $term2);
        if ($distance > 0 && $distance <= 2) {
            return true;
        }
    }

    return false;
}

// Handle Glossar CSV Import
function simple_clean_handle_glossar_import() {
    // Check permissions
    if (!current_user_can('manage_options')) {
        return array(
            'success' => false,
            'message' => 'Sie haben keine Berechtigung, diese Aktion auszuführen.'
        );
    }

    // Check if file was uploaded
    if (!isset($_FILES['glossar_csv']) || $_FILES['glossar_csv']['error'] !== UPLOAD_ERR_OK) {
        return array(
            'success' => false,
            'message' => 'Keine Datei hochgeladen oder Upload-Fehler.'
        );
    }

    // Check file type
    $file_info = pathinfo($_FILES['glossar_csv']['name']);
    if (strtolower($file_info['extension']) !== 'csv') {
        return array(
            'success' => false,
            'message' => 'Ungültiges Dateiformat. Nur CSV-Dateien sind erlaubt.'
        );
    }

    // Get overwrite setting
    $overwrite = isset($_POST['glossar_import_overwrite']) && $_POST['glossar_import_overwrite'] === '1';

    // Read CSV file
    $file = fopen($_FILES['glossar_csv']['tmp_name'], 'r');
    if (!$file) {
        return array(
            'success' => false,
            'message' => 'Fehler beim Öffnen der CSV-Datei.'
        );
    }

    $imported = 0;
    $updated = 0;
    $skipped = 0;
    $errors = array();
    $row_number = 0;

    while (($data = fgetcsv($file, 10000, ',')) !== false) {
        $row_number++;

        // Skip header row
        if ($row_number === 1 && ($data[0] === 'Begriff' || $data[0] === 'Term')) {
            continue;
        }

        // Validate row has at least 2 columns (Begriff and Definition)
        if (count($data) < 2) {
            $errors[] = "Zeile $row_number: Zu wenige Spalten (mindestens 2 erforderlich)";
            $skipped++;
            continue;
        }

        // Extract data
        $term = trim($data[0]);
        $definition = trim($data[1]);
        $slug = isset($data[2]) ? sanitize_title(trim($data[2])) : '';
        $status = isset($data[3]) ? trim($data[3]) : 'publish';

        // Validate required fields
        if (empty($term)) {
            $errors[] = "Zeile $row_number: Begriff ist leer";
            $skipped++;
            continue;
        }

        if (empty($definition)) {
            $errors[] = "Zeile $row_number: Definition ist leer für '$term'";
            $skipped++;
            continue;
        }

        // Generate slug if empty
        if (empty($slug)) {
            $slug = sanitize_title($term);
        }

        // Validate status
        if (!in_array($status, array('publish', 'draft', 'pending'))) {
            $status = 'publish';
        }

        // Check if term or variation already exists
        $exists_check = simple_clean_glossar_term_exists_or_similar($term);

        if ($exists_check['exists']) {
            // Term or variation already exists
            $existing_post = $exists_check['post'];

            // Check if it's an exact slug match AND overwrite is enabled
            if ($overwrite && $existing_post->post_name === $slug) {
                // Only update if exact slug match AND overwrite enabled
                $post_id = wp_update_post(array(
                    'ID' => $existing_post->ID,
                    'post_title' => $term,
                    'post_content' => $definition,
                    'post_status' => $status,
                    'post_type' => 'glossar',
                ));

                if (is_wp_error($post_id)) {
                    $errors[] = "Zeile $row_number: Fehler beim Aktualisieren von '$term': " . $post_id->get_error_message();
                    $skipped++;
                } else {
                    $updated++;
                }
            } else {
                // Skip: either overwrite disabled or it's a variation
                $errors[] = "Zeile $row_number: Begriff übersprungen: '$term' - {$exists_check['reason']}";
                $skipped++;
            }
        } else {
            // Create new post
            $post_id = wp_insert_post(array(
                'post_title' => $term,
                'post_content' => $definition,
                'post_status' => $status,
                'post_type' => 'glossar',
                'post_name' => $slug,
            ));

            if (is_wp_error($post_id)) {
                $errors[] = "Zeile $row_number: Fehler beim Erstellen von '$term': " . $post_id->get_error_message();
                $skipped++;
            } else {
                $imported++;
            }
        }
    }

    fclose($file);

    return array(
        'success' => true,
        'imported' => $imported,
        'updated' => $updated,
        'skipped' => $skipped,
        'errors' => $errors
    );
}

// Enqueue Glossar Assets
function simple_clean_glossar_assets() {
    // Only load on pages/posts, not admin
    if (is_admin()) {
        return;
    }

    // Enqueue dashicons for usage links icon
    if (is_singular('glossar')) {
        wp_enqueue_style('dashicons');
    }

    // Enqueue glossar CSS (always load for styling)
    $glossar_css = get_template_directory() . '/dist/css/glossar-style.css';
    if (file_exists($glossar_css)) {
        wp_enqueue_style(
            'simple-clean-glossar-style',
            get_template_directory_uri() . '/dist/css/glossar-style.css',
            array(),
            filemtime($glossar_css)
        );
    }

    // Enqueue glossar JavaScript (only for modals, NOT for auto-linking)
    $glossar_js = get_template_directory() . '/dist/js/glossar.js';
    if (file_exists($glossar_js)) {
        wp_enqueue_script(
            'simple-clean-glossar',
            get_template_directory_uri() . '/dist/js/glossar.js',
            array(),
            filemtime($glossar_js),
            true
        );

        // Pass settings to JavaScript (terms are now handled server-side)
        wp_localize_script('simple-clean-glossar', 'glossarData', array(
            'modalType' => get_option('glossar_modal_type', 'tooltip'),
            'autoLink' => '0', // Disabled, handled server-side now
            'firstOnly' => get_option('glossar_first_only', '1'),
            'caseSensitive' => get_option('glossar_case_sensitive', '0'),
            'terms' => simple_clean_get_glossar_terms(), // Still needed for modal display
        ));
    }
}
add_action('wp_enqueue_scripts', 'simple_clean_glossar_assets');

// Server-Side Glossar Auto-Linking (much faster than JavaScript)
function simple_clean_glossar_auto_link_content($content) {
    // Skip if in admin, feed, or auto-linking is disabled
    if (is_admin() || is_feed() || get_option('glossar_auto_link', '1') !== '1') {
        return $content;
    }

    // Skip if this is a glossar post itself
    if (is_singular('glossar')) {
        return $content;
    }

    // Get all glossar terms (cached)
    $terms = simple_clean_get_glossar_terms();
    if (empty($terms)) {
        return $content;
    }

    // Sort terms by length (longest first) to avoid partial matches
    usort($terms, function($a, $b) {
        return mb_strlen($b['term']) - mb_strlen($a['term']);
    });

    $first_only = get_option('glossar_first_only', '1') === '1';
    $case_sensitive = get_option('glossar_case_sensitive', '0') === '1';
    $linked_terms = array();

    // Split content into HTML tags and text
    $parts = preg_split('/(<[^>]+>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

    $result = '';
    $in_link = false;
    $in_script = false;

    foreach ($parts as $part) {
        // Check if this is an HTML tag
        if (preg_match('/^<[^>]+>$/', $part)) {
            // Track if we're inside a link or script tag
            if (preg_match('/^<a\b/i', $part)) {
                $in_link = true;
            } elseif (preg_match('/^<\/a>/i', $part)) {
                $in_link = false;
            } elseif (preg_match('/^<(script|style|code|pre)\b/i', $part)) {
                $in_script = true;
            } elseif (preg_match('/^<\/(script|style|code|pre)>/i', $part)) {
                $in_script = false;
            }

            $result .= $part;
            continue;
        }

        // Skip processing if we're inside a link, script, or if already a glossar term
        if ($in_link || $in_script || strpos($part, 'glossar-term') !== false) {
            $result .= $part;
            continue;
        }

        // Process this text part
        $processed_part = $part;

        foreach ($terms as $term_data) {
            $term = $term_data['term'];

            // Skip if first_only is enabled and term already linked globally
            if ($first_only && in_array(strtolower($term), $linked_terms)) {
                continue;
            }

            // Get all variants of this term
            $variants = simple_clean_get_glossar_term_variants($term);

            // Process each variant
            foreach ($variants as $variant) {
                // Build regex pattern with word boundaries
                $pattern = $case_sensitive ?
                    '/\b(' . preg_quote($variant, '/') . ')\b/' :
                    '/\b(' . preg_quote($variant, '/') . ')\b/iu';

                // Check if variant exists in this part
                if (!preg_match($pattern, $processed_part)) {
                    continue;
                }

                // Replace callback
                $processed_part = preg_replace_callback($pattern, function($matches) use ($term_data, &$linked_terms, $first_only, $term) {
                    // Create the glossar link
                    $replacement = '<span class="glossar-term glossar-clickable" ' .
                                  'data-glossar-id="' . esc_attr($term_data['id']) . '" ' .
                                  'id="glossar-term-' . esc_attr($term_data['id']) . '" ' .
                                  'role="button" tabindex="0" ' .
                                  'aria-label="Glossar-Begriff: ' . esc_attr($term_data['term']) . '">' .
                                  $matches[1] . '</span>';

                    // Mark term as linked if first_only is enabled
                    if ($first_only) {
                        $linked_terms[] = strtolower($term);
                    }

                    return $replacement;
                }, $processed_part, $first_only ? 1 : -1);

                // If first_only and term was linked, break variant loop
                if ($first_only && in_array(strtolower($term), $linked_terms)) {
                    break 2; // Break both variant and term loop
                }
            }
        }

        $result .= $processed_part;
    }

    return $result;
}

// Use priority 10000 to run AFTER all other content filters
// CDB-Designer LaTeX Parser runs at priority 999
// This ensures Container Blocks are FULLY rendered before processing

// OPTIMIZED VERSION: Kandidaten-basiert (98% faster with 500+ terms)
add_filter('the_content', 'simple_clean_glossar_auto_link_content_optimized', 10000);

// Get all glossar terms (with optimized caching for performance)
function simple_clean_get_glossar_terms() {
    // Try to get cached terms from object cache (faster than transients)
    $cache_key = 'glossar_terms';
    $cache_group = 'simple_clean_glossar';
    $cached_terms = wp_cache_get($cache_key, $cache_group);

    // Return cached data if available
    if ($cached_terms !== false) {
        return $cached_terms;
    }

    // No cache found, fetch from database
    $terms = array();

    $glossar_posts = get_posts(array(
        'post_type' => 'glossar',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));

    foreach ($glossar_posts as $post) {
        $terms[] = array(
            'id' => $post->ID,
            'term' => $post->post_title,
            'definition' => wp_strip_all_tags($post->post_content),
            'permalink' => get_permalink($post->ID),
        );
    }

    // Cache the results (object cache is faster than transients for per-request caching)
    wp_cache_set($cache_key, $terms, $cache_group, HOUR_IN_SECONDS);

    return $terms;
}

// Clear glossar terms cache when terms are modified
function simple_clean_clear_glossar_cache($post_id, $post = null) {
    // Only clear cache for glossar post type
    if ($post && get_post_type($post) !== 'glossar') {
        return;
    }

    // Clear the object cache
    wp_cache_delete('glossar_terms', 'simple_clean_glossar');
}
add_action('save_post', 'simple_clean_clear_glossar_cache', 10, 2);
add_action('delete_post', 'simple_clean_clear_glossar_cache', 10, 2);
add_action('wp_trash_post', 'simple_clean_clear_glossar_cache', 10, 1);
add_action('untrash_post', 'simple_clean_clear_glossar_cache', 10, 1);

// ===================================================================
// GLOSSAR PER-PAGE OPTIMIZATION (Phase 1: Content Analysis)
// ===================================================================
// This system scans post content when saving and creates an index of
// potentially relevant glossar terms. This allows loading only relevant
// terms at render time instead of ALL terms, dramatically improving
// performance with 500+ glossar entries.
// ===================================================================

/**
 * Extrahiert reinen Text aus Gutenberg Blocks (rekursiv)
 * Ignoriert HTML-Tags und extrahiert nur Textinhalt
 *
 * @param array $blocks Array von Gutenberg Blocks (von parse_blocks())
 * @param int $depth Rekursions-Tiefe (verhindert infinite loops)
 * @return string Extrahierter Text ohne HTML-Tags
 */
function simple_clean_extract_text_from_blocks($blocks, $depth = 0) {
    // Früher Exit bei leerem Input
    if (empty($blocks)) {
        return '';
    }

    // Verhindere infinite recursion
    if ($depth > 10) {
        return '';
    }

    $text = '';

    foreach ($blocks as $block) {
        // Block Content extrahieren
        if (!empty($block['innerHTML'])) {
            // HTML-Tags entfernen
            $text .= ' ' . wp_strip_all_tags($block['innerHTML']);
        }

        // Attribute durchsuchen (z.B. für Custom Blocks)
        if (!empty($block['attrs']) && is_array($block['attrs'])) {
            foreach ($block['attrs'] as $attr_value) {
                if (is_string($attr_value)) {
                    $text .= ' ' . wp_strip_all_tags($attr_value);
                }
            }
        }

        // Inner Blocks rekursiv (verschachtelte Blocks)
        if (!empty($block['innerBlocks'])) {
            $text .= ' ' . simple_clean_extract_text_from_blocks($block['innerBlocks'], $depth + 1);
        }

        // Reusable Blocks auflösen
        if ($block['blockName'] === 'core/block' && !empty($block['attrs']['ref'])) {
            $reusable_post = get_post($block['attrs']['ref']);
            if ($reusable_post) {
                $reusable_blocks = parse_blocks($reusable_post->post_content);
                $text .= ' ' . simple_clean_extract_text_from_blocks($reusable_blocks, $depth + 1);
            }
        }
    }

    return $text;
}

/**
 * Scannt Post-Content und identifiziert potenzielle Glossarbegriffe
 * Wird beim Speichern ausgeführt (save_post Hook)
 *
 * Verwendet einfache String-Suche (stripos) statt Regex für Performance.
 * Ergebnis: Array von Glossar-Term-IDs die im Content vorkommen könnten.
 *
 * @param int $post_id Post ID
 * @return array Array von Glossar-Term-IDs
 */
function simple_clean_scan_glossar_candidates($post_id) {
    // Static cache für Glossar-Terms (verhindert mehrfaches Laden pro Request)
    static $all_terms_cache = null;

    $post = get_post($post_id);
    if (!$post) {
        return [];
    }

    // Content holen (alle Blöcke, inkl. Reusable Blocks)
    $content = $post->post_content;

    // Parse Gutenberg Blocks und extrahiere Text
    $blocks = parse_blocks($content);
    $text_content = simple_clean_extract_text_from_blocks($blocks);

    // Auch Title und Excerpt einbeziehen
    $searchable_text = mb_strtolower(
        $post->post_title . ' ' .
        $post->post_excerpt . ' ' .
        $text_content,
        'UTF-8'
    );

    // Alle Glossarbegriffe laden (nur einmal pro Request)
    if ($all_terms_cache === null) {
        $all_terms_cache = wp_cache_get('glossar_terms', 'simple_clean_glossar');
        if (false === $all_terms_cache) {
            $all_terms_cache = simple_clean_get_glossar_terms();
            wp_cache_set('glossar_terms', $all_terms_cache, 'simple_clean_glossar', HOUR_IN_SECONDS);
        }
    }

    $candidates = [];

    foreach ($all_terms_cache as $term) {
        $term_lower = mb_strtolower($term['term'], 'UTF-8');

        // Einfache String-Suche (NICHT Regex = schnell)
        // Bei 500 Begriffen × 5000 Zeichen: ~50ms
        if (mb_stripos($searchable_text, $term_lower) !== false) {
            $candidates[] = $term['id'];
        }
    }

    return $candidates;
}

/**
 * Hook: Glossar-Kandidaten bei Post-Speicherung aktualisieren
 * Läuft bei jedem save_post Event für Posts, Pages und Drafts
 *
 * @param int $post_id Post ID
 * @param WP_Post $post Post object
 */
function simple_clean_update_glossar_candidates($post_id, $post) {
    // Autosave überspringen
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Nur für relevante Post Types
    $allowed_types = ['post', 'page', 'glossar'];
    if (!in_array($post->post_type, $allowed_types)) {
        return;
    }

    // Permissions Check
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // FÜR SITE IM AUFBAU: Auch Drafts scannen (für Preview-Funktion)
    // Erlaubte Status: publish, draft, pending
    $allowed_statuses = ['publish', 'draft', 'pending'];
    if (!in_array($post->post_status, $allowed_statuses)) {
        return;
    }

    // Scan durchführen
    $candidates = simple_clean_scan_glossar_candidates($post_id);

    // Meta speichern
    update_post_meta($post_id, '_glossar_term_candidates', $candidates);
    update_post_meta($post_id, '_glossar_scan_version', 1); // Versionsnummer
    update_post_meta($post_id, '_glossar_last_scanned', current_time('mysql'));

    // Logging für große Glossare (optional, für Debugging)
    if (count($candidates) > 50) {
        error_log(sprintf(
            'Glossar: Post %d hat %d Kandidaten (ungewöhnlich hoch)',
            $post_id,
            count($candidates)
        ));
    }
}
add_action('save_post', 'simple_clean_update_glossar_candidates', 20, 2);

// ===================================================================
// GLOSSAR PER-PAGE OPTIMIZATION (Phase 2: Rendering Optimization)
// ===================================================================

/**
 * Lädt nur spezifische Glossarbegriffe nach IDs
 * Nutzt Object Cache für Performance
 *
 * @param array $term_ids Array von Glossar-Term-IDs
 * @return array Array von Glossar-Terms (filtered)
 */
function simple_clean_get_glossar_terms_by_ids($term_ids) {
    if (empty($term_ids)) {
        return [];
    }

    // Versuche aus Cache zu laden
    $all_terms = wp_cache_get('glossar_terms', 'simple_clean_glossar');

    if (false === $all_terms) {
        // Cache Miss: Alle Begriffe laden und cachen
        $all_terms = simple_clean_get_glossar_terms();
        wp_cache_set('glossar_terms', $all_terms, 'simple_clean_glossar', HOUR_IN_SECONDS);
    }

    // Nur gewünschte IDs filtern
    $filtered_terms = array_filter($all_terms, function($term) use ($term_ids) {
        return in_array($term['id'], $term_ids);
    });

    return array_values($filtered_terms);
}

/**
 * Erstellt Regex-Pattern nur für spezifische Begriffe
 * Analog zu simple_clean_build_glossar_pattern() aber optimiert für kleine Term-Sets
 *
 * @param array $terms Array von Glossar-Terms
 * @return array ['pattern' => string, 'mapping' => array]
 */
function simple_clean_build_glossar_pattern_for_terms($terms) {
    $variants = [];
    $variant_to_term = [];

    foreach ($terms as $term) {
        $base_term = $term['term'];

        // Varianten generieren (nutzt bestehende Varianten-Logik)
        // simple_clean_get_glossar_term_variants() ist weiter unten definiert
        if (function_exists('simple_clean_get_glossar_term_variants')) {
            $term_variants = simple_clean_get_glossar_term_variants($base_term);
        } else {
            // Fallback: Nur base term
            $term_variants = [$base_term];
        }

        foreach ($term_variants as $variant) {
            $escaped = preg_quote($variant, '/');
            $variants[] = $escaped;
            $variant_to_term[$variant] = [
                'id' => $term['id'],
                'term' => $base_term,
                'definition' => $term['definition'],
                'permalink' => $term['permalink']
            ];
        }
    }

    if (empty($variants)) {
        return ['pattern' => '', 'mapping' => []];
    }

    // Nach Länge sortieren (längste zuerst = spezifischere Matches)
    usort($variants, function($a, $b) {
        return mb_strlen($b) - mb_strlen($a);
    });

    // Pattern erstellen
    $case_flag = get_option('glossar_case_sensitive', '0') === '1' ? '' : 'i';
    $pattern = '/\b(' . implode('|', $variants) . ')\b/u' . $case_flag;

    return [
        'pattern' => $pattern,
        'mapping' => $variant_to_term
    ];
}

/**
 * Verarbeitet Content mit Glossar-Links (OPTIMIERTE VERSION)
 * Nutzt nur relevante Begriffe aus Kandidaten-Liste
 *
 * @param string $content HTML Content
 * @param array $candidates Array von Term-IDs (optional, wird aus Post-Meta geladen falls nicht angegeben)
 * @return array ['content' => string, 'terms_found' => array]
 */
function simple_clean_process_glossar_links_optimized($content, $candidates = null) {
    global $post;

    // Kandidaten laden falls nicht übergeben
    if ($candidates === null && $post) {
        $candidates = get_post_meta($post->ID, '_glossar_term_candidates', true);
    }

    // Keine Kandidaten = keine Verarbeitung nötig
    if (empty($candidates) || !is_array($candidates)) {
        return array(
            'content' => $content,
            'terms_found' => array()
        );
    }

    // Nur relevante Begriffe laden
    $relevant_terms = simple_clean_get_glossar_terms_by_ids($candidates);

    if (empty($relevant_terms)) {
        return array(
            'content' => $content,
            'terms_found' => array()
        );
    }

    // Mini-Pattern nur für diese Begriffe erstellen
    $pattern_data = simple_clean_build_glossar_pattern_for_terms($relevant_terms);

    if (empty($pattern_data['pattern'])) {
        return array(
            'content' => $content,
            'terms_found' => array()
        );
    }

    $first_only = get_option('glossar_first_only', '1') === '1';
    $case_sensitive = get_option('glossar_case_sensitive', '0') === '1';
    $linked_terms = array();
    $terms_found = array(); // Track term IDs for usage tracking

    // Split content into HTML tags and text
    $parts = preg_split('/(<[^>]+>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

    $result = '';
    $in_link = false;
    $in_script = false;

    foreach ($parts as $part) {
        // Check if this is an HTML tag
        if (preg_match('/^<[^>]+>$/', $part)) {
            // Track if we're inside a link or script tag
            if (preg_match('/^<a\b/i', $part)) {
                $in_link = true;
            } elseif (preg_match('/^<\/a>/i', $part)) {
                $in_link = false;
            } elseif (preg_match('/^<(script|style|code|pre)\b/i', $part)) {
                $in_script = true;
            } elseif (preg_match('/^<\/(script|style|code|pre)>/i', $part)) {
                $in_script = false;
            }

            $result .= $part;
            continue;
        }

        // Skip processing if we're inside a link, script, or if already a glossar term
        if ($in_link || $in_script || strpos($part, 'glossar-term') !== false) {
            $result .= $part;
            continue;
        }

        // Process this text part with optimized mini-pattern
        $processed_part = $part;

        // Use preg_replace_callback with mini-pattern
        $processed_part = preg_replace_callback(
            $pattern_data['pattern'],
            function($matches) use ($pattern_data, &$linked_terms, &$terms_found, $first_only, $case_sensitive) {
                $matched_text = $matches[1];
                $key = $matched_text; // Exact match for lookup

                // Get term data from mapping
                if (!isset($pattern_data['mapping'][$key])) {
                    return $matched_text; // No match found
                }

                $term_data = $pattern_data['mapping'][$key];
                $term_lower = mb_strtolower($term_data['term'], 'UTF-8');

                // Skip if first_only is enabled and term already linked
                if ($first_only && in_array($term_lower, $linked_terms)) {
                    return $matched_text;
                }

                // Create the glossar link
                $replacement = '<span class="glossar-term glossar-clickable" ' .
                              'data-glossar-id="' . esc_attr($term_data['id']) . '" ' .
                              'id="glossar-term-' . esc_attr($term_data['id']) . '" ' .
                              'role="button" tabindex="0" ' .
                              'aria-label="Glossar-Begriff: ' . esc_attr($term_data['term']) . '">' .
                              $matched_text . '</span>';

                // Mark term as linked if first_only is enabled
                if ($first_only) {
                    $linked_terms[] = $term_lower;
                }

                // Track term ID for usage tracking
                if (!in_array($term_data['id'], $terms_found)) {
                    $terms_found[] = $term_data['id'];
                }

                return $replacement;
            },
            $processed_part
        );

        $result .= $processed_part;
    }

    return array(
        'content' => $result,
        'terms_found' => $terms_found
    );
}

/**
 * OPTIMIERTE Auto-Link Funktion (Kandidaten-basiert)
 * Ersetzt simple_clean_glossar_auto_link_content_cached()
 *
 * Lädt nur relevante Glossarbegriffe basierend auf der Kandidaten-Liste.
 * Keine Content-Cache-Tabelle mehr nötig (funktioniert nicht mit CDB-Blöcken).
 *
 * @param string $content Post Content
 * @return string Processed Content mit Glossar-Links
 */
function simple_clean_glossar_auto_link_content_optimized($content) {
    global $post;

    // Skip if in admin, feed, or auto-linking is disabled
    if (is_admin() || is_feed() || get_option('glossar_auto_link', '1') !== '1') {
        return $content;
    }

    // Skip if this is a glossar post itself
    if (is_singular('glossar')) {
        return $content;
    }

    // Benötigen wir einen Post-Context
    if (!$post || !$post->ID) {
        return $content;
    }

    $post_id = $post->ID;

    // Kandidaten aus Post-Meta laden
    $candidates = get_post_meta($post_id, '_glossar_term_candidates', true);

    // Fallback: Keine Kandidaten gefunden
    // Dies kann passieren wenn Post noch nicht gescannt wurde
    // In diesem Fall: Kein Glossar-Linking (Post muss gespeichert werden)
    if (empty($candidates) || !is_array($candidates)) {
        // Log für Debugging
        if (defined('GLOSSAR_DEBUG') && GLOSSAR_DEBUG) {
            error_log(sprintf(
                'Glossar: Post %d hat keine Kandidaten - speichere den Post um Kandidaten zu generieren',
                $post_id
            ));
        }

        // Kein Linking ohne Kandidaten
        // User muss den Post einmal speichern damit Kandidaten generiert werden
        return $content;
    }

    // Optimierte Verarbeitung mit nur relevanten Begriffen
    $processed = simple_clean_process_glossar_links_optimized($content, $candidates);

    return $processed['content'];
}

// ===================================================================
// GLOSSAR PER-PAGE OPTIMIZATION (Phase 3: Cache Invalidierung)
// ===================================================================

/**
 * Bei Glossar-Änderung: Betroffene Posts finden und re-scannen
 * Wird ausgelöst wenn ein Glossar-Begriff gespeichert/geändert wird
 *
 * @param int $post_id Glossar Post ID
 * @param WP_Post $post Post Object
 */
function simple_clean_invalidate_affected_posts_optimized($post_id, $post) {
    // Nur für Glossar-Posts
    if ($post->post_type !== 'glossar') {
        return;
    }

    // Glossar-Version erhöhen (für Kompatibilität mit altem System)
    $version = (int) get_option('_glossar_version', 0);
    update_option('_glossar_version', $version + 1);

    // Cache löschen
    wp_cache_delete('glossar_terms', 'simple_clean_glossar');
    wp_cache_delete('glossar_combined_pattern', 'simple_clean_glossar');

    // Finde betroffene Posts (Posts die diesen Begriff als Kandidat haben)
    $term_title = mb_strtolower($post->post_title, 'UTF-8');

    // Suche nach Posts die diesen Begriff enthalten könnten
    // WICHTIG: Dies ist eine approximative Suche, da Kandidaten als serialisiertes Array gespeichert sind
    global $wpdb;

    // Hole alle Posts mit Kandidaten-Meta
    $posts_with_candidates = $wpdb->get_results(
        "SELECT post_id, meta_value
         FROM {$wpdb->postmeta}
         WHERE meta_key = '_glossar_term_candidates'",
        ARRAY_A
    );

    $affected_post_ids = [];

    foreach ($posts_with_candidates as $row) {
        $candidates = maybe_unserialize($row['meta_value']);
        if (is_array($candidates) && in_array($post_id, $candidates)) {
            $affected_post_ids[] = $row['post_id'];
        }
    }

    // Entscheide ob sofort oder async re-scannen
    if (count($affected_post_ids) > 50) {
        // Viele Posts betroffen → Async Background-Job
        wp_schedule_single_event(
            time() + 60, // 60 Sekunden Verzögerung
            'simple_clean_bulk_rescan_glossar_optimized',
            [$affected_post_ids]
        );

        // Optional: Admin-Notice
        if (is_admin()) {
            add_action('admin_notices', function() use ($affected_post_ids) {
                echo '<div class="notice notice-info is-dismissible">';
                echo '<p><strong>Glossar-Update:</strong> ' . count($affected_post_ids) . ' Posts werden im Hintergrund aktualisiert.</p>';
                echo '</div>';
            });
        }
    } else {
        // Wenige Posts → Sofort re-scannen
        foreach ($affected_post_ids as $affected_id) {
            $affected_post = get_post($affected_id);
            if ($affected_post) {
                simple_clean_update_glossar_candidates($affected_id, $affected_post);
            }
        }
    }
}
add_action('save_post_glossar', 'simple_clean_invalidate_affected_posts_optimized', 10, 2);

/**
 * Background Job: Bulk Re-Scan von Posts
 * Wird async ausgeführt via WP-Cron
 *
 * @param array $post_ids Array von Post-IDs zum re-scannen
 */
function simple_clean_do_bulk_rescan_optimized($post_ids) {
    if (empty($post_ids) || !is_array($post_ids)) {
        return;
    }

    $count = 0;
    foreach ($post_ids as $post_id) {
        $post = get_post($post_id);
        if ($post) {
            simple_clean_update_glossar_candidates($post_id, $post);
            $count++;

            // Throttle: 100ms Pause zwischen Posts (verhindert Server-Überlastung)
            if ($count % 10 === 0) {
                usleep(100000); // 100ms
            }
        }
    }

    // Log für Debugging
    error_log(sprintf(
        'Glossar Bulk Re-Scan: %d von %d Posts aktualisiert',
        $count,
        count($post_ids)
    ));
}
add_action('simple_clean_bulk_rescan_glossar_optimized', 'simple_clean_do_bulk_rescan_optimized');

/**
 * Cleanup: Entferne gelöschte Glossar-Begriffe aus Kandidaten-Listen
 *
 * @param int $post_id Gelöschter Glossar Post ID
 */
function simple_clean_cleanup_deleted_glossar_term($post_id) {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'glossar') {
        return;
    }

    // Finde alle Posts die diesen Begriff referenzieren
    global $wpdb;
    $posts_with_this_term = $wpdb->get_results(
        "SELECT post_id, meta_value
         FROM {$wpdb->postmeta}
         WHERE meta_key = '_glossar_term_candidates'",
        ARRAY_A
    );

    foreach ($posts_with_this_term as $row) {
        $candidates = maybe_unserialize($row['meta_value']);
        if (is_array($candidates) && in_array($post_id, $candidates)) {
            // Entferne diesen Begriff aus der Kandidaten-Liste
            $updated_candidates = array_diff($candidates, [$post_id]);
            update_post_meta($row['post_id'], '_glossar_term_candidates', $updated_candidates);
        }
    }

    // Cache löschen
    wp_cache_delete('glossar_terms', 'simple_clean_glossar');
}
add_action('delete_post', 'simple_clean_cleanup_deleted_glossar_term', 10, 1);

// ===================================================================
// GLOSSAR CONTENT CACHE - REMOVED
// ===================================================================
// Content cache has been removed as it doesn't work with CDB blocks.
// Using optimized per-page candidate system instead.

/**
 * Build optimized combined regex pattern for all glossar terms
 * Returns pattern and variant-to-term mapping
 * Cached for performance
 */
function simple_clean_build_glossar_pattern() {
    $cache_key = 'glossar_combined_pattern';
    $cache_group = 'simple_clean_glossar';

    $cached = wp_cache_get($cache_key, $cache_group);
    if ($cached !== false) {
        return $cached;
    }

    $terms = simple_clean_get_glossar_terms();
    if (empty($terms)) {
        return array(
            'pattern' => null,
            'variant_map' => array()
        );
    }

    $all_variants = array();
    $variant_to_term = array(); // Maps lowercase variant -> term data

    foreach ($terms as $term_data) {
        $variants = simple_clean_get_glossar_term_variants($term_data['term']);

        foreach ($variants as $variant) {
            $escaped = preg_quote($variant, '/');
            $all_variants[] = $escaped;

            // Store mapping (case-insensitive key)
            $key = mb_strtolower($variant, 'UTF-8');
            $variant_to_term[$key] = $term_data;
        }
    }

    // Remove duplicates
    $all_variants = array_unique($all_variants);

    // Sort by length (longest first) to ensure proper matching
    // e.g., "Molekülorbital" before "Molekül"
    usort($all_variants, function($a, $b) {
        return mb_strlen($b) - mb_strlen($a);
    });

    // Build combined pattern
    $pattern = '/\b(' . implode('|', $all_variants) . ')\b/iu';

    $result = array(
        'pattern' => $pattern,
        'variant_map' => $variant_to_term
    );

    wp_cache_set($cache_key, $result, $cache_group, HOUR_IN_SECONDS);

    return $result;
}

// Clear pattern cache when glossar terms are modified
add_action('save_post_glossar', function($post_id, $post, $update) {
    wp_cache_delete('glossar_combined_pattern', 'simple_clean_glossar');
}, 10, 3);

/**
 * Process content and add glossar links using optimized pattern matching
 * Returns processed content and list of linked term IDs
 */
function simple_clean_process_glossar_links($content) {
    $pattern_data = simple_clean_build_glossar_pattern();

    if (!$pattern_data['pattern']) {
        return array(
            'content' => $content,
            'terms_found' => array()
        );
    }

    $first_only = get_option('glossar_first_only', '1') === '1';
    $case_sensitive = get_option('glossar_case_sensitive', '0') === '1';
    $linked_terms = array();
    $terms_found = array(); // Track term IDs for usage tracking

    // Split content into HTML tags and text
    $parts = preg_split('/(<[^>]+>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE);

    $result = '';
    $in_link = false;
    $in_script = false;

    foreach ($parts as $part) {
        // Check if this is an HTML tag
        if (preg_match('/^<[^>]+>$/', $part)) {
            // Track if we're inside a link or script tag
            if (preg_match('/^<a\b/i', $part)) {
                $in_link = true;
            } elseif (preg_match('/^<\/a>/i', $part)) {
                $in_link = false;
            } elseif (preg_match('/^<(script|style|code|pre)\b/i', $part)) {
                $in_script = true;
            } elseif (preg_match('/^<\/(script|style|code|pre)>/i', $part)) {
                $in_script = false;
            }

            $result .= $part;
            continue;
        }

        // Skip processing if we're inside a link, script, or if already a glossar term
        if ($in_link || $in_script || strpos($part, 'glossar-term') !== false) {
            $result .= $part;
            continue;
        }

        // Process this text part with optimized pattern
        $processed_part = $part;

        // Use preg_replace_callback with combined pattern
        $processed_part = preg_replace_callback(
            $pattern_data['pattern'],
            function($matches) use ($pattern_data, &$linked_terms, &$terms_found, $first_only, $case_sensitive) {
                $matched_text = $matches[1];
                $key = $case_sensitive ? $matched_text : mb_strtolower($matched_text, 'UTF-8');

                // Get term data from variant map
                if (!isset($pattern_data['variant_map'][$key])) {
                    return $matched_text; // No match found, shouldn't happen
                }

                $term_data = $pattern_data['variant_map'][$key];
                $term_lower = strtolower($term_data['term']);

                // Skip if first_only is enabled and term already linked
                if ($first_only && in_array($term_lower, $linked_terms)) {
                    return $matched_text;
                }

                // Create the glossar link
                $replacement = '<span class="glossar-term glossar-clickable" ' .
                              'data-glossar-id="' . esc_attr($term_data['id']) . '" ' .
                              'id="glossar-term-' . esc_attr($term_data['id']) . '" ' .
                              'role="button" tabindex="0" ' .
                              'aria-label="Glossar-Begriff: ' . esc_attr($term_data['term']) . '">' .
                              $matched_text . '</span>';

                // Mark term as linked if first_only is enabled
                if ($first_only) {
                    $linked_terms[] = $term_lower;
                }

                // Track term ID for usage tracking
                if (!in_array($term_data['id'], $terms_found)) {
                    $terms_found[] = $term_data['id'];
                }

                return $replacement;
            },
            $processed_part
        );

        $result .= $processed_part;
    }

    return array(
        'content' => $result,
        'terms_found' => $terms_found
    );
}

// Old content cache functions removed - using optimized per-page system instead

// ===================================================================
// GLOSSAR USAGE TRACKING (Wo wird Begriff verwendet)
// ===================================================================

/**
 * Generate variants of a glossar term (German declensions)
 * Matches the logic from glossar.js frontend
 */
function simple_clean_get_glossar_term_variants($term) {
    $variants = array($term);

    // Check if multi-word term
    $words = preg_split('/\s+/', trim($term));

    if (count($words) > 1) {
        // Multi-word term: handle adjective + noun combinations
        $noun = end($words);
        $adjective = $words[0];

        $noun_variants = simple_clean_get_noun_variants($noun);
        $adjective_variants = simple_clean_get_adjective_variants($adjective);

        // Combine adjective and noun variants
        foreach ($adjective_variants as $adj) {
            foreach ($noun_variants as $noun_var) {
                if (count($words) === 2) {
                    $variants[] = $adj . ' ' . $noun_var;
                } else {
                    // Keep middle words unchanged
                    $middle = implode(' ', array_slice($words, 1, -1));
                    $variants[] = $adj . ' ' . $middle . ' ' . $noun_var;
                }
            }
        }
    } else {
        // Single word: apply noun variant generation
        $noun_variants = simple_clean_get_noun_variants($term);
        $variants = array_merge($variants, $noun_variants);
    }

    return array_unique($variants);
}

/**
 * Generate noun variants (German noun inflections)
 */
function simple_clean_get_noun_variants($noun) {
    $variants = array($noun);

    // German noun inflections
    if (mb_substr($noun, -1) === 'e') {
        // Words ending in -e: Atome -> Atomen (Dativ Plural)
        $variants[] = $noun . 'n';
        $variants[] = $noun . 's';
    } elseif (mb_substr($noun, -2) === 'er') {
        // Words ending in -er
        $variants[] = $noun . 's';
        $variants[] = $noun . 'n';
    } elseif (mb_substr($noun, -2) === 'el') {
        // Words ending in -el
        $variants[] = $noun . 's';
        $variants[] = $noun . 'n';
    } elseif (mb_substr($noun, -2) === 'en') {
        // Words ending in -en
        $variants[] = $noun . 's';
    } else {
        // Standard endings for most nouns
        $variants[] = $noun . 'e';   // Plural: System -> Systeme
        $variants[] = $noun . 's';   // Genitiv: Systems
        $variants[] = $noun . 'es';  // Genitiv: Systemes
        $variants[] = $noun . 'en';  // Dativ/Akkusativ Plural: Systemen
    }

    // Umlaut variants for plurals
    $umlaut_variants = simple_clean_add_umlaut_variants($noun);
    foreach ($umlaut_variants as $variant) {
        $variants[] = $variant;
        $variants[] = $variant . 'e';
        $variants[] = $variant . 'en';
        $variants[] = $variant . 's';
        $variants[] = $variant . 'es';
    }

    return $variants;
}

/**
 * Generate adjective variants (German adjective declension)
 */
function simple_clean_get_adjective_variants($adjective) {
    $variants = array($adjective);

    // Remove existing ending if present
    $stem = $adjective;
    $common_endings = array('es', 'er', 'en', 'em', 'e');

    foreach ($common_endings as $ending) {
        if (mb_strlen($adjective) > mb_strlen($ending) + 2 && mb_substr($adjective, -mb_strlen($ending)) === $ending) {
            $stem = mb_substr($adjective, 0, -mb_strlen($ending));
            break;
        }
    }

    // Generate all common declension forms
    $endings = array('e', 'es', 'er', 'en', 'em');
    foreach ($endings as $ending) {
        $variants[] = $stem . $ending;
    }

    // Also include the stem without ending
    $variants[] = $stem;

    return $variants;
}

/**
 * Generate umlaut variants for common German plural patterns
 */
function simple_clean_add_umlaut_variants($term) {
    $variants = array();

    // Only add umlaut variants if term contains a, o, u, or au
    if (!preg_match('/[aouäöü]|au/', $term)) {
        return $variants;
    }

    // Common patterns: a -> ä, o -> ö, u -> ü, au -> äu
    $umlaut_map = array(
        'a' => 'ä',
        'o' => 'ö',
        'u' => 'ü',
        'au' => 'äu'
    );

    // Try replacing vowels with umlauts (only once per word)
    foreach ($umlaut_map as $vowel => $umlaut) {
        // Find last occurrence of vowel
        $last_pos = mb_strrpos($term, $vowel);

        if ($last_pos !== false) {
            $with_umlaut = mb_substr($term, 0, $last_pos) . $umlaut . mb_substr($term, $last_pos + mb_strlen($vowel));

            // Only add if different from original
            if ($with_umlaut !== $term) {
                $variants[] = $with_umlaut;
            }
        }
    }

    return $variants;
}

/**
 * Track glossar terms used in post content
 * This runs when a post/page is viewed and stores which terms are used
 * IMPROVED: Now uses the same term variant logic as frontend JavaScript
 */
function simple_clean_track_glossar_usage($post_id = null) {
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    if (!$post_id || get_post_type($post_id) === 'glossar') {
        return; // Don't track usage in glossar posts themselves
    }

    $content = get_post_field('post_content', $post_id);
    if (empty($content)) {
        return;
    }

    // Get all glossar terms
    $glossar_terms = simple_clean_get_glossar_terms();
    if (empty($glossar_terms)) {
        return;
    }

    // Sort terms by word count (longest first) to match multi-word terms first
    usort($glossar_terms, function($a, $b) {
        $a_words = count(explode(' ', trim($a['term'])));
        $b_words = count(explode(' ', trim($b['term'])));
        return $b_words - $a_words;
    });

    $used_terms = array();

    // Check which terms appear in the content
    foreach ($glossar_terms as $term_data) {
        $term = $term_data['term'];
        $term_id = $term_data['id'];

        // IMPROVED: Generate all variants (like frontend JavaScript)
        $variants = simple_clean_get_glossar_term_variants($term);

        $found = false;
        foreach ($variants as $variant) {
            // Case-insensitive search with word boundaries
            // Using negative lookbehind/lookahead to support terms with special characters (e.g., parentheses)
            // (?<!\w) = no word character before
            // (?!\w) = no word character after
            $pattern = '/(?<!\w)' . preg_quote($variant, '/') . '(?!\w)/iu';

            if (preg_match($pattern, $content)) {
                $used_terms[] = $term_id;
                $found = true;
                break; // Found a match, no need to check other variants
            }
        }
    }

    // Store used terms as post meta
    if (!empty($used_terms)) {
        update_post_meta($post_id, '_glossar_terms_used', array_unique($used_terms));
    } else {
        delete_post_meta($post_id, '_glossar_terms_used');
    }
}

// Track usage when post is saved
add_action('save_post', 'simple_clean_track_glossar_usage', 20, 1);

// Track usage when post is viewed (with caching to avoid repeated processing)
add_action('wp', function() {
    if (is_singular() && !is_admin()) {
        $post_id = get_the_ID();
        $last_tracked = get_post_meta($post_id, '_glossar_last_tracked', true);
        $post_modified = get_post_modified_time('U', false, $post_id);
        $last_glossar_change = get_option('_glossar_last_change', 0);

        // Re-track if:
        // 1. Never tracked before, OR
        // 2. Post was modified since last tracking, OR
        // 3. A glossar term was added/modified since last tracking
        if (empty($last_tracked) || $post_modified > $last_tracked || $last_glossar_change > $last_tracked) {
            simple_clean_track_glossar_usage($post_id);
            update_post_meta($post_id, '_glossar_last_tracked', time());
        }
    }
});

/**
 * When a glossar term is published or updated, trigger re-tracking of all pages
 * This ensures new terms are immediately detected across the site
 */
add_action('save_post_glossar', function($post_id, $post, $update) {
    // Only trigger for published posts (not drafts)
    if ($post->post_status !== 'publish') {
        return;
    }

    // Skip autosaves and revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Update timestamp to trigger re-tracking on next page view (lazy rebuild)
    update_option('_glossar_last_change', time());

    // Check if auto-rebuild is enabled in settings
    $auto_rebuild = get_option('glossar_auto_rebuild', '0');

    if ($auto_rebuild === '1') {
        // Immediately rebuild all tracking (can take a few seconds)
        simple_clean_rebuild_usage_tracking();

        // Set a transient to show success message
        set_transient('glossar_auto_rebuild_done', true, 30);
    }
}, 10, 3);

/**
 * Get all posts/pages that use a specific glossar term
 *
 * @param int $term_id The glossar term ID
 * @return array Array of post objects
 */
function simple_clean_get_term_usage($term_id) {
    global $wpdb;

    // Query all posts that have this term ID in their _glossar_terms_used meta
    $query = "
        SELECT DISTINCT p.ID, p.post_title, p.post_type, p.post_date
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE pm.meta_key = '_glossar_terms_used'
        AND p.post_status = 'publish'
        AND p.post_type IN ('post', 'page')
        ORDER BY p.post_date DESC
    ";

    $results = $wpdb->get_results($query);

    // Filter results to only include posts where our term_id is in the array
    $matching_posts = array();
    foreach ($results as $post) {
        $used_terms = get_post_meta($post->ID, '_glossar_terms_used', true);
        if (is_array($used_terms) && in_array($term_id, $used_terms)) {
            $matching_posts[] = $post;
        }
    }

    return $matching_posts;
}

/**
 * Rebuild usage tracking for all posts/pages
 * Useful after bulk changes or initial setup
 */
function simple_clean_rebuild_usage_tracking() {
    $args = array(
        'post_type' => array('post', 'page'),
        'post_status' => 'publish',
        'posts_per_page' => -1,
    );

    $posts = get_posts($args);
    $count = 0;

    foreach ($posts as $post) {
        simple_clean_track_glossar_usage($post->ID);
        $count++;
    }

    return $count;
}

// Enqueue Block Editor Assets
function simple_clean_glossar_editor_assets() {
    $editor_js = get_template_directory() . '/dist/js/glossar-editor.js';
    if (file_exists($editor_js)) {
        wp_enqueue_script(
            'simple-clean-glossar-editor',
            get_template_directory_uri() . '/dist/js/glossar-editor.js',
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-rich-text', 'wp-api-fetch'),
            filemtime($editor_js),
            true
        );

        // Pass data to JavaScript
        wp_localize_script('simple-clean-glossar-editor', 'glossarEditorData', array(
            'restUrl' => rest_url('simple-clean/v1/'),
            'nonce' => wp_create_nonce('wp_rest'),
            'categories' => simple_clean_get_glossar_categories(),
        ));
    }
}
add_action('enqueue_block_editor_assets', 'simple_clean_glossar_editor_assets');

// Prevent duplicate glossar terms in admin
function simple_clean_prevent_duplicate_glossar_terms($post_id, $post, $update) {
    // Only check for glossar post type
    if ($post->post_type !== 'glossar') {
        return;
    }

    // Skip for autosaves and revisions
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Skip if user doesn't have permission
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Check for duplicate title (case-insensitive)
    global $wpdb;
    $title = $post->post_title;

    // Find existing posts with same title (excluding current post)
    $duplicate_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $wpdb->posts
         WHERE post_type = 'glossar'
         AND post_status IN ('publish', 'draft', 'pending')
         AND ID != %d
         AND LOWER(post_title) = LOWER(%s)",
        $post_id,
        $title
    ));

    if ($duplicate_count > 0) {
        // Remove the action to prevent infinite loop
        remove_action('save_post', 'simple_clean_prevent_duplicate_glossar_terms', 10);

        // Change post status to draft
        wp_update_post(array(
            'ID' => $post_id,
            'post_status' => 'draft'
        ));

        // Add the action back
        add_action('save_post', 'simple_clean_prevent_duplicate_glossar_terms', 10, 3);

        // Set admin notice
        set_transient('glossar_duplicate_error_' . get_current_user_id(), $title, 30);
    }
}
add_action('save_post', 'simple_clean_prevent_duplicate_glossar_terms', 10, 3);

// Display admin notice for duplicate glossar terms
function simple_clean_glossar_duplicate_notice() {
    $user_id = get_current_user_id();
    $duplicate_title = get_transient('glossar_duplicate_error_' . $user_id);

    if ($duplicate_title) {
        delete_transient('glossar_duplicate_error_' . $user_id);
        ?>
        <div class="notice notice-error is-dismissible">
            <p>
                <strong>❌ Glossar-Begriff existiert bereits!</strong><br>
                Der Begriff "<strong><?php echo esc_html($duplicate_title); ?></strong>" ist bereits im Glossar vorhanden.
                Dieser Eintrag wurde als Entwurf gespeichert. Bitte wählen Sie einen anderen Begriff oder bearbeiten Sie den bestehenden Eintrag.
            </p>
        </div>
        <?php
    }

    // Show notice when auto-rebuild was performed
    if (get_transient('glossar_auto_rebuild_done')) {
        delete_transient('glossar_auto_rebuild_done');
        ?>
        <div class="notice notice-success is-dismissible">
            <p>
                <strong>✓ Glossar-Begriff gespeichert!</strong><br>
                Alle Seiten wurden automatisch analysiert. Die Verwendung des Begriffs ist jetzt sofort sichtbar.
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'simple_clean_glossar_duplicate_notice');

// Register Glossar Taxonomy (Categories)
function simple_clean_register_glossar_taxonomy() {
    $labels = array(
        'name'              => 'Glossar-Kategorien',
        'singular_name'     => 'Kategorie',
        'search_items'      => 'Kategorien durchsuchen',
        'all_items'         => 'Alle Kategorien',
        'edit_item'         => 'Kategorie bearbeiten',
        'update_item'       => 'Kategorie aktualisieren',
        'add_new_item'      => 'Neue Kategorie hinzufügen',
        'new_item_name'     => 'Neuer Kategoriename',
        'menu_name'         => 'Kategorien',
    );

    register_taxonomy('glossar_category', array('glossar'), array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_in_rest'      => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'glossar-kategorie'),
    ));
}
add_action('init', 'simple_clean_register_glossar_taxonomy');

// Get glossar categories
function simple_clean_get_glossar_categories() {
    $terms = get_terms(array(
        'taxonomy' => 'glossar_category',
        'hide_empty' => false,
    ));

    $categories = array();
    if (!is_wp_error($terms)) {
        foreach ($terms as $term) {
            $categories[] = array(
                'id' => $term->term_id,
                'name' => $term->name,
                'slug' => $term->slug,
            );
        }
    }

    return $categories;
}

// REST API Endpoint for creating glossary terms
function simple_clean_register_glossar_rest_routes() {
    register_rest_route('simple-clean/v1', '/glossar', array(
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'simple_clean_create_glossar_term',
        'permission_callback' => function() {
            return current_user_can('edit_posts');
        },
        'args' => array(
            'title' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
            ),
            'definition' => array(
                'required' => true,
                'type' => 'string',
                'sanitize_callback' => 'wp_kses_post',
            ),
            'category' => array(
                'required' => false,
                'type' => 'integer',
            ),
            'tags' => array(
                'required' => false,
                'type' => 'string',
            ),
            'links' => array(
                'required' => false,
                'type' => 'string',
            ),
        ),
    ));
}
add_action('rest_api_init', 'simple_clean_register_glossar_rest_routes');

// Create glossar term via REST API
function simple_clean_create_glossar_term($request) {
    // Get parameters (already validated and sanitized by REST API args schema)
    $title = $request->get_param('title');
    $definition = $request->get_param('definition');
    $category = $request->get_param('category');
    $tags = $request->get_param('tags');
    $links = $request->get_param('links');

    // Check if term already exists (prevent duplicates)
    $existing_term = get_page_by_title($title, OBJECT, 'glossar');
    if ($existing_term) {
        return new WP_Error(
            'duplicate_term',
            sprintf('Der Begriff "%s" existiert bereits im Glossar.', $title),
            array('status' => 409) // 409 Conflict
        );
    }

    // Create the post
    $post_data = array(
        'post_title'   => $title,
        'post_content' => $definition,
        'post_type'    => 'glossar',
        'post_status'  => 'publish',
    );

    $post_id = wp_insert_post($post_data);

    if (is_wp_error($post_id)) {
        return new WP_Error('creation_failed', 'Fehler beim Erstellen des Glossar-Eintrags.', array('status' => 500));
    }

    // Add category if provided
    if (!empty($category)) {
        wp_set_object_terms($post_id, intval($category), 'glossar_category');
    }

    // Add tags if provided
    if (!empty($tags)) {
        $tags_array = array_map('trim', explode(',', $tags));
        wp_set_post_tags($post_id, $tags_array);
    }

    // Add custom meta for links if provided
    if (!empty($links)) {
        update_post_meta($post_id, '_glossar_links', $links);
    }

    // Return success response
    return rest_ensure_response(array(
        'success' => true,
        'id' => $post_id,
        'title' => get_the_title($post_id),
        'permalink' => get_permalink($post_id),
        'message' => 'Glossar-Eintrag erfolgreich erstellt!',
    ));
}

/**
 * =============================================================================
 * WEBSITE PASSWORD PROTECTION
 * =============================================================================
 * Protects the entire website with a password unless user is logged in.
 * Prevents copyright violations by requiring authentication or password entry.
 */

/**
 * Add admin menu for password protection settings
 */
function simple_clean_password_protection_menu() {
    add_options_page(
        'Website-Passwortschutz',
        'Passwortschutz',
        'manage_options',
        'website-password-protection',
        'simple_clean_password_protection_page'
    );
}
add_action('admin_menu', 'simple_clean_password_protection_menu');

/**
 * Render the password protection settings page
 */
function simple_clean_password_protection_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        wp_die('Sie haben keine Berechtigung, auf diese Seite zuzugreifen.');
    }

    // Save settings
    if (isset($_POST['password_protection_save']) && check_admin_referer('password_protection_settings', 'password_protection_nonce')) {
        $enabled = isset($_POST['password_protection_enabled']) ? '1' : '0';
        $password = sanitize_text_field($_POST['password_protection_password']);

        // Save settings
        update_option('simple_clean_password_protection_enabled', $enabled);

        // Only update password if a new one is provided
        if (!empty($password)) {
            // Hash the password for security
            update_option('simple_clean_password_protection_password', wp_hash_password($password));
            echo '<div class="notice notice-success"><p><strong>Einstellungen gespeichert!</strong> Passwort wurde aktualisiert.</p></div>';
        } else {
            echo '<div class="notice notice-success"><p><strong>Einstellungen gespeichert!</strong></p></div>';
        }
    }

    // Get current settings
    $enabled = get_option('simple_clean_password_protection_enabled', '0');
    $has_password = !empty(get_option('simple_clean_password_protection_password'));

    ?>
    <div class="wrap">
        <h1>🔒 Website-Passwortschutz</h1>

        <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin: 20px 0; border-left: 4px solid #2271b1;">
            <h2 style="margin-top: 0;">ℹ️ Funktionsweise</h2>
            <p>Wenn der Passwortschutz aktiviert ist:</p>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><strong>Eingeloggte Benutzer:</strong> Können die Website ohne Passwort-Eingabe nutzen</li>
                <li><strong>Nicht-eingeloggte Besucher:</strong> Müssen das Passwort eingeben oder sich anmelden</li>
                <li><strong>Alle Seiten geschützt:</strong> Das Passwort wird bei JEDER Seite der Website abgefragt</li>
                <li><strong>Copyright-Schutz:</strong> Verhindert unbefugten Zugriff auf geschützte Inhalte</li>
            </ul>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('password_protection_settings', 'password_protection_nonce'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">Passwortschutz aktivieren</th>
                    <td>
                        <label>
                            <input type="checkbox" name="password_protection_enabled" value="1" <?php checked($enabled, '1'); ?>>
                            Website mit Passwort schützen
                        </label>
                        <?php if ($enabled === '1'): ?>
                            <p class="description" style="color: #d63638; font-weight: 600;">
                                ⚠️ <strong>Passwortschutz ist AKTIV</strong> - Besucher benötigen das Passwort oder müssen angemeldet sein.
                            </p>
                        <?php else: ?>
                            <p class="description">
                                Wenn aktiviert, müssen Besucher das Passwort eingeben oder sich anmelden.
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="password_protection_password">Website-Passwort</label>
                    </th>
                    <td>
                        <input
                            type="text"
                            id="password_protection_password"
                            name="password_protection_password"
                            class="regular-text"
                            placeholder="<?php echo $has_password ? 'Aktuelles Passwort beibehalten' : 'Neues Passwort eingeben'; ?>"
                            autocomplete="off"
                        >
                        <?php if ($has_password): ?>
                            <p class="description">
                                ✅ Ein Passwort ist gesetzt. Lassen Sie dieses Feld leer, um das aktuelle Passwort beizubehalten.
                            </p>
                        <?php else: ?>
                            <p class="description">
                                ⚠️ Kein Passwort gesetzt. Bitte ein Passwort eingeben, bevor Sie den Schutz aktivieren.
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="password_protection_save" class="button button-primary">
                    💾 Einstellungen speichern
                </button>
            </p>
        </form>

        <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin: 20px 0; border-left: 4px solid #d63638;">
            <h2 style="margin-top: 0;">⚠️ Wichtige Hinweise</h2>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><strong>Sicherheit:</strong> Das Passwort wird verschlüsselt gespeichert.</li>
                <li><strong>Cookies:</strong> Nach erfolgreicher Passwort-Eingabe wird ein Cookie gesetzt (gültig für 30 Tage).</li>
                <li><strong>Admin-Zugang:</strong> Administratoren können sich jederzeit über /wp-admin anmelden.</li>
                <li><strong>Session:</strong> Besucher müssen das Passwort auf jedem Gerät/Browser einzeln eingeben.</li>
                <li><strong>Logout:</strong> Cookie wird automatisch nach 30 Tagen gelöscht oder bei Browser-Neustart (je nach Browser-Einstellung).</li>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Check if password protection is enabled
 */
function simple_clean_is_password_protection_enabled() {
    return get_option('simple_clean_password_protection_enabled', '0') === '1';
}

/**
 * Check if user has valid access (logged in OR correct password entered)
 */
function simple_clean_has_valid_access() {
    // Logged-in users always have access
    if (is_user_logged_in()) {
        return true;
    }

    // Check if password cookie is set and valid
    if (isset($_COOKIE['simple_clean_password_granted'])) {
        $cookie_value = $_COOKIE['simple_clean_password_granted'];
        $stored_hash = get_option('simple_clean_password_protection_password');

        // Verify cookie matches current password hash
        if (wp_check_password($cookie_value, $stored_hash)) {
            return true;
        }
    }

    return false;
}

/**
 * Handle password form submission
 */
function simple_clean_handle_password_submission() {
    if (!isset($_POST['website_password_submit']) || !isset($_POST['website_password'])) {
        return false;
    }

    // Verify nonce
    if (!isset($_POST['website_password_nonce']) || !wp_verify_nonce($_POST['website_password_nonce'], 'website_password_check')) {
        return false;
    }

    $submitted_password = $_POST['website_password'];
    $stored_hash = get_option('simple_clean_password_protection_password');

    // Check password
    if (wp_check_password($submitted_password, $stored_hash)) {
        // Set cookie for 30 days
        setcookie(
            'simple_clean_password_granted',
            $submitted_password, // Store plain password for cookie verification
            time() + (30 * DAY_IN_SECONDS),
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true // HTTP only
        );

        return true;
    }

    return false;
}

/**
 * Template redirect - check password protection
 */
function simple_clean_password_protection_check() {
    // Skip if password protection is disabled
    if (!simple_clean_is_password_protection_enabled()) {
        return;
    }

    // Skip for admin pages
    if (is_admin()) {
        return;
    }

    // Skip for login/register pages
    if (in_array($GLOBALS['pagenow'], array('wp-login.php', 'wp-register.php'))) {
        return;
    }

    // Handle password form submission
    if (simple_clean_handle_password_submission()) {
        // Password correct - reload page to show content
        wp_redirect($_SERVER['REQUEST_URI']);
        exit;
    }

    // Check if user has valid access
    if (simple_clean_has_valid_access()) {
        return;
    }

    // No valid access - show password form
    simple_clean_show_password_form();
    exit;
}
add_action('template_redirect', 'simple_clean_password_protection_check');

/**
 * Display password protection form
 */
function simple_clean_show_password_form() {
    $error = isset($_POST['website_password_submit']) ? true : false;
    $site_name = get_bloginfo('name');
    $login_url = wp_login_url($_SERVER['REQUEST_URI']);

    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Passwortgeschützt - <?php echo esc_html($site_name); ?></title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .password-container {
                background: white;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                padding: 40px;
                max-width: 450px;
                width: 100%;
                animation: slideUp 0.4s ease-out;
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .lock-icon {
                font-size: 64px;
                text-align: center;
                margin-bottom: 20px;
                animation: lockPulse 2s ease-in-out infinite;
            }

            @keyframes lockPulse {
                0%, 100% {
                    transform: scale(1);
                }
                50% {
                    transform: scale(1.05);
                }
            }

            h1 {
                font-size: 28px;
                color: #333;
                text-align: center;
                margin-bottom: 10px;
            }

            .subtitle {
                text-align: center;
                color: #666;
                margin-bottom: 30px;
                font-size: 14px;
            }

            .info-box {
                background: #f0f4ff;
                border-left: 4px solid #667eea;
                padding: 15px;
                margin-bottom: 25px;
                border-radius: 4px;
                font-size: 14px;
                color: #555;
            }

            .info-box strong {
                color: #333;
            }

            .error-message {
                background: #fee;
                border-left: 4px solid #d63638;
                padding: 15px;
                margin-bottom: 20px;
                border-radius: 4px;
                color: #d63638;
                font-weight: 600;
                animation: shake 0.4s ease-in-out;
            }

            @keyframes shake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-10px); }
                75% { transform: translateX(10px); }
            }

            .form-group {
                margin-bottom: 20px;
            }

            label {
                display: block;
                margin-bottom: 8px;
                color: #333;
                font-weight: 600;
                font-size: 14px;
            }

            input[type="password"] {
                width: 100%;
                padding: 14px 16px;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                font-size: 16px;
                transition: all 0.3s ease;
            }

            input[type="password"]:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }

            .submit-button {
                width: 100%;
                padding: 14px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                margin-bottom: 15px;
            }

            .submit-button:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            }

            .submit-button:active {
                transform: translateY(0);
            }

            .divider {
                text-align: center;
                margin: 20px 0;
                position: relative;
                color: #999;
                font-size: 14px;
            }

            .divider::before,
            .divider::after {
                content: '';
                position: absolute;
                top: 50%;
                width: 40%;
                height: 1px;
                background: #e0e0e0;
            }

            .divider::before {
                left: 0;
            }

            .divider::after {
                right: 0;
            }

            .login-link {
                display: block;
                text-align: center;
                padding: 12px;
                background: #f8f9fa;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                color: #667eea;
                text-decoration: none;
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .login-link:hover {
                background: #e9ecef;
                border-color: #667eea;
            }

            .footer-note {
                text-align: center;
                margin-top: 25px;
                padding-top: 20px;
                border-top: 1px solid #e0e0e0;
                color: #999;
                font-size: 12px;
            }

            @media (max-width: 480px) {
                .password-container {
                    padding: 30px 20px;
                }

                h1 {
                    font-size: 24px;
                }

                .lock-icon {
                    font-size: 48px;
                }
            }
        </style>
    </head>
    <body>
        <div class="password-container">
            <div class="lock-icon">🔒</div>
            <h1>Geschützter Bereich</h1>
            <p class="subtitle"><?php echo esc_html($site_name); ?></p>

            <div class="info-box">
                <strong>📋 Diese Website ist passwortgeschützt.</strong><br>
                Bitte geben Sie das Passwort ein oder melden Sie sich an, um auf die Inhalte zuzugreifen.
            </div>

            <?php if ($error): ?>
                <div class="error-message">
                    ❌ <strong>Falsches Passwort!</strong> Bitte versuchen Sie es erneut.
                </div>
            <?php endif; ?>

            <form method="post">
                <?php wp_nonce_field('website_password_check', 'website_password_nonce'); ?>

                <div class="form-group">
                    <label for="website_password">🔑 Passwort eingeben</label>
                    <input
                        type="password"
                        id="website_password"
                        name="website_password"
                        placeholder="Passwort..."
                        required
                        autofocus
                    >
                </div>

                <button type="submit" name="website_password_submit" class="submit-button">
                    🚀 Zugang erhalten
                </button>
            </form>

            <div class="divider">ODER</div>

            <a href="<?php echo esc_url($login_url); ?>" class="login-link">
                👤 Als Benutzer anmelden
            </a>

            <div class="footer-note">
                🔐 Copyright-geschützte Inhalte<br>
                Zugriff nur für autorisierte Personen
            </div>
        </div>
    </body>
    </html>
    <?php
}

/**
 * =============================================================================
 * AI CRAWLER PROTECTION
 * =============================================================================
 * Protects the website from AI crawlers and automated bots.
 * Includes robots.txt generation and User-Agent blocking.
 */

/**
 * Add AI protection settings to the password protection page
 */
function simple_clean_ai_protection_settings_section() {
    // Check if we're on the password protection page
    if (!isset($_GET['page']) || $_GET['page'] !== 'website-password-protection') {
        return;
    }

    // Save AI protection settings
    if (isset($_POST['ai_protection_save']) && check_admin_referer('ai_protection_settings', 'ai_protection_nonce')) {
        $enabled = isset($_POST['ai_protection_enabled']) ? '1' : '0';
        $block_user_agents = isset($_POST['ai_protection_block_user_agents']) ? '1' : '0';

        update_option('simple_clean_ai_protection_enabled', $enabled);
        update_option('simple_clean_ai_protection_block_user_agents', $block_user_agents);

        echo '<div class="notice notice-success"><p><strong>AI-Schutz-Einstellungen gespeichert!</strong></p></div>';
    }

    // Get current settings
    $enabled = get_option('simple_clean_ai_protection_enabled', '0');
    $block_user_agents = get_option('simple_clean_ai_protection_block_user_agents', '0');

    ?>
    <div class="wrap" style="margin-top: 40px;">
        <h1>🤖 AI-Crawler Schutz</h1>

        <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin: 20px 0; border-left: 4px solid #2271b1;">
            <h2 style="margin-top: 0;">ℹ️ Was macht der AI-Schutz?</h2>
            <p>Schützt die Website vor KI-Crawlern, die Inhalte für Training von AI-Modellen sammeln:</p>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><strong>robots.txt:</strong> Weist ehrliche AI-Crawler höflich ab (GPTBot, Claude-Web, etc.)</li>
                <li><strong>User-Agent Blocking:</strong> Blockiert bekannte AI-Crawler programmatisch (403 Fehler)</li>
                <li><strong>Standard-Suchmaschinen:</strong> Google, Bing, etc. bleiben erlaubt (wichtig für SEO)</li>
                <li><strong>Kombiniert mit Passwortschutz:</strong> Maximaler Schutz vor unbefugtem Zugriff</li>
            </ul>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('ai_protection_settings', 'ai_protection_nonce'); ?>

            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">robots.txt AI-Blocking</th>
                    <td>
                        <label>
                            <input type="checkbox" name="ai_protection_enabled" value="1" <?php checked($enabled, '1'); ?>>
                            AI-Crawler in robots.txt blockieren
                        </label>
                        <?php if ($enabled === '1'): ?>
                            <p class="description" style="color: #00a32a; font-weight: 600;">
                                ✅ <strong>robots.txt AI-Blocking ist AKTIV</strong> - Ehrliche AI-Crawler werden abgewiesen.
                            </p>
                        <?php else: ?>
                            <p class="description">
                                Fügt Regeln zu robots.txt hinzu, die AI-Crawler blockieren (GPTBot, Claude-Web, etc.).
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>

                <tr>
                    <th scope="row">User-Agent Blocking (Server-seitig)</th>
                    <td>
                        <label>
                            <input type="checkbox" name="ai_protection_block_user_agents" value="1" <?php checked($block_user_agents, '1'); ?>>
                            Bekannte AI-Crawler programmatisch blockieren (403 Fehler)
                        </label>
                        <?php if ($block_user_agents === '1'): ?>
                            <p class="description" style="color: #00a32a; font-weight: 600;">
                                ✅ <strong>User-Agent Blocking ist AKTIV</strong> - AI-Crawler erhalten 403 Forbidden.
                            </p>
                        <?php else: ?>
                            <p class="description">
                                Blockiert AI-Crawler auf Server-Ebene, auch wenn sie robots.txt ignorieren.
                            </p>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <p class="submit">
                <button type="submit" name="ai_protection_save" class="button button-primary">
                    💾 AI-Schutz speichern
                </button>
            </p>
        </form>

        <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin: 20px 0; border-left: 4px solid #d63638;">
            <h2 style="margin-top: 0;">🛡️ Blockierte AI-Crawler</h2>
            <p><strong>Die folgenden AI-Crawler werden blockiert, wenn der Schutz aktiviert ist:</strong></p>
            <ul style="list-style: disc; margin-left: 20px; columns: 2; column-gap: 40px;">
                <li>GPTBot (OpenAI ChatGPT)</li>
                <li>ChatGPT-User (OpenAI)</li>
                <li>Claude-Web (Anthropic)</li>
                <li>anthropic-ai (Anthropic)</li>
                <li>Google-Extended (Google Bard/Gemini)</li>
                <li>PerplexityBot (Perplexity AI)</li>
                <li>CCBot (Common Crawl)</li>
                <li>FacebookBot (Meta AI)</li>
                <li>Meta-ExternalAgent (Meta AI)</li>
                <li>Applebot-Extended (Apple AI)</li>
                <li>Bytespider (TikTok/Bytedance)</li>
                <li>Amazonbot (Amazon AI)</li>
                <li>cohere-ai (Cohere)</li>
                <li>Diffbot (Diffbot)</li>
                <li>Omgilibot / Omgili</li>
                <li>ai2bot (Allen Institute)</li>
                <li>Scrapy, curl, wget, python-requests</li>
            </ul>
            <p style="margin-top: 15px;"><strong>Standard-Suchmaschinen bleiben ERLAUBT:</strong> Googlebot, Bingbot, DuckDuckBot, Yahoo Slurp (wichtig für SEO)</p>
        </div>

        <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin: 20px 0; border-left: 4px solid #d63638;">
            <h2 style="margin-top: 0;">⚠️ Wichtige Hinweise</h2>
            <ul style="list-style: disc; margin-left: 20px;">
                <li><strong>robots.txt:</strong> Nur ehrliche Crawler respektieren diese Regeln. Manche Bots ignorieren robots.txt.</li>
                <li><strong>User-Agent Blocking:</strong> Schwieriger zu umgehen, aber User-Agents können theoretisch gefälscht werden.</li>
                <li><strong>Kombination empfohlen:</strong> Aktiviere BEIDE Optionen für maximalen Schutz.</li>
                <li><strong>SEO sicher:</strong> Standard-Suchmaschinen (Google, Bing) werden NICHT blockiert.</li>
                <li><strong>Passwortschutz:</strong> Kombiniere mit dem Website-Passwortschutz oben für maximale Sicherheit.</li>
                <li><strong>100% Schutz gibt es nicht:</strong> Fortgeschrittene Angreifer können diese Maßnahmen umgehen, aber sie schrecken 95%+ der automatisierten Zugriffe ab.</li>
            </ul>
        </div>

        <div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; margin: 20px 0; border-left: 4px solid #00a32a;">
            <h2 style="margin-top: 0;">✅ Empfohlene Konfiguration</h2>
            <p><strong>Für maximalen Copyright-Schutz empfehlen wir:</strong></p>
            <ol style="margin-left: 20px;">
                <li>✅ <strong>Passwortschutz aktivieren</strong> (oben)</li>
                <li>✅ <strong>robots.txt AI-Blocking aktivieren</strong></li>
                <li>✅ <strong>User-Agent Blocking aktivieren</strong></li>
            </ol>
            <p style="margin-top: 15px; font-weight: 600; color: #00a32a;">
                🔒 Diese Kombination bietet den besten Schutz vor unbefugtem Zugriff und AI-Training mit Ihren Inhalten.
            </p>
        </div>
    </div>
    <?php
}
add_action('admin_notices', 'simple_clean_ai_protection_settings_section');

/**
 * Generate dynamic robots.txt with AI protection
 */
function simple_clean_generate_robots_txt($output, $public) {
    // If AI protection is disabled, return default WordPress robots.txt
    if (get_option('simple_clean_ai_protection_enabled', '0') !== '1') {
        return $output;
    }

    // Generate robots.txt with AI crawler blocking
    $robots_txt = "# robots.txt - AI Crawler Protection\n";
    $robots_txt .= "# Generated by WordPress Theme\n\n";

    $robots_txt .= "# =============================================================================\n";
    $robots_txt .= "# AI CRAWLERS - BLOCKED\n";
    $robots_txt .= "# =============================================================================\n\n";

    // List of AI crawlers to block
    $ai_crawlers = array(
        'GPTBot',
        'ChatGPT-User',
        'ChatGPT',
        'Claude-Web',
        'anthropic-ai',
        'ClaudeBot',
        'Google-Extended',
        'PerplexityBot',
        'Omgilibot',
        'Omgili',
        'FacebookBot',
        'Meta-ExternalAgent',
        'Applebot-Extended',
        'Bytespider',
        'Amazonbot',
        'cohere-ai',
        'ai2bot',
        'Diffbot',
        'CCBot',
        'Scrapy',
        'python-requests',
        'curl',
        'wget',
    );

    foreach ($ai_crawlers as $crawler) {
        $robots_txt .= "User-agent: {$crawler}\n";
        $robots_txt .= "Disallow: /\n\n";
    }

    $robots_txt .= "# =============================================================================\n";
    $robots_txt .= "# STANDARD SEARCH ENGINES - ALLOWED (SEO)\n";
    $robots_txt .= "# =============================================================================\n\n";

    $robots_txt .= "User-agent: Googlebot\n";
    $robots_txt .= "Allow: /\n\n";

    $robots_txt .= "User-agent: Bingbot\n";
    $robots_txt .= "Allow: /\n\n";

    $robots_txt .= "User-agent: Slurp\n";
    $robots_txt .= "Allow: /\n\n";

    $robots_txt .= "User-agent: DuckDuckBot\n";
    $robots_txt .= "Allow: /\n\n";

    $robots_txt .= "# =============================================================================\n";
    $robots_txt .= "# DEFAULT RULE\n";
    $robots_txt .= "# =============================================================================\n\n";

    $robots_txt .= "User-agent: *\n";
    $robots_txt .= "Allow: /\n";

    return $robots_txt;
}
add_filter('robots_txt', 'simple_clean_generate_robots_txt', 10, 2);

/**
 * Block AI crawlers via User-Agent (server-side)
 */
function simple_clean_block_ai_user_agents() {
    // Skip if User-Agent blocking is disabled
    if (get_option('simple_clean_ai_protection_block_user_agents', '0') !== '1') {
        return;
    }

    // Skip for admin area
    if (is_admin()) {
        return;
    }

    // Get the User-Agent
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? strtolower($_SERVER['HTTP_USER_AGENT']) : '';

    if (empty($user_agent)) {
        return;
    }

    // List of blocked AI crawler patterns
    $blocked_patterns = array(
        'gptbot',
        'chatgpt-user',
        'chatgpt',
        'claude-web',
        'anthropic-ai',
        'claudebot',
        'google-extended',
        'perplexitybot',
        'omgilibot',
        'omgili',
        'facebookbot',
        'meta-externalagent',
        'applebot-extended',
        'bytespider',
        'amazonbot',
        'cohere-ai',
        'ai2bot',
        'diffbot',
        'ccbot',
        'scrapy',
        'python-requests',
        'curl/',
        'wget/',
    );

    // Check if User-Agent matches any blocked pattern
    foreach ($blocked_patterns as $pattern) {
        if (strpos($user_agent, $pattern) !== false) {
            // Log the blocked attempt (optional)
            error_log("AI Crawler blocked: {$user_agent}");

            // Send 403 Forbidden header
            header('HTTP/1.1 403 Forbidden');
            echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden - AI Crawler Blocked</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 12px;
            padding: 40px;
            max-width: 500px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        h1 {
            font-size: 72px;
            margin: 0 0 20px 0;
        }
        h2 {
            color: #333;
            margin: 0 0 15px 0;
        }
        p {
            color: #666;
            line-height: 1.6;
        }
        .code {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            margin-top: 20px;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚫</h1>
        <h2>403 Forbidden</h2>
        <p><strong>AI Crawler Blocked</strong></p>
        <p>This website does not allow automated AI crawlers to access its content.</p>
        <p>If you believe this is an error, please contact the website administrator.</p>
        <div class="code">
            User-Agent: ' . esc_html($user_agent) . '
        </div>
    </div>
</body>
</html>';
            exit;
        }
    }
}
add_action('template_redirect', 'simple_clean_block_ai_user_agents', 1); // Priority 1 = runs before password protection