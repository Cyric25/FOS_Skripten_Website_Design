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

        // Find the root page (top-level ancestor of current page)
        $root_page_id = get_root_page_id($current_page_id);

        // Get the root page title for display
        $root_page = get_post($root_page_id);
        $root_title = $root_page ? $root_page->post_title : '';

        // Display root page title as section header
        if ($root_title) {
            echo '<div class="sidebar-section-title">' . esc_html($root_title) . '</div>';
        }

        // Get child pages of the root page
        $pages = get_pages(array(
            'child_of' => $root_page_id,
            'parent' => $root_page_id,
            'sort_column' => 'menu_order, post_title',
        ));

        if ($pages) {
            echo '<ul class="page-tree">';

            foreach ($pages as $page) {
                display_page_tree_item($page, $current_page_id, 0, true); // true = auto-expand all
            }

            echo '</ul>';
        } else {
            // If no child pages, show a message
            echo '<p class="no-pages-message">Keine Unterseiten vorhanden.</p>';
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
 * Get the root page ID (top-level ancestor) for the current page
 *
 * @param int $page_id Current page ID
 * @return int Root page ID
 */
function get_root_page_id($page_id) {
    $ancestors = get_post_ancestors($page_id);

    if (!empty($ancestors)) {
        // Return the topmost ancestor
        return end($ancestors);
    }

    // If no ancestors, the current page is the root
    return $page_id;
}

/**
 * Recursively display page tree items
 *
 * @param WP_Post $page Current page object
 * @param int $current_page_id Currently viewed page ID
 * @param int $depth Current depth level
 * @param bool $auto_expand Auto-expand all items
 */
function display_page_tree_item($page, $current_page_id, $depth = 0, $auto_expand = true) {
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
    }

    // Auto-expand all items if specified, or just ancestors
    if ($auto_expand || $is_ancestor) {
        $classes[] = 'expanded';
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
            display_page_tree_item($child, $current_page_id, $depth + 1, $auto_expand);
        }
        echo '</ul>';
    }

    echo '</li>';
}
?>
