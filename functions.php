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

    // Enqueue glossar JavaScript
    $glossar_js = get_template_directory() . '/dist/js/glossar.js';
    if (file_exists($glossar_js)) {
        wp_enqueue_script(
            'simple-clean-glossar',
            get_template_directory_uri() . '/dist/js/glossar.js',
            array(),
            filemtime($glossar_js),
            true
        );

        // Pass settings and terms to JavaScript
        $glossar_terms = simple_clean_get_glossar_terms();
        wp_localize_script('simple-clean-glossar', 'glossarData', array(
            'modalType' => get_option('glossar_modal_type', 'tooltip'),
            'autoLink' => get_option('glossar_auto_link', '1'),
            'firstOnly' => get_option('glossar_first_only', '1'),
            'caseSensitive' => get_option('glossar_case_sensitive', '0'),
            'terms' => $glossar_terms,
        ));
    }

    // Enqueue glossar CSS
    $glossar_css = get_template_directory() . '/dist/css/glossar-style.css';
    if (file_exists($glossar_css)) {
        wp_enqueue_style(
            'simple-clean-glossar-style',
            get_template_directory_uri() . '/dist/css/glossar-style.css',
            array(),
            filemtime($glossar_css)
        );
    }
}
add_action('wp_enqueue_scripts', 'simple_clean_glossar_assets');

// Get all glossar terms
function simple_clean_get_glossar_terms() {
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

    return $terms;
}

// ===================================================================
// GLOSSAR USAGE TRACKING (Wo wird Begriff verwendet)
// ===================================================================

/**
 * Track glossar terms used in post content
 * This runs when a post/page is viewed and stores which terms are used
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

        // Simple case-insensitive search
        // Using word boundaries to avoid partial matches
        $pattern = '/\b' . preg_quote($term, '/') . '\b/i';

        if (preg_match($pattern, $content)) {
            $used_terms[] = $term_id;
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

        // Only re-track if post was modified since last tracking
        if (empty($last_tracked) || $post_modified > $last_tracked) {
            simple_clean_track_glossar_usage($post_id);
            update_post_meta($post_id, '_glossar_last_tracked', time());
        }
    }
});

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