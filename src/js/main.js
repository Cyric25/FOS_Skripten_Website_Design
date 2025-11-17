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
    });
  }

  // Sidebar close button
  if (sidebarToggleClose) {
    sidebarToggleClose.addEventListener('click', () => {
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
    });
  }

  // Close sidebar when clicking overlay (on mobile)
  sidebar.addEventListener('click', (e) => {
    if (isMobile()) {
      // Check if click is on the overlay (pseudo-element)
      const rect = sidebar.getBoundingClientRect();
      if (e.clientX > rect.right) {
        sidebar.classList.remove('active');
        if (sidebarToggleBtn) {
          sidebarToggleBtn.setAttribute('aria-expanded', 'false');
        }
      }
    }
  });

  // Close sidebar on ESC key
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      if (isMobile() && sidebar.classList.contains('active')) {
        sidebar.classList.remove('active');
        if (sidebarToggleBtn) {
          sidebarToggleBtn.setAttribute('aria-expanded', 'false');
        }
      } else if (!isMobile() && !sidebar.classList.contains('hidden')) {
        sidebar.classList.add('hidden');
        if (sidebarToggleBtn) {
          sidebarToggleBtn.setAttribute('aria-expanded', 'false');
        }
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

  console.log('Sidebar initialized with', pageToggles.length, 'expandable items');
}

// Log theme initialization
console.log('Simple Clean Theme initialized');
