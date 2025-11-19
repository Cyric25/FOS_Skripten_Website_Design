<?php
/**
 * Template Name: Debug Navigation Meta
 *
 * This is a temporary debug template to check if the custom field is working.
 * Use this template on any page to see the navigation meta information.
 */

get_header();
?>

<main id="main" class="site-main">
    <div class="container">
        <div class="content-area">
            <article style="padding: 2rem; background: #f8f9fa; border-radius: 8px;">
                <h1>🔍 Navigation Meta Debug</h1>

                <?php
                $page_id = get_the_ID();
                $hide_nav = get_post_meta($page_id, '_simple_clean_hide_navigation', true);
                $all_meta = get_post_meta($page_id);
                ?>

                <div style="background: white; padding: 1.5rem; margin: 1rem 0; border-left: 4px solid #0073aa;">
                    <h2>Aktuelle Seite</h2>
                    <p><strong>Seiten-ID:</strong> <?php echo $page_id; ?></p>
                    <p><strong>Seitentitel:</strong> <?php the_title(); ?></p>
                    <p><strong>Seitentyp:</strong> <?php echo get_post_type(); ?></p>
                </div>

                <div style="background: white; padding: 1.5rem; margin: 1rem 0; border-left: 4px solid #e24614;">
                    <h2>Custom Field Status</h2>
                    <p><strong>Meta-Feld Name:</strong> <code>_simple_clean_hide_navigation</code></p>
                    <p><strong>Wert:</strong>
                        <?php if ($hide_nav === '1' || $hide_nav === 1): ?>
                            <span style="color: green; font-weight: bold;">✅ '<?php echo esc_html($hide_nav); ?>' (Navigation wird ausgeblendet)</span>
                        <?php elseif ($hide_nav === '' || $hide_nav === false): ?>
                            <span style="color: orange; font-weight: bold;">⚠️ Leer/Nicht gesetzt (Navigation wird angezeigt)</span>
                        <?php else: ?>
                            <span style="color: red; font-weight: bold;">❌ '<?php echo esc_html($hide_nav); ?>' (Unerwarteter Wert)</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Typ:</strong> <?php echo gettype($hide_nav); ?></p>
                </div>

                <div style="background: white; padding: 1.5rem; margin: 1rem 0; border-left: 4px solid #71230a;">
                    <h2>Funktion Check</h2>
                    <p><strong>simple_clean_should_hide_navigation():</strong>
                        <?php if (simple_clean_should_hide_navigation()): ?>
                            <span style="color: green; font-weight: bold;">✅ TRUE - Navigation sollte ausgeblendet sein</span>
                        <?php else: ?>
                            <span style="color: red; font-weight: bold;">❌ FALSE - Navigation wird angezeigt</span>
                        <?php endif; ?>
                    </p>
                    <p><strong>is_page():</strong> <?php echo is_page() ? '✅ TRUE' : '❌ FALSE'; ?></p>
                    <p><strong>is_admin():</strong> <?php echo is_admin() ? '✅ TRUE' : '❌ FALSE'; ?></p>
                </div>

                <div style="background: white; padding: 1.5rem; margin: 1rem 0; border-left: 4px solid #666;">
                    <h2>Alle Post Meta Felder (zur Kontrolle)</h2>
                    <pre style="background: #f1f1f1; padding: 1rem; overflow-x: auto; font-size: 0.85rem;"><?php
                        echo esc_html(print_r($all_meta, true));
                    ?></pre>
                </div>

                <div style="background: #fff3cd; padding: 1.5rem; margin: 1rem 0; border-left: 4px solid #ffc107;">
                    <h2>💡 Anleitung zum Testen</h2>
                    <ol>
                        <li>Gehe in den WordPress-Admin</li>
                        <li>Bearbeite diese Seite</li>
                        <li>Finde die Meta-Box "Navigation Einstellungen" in der rechten Sidebar</li>
                        <li>Setze/entferne den Haken bei "Navigation auf dieser Seite ausblenden"</li>
                        <li>Speichere die Seite</li>
                        <li>Lade diese Seite neu und prüfe die Werte oben</li>
                    </ol>
                </div>

                <div style="background: #f8d7da; padding: 1.5rem; margin: 1rem 0; border-left: 4px solid #dc3545;">
                    <h2>⚠️ Wichtig</h2>
                    <p>Diese Debug-Seite sollte nur temporär verwendet werden. Lösche sie nach dem Testen oder ändere das Template zurück zu "Standard".</p>
                </div>

                <?php the_content(); ?>
            </article>
        </div>
    </div>
</main>

<?php get_footer(); ?>
