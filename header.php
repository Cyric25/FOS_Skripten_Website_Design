<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <?php // Darkmode: blockierendes Inline-Script, MUSS vor jeder Style-Ausgabe
          // (insbesondere vor wp_head() mit simple_clean_customizer_css()) laufen,
          // damit bei gespeicherter Dark-Praeferenz kein Lightmode aufblitzt (FOUC).
          // Kein defer/async, kein matchMedia/prefers-color-scheme. ?>
    <script>
    (function() {
        try {
            var gespeichert = localStorage.getItem('fos-color-scheme');
            if (gespeichert === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        } catch (e) { /* localStorage nicht verfuegbar (z. B. privater Modus) - Lightmode bleibt Standard */ }
    })();
    </script>
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

                <?php // Darkmode-Umschalter: bewusst VOR dem Menue-Knopf, damit der
                      // Hamburger auf schmalen Breiten ganz rechts stehen bleibt.
                      // Ausrichtung uebernimmt .theme-toggle-btn { margin-left: auto } in style.css. ?>
                <button type="button" id="fos-theme-toggle" class="theme-toggle-btn" aria-label="Dunkelmodus umschalten" aria-pressed="false">
                    <span class="theme-toggle-icon" aria-hidden="true">🌙</span>
                </button>

                <button class="menu-toggle" id="menu-toggle" aria-label="Menü öffnen">
                    ☰
                </button>

                <nav class="main-navigation" id="main-navigation">
                    <?php
                    // Always try to display the menu first
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'container' => false,
                        'menu_class' => 'primary-menu',
                        'fallback_cb' => 'simple_clean_fallback_menu'
                    ));
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

    <?php // Darkmode: Klick-Handler des Umschalters. Bewusst NACH dem Menue-Toggle-Script
          // und in eigener IIFE - beide Scripts sind voneinander unabhaengig.
          // Kein matchMedia/prefers-color-scheme: ohne gespeicherten Wert bleibt es hell. ?>
    <script>
    (function() {
        var knopf = document.getElementById('fos-theme-toggle');
        if (!knopf) { return; }
        function aktuellerModus() {
            return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        }
        function anzeigeAktualisieren() {
            var istDark = aktuellerModus() === 'dark';
            knopf.setAttribute('aria-pressed', istDark ? 'true' : 'false');
            knopf.querySelector('.theme-toggle-icon').textContent = istDark ? '☀️' : '🌙';
        }
        knopf.addEventListener('click', function() {
            var neu = aktuellerModus() === 'dark' ? 'light' : 'dark';
            if (neu === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
            }
            try {
                localStorage.setItem('fos-color-scheme', neu);
            } catch (e) { /* localStorage nicht verfuegbar - Umschaltung wirkt nur fuer diese Seitenansicht */ }
            anzeigeAktualisieren();
        });
        anzeigeAktualisieren();
    })();
    </script>