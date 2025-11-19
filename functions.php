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
        'all_items'          => 'Alle Begriffe',
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
    ?>
    <div class="wrap">
        <h1>Glossar Einstellungen</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('simple_clean_glossar');
            do_settings_sections('simple_clean_glossar');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Enqueue Glossar Assets
function simple_clean_glossar_assets() {
    // Only load on pages/posts, not admin
    if (is_admin()) {
        return;
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