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