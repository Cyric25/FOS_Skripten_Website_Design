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
});

// Log theme initialization
console.log('Simple Clean Theme initialized');
