/**
 * Simple Clean Theme - Main JavaScript
 *
 * This file contains all the interactive JavaScript for the theme.
 */

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', () => {
  const menuToggle = document.querySelector('.menu-toggle');
  const mainNavigation = document.querySelector('.main-navigation');

  if (menuToggle && mainNavigation) {
    menuToggle.addEventListener('click', () => {
      mainNavigation.classList.toggle('active');

      // Update ARIA attributes for accessibility
      const isExpanded = mainNavigation.classList.contains('active');
      menuToggle.setAttribute('aria-expanded', isExpanded);
    });

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
      if (!menuToggle.contains(e.target) && !mainNavigation.contains(e.target)) {
        mainNavigation.classList.remove('active');
        menuToggle.setAttribute('aria-expanded', 'false');
      }
    });

    // Close menu on ESC key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && mainNavigation.classList.contains('active')) {
        mainNavigation.classList.remove('active');
        menuToggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Sidebar functionality
  initializeSidebar();
});

/**
 * Initialize Sidebar Navigation
 */
function initializeSidebar() {
  const sidebar = document.getElementById('sidebar');
  const sidebarToggleBtn = document.getElementById('sidebar-toggle-btn');
  const sidebarToggleClose = document.getElementById('sidebar-toggle-close');
  const pageToggles = document.querySelectorAll('.page-toggle');

  if (!sidebar) return; // Exit if sidebar doesn't exist on this page

  // Detect if we're on mobile or desktop
  const isMobile = () => window.innerWidth <= 992;

  // Sidebar toggle button - open/close
  if (sidebarToggleBtn) {
    sidebarToggleBtn.addEventListener('click', () => {
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
    sidebarToggleClose.addEventListener('click', () => {
      closeSidebar();
    });
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
      // Desktop: add .hidden class
      sidebar.classList.add('hidden');
      if (sidebarToggleBtn) {
        sidebarToggleBtn.setAttribute('aria-expanded', 'false');
      }
    }
  }

  // Click outside to close sidebar (desktop)
  document.addEventListener('click', (e) => {
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
  sidebar.addEventListener('click', (e) => {
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

  sidebar.addEventListener('touchstart', (e) => {
    touchStartX = e.changedTouches[0].screenX;
    touchStartY = e.changedTouches[0].screenY;
  }, { passive: true });

  sidebar.addEventListener('touchend', (e) => {
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
  document.addEventListener('keydown', (e) => {
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
  window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
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
  pageToggles.forEach(toggle => {
    toggle.addEventListener('click', (e) => {
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

  // Helper function to scroll to current page in sidebar
  function scrollToCurrentPage() {
    // Wait for sidebar animation to complete
    setTimeout(() => {
      const currentPageItem = sidebar.querySelector('.page-item.current-page');
      if (currentPageItem) {
        // The sidebar itself is the scrollable element (has overflow-y: auto)
        // Get the position of current page relative to sidebar top
        const itemOffsetTop = currentPageItem.offsetTop;
        const sidebarHeight = sidebar.clientHeight;
        const itemHeight = currentPageItem.clientHeight;

        // Calculate scroll position to center the current page
        const scrollPosition = itemOffsetTop - (sidebarHeight / 2) + (itemHeight / 2);

        // Smooth scroll to current page
        sidebar.scrollTo({
          top: scrollPosition,
          behavior: 'smooth'
        });
      }
    }, 350); // Wait for sidebar open animation (300ms) + buffer
  }

  // Note: Sidebar starts hidden by default now (on all devices)
  // scrollToCurrentPage() is called when sidebar is opened via toggle button
  // No need to scroll on initial load anymore

  console.log('Sidebar initialized with', pageToggles.length, 'expandable items');
}

// Log theme initialization
console.log('Simple Clean Theme initialized');
