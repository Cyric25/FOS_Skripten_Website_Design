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

<aside class="sidebar hidden" id="sidebar">
    <div class="sidebar-header">
        <h3 class="sidebar-title">Navigation</h3>
        <button class="sidebar-toggle-close" id="sidebar-toggle-close" aria-label="Sidebar schließen" title="Navigation schließen">
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

<!-- Sidebar toggle button (works on mobile and desktop) -->
<button class="sidebar-toggle-btn" id="sidebar-toggle-btn" aria-label="Navigation öffnen" title="Navigation öffnen/schließen">
    <span class="toggle-icon">☰</span>
    <span class="toggle-text">Navigation</span>
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

<script>
// Sidebar Navigation - Executed immediately after sidebar HTML is rendered
(function() {
  const sidebar = document.getElementById('sidebar');
  const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
  const sidebarToggleClose = document.getElementById('sidebar-toggle-close');
  const pageToggles = document.querySelectorAll('.page-toggle');

  if (!sidebar || !sidebarToggleBtn) return;

  // Detect if we're on mobile or desktop
  const isMobile = function() { return window.innerWidth <= 992; };

  // Initial cleanup: Remove .hidden if we're in mobile mode on page load
  if (isMobile()) {
    sidebar.classList.remove('hidden');
  }

  // Helper function to close sidebar
  function closeSidebar() {
    if (isMobile()) {
      // Mobile: remove .active class
      sidebar.classList.remove('active');
      if (sidebarToggleBtn) {
        sidebarToggleBtn.setAttribute('aria-expanded', 'false');
      }
    } else {
      // Desktop: add .hidden class, remove .active
      sidebar.classList.add('hidden');
      sidebar.classList.remove('active');
      if (sidebarToggleBtn) {
        sidebarToggleBtn.setAttribute('aria-expanded', 'false');
      }
    }
  }

  // Helper function to scroll to current page in sidebar
  function scrollToCurrentPage() {
    // Wait for sidebar animation to complete
    setTimeout(function() {
      const currentPageItem = sidebar.querySelector('.page-item.current-page');
      if (currentPageItem) {
        const pageLink = currentPageItem.querySelector('.page-link');
        if (pageLink) {
          // Use getBoundingClientRect for accurate positioning
          const sidebarRect = sidebar.getBoundingClientRect();
          const linkRect = pageLink.getBoundingClientRect();

          // Calculate the position of the link relative to the sidebar's current scroll position
          const relativeTop = linkRect.top - sidebarRect.top + sidebar.scrollTop;

          // Center the current page in the visible area
          const sidebarHeight = sidebar.clientHeight;
          const linkHeight = linkRect.height;
          const scrollPosition = relativeTop - (sidebarHeight / 2) + (linkHeight / 2);

          // Smooth scroll to current page
          sidebar.scrollTo({
            top: Math.max(0, scrollPosition),
            behavior: 'smooth'
          });
        }
      }
    }, 400);
  }

  // Sidebar toggle button - open/close
  sidebarToggleBtn.addEventListener('click', function() {
    if (isMobile()) {
      // Mobile: use .active class AND remove .hidden class
      sidebar.classList.remove('hidden');
      sidebar.classList.add('active');
      sidebarToggleBtn.setAttribute('aria-expanded', 'true');
    } else {
      // Desktop: remove .hidden class
      sidebar.classList.remove('hidden');
      sidebar.classList.remove('active');
      sidebarToggleBtn.setAttribute('aria-expanded', 'true');
    }

    // Scroll to current page after opening
    scrollToCurrentPage();
  });

  // Sidebar close button
  if (sidebarToggleClose) {
    sidebarToggleClose.addEventListener('click', function() {
      closeSidebar();
    });
  }

  // Click outside to close sidebar (desktop)
  document.addEventListener('click', function(e) {
    if (!isMobile() && !sidebar.classList.contains('hidden')) {
      // Check if click is outside sidebar and not on toggle button
      if (!sidebar.contains(e.target) &&
          !sidebarToggleBtn.contains(e.target) &&
          e.target !== sidebarToggleBtn) {
        closeSidebar();
      }
    }
  });

  // Close sidebar when clicking overlay (on mobile)
  sidebar.addEventListener('click', function(e) {
    if (isMobile()) {
      // Check if click is on the overlay (pseudo-element)
      const rect = sidebar.getBoundingClientRect();
      if (e.clientX > rect.right) {
        closeSidebar();
      }
    }
  });

  // Swipe gesture support for touch devices
  let touchStartX = 0;
  let touchStartY = 0;
  let touchEndX = 0;
  let touchEndY = 0;

  sidebar.addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
    touchStartY = e.changedTouches[0].screenY;
  }, { passive: true });

  sidebar.addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    touchEndY = e.changedTouches[0].screenY;
    handleSwipeGesture();
  }, { passive: true });

  function handleSwipeGesture() {
    const swipeThreshold = 50;
    const swipeX = touchEndX - touchStartX;
    const swipeY = touchEndY - touchStartY;

    if (Math.abs(swipeX) > Math.abs(swipeY) && Math.abs(swipeX) > swipeThreshold) {
      if (swipeX < 0) {
        closeSidebar();
      }
    }
  }

  // Close sidebar on ESC key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      if (isMobile() && sidebar.classList.contains('active')) {
        closeSidebar();
      } else if (!isMobile() && !sidebar.classList.contains('hidden')) {
        closeSidebar();
      }
    }
  });

  // Handle window resize
  let resizeTimer;
  window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
      if (isMobile()) {
        // Switched to mobile: remove .hidden so mobile CSS rules work
        sidebar.classList.remove('hidden');
      } else {
        // Switched to desktop: remove .active and add .hidden
        sidebar.classList.remove('active');
        sidebar.classList.add('hidden');
      }
    }, 250);
  });

  // Page expand/collapse toggles
  pageToggles.forEach(function(toggle) {
    toggle.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();

      const pageItem = toggle.closest('.page-item');
      if (pageItem) {
        pageItem.classList.toggle('expanded');
        const isExpanded = pageItem.classList.contains('expanded');
        toggle.setAttribute('aria-expanded', isExpanded);
      }
    });
  });

})();
</script>
