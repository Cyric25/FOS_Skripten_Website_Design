<?php
/**
 * Seitenmanager - Hierarchical Page Manager
 *
 * Provides hierarchical view and drag & drop parent-child management for pages.
 *
 * @package FOS_Online_Schulbuch
 * @since 1.4.7
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Simple_Clean_Page_Manager
 *
 * Handles the hierarchical page manager admin functionality.
 * Focuses on parent-child relationships (post_parent), not ordering (menu_order).
 */
class Simple_Clean_Page_Manager {

    /**
     * Initialize the page manager
     */
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_admin_assets']);
        add_action('wp_ajax_page_manager_update_order', [__CLASS__, 'ajax_update_order']);
        add_action('wp_ajax_page_manager_create_page', [__CLASS__, 'ajax_create_page']);
        add_action('wp_ajax_page_manager_delete_page', [__CLASS__, 'ajax_delete_page']);
    }

    /**
     * Register admin menu
     */
    public static function add_admin_menu() {
        add_menu_page(
            'Seitenmanager',                     // Page title
            'Seitenmanager',                     // Menu title
            'edit_pages',                        // Capability
            'page-manager',                      // Menu slug
            [__CLASS__, 'render_admin_page'],    // Callback
            'dashicons-sort',                    // Icon
            26                                   // Position (after Pages at 20)
        );
    }

    /**
     * Enqueue admin assets (only on our page)
     */
    public static function enqueue_admin_assets($hook) {
        if ($hook !== 'toplevel_page_page-manager') {
            return;
        }

        // jQuery UI Sortable (bundled with WordPress)
        wp_enqueue_script('jquery-ui-sortable');

        // Our custom JavaScript
        $js_file = get_template_directory() . '/dist/js/page-manager.js';
        if (file_exists($js_file)) {
            wp_enqueue_script(
                'page-manager-script',
                get_template_directory_uri() . '/dist/js/page-manager.js',
                ['jquery', 'jquery-ui-sortable'],
                filemtime($js_file),
                true
            );

            // Pass data to JavaScript
            wp_localize_script('page-manager-script', 'pageManagerData', [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('page_manager_nonce'),
                'strings' => [
                    'saved' => 'Hierarchie gespeichert.',
                    'error' => 'Fehler beim Speichern der Hierarchie.',
                    'loading' => 'Speichert...',
                ]
            ]);
        }

        // Our custom CSS
        $css_file = get_template_directory() . '/dist/css/page-manager-style.css';
        if (file_exists($css_file)) {
            wp_enqueue_style(
                'page-manager-style',
                get_template_directory_uri() . '/dist/css/page-manager-style.css',
                [],
                filemtime($css_file)
            );
        }
    }

    /**
     * Render the admin page
     */
    public static function render_admin_page() {
        // Permission check
        if (!current_user_can('edit_pages')) {
            wp_die(__('Sie haben keine Berechtigung für diese Seite.'));
        }

        // Get all top-level pages
        $pages = get_pages([
            'parent' => 0,
            'sort_column' => 'menu_order, post_title',
            'post_status' => ['publish', 'draft', 'pending', 'private'],
        ]);

        ?>
        <div class="wrap page-manager-wrap">
            <h1>
                <span class="dashicons dashicons-sort"></span>
                Seitenmanager
            </h1>

            <p class="description">
                Ziehen Sie Seiten, um die Reihenfolge und Hierarchie zu ändern.
                Die Position innerhalb einer Ebene bestimmt die Reihenfolge (menu_order),
                das Verschieben auf eine andere Seite ändert die Hierarchie (Eltern-Kind-Beziehung).
            </p>

            <div class="page-manager-toolbar">
                <button type="button" id="create-new-page" class="button button-primary">
                    <span class="dashicons dashicons-plus-alt"></span>
                    Neue Seite
                </button>
                <button type="button" id="expand-all" class="button">
                    <span class="dashicons dashicons-arrow-down-alt2"></span>
                    Alle aufklappen
                </button>
                <button type="button" id="collapse-all" class="button">
                    <span class="dashicons dashicons-arrow-up-alt2"></span>
                    Alle zuklappen
                </button>
                <span class="spinner" id="save-spinner"></span>
                <span class="save-status" id="save-status"></span>
            </div>

            <!-- Modal für neue Seite -->
            <div id="new-page-modal" class="page-manager-modal" style="display:none;">
                <div class="page-manager-modal-content">
                    <h2>Neue Seite erstellen</h2>
                    <p>
                        <label for="new-page-title">Seitentitel:</label>
                        <input type="text" id="new-page-title" class="widefat" placeholder="Titel der neuen Seite" />
                    </p>
                    <div class="page-manager-modal-buttons">
                        <button type="button" id="create-page-submit" class="button button-primary">Erstellen</button>
                        <button type="button" id="create-page-cancel" class="button">Abbrechen</button>
                    </div>
                </div>
            </div>

            <div class="page-manager-container">
                <?php if ($pages): ?>
                    <ul class="page-tree sortable-list" id="page-tree-root" data-parent="0">
                        <?php foreach ($pages as $page): ?>
                            <?php self::render_page_item($page); ?>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="no-pages">Keine Seiten vorhanden.</p>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Render a single page item with its children
     *
     * @param WP_Post $page The page object
     */
    private static function render_page_item($page) {
        // Get children
        $children = get_pages([
            'parent' => $page->ID,
            'sort_column' => 'menu_order, post_title',
            'post_status' => ['publish', 'draft', 'pending', 'private'],
        ]);

        $has_children = !empty($children);
        $status_class = 'status-' . $page->post_status;

        ?>
        <li class="page-item <?php echo $status_class; ?> <?php echo $has_children ? 'has-children' : ''; ?>"
            data-page-id="<?php echo esc_attr($page->ID); ?>"
            data-parent-id="<?php echo esc_attr($page->post_parent); ?>">

            <div class="page-item-row">
                <span class="drag-handle" title="Ziehen zum Verschieben">
                    <span class="dashicons dashicons-menu"></span>
                </span>

                <?php if ($has_children): ?>
                    <button class="toggle-children" aria-expanded="false" title="Unterseiten ein-/ausklappen">
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
                <?php else: ?>
                    <span class="toggle-placeholder"></span>
                <?php endif; ?>

                <span class="page-title">
                    <?php echo esc_html($page->post_title); ?>
                </span>

                <?php self::render_status_badge($page->post_status); ?>

                <span class="page-actions">
                    <a href="<?php echo esc_url(get_edit_post_link($page->ID)); ?>"
                       class="button button-small" title="Bearbeiten">
                        <span class="dashicons dashicons-edit"></span>
                    </a>
                    <a href="<?php echo esc_url(get_permalink($page->ID)); ?>"
                       class="button button-small" target="_blank" title="Ansehen">
                        <span class="dashicons dashicons-visibility"></span>
                    </a>
                    <button type="button" class="button button-small delete-page"
                            data-page-id="<?php echo esc_attr($page->ID); ?>"
                            data-page-title="<?php echo esc_attr($page->post_title); ?>"
                            title="Löschen">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </span>
            </div>

            <?php if ($has_children): ?>
                <ul class="page-tree-children sortable-list" data-parent="<?php echo esc_attr($page->ID); ?>">
                    <?php foreach ($children as $child): ?>
                        <?php self::render_page_item($child); ?>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <!-- Empty drop zone for reparenting -->
                <ul class="page-tree-children sortable-list empty-children"
                    data-parent="<?php echo esc_attr($page->ID); ?>">
                </ul>
            <?php endif; ?>
        </li>
        <?php
    }

    /**
     * Render status badge
     *
     * @param string $status Post status
     */
    private static function render_status_badge($status) {
        $badges = [
            'draft' => ['label' => 'Entwurf', 'class' => 'badge-draft'],
            'pending' => ['label' => 'Ausstehend', 'class' => 'badge-pending'],
            'private' => ['label' => 'Privat', 'class' => 'badge-private'],
        ];

        if (isset($badges[$status])) {
            echo '<span class="page-status-badge ' . $badges[$status]['class'] . '">';
            echo esc_html($badges[$status]['label']);
            echo '</span>';
        }
    }

    /**
     * AJAX handler: Update page hierarchy and order
     */
    public static function ajax_update_order() {
        // Security check
        check_ajax_referer('page_manager_nonce', 'nonce');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(['message' => 'Keine Berechtigung.']);
        }

        // Get the order data
        $order_data = isset($_POST['order']) ? $_POST['order'] : [];

        if (empty($order_data)) {
            wp_send_json_error(['message' => 'Keine Daten erhalten.']);
        }

        global $wpdb;
        $updated = 0;
        $errors = [];

        foreach ($order_data as $item) {
            $page_id = absint($item['id']);
            $new_parent = absint($item['parent']);
            $new_order = absint($item['order']);

            // Verify page exists
            $page = get_post($page_id);
            if (!$page || $page->post_type !== 'page') {
                $errors[] = "Seite ID $page_id nicht gefunden";
                continue;
            }

            // Prevent circular reference
            if ($new_parent == $page_id) {
                $errors[] = "Seite ID $page_id kann nicht ihr eigenes Elternteil sein";
                continue;
            }

            // Check if new parent would create circular reference
            if ($new_parent > 0 && self::would_create_circular_reference($page_id, $new_parent)) {
                $errors[] = "Seite ID $page_id würde zirkuläre Referenz erzeugen";
                continue;
            }

            // Get current values
            $current = $wpdb->get_row($wpdb->prepare(
                "SELECT post_parent, menu_order FROM {$wpdb->posts} WHERE ID = %d",
                $page_id
            ));

            if (!$current) {
                continue;
            }

            // Only update if something changed
            if ($current->post_parent != $new_parent || $current->menu_order != $new_order) {
                $result = $wpdb->update(
                    $wpdb->posts,
                    [
                        'post_parent' => $new_parent,
                        'menu_order' => $new_order,
                    ],
                    ['ID' => $page_id],
                    ['%d', '%d'],
                    ['%d']
                );

                if ($result !== false) {
                    $updated++;
                    // Clear post cache
                    clean_post_cache($page_id);
                } else {
                    $errors[] = "Fehler beim Aktualisieren von Seite ID $page_id";
                }
            }
        }

        if ($updated > 0) {
            wp_send_json_success([
                'updated' => $updated,
                'message' => sprintf('%d Seite(n) aktualisiert.', $updated),
                'errors' => $errors
            ]);
        } else {
            wp_send_json_error([
                'message' => 'Keine Änderungen vorgenommen.',
                'errors' => $errors
            ]);
        }
    }

    /**
     * Check if moving a page would create a circular reference
     *
     * @param int $page_id Page being moved
     * @param int $new_parent New parent ID
     * @return bool True if circular reference would be created
     */
    private static function would_create_circular_reference($page_id, $new_parent) {
        // Walk up the parent tree from new_parent
        $current = $new_parent;
        $max_depth = 10; // Prevent infinite loop
        $depth = 0;

        while ($current > 0 && $depth < $max_depth) {
            if ($current == $page_id) {
                return true; // Circular reference detected
            }

            $parent_page = get_post($current);
            if (!$parent_page) {
                break;
            }

            $current = $parent_page->post_parent;
            $depth++;
        }

        return false;
    }

    /**
     * AJAX handler: Create new page
     */
    public static function ajax_create_page() {
        // Security check
        check_ajax_referer('page_manager_nonce', 'nonce');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(['message' => 'Keine Berechtigung.']);
        }

        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';

        if (empty($title)) {
            wp_send_json_error(['message' => 'Titel erforderlich.']);
        }

        // Create new page at top level
        $page_id = wp_insert_post([
            'post_title' => $title,
            'post_type' => 'page',
            'post_status' => 'draft',
            'menu_order' => 0,
            'post_parent' => 0
        ]);

        if (is_wp_error($page_id)) {
            wp_send_json_error(['message' => $page_id->get_error_message()]);
        }

        wp_send_json_success([
            'message' => 'Seite erstellt.',
            'page_id' => $page_id
        ]);
    }

    /**
     * AJAX handler: Delete page
     */
    public static function ajax_delete_page() {
        // Security check
        check_ajax_referer('page_manager_nonce', 'nonce');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(['message' => 'Keine Berechtigung.']);
        }

        $page_id = isset($_POST['page_id']) ? absint($_POST['page_id']) : 0;

        if (!$page_id) {
            wp_send_json_error(['message' => 'Keine Seiten-ID.']);
        }

        // Check if page exists
        $page = get_post($page_id);
        if (!$page || $page->post_type !== 'page') {
            wp_send_json_error(['message' => 'Seite nicht gefunden.']);
        }

        // Delete page permanently
        $result = wp_delete_post($page_id, true);

        if (!$result) {
            wp_send_json_error(['message' => 'Fehler beim Löschen.']);
        }

        wp_send_json_success(['message' => 'Seite gelöscht.']);
    }
}

// Initialize
Simple_Clean_Page_Manager::init();
