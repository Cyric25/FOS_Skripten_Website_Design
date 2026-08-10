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
        add_action('wp_ajax_page_manager_toggle_status', [__CLASS__, 'ajax_toggle_status']);
        add_action('wp_ajax_page_manager_bulk_action', [__CLASS__, 'ajax_bulk_action']);
    }

    /**
     * Erlaubte Sammelaktionen.
     *
     * Bewusst eine Whitelist: Der Wert aus $_POST wird nur gegen diese Liste
     * geprüft und nie in einen Methodennamen o. Ä. übersetzt.
     *
     * @return array Aktionsschlüssel => Beschriftung
     */
    private static function bulk_aktionen() {
        return [
            'status_publish' => 'Veröffentlichen',
            'status_draft'   => 'Auf Entwurf setzen',
            'set_parent'     => 'Elternseite zuweisen',
            'hide_index'     => 'Aus Inhaltsverzeichnis ausnehmen',
            'show_index'     => 'Wieder ins Inhaltsverzeichnis aufnehmen',
            'hide_nav'       => 'Aus Seitenleiste ausnehmen',
            'show_nav'       => 'Wieder in Seitenleiste aufnehmen',
            'trash'          => 'In den Papierkorb',
        ];
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

        // Get ALL pages in one query and build a parent => children map
        // (avoids one query per tree node)
        $all_pages = get_pages([
            'sort_column' => 'menu_order, post_title',
            'post_status' => ['publish', 'draft', 'pending', 'private'],
        ]);

        $children_map = [];
        foreach ($all_pages as $tree_page) {
            $children_map[$tree_page->post_parent][] = $tree_page;
        }

        $pages = isset($children_map[0]) ? $children_map[0] : [];

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

            <div class="page-bulk-bar">
                <label class="page-bulk-all">
                    <input type="checkbox" id="page-select-all" />
                    Alle auswählen
                </label>

                <span id="page-bulk-count">0 ausgewählt</span>

                <select id="page-bulk-action">
                    <option value="">— Aktion wählen —</option>
                    <optgroup label="Status">
                        <option value="status_publish">Veröffentlichen</option>
                        <option value="status_draft">Auf Entwurf setzen</option>
                    </optgroup>
                    <optgroup label="Hierarchie">
                        <option value="set_parent">Elternseite zuweisen</option>
                    </optgroup>
                    <optgroup label="Sichtbarkeit">
                        <option value="hide_index">Aus Inhaltsverzeichnis ausnehmen</option>
                        <option value="show_index">Wieder ins Inhaltsverzeichnis aufnehmen</option>
                        <option value="hide_nav">Aus Seitenleiste ausnehmen</option>
                        <option value="show_nav">Wieder in Seitenleiste aufnehmen</option>
                    </optgroup>
                    <optgroup label="Löschen">
                        <option value="trash">In den Papierkorb</option>
                    </optgroup>
                </select>

                <select id="page-bulk-parent" hidden>
                    <option value="0">(oberste Ebene)</option>
                    <?php
                    // Aus dem bereits geladenen Baum, ohne zusätzliche Abfrage.
                    self::render_parent_options($children_map, 0, 0);
                    ?>
                </select>

                <button type="button" class="button" id="page-bulk-apply" disabled>
                    Ausführen
                </button>
            </div>

            <!-- Modal für neue Seite -->
            <div id="new-page-modal" class="page-manager-modal" style="display:none;">
                <div class="page-manager-modal-content">
                    <h2 id="new-page-modal-title">Neue Seite erstellen</h2>
                    <p id="new-page-modal-parent-info" class="modal-parent-info" style="display:none;">
                        Unterseite von: <strong id="new-page-parent-name"></strong>
                    </p>
                    <input type="hidden" id="new-page-parent-id" value="0" />
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
                            <?php self::render_page_item($page, $children_map); ?>
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
     * @param array $children_map Prebuilt map: parent ID => array of child WP_Post objects
     */
    private static function render_page_item($page, $children_map) {
        // Get children from the prebuilt map (no extra query per node)
        $children = isset($children_map[$page->ID]) ? $children_map[$page->ID] : [];

        $has_children = !empty($children);
        $status_class = 'status-' . $page->post_status;

        ?>
        <li class="page-item <?php echo $status_class; ?> <?php echo $has_children ? 'has-children' : ''; ?>"
            data-page-id="<?php echo esc_attr($page->ID); ?>"
            data-parent-id="<?php echo esc_attr($page->post_parent); ?>">

            <div class="page-item-row">
                <input type="checkbox" class="page-select"
                       value="<?php echo esc_attr($page->ID); ?>"
                       aria-label="<?php echo esc_attr(sprintf('Seite „%s" auswählen', $page->post_title)); ?>" />

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
                    <button type="button" class="button button-small toggle-status"
                            data-page-id="<?php echo esc_attr($page->ID); ?>"
                            data-current-status="<?php echo esc_attr($page->post_status); ?>"
                            title="Status ändern">
                        <?php if ($page->post_status === 'publish'): ?>
                            <span class="dashicons dashicons-visibility"></span>
                        <?php else: ?>
                            <span class="dashicons dashicons-hidden"></span>
                        <?php endif; ?>
                    </button>
                    <a href="<?php echo esc_url(get_edit_post_link($page->ID)); ?>"
                       class="button button-small" title="Bearbeiten">
                        <span class="dashicons dashicons-edit"></span>
                    </a>
                    <a href="<?php echo esc_url(get_permalink($page->ID)); ?>"
                       class="button button-small" target="_blank" title="Ansehen">
                        <span class="dashicons dashicons-external"></span>
                    </a>
                    <button type="button" class="button button-small create-child-page"
                            data-page-id="<?php echo esc_attr($page->ID); ?>"
                            data-page-title="<?php echo esc_attr($page->post_title); ?>"
                            title="Unterseite erstellen">
                        <span class="dashicons dashicons-plus-alt2"></span>
                    </button>
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
                        <?php self::render_page_item($child, $children_map); ?>
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
     * Gibt die Auswahloptionen für „Elternseite zuweisen" aus.
     *
     * Rekursiv über die bereits aufgebaute Kind-Karte – kein zusätzlicher
     * Datenbankzugriff. Die Tiefe wird durch Einrückung sichtbar gemacht.
     *
     * @param array $children_map Karte: Eltern-ID => Kindseiten
     * @param int   $parent_id    Aktuelle Ebene
     * @param int   $tiefe        Verschachtelungstiefe (für die Einrückung)
     */
    private static function render_parent_options($children_map, $parent_id, $tiefe) {
        if (empty($children_map[$parent_id]) || $tiefe > 6) {
            return;
        }
        foreach ($children_map[$parent_id] as $page) {
            $einzug = str_repeat('— ', $tiefe);
            echo '<option value="' . esc_attr($page->ID) . '">'
                . esc_html($einzug . $page->post_title)
                . '</option>';
            self::render_parent_options($children_map, $page->ID, $tiefe + 1);
        }
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

            // Per-page permission check
            if (!current_user_can('edit_page', $page_id)) {
                $errors[] = "Keine Berechtigung für Seite ID $page_id";
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
        $parent_id = isset($_POST['parent_id']) ? absint($_POST['parent_id']) : 0;

        if (empty($title)) {
            wp_send_json_error(['message' => 'Titel erforderlich.']);
        }

        // Verify parent exists if specified
        if ($parent_id > 0) {
            $parent = get_post($parent_id);
            if (!$parent || $parent->post_type !== 'page') {
                wp_send_json_error(['message' => 'Elternseite nicht gefunden.']);
            }
        }

        // Create new page (top level or as child)
        $page_id = wp_insert_post([
            'post_title' => $title,
            'post_type' => 'page',
            'post_status' => 'draft',
            'menu_order' => 0,
            'post_parent' => $parent_id
        ]);

        if (is_wp_error($page_id)) {
            wp_send_json_error(['message' => $page_id->get_error_message()]);
        }

        wp_send_json_success([
            'message' => 'Seite erstellt.',
            'page_id' => $page_id,
            'parent_id' => $parent_id
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

        // Deleting requires the delete capability for this specific page
        if (!current_user_can('delete_page', $page_id)) {
            wp_send_json_error(['message' => 'Keine Berechtigung zum Löschen dieser Seite.']);
        }

        // Move page to trash (recoverable, not permanent)
        $result = wp_trash_post($page_id);

        if (!$result) {
            wp_send_json_error(['message' => 'Fehler beim Löschen.']);
        }

        wp_send_json_success(['message' => 'Seite in den Papierkorb verschoben.']);
    }

    /**
     * AJAX handler: Toggle page status
     */
    public static function ajax_toggle_status() {
        // Security check
        check_ajax_referer('page_manager_nonce', 'nonce');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(['message' => 'Keine Berechtigung.']);
        }

        $page_id = isset($_POST['page_id']) ? absint($_POST['page_id']) : 0;

        if (!$page_id) {
            wp_send_json_error(['message' => 'Keine Seiten-ID.']);
        }

        // Get page
        $page = get_post($page_id);
        if (!$page || $page->post_type !== 'page') {
            wp_send_json_error(['message' => 'Seite nicht gefunden.']);
        }

        if (!current_user_can('edit_page', $page_id)) {
            wp_send_json_error(['message' => 'Keine Berechtigung für diese Seite.']);
        }

        // Toggle status: publish <-> draft
        $new_status = ($page->post_status === 'publish') ? 'draft' : 'publish';

        // Publishing requires the publish capability
        if ($new_status === 'publish' && !current_user_can('publish_pages')) {
            wp_send_json_error(['message' => 'Keine Berechtigung zum Veröffentlichen.']);
        }

        // Update status
        $result = wp_update_post([
            'ID' => $page_id,
            'post_status' => $new_status
        ]);

        if (is_wp_error($result)) {
            wp_send_json_error(['message' => 'Fehler beim Ändern des Status.']);
        }

        // Get localized status labels
        $status_labels = [
            'publish' => 'Veröffentlicht',
            'draft' => 'Entwurf'
        ];

        wp_send_json_success([
            'message' => 'Status geändert zu: ' . $status_labels[$new_status],
            'new_status' => $new_status,
            'icon' => ($new_status === 'publish') ? 'dashicons-visibility' : 'dashicons-hidden'
        ]);
    }

    /**
     * AJAX handler: Sammelaktion für mehrere Seiten
     *
     * Folgt dem Muster von ajax_update_order(): Rechteprüfung je EINZELSEITE,
     * Fehler werden gesammelt statt beim ersten Problem abzubrechen.
     *
     * ZWEI SCHREIBWEGE, BEWUSST UNTERSCHIEDLICH:
     * - Status über wp_update_post(), weil dabei save_post feuert. Nur so
     *   läuft simple_clean_update_glossar_candidates() mit und die Seite
     *   bekommt ihr Meta _glossar_scan_version. Ohne dieses Meta fällt sie
     *   beim Rendern auf ALLE Glossarbegriffe zurück — gemessen 1,998 s statt
     *   0,058 s bei 1049 Begriffen.
     * - Elternzuweisung über $wpdb->update() plus clean_post_cache(), wie in
     *   ajax_update_order(). Der Inhalt ändert sich dabei nicht, ein
     *   Glossar-Scan wäre unnötig.
     */
    public static function ajax_bulk_action() {
        check_ajax_referer('page_manager_nonce', 'nonce');

        if (!current_user_can('edit_pages')) {
            wp_send_json_error(['message' => 'Keine Berechtigung.']);
        }

        $aktion = isset($_POST['bulk_action']) ? sanitize_key($_POST['bulk_action']) : '';
        $erlaubt = self::bulk_aktionen();
        if (!isset($erlaubt[$aktion])) {
            wp_send_json_error(['message' => 'Unbekannte Aktion.']);
        }

        $ids = isset($_POST['page_ids']) ? (array) $_POST['page_ids'] : [];
        $ids = array_values(array_unique(array_filter(array_map('absint', $ids))));
        $ids = array_slice($ids, 0, 500);

        if (empty($ids)) {
            wp_send_json_error(['message' => 'Keine Seiten ausgewählt.']);
        }

        // Zielelternteil nur bei set_parent
        $neuer_parent = 0;
        if ($aktion === 'set_parent') {
            $neuer_parent = isset($_POST['parent_id']) ? absint($_POST['parent_id']) : 0;
            if ($neuer_parent > 0) {
                $ziel = get_post($neuer_parent);
                if (!$ziel || $ziel->post_type !== 'page') {
                    wp_send_json_error(['message' => 'Zielseite nicht gefunden.']);
                }
            }
        }

        global $wpdb;
        $geaendert = 0;
        $uebersprungen = 0;
        $errors = [];

        foreach ($ids as $id) {
            $page = get_post($id);
            if (!$page || $page->post_type !== 'page') {
                $errors[] = "Seite ID $id nicht gefunden";
                continue;
            }

            if (!current_user_can('edit_page', $id)) {
                $errors[] = "Keine Berechtigung für „{$page->post_title}\"";
                continue;
            }

            switch ($aktion) {
                case 'status_publish':
                    if (!current_user_can('publish_pages')) {
                        $errors[] = "Keine Berechtigung zum Veröffentlichen von „{$page->post_title}\"";
                        break;
                    }
                    if ($page->post_status === 'publish') {
                        $uebersprungen++;
                        break;
                    }
                    $r = wp_update_post(['ID' => $id, 'post_status' => 'publish'], true);
                    if (is_wp_error($r)) {
                        $errors[] = "„{$page->post_title}\": " . $r->get_error_message();
                    } else {
                        $geaendert++;
                    }
                    break;

                case 'status_draft':
                    if ($page->post_status === 'draft') {
                        $uebersprungen++;
                        break;
                    }
                    $r = wp_update_post(['ID' => $id, 'post_status' => 'draft'], true);
                    if (is_wp_error($r)) {
                        $errors[] = "„{$page->post_title}\": " . $r->get_error_message();
                    } else {
                        $geaendert++;
                    }
                    break;

                case 'trash':
                    if (!current_user_can('delete_page', $id)) {
                        $errors[] = "Keine Berechtigung zum Löschen von „{$page->post_title}\"";
                        break;
                    }
                    if (wp_trash_post($id)) {
                        $geaendert++;
                    } else {
                        $errors[] = "Fehler beim Löschen von „{$page->post_title}\"";
                    }
                    break;

                case 'set_parent':
                    if ($id === $neuer_parent) {
                        $errors[] = "„{$page->post_title}\" kann nicht ihr eigenes Elternteil sein";
                        break;
                    }
                    if ($neuer_parent > 0 && self::would_create_circular_reference($id, $neuer_parent)) {
                        $errors[] = "„{$page->post_title}\" würde eine Schleife in der Hierarchie erzeugen";
                        break;
                    }
                    if ((int) $page->post_parent === $neuer_parent) {
                        $uebersprungen++;
                        break;
                    }
                    $r = $wpdb->update(
                        $wpdb->posts,
                        ['post_parent' => $neuer_parent],
                        ['ID' => $id],
                        ['%d'],
                        ['%d']
                    );
                    if ($r === false) {
                        $errors[] = "Fehler beim Verschieben von „{$page->post_title}\"";
                    } else {
                        clean_post_cache($id);
                        $geaendert++;
                    }
                    break;

                case 'hide_index':
                    update_post_meta($id, '_simple_clean_hide_from_index', '1');
                    $geaendert++;
                    break;

                case 'show_index':
                    delete_post_meta($id, '_simple_clean_hide_from_index');
                    $geaendert++;
                    break;

                case 'hide_nav':
                    update_post_meta($id, '_simple_clean_hide_navigation', '1');
                    $geaendert++;
                    break;

                case 'show_nav':
                    delete_post_meta($id, '_simple_clean_hide_navigation');
                    $geaendert++;
                    break;
            }
        }

        // Nur Aktionen, die den Baum sichtbar verändern, brauchen ein Neuladen.
        $reload = in_array($aktion, ['status_publish', 'status_draft', 'trash', 'set_parent'], true);

        $meldung = sprintf('%d Seite(n) geändert.', $geaendert);
        if ($uebersprungen > 0) {
            $meldung .= sprintf(' %d ohne Änderung.', $uebersprungen);
        }

        wp_send_json_success([
            'aktion'        => $aktion,
            'geaendert'     => $geaendert,
            'uebersprungen' => $uebersprungen,
            'errors'        => $errors,
            'message'       => $meldung,
            'reload'        => $reload,
        ]);
    }
}

// Initialize
Simple_Clean_Page_Manager::init();
