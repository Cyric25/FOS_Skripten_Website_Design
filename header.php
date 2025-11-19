<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php
    // TEMPORARY DEBUG - Shows what's happening
    if (is_page()) {
        $page_id = get_the_ID();
        $hide_meta = get_post_meta($page_id, '_simple_clean_hide_navigation', true);
        $should_hide = simple_clean_should_hide_navigation();

        // Visible debug info (remove after testing)
        echo '<div style="position: fixed; top: 10px; right: 10px; background: #000; color: #fff; padding: 15px; z-index: 99999; border-radius: 5px; font-size: 12px; max-width: 300px;">';
        echo '<strong>🔍 DEBUG INFO:</strong><br>';
        echo 'Seiten-ID: ' . $page_id . '<br>';
        echo 'Custom Field Wert: "' . esc_html($hide_meta) . '"<br>';
        echo 'Typ: ' . gettype($hide_meta) . '<br>';
        echo 'Funktion Ergebnis: ' . ($should_hide ? 'TRUE (verstecken)' : 'FALSE (anzeigen)') . '<br>';
        echo '<small>Wenn Wert = "1" und TRUE → Navigation weg!</small>';
        echo '</div>';
    }

    <?php
    // Check if navigation should be hidden
    $hide_navigation = simple_clean_should_hide_navigation();

    // Debug: show what we're checking
    if (is_page()) {
        echo '<div style="position: fixed; bottom: 10px; right: 10px; background: ' . ($hide_navigation ? '#28a745' : '#dc3545') . '; color: #fff; padding: 10px; z-index: 99999; border-radius: 5px; font-size: 11px;">';
        echo '<strong>Header Check:</strong><br>';
        echo 'Hide Navigation: ' . ($hide_navigation ? 'TRUE ✅' : 'FALSE ❌') . '<br>';
        echo 'Render Header: ' . (!$hide_navigation ? 'YES' : 'NO');
        echo '</div>';
    }

    // Only show header if hide_navigation is FALSE
    if (!$hide_navigation):
    ?>
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
    <?php else: ?>
    <!-- Navigation is hidden for this page (ID: <?php echo get_the_ID(); ?>) -->
    <?php endif; ?>