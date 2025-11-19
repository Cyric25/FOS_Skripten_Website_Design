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
    // Only initialize menu toggle if navigation exists
    const menuToggle = document.getElementById('menu-toggle');
    const mainNav = document.getElementById('main-navigation');
    if (menuToggle && mainNav) {
        menuToggle.addEventListener('click', function() {
            mainNav.classList.toggle('active');
        });
    }
    </script>