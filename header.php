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
    // Mobile Menu Toggle - Inline for immediate availability
    document.addEventListener('DOMContentLoaded', function() {
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
    });
    </script>