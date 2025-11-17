<?php
/**
 * Sidebar Template - Hierarchical Page Navigation
 *
 * Displays a collapsible page tree for easy navigation between pages
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h3 class="sidebar-title">Navigation</h3>
        <button class="sidebar-toggle-close" id="sidebar-toggle-close" aria-label="Sidebar schließen">
            ✕
        </button>
    </div>

    <nav class="sidebar-navigation">
        <?php
        // Get current page ID
        $current_page_id = get_the_ID();

        // Get all pages in hierarchical structure
        $pages = get_pages(array(
            'sort_column' => 'menu_order, post_title',
            'hierarchical' => true,
            'parent' => 0, // Only top-level pages
        ));

        if ($pages) {
            echo '<ul class="page-tree">';

            foreach ($pages as $page) {
                display_page_tree_item($page, $current_page_id);
            }

            echo '</ul>';
        } else {
            echo '<p>Keine Seiten gefunden.</p>';
        }
        ?>
    </nav>
</aside>

<!-- Mobile sidebar toggle button -->
<button class="sidebar-toggle-mobile" id="sidebar-toggle-mobile" aria-label="Navigation öffnen">
    <span class="toggle-icon">☰</span>
    <span class="toggle-text">Seiten</span>
</button>

<?php
/**
 * Recursively display page tree items
 *
 * @param WP_Post $page Current page object
 * @param int $current_page_id Currently viewed page ID
 * @param int $depth Current depth level
 */
function display_page_tree_item($page, $current_page_id, $depth = 0) {
    // Get child pages
    $children = get_pages(array(
        'child_of' => $page->ID,
        'parent' => $page->ID,
        'sort_column' => 'menu_order, post_title',
    ));

    $has_children = !empty($children);
    $is_current = ($page->ID == $current_page_id);
    $is_ancestor = false;

    // Check if current page is a descendant of this page
    if (!$is_current) {
        $ancestors = get_post_ancestors($current_page_id);
        $is_ancestor = in_array($page->ID, $ancestors);
    }

    // Build CSS classes
    $classes = array('page-item');
    if ($has_children) {
        $classes[] = 'has-children';
    }
    if ($is_current) {
        $classes[] = 'current-page';
    }
    if ($is_ancestor) {
        $classes[] = 'current-page-ancestor';
        $classes[] = 'expanded'; // Auto-expand ancestor pages
    }

    echo '<li class="' . esc_attr(implode(' ', $classes)) . '">';

    // Toggle button for pages with children
    if ($has_children) {
        echo '<button class="page-toggle" aria-label="Unterseiten anzeigen/verbergen">';
        echo '<span class="toggle-icon">▸</span>';
        echo '</button>';
    }

    // Page link
    echo '<a href="' . esc_url(get_permalink($page->ID)) . '" class="page-link">';
    echo '<span class="page-title">' . esc_html($page->post_title) . '</span>';
    echo '</a>';

    // Render child pages if they exist
    if ($has_children) {
        echo '<ul class="page-tree-children">';
        foreach ($children as $child) {
            display_page_tree_item($child, $current_page_id, $depth + 1);
        }
        echo '</ul>';
    }

    echo '</li>';
}
?>
