<?php
/**
 * Kapitellinks: REST-Endpunkte für die zweistufige Zielauswahl im Editor.
 *
 * Vorhaben „Kapitellinks" (PLAN-Summary-PDF-und-Content-Links.md, Phase 3,
 * AP-3.3). Das Editor-Werkzeug aus AP-3.4 fragt hier nacheinander zwei Dinge
 * ab:
 *
 *   1. Welche Seiten enthalten überhaupt einen Block fos/inhaltsverzeichnis?
 *   2. Welche Kapitel zeigt der Block auf einer bestimmten dieser Seiten?
 *
 * Aus der Antwort baut das Werkzeug den Link
 * <Permalink der Seite>#page-index-kapitel-<Kapitel-ID> — Ankerschema aus
 * AP-3.1 (Architekturentscheidung A6).
 *
 * WARUM ZWEI STUFEN UND KEINE AUTOMATIK: Das Theme erlaubt mehrere
 * fos/inhaltsverzeichnis-Blöcke mit unterschiedlichem rootPage. Zu einem
 * gegebenen Kapitel gäbe es deshalb nicht zwangsläufig genau eine passende
 * Zielseite. Die Auswahl trifft bewusst der Redakteur
 * (Architekturentscheidung A5, ausdrückliches Nicht-Ziel: „keine automatische
 * Ermittlung der Kapitellink-Zielseite").
 *
 * SICHERHEIT — zwei Punkte, die nicht aufgeweicht werden dürfen:
 *
 * a) Beide Routen sind auf `edit_posts` beschränkt. Sie geben Titel von
 *    Seiten aus, die als „Nur für Lehrpersonen sichtbar" gesperrt sein
 *    können; für nicht Berechtigte darf hier nichts herauskommen (Risiko R3
 *    im Plan, Abschnitt 5).
 *
 * b) Es wird ausschließlich mit WordPress-APIs gearbeitet — get_pages(),
 *    has_block(), get_post(), parse_blocks() — und mit der bereits
 *    vorhandenen simple_clean_page_index_daten(). KEINE rohen $wpdb-Abfragen:
 *    an denen greifen die Sichtbarkeitsfilter des Themes nicht ab (siehe
 *    CLAUDE.md, Abschnitt „Falle: rohe SQL-Abfragen greifen die Filter nicht
 *    ab" — dort hing schon einmal ein Leck).
 *
 * KEIN ZWISCHENSPEICHER. Der Seitenbaum wird bei jedem Aufruf frisch
 * ermittelt (dokumentierte, gemessene Projektentscheidung, siehe CLAUDE.md,
 * Abschnitt „Inhaltsverzeichnis-Block"). simple_clean_page_index_daten() hält
 * sein Ergebnis nur für die Dauer EINES Aufrufs statisch — das ist
 * unbedenklich und genau das, was hier genutzt wird.
 *
 * @package FOS_Online_Schulbuch
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Der Name des Blocks, um den sich hier alles dreht.
 *
 * Als Konstante, damit AP-3.4 und spätere Arbeiten nicht an drei Stellen
 * denselben String tippen und einer davon irgendwann abweicht.
 */
if (!defined('SIMPLE_CLEAN_PAGE_INDEX_BLOCK')) {
    define('SIMPLE_CLEAN_PAGE_INDEX_BLOCK', 'fos/inhaltsverzeichnis');
}

/**
 * Registriert die beiden Routen.
 *
 * Namespace simple-clean/v1 wie beim bestehenden Glossar-Endpunkt in
 * functions.php (simple_clean_register_glossar_rest_routes()).
 *
 * @return void
 */
function simple_clean_kapitellink_rest_routes() {
    // Eine gemeinsame Berechtigungsprüfung für beide Routen, damit sie nicht
    // auseinanderlaufen können.
    $darf_bearbeiten = function () {
        return current_user_can('edit_posts');
    };

    register_rest_route('simple-clean/v1', '/inhaltsverzeichnis-seiten', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'simple_clean_kapitellink_seiten',
        'permission_callback' => $darf_bearbeiten,
    ));

    register_rest_route('simple-clean/v1', '/inhaltsverzeichnis-kapitel', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'simple_clean_kapitellink_kapitel',
        'permission_callback' => $darf_bearbeiten,
        'args'                => array(
            'seite' => array(
                'required'          => true,
                'sanitize_callback' => 'absint',
                // Die Prüfung auf "existiert und ist eine Seite" macht der
                // Callback selbst — hier nur die Form.
                'validate_callback' => function ($wert) {
                    return is_numeric($wert) && (int) $wert > 0;
                },
            ),
        ),
    ));
}
add_action('rest_api_init', 'simple_clean_kapitellink_rest_routes');

/**
 * Alle veröffentlichten Seiten, die einen Inhaltsverzeichnis-Block enthalten.
 *
 * has_block() findet den Block auch dann, wenn er in einem anderen Block
 * verschachtelt ist (etwa in einem Container-Block des CDB-Designers) — die
 * Funktion sucht im serialisierten Blockkommentar, nicht in einer geparsten
 * Baumstruktur.
 *
 * @return WP_REST_Response Liste aus {id, title}.
 */
function simple_clean_kapitellink_seiten() {
    $seiten = get_pages(array(
        'post_status' => 'publish',
        'sort_column' => 'menu_order,post_title',
    ));

    $treffer = array();

    foreach ((array) $seiten as $seite) {
        if (!has_block(SIMPLE_CLEAN_PAGE_INDEX_BLOCK, $seite->post_content)) {
            continue;
        }

        $treffer[] = array(
            'id'    => (int) $seite->ID,
            // get_the_title() wendet die üblichen Filter an (u. a.
            // Sonderzeichen-Behandlung) — dasselbe, was der Editor sonst
            // überall anzeigt.
            'title' => get_the_title($seite),
        );
    }

    return rest_ensure_response($treffer);
}

/**
 * Die Kapitel des Inhaltsverzeichnis-Blocks einer bestimmten Seite.
 *
 * „Kapitel" heißt hier exakt dasselbe wie im Renderer: die Knoten auf Ebene 0
 * RELATIV ZUM rootPage des Blocks — also genau die Einträge, an denen AP-3.1
 * die Anker-IDs vergibt.
 *
 * ABWEICHUNG VOM PLAN-TEXT (bewusst, siehe Übergabenotiz zu AP-3.3): Der Plan
 * schlägt vor, „alle Knoten mit depth === 0" zurückzugeben. Das Feld `depth`
 * in simple_clean_page_index_daten() zählt aber die Tiefe ab der WURZEL DER
 * WEBSITE, nicht ab rootPage. Bei einem Block mit rootPage != 0 hätte der
 * Vorschlag die obersten Seiten der ganzen Website geliefert statt der
 * Kapitel dieses Blocks. Verwendet wird deshalb $daten['children'][$rootPage]
 * — dieselbe Quelle, aus der simple_clean_render_page_index() seine
 * $start_ids zieht. Damit stimmt die Auswahlliste im Editor zwangsläufig mit
 * dem überein, was auf der Seite tatsächlich steht.
 *
 * @param WP_REST_Request $anfrage Anfrage mit dem Parameter `seite`.
 * @return WP_REST_Response Liste aus {id, title}; leer, wenn kein Block da ist.
 */
function simple_clean_kapitellink_kapitel($anfrage) {
    $seiten_id = absint($anfrage->get_param('seite'));
    $seite     = get_post($seiten_id);

    // Kein Fehlerstatus, sondern eine leere Liste: Für das Auswahlfeld im
    // Editor ist „hier gibt es nichts zu wählen" die brauchbarere Antwort als
    // eine Ausnahme, und es verrät nebenbei nicht, ob es die ID gibt.
    if (!$seite || 'page' !== $seite->post_type || 'publish' !== $seite->post_status) {
        return rest_ensure_response(array());
    }

    $block = simple_clean_kapitellink_finde_block(parse_blocks($seite->post_content));
    if (null === $block) {
        return rest_ensure_response(array());
    }

    // Über dieselbe Bereinigung wie der Renderer, damit rootPage hier und dort
    // identisch ausgelegt wird (absint, Standard 0).
    $attrs  = simple_clean_page_index_sanitize_attrs(
        isset($block['attrs']) ? $block['attrs'] : array()
    );
    $daten  = simple_clean_page_index_daten();
    $wurzel = $attrs['rootPage'];

    // Denselben Rückfall wie simple_clean_render_page_index(): Zeigt rootPage
    // auf eine Seite, die im Baum nicht (mehr) vorkommt, rendert der Block die
    // oberste Ebene. Ohne diesen Zweig böte der Editor eine leere Kapitelliste
    // an, während die Seite sichtbar Kapitel zeigt.
    if ($wurzel > 0 && !isset($daten['nodes'][$wurzel])) {
        $wurzel = 0;
    }

    $kapitel_ids = isset($daten['children'][$wurzel]) ? $daten['children'][$wurzel] : array();

    $kapitel = array();
    foreach ($kapitel_ids as $id) {
        if (!isset($daten['nodes'][$id])) {
            continue;
        }
        $kapitel[] = array(
            'id'    => (int) $id,
            'title' => $daten['nodes'][$id]['title'],
        );
    }

    return rest_ensure_response($kapitel);
}

/**
 * Hängt das Editor-Script für das Werkzeug „Kapitellink" ein (AP-3.4).
 *
 * PLATZIERUNG: Der AP-Text lässt die Wahl zwischen dieser Datei und
 * `includes/page-index.php` (neben `simple_clean_page_index_editor_assets()`).
 * Sie steht hier, weil das Script ausschließlich die beiden REST-Routen
 * dieser Datei bedient und mit dem Verzeichnis-Block selbst nichts zu tun hat
 * — der Block wird vom Werkzeug nur als Datenquelle gelesen, nicht bearbeitet.
 * So bleibt „Kapitellinks" in einer Datei beisammen und `page-index.php`
 * kümmert sich weiterhin allein um den Block.
 *
 * Struktur wie `simple_clean_page_index_editor_assets()`: Existenzprüfung der
 * gebauten Datei, `filemtime()` als Version (Cache-Busting), im Fußbereich.
 *
 * @return void
 */
function simple_clean_kapitellink_editor_assets() {
    $js_datei = get_template_directory() . '/dist/js/kapitellink-format.js';

    if (!file_exists($js_datei)) {
        return;
    }

    wp_enqueue_script(
        'simple-clean-kapitellink-format',
        get_template_directory_uri() . '/dist/js/kapitellink-format.js',
        array(
            'wp-rich-text',
            'wp-element',
            'wp-components',
            'wp-block-editor',
            'wp-api-fetch',
        ),
        filemtime($js_datei),
        true
    );
}
add_action('enqueue_block_editor_assets', 'simple_clean_kapitellink_editor_assets');

/**
 * Sucht rekursiv den ersten Inhaltsverzeichnis-Block in einem Blockbaum.
 *
 * Rekursiv, weil der Block auch in einem Container-Block des CDB-Designers
 * stecken kann — parse_blocks() liefert solche Kinder in `innerBlocks`, und
 * ein flacher Durchlauf übersähe sie.
 *
 * „Der erste" ist eine bewusste Festlegung: Stehen mehrere Verzeichnisblöcke
 * auf einer Seite, gewinnt der in der Dokumentreihenfolge erste. Dasselbe
 * Vorkommen gewinnt auch bei den Anker-IDs aus AP-3.1, wenn IDs doppelt
 * vorkommen (getElementById nimmt das erste) — beide Stellen treffen damit
 * dieselbe Wahl.
 *
 * @param array $bloecke Rückgabewert von parse_blocks().
 * @return array|null Der Block als Array oder null.
 */
function simple_clean_kapitellink_finde_block($bloecke) {
    foreach ((array) $bloecke as $block) {
        if (isset($block['blockName']) && SIMPLE_CLEAN_PAGE_INDEX_BLOCK === $block['blockName']) {
            return $block;
        }

        if (!empty($block['innerBlocks'])) {
            $treffer = simple_clean_kapitellink_finde_block($block['innerBlocks']);
            if (null !== $treffer) {
                return $treffer;
            }
        }
    }

    return null;
}
