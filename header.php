<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <header class="site-header">
        <div class="container">
            <div class="header-content">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="site-title">
                    <?php bloginfo('name'); ?>
                </a>

                <button class="menu-toggle" id="menu-toggle" aria-label="Menü öffnen">
                    ☰
                </button>

                <nav class="main-navigation" id="main-navigation">
                    <?php
                    if (has_nav_menu('primary')) {
                        wp_nav_menu(array(
                            'theme_location' => 'primary',
                            'container' => false,
                            'menu_class' => 'primary-menu',
                            'fallback_cb' => false
                        ));
                    } else {
                        echo '<ul class="primary-menu">';
                        echo '<li><a href="' . esc_url(home_url('/')) . '">Home</a></li>';
                        wp_list_pages(array(
                            'title_li' => '',
                            'container' => false
                        ));
                        echo '</ul>';
                    }
                    ?>
                </nav>
            </div>
        </div>
    </header>

    <script>
    // Mobile Menu Toggle & Sidebar - Inline for immediate availability
    document.addEventListener('DOMContentLoaded', function() {
      // ==================== MOBILE MENU ====================
      const menuToggle = document.querySelector('.menu-toggle');
      const mainNavigation = document.querySelector('.main-navigation');

      if (menuToggle && mainNavigation) {
        menuToggle.addEventListener('click', function() {
          mainNavigation.classList.toggle('active');

          // Update ARIA attributes for accessibility
          const isExpanded = mainNavigation.classList.contains('active');
          menuToggle.setAttribute('aria-expanded', isExpanded);
        });

        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
          if (!menuToggle.contains(e.target) && !mainNavigation.contains(e.target)) {
            mainNavigation.classList.remove('active');
            menuToggle.setAttribute('aria-expanded', 'false');
          }
        });

        // Close menu on ESC key
        document.addEventListener('keydown', function(e) {
          if (e.key === 'Escape' && mainNavigation.classList.contains('active')) {
            mainNavigation.classList.remove('active');
            menuToggle.setAttribute('aria-expanded', 'false');
          }
        });
      }

      // ==================== SIDEBAR NAVIGATION ====================
      const sidebar = document.getElementById('sidebar');
      const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
      const sidebarToggleClose = document.getElementById('sidebar-toggle-close');
      const pageToggles = document.querySelectorAll('.page-toggle');

      if (!sidebar) return; // Exit if sidebar doesn't exist on this page

      // Detect if we're on mobile or desktop
      const isMobile = function() { return window.innerWidth <= 992; };

      // Helper function to close sidebar
      function closeSidebar() {
        if (isMobile()) {
          // Mobile: remove .active class
          sidebar.classList.remove('active');
          if (sidebarToggleBtn) {
            sidebarToggleBtn.setAttribute('aria-expanded', 'false');
          }
        } else {
          // Desktop: add .hidden class
          sidebar.classList.add('hidden');
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
                top: Math.max(0, scrollPosition), // Ensure we don't scroll to negative position
                behavior: 'smooth'
              });
            }
          }
        }, 400); // Wait for sidebar open animation + expansion animations
      }

      // Sidebar toggle button - open/close
      if (sidebarToggleBtn) {
        sidebarToggleBtn.addEventListener('click', function() {
          if (isMobile()) {
            // Mobile: use .active class
            sidebar.classList.add('active');
            sidebarToggleBtn.setAttribute('aria-expanded', 'true');
          } else {
            // Desktop: remove .hidden class
            sidebar.classList.remove('hidden');
            sidebarToggleBtn.setAttribute('aria-expanded', 'true');
          }

          // Scroll to current page after opening
          scrollToCurrentPage();
        });
      }

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
        const swipeThreshold = 50; // minimum distance for swipe
        const swipeX = touchEndX - touchStartX;
        const swipeY = touchEndY - touchStartY;

        // Check if horizontal swipe is dominant (not vertical scroll)
        if (Math.abs(swipeX) > Math.abs(swipeY) && Math.abs(swipeX) > swipeThreshold) {
          // Swipe left to close sidebar
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

      // Handle window resize - clean up classes when switching between mobile/desktop
      let resizeTimer;
      window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
          if (isMobile()) {
            // On mobile, remove desktop .hidden class
            sidebar.classList.remove('hidden');
          } else {
            // On desktop, remove mobile .active class
            sidebar.classList.remove('active');
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

            // Update ARIA attribute
            const isExpanded = pageItem.classList.contains('expanded');
            toggle.setAttribute('aria-expanded', isExpanded);
          }
        });
      });

      console.log('Sidebar initialized with', pageToggles.length, 'expandable items');
    });
    </script>