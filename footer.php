    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-text">
                    &copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. Alle Rechte vorbehalten.
                </div>
                <div class="footer-links">
                    <?php
                    // „Fehler melden" auf jeder Seite. Oeffnet per JavaScript das
                    // Fenster direkt hier, damit der Lesepunkt nicht verloren geht;
                    // ohne JavaScript fuehrt der Link auf die Formularseite.
                    if (function_exists('simple_clean_meldung_footer_link')) {
                        simple_clean_meldung_footer_link();
                    }
                    ?>
                    <a href="<?php echo esc_url(wp_login_url()); ?>" class="admin-link">Anmelden</a>
                </div>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>