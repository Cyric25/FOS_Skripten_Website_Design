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
  const sidebarToggleMobile = document.getElementById('sidebar-toggle-mobile');
  const sidebarToggleClose = document.getElementById('sidebar-toggle-close');
  const pageToggles = document.querySelectorAll('.page-toggle');

  if (!sidebar) return; // Exit if sidebar doesn't exist on this page

  // Mobile sidebar toggle - open
  if (sidebarToggleMobile) {
    sidebarToggleMobile.addEventListener('click', () => {
      sidebar.classList.add('active');
      sidebarToggleMobile.setAttribute('aria-expanded', 'true');
    });
  }

  // Mobile sidebar toggle - close
  if (sidebarToggleClose) {
    sidebarToggleClose.addEventListener('click', () => {
      sidebar.classList.remove('active');
      if (sidebarToggleMobile) {
        sidebarToggleMobile.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // Close sidebar when clicking overlay (on mobile)
  sidebar.addEventListener('click', (e) => {
    // Check if click is on the overlay (pseudo-element)
    const rect = sidebar.getBoundingClientRect();
    if (e.clientX > rect.right) {
      sidebar.classList.remove('active');
      if (sidebarToggleMobile) {
        sidebarToggleMobile.setAttribute('aria-expanded', 'false');
      }
    }
  });

  // Close sidebar on ESC key (on mobile)
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && sidebar.classList.contains('active')) {
      sidebar.classList.remove('active');
      if (sidebarToggleMobile) {
        sidebarToggleMobile.setAttribute('aria-expanded', 'false');
      }
    }
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
