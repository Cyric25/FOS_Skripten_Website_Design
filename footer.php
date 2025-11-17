    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-text">
                    &copy; <?php echo esc_html(date('Y')); ?> <?php bloginfo('name'); ?>. Alle Rechte vorbehalten.
                </div>
                <div class="footer-links">
                    <a href="<?php echo esc_url(wp_login_url()); ?>" class="admin-link">Anmelden</a>
                </div>
            </div>
        </div>
    </footer>

    <?php wp_footer(); ?>
</body>
</html>