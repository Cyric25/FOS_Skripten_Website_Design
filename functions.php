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

// Add meta box for hiding navigation
function simple_clean_add_navigation_meta_box() {
    add_meta_box(
        'simple_clean_hide_navigation',
        'Navigation Einstellungen',
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
    <label for="simple_clean_hide_navigation">
        <input type="checkbox"
               id="simple_clean_hide_navigation"
               name="simple_clean_hide_navigation"
               value="1"
               <?php checked($hide_navigation, '1'); ?>>
        Navigation auf dieser Seite ausblenden
    </label>
    <p class="description">
        Wenn aktiviert, wird die Hauptnavigation auf dieser Seite nicht angezeigt.
    </p>
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

    // Save or delete meta
    if (isset($_POST['simple_clean_hide_navigation']) && $_POST['simple_clean_hide_navigation'] === '1') {
        update_post_meta($post_id, '_simple_clean_hide_navigation', '1');
    } else {
        delete_post_meta($post_id, '_simple_clean_hide_navigation');
    }
}
add_action('save_post', 'simple_clean_save_navigation_meta');

// Helper function to check if navigation should be hidden
function simple_clean_should_hide_navigation() {
    if (is_page()) {
        $hide = get_post_meta(get_the_ID(), '_simple_clean_hide_navigation', true);
        return $hide === '1';
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
    $glossar_css = get_template_directory() . '/dist/css/glossar.css';
    if (file_exists($glossar_css)) {
        wp_enqueue_style(
            'simple-clean-glossar-style',
            get_template_directory_uri() . '/dist/css/glossar.css',
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
            array('wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-data', 'wp-rich-text'),
            filemtime($editor_js),
            true
        );
    }
}
add_action('enqueue_block_editor_assets', 'simple_clean_glossar_editor_assets');