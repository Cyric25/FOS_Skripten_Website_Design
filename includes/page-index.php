<?php
/**
 * Inhaltsverzeichnis-Block (fos/inhaltsverzeichnis)
 *
 * Erzeugt die Übersicht der Unterseiten eines Kapitels. Ersetzt den
 * Core-Block "Seitenliste" (core/page-list) auf den Kapitelübersichten.
 *
 * WARUM EIN EIGENER BLOCK
 * core/page-list ruft get_pages() auf, lädt damit alle Seiten der Website als
 * vollständige WP_Post-Objekte einschließlich post_content und ruft für jede
 * Seite einzeln get_permalink(), was die Elternkette je Seite erneut auflöst.
 * Zudem kennt er weder Tiefenbegrenzung noch Aufklappen, sodass bei vielen
 * Seiten eine sehr lange Liste entsteht.
 *
 * WARUM OHNE ZWISCHENSPEICHER
 * Der ursprüngliche Plan sah einen vorberechneten Seitenindex in wp_options
 * mit Versionszähler, Invalidierungshooks und Fragment-Cache vor. Eine
 * Messung am 2026-08-08 hat diese Annahme widerlegt: Der Seitenbaum kostet
 * bei 258 Seiten rund 0,03 s. Die eigentliche Langsamkeit kam aus der
 * Glossar-Verarbeitung (behoben in v1.5.70). Ein Zwischenspeicher würde damit
 * kein gemessenes Problem lösen, aber eigene Fehlerquellen einführen — allen
 * voran veraltete Ausgabe nach direkten SQL-Änderungen im Seitenmanager, der
 * post_parent und menu_order an save_post vorbei schreibt.
 * Stattdessen: eine schlanke Abfrage je Seitenaufruf, Ergebnis in einer
 * statischen Variablen. Begründung ausführlich in
 * docs/PLAN-Seitenindex.md, Abschnitt 11.
 *
 * GRUNDREGEL FÜR DEN RENDERER
 * Die Ausgabe hängt allein von den Blockattributen ab — nicht davon, welche
 * Seite gerade aufgerufen wird oder wer angemeldet ist. Keine Hervorhebung
 * der aktuellen Seite. Das hält den Renderer einfach und sein Verhalten
 * vorhersagbar.
 *
 * @package FOS_Online_Schulbuch
 * @since 1.5.71
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Erlaubte Werte für das Attribut "layout".
 *
 * Als Funktion statt als Konstante, damit die Liste an genau einer Stelle
 * steht und sowohl die Bereinigung als auch spätere Prüfungen sie nutzen.
 *
 * @return array
 */
function simple_clean_page_index_layouts() {
    return array('cards', 'list', 'columns');
}

/**
 * Bereinigt die Blockattribute und füllt fehlende mit den Standardwerten.
 *
 * Einzige Stelle, an der Attributwerte normalisiert werden. Renderer und
 * Editor verlassen sich darauf, dass hier IMMER alle acht Schlüssel in
 * fester Reihenfolge zurückkommen — auch wenn ein leeres Array hereingegeben
 * wird.
 *
 * Bewusst OHNE Datenbankzugriff: Ob die in rootPage genannte Seite existiert,
 * prüft der Renderer, wenn er die Daten ohnehin vorliegen hat. Diese Funktion
 * bleibt dadurch rein und ohne Seiteneffekte prüfbar.
 *
 * @param array $attrs Rohe Attribute aus dem Block.
 * @return array Bereinigte Attribute, feste Schlüsselreihenfolge.
 */
function simple_clean_page_index_sanitize_attrs($attrs) {
    if (!is_array($attrs)) {
        $attrs = array();
    }

    // Hilfsfunktion für Ganzzahlen mit Ober- und Untergrenze.
    $zahl_im_bereich = function ($wert, $min, $max, $standard) {
        if (!isset($wert) || !is_numeric($wert)) {
            return $standard;
        }
        $wert = (int) $wert;
        if ($wert < $min) {
            return $min;
        }
        if ($wert > $max) {
            return $max;
        }
        return $wert;
    };

    // Hilfsfunktion für Wahrheitswerte: fehlt das Attribut, gilt der Standard.
    $wahrheitswert = function ($attrs, $schluessel, $standard) {
        return array_key_exists($schluessel, $attrs)
            ? (bool) $attrs[$schluessel]
            : $standard;
    };

    $layout = isset($attrs['layout']) ? (string) $attrs['layout'] : 'cards';
    if (!in_array($layout, simple_clean_page_index_layouts(), true)) {
        $layout = 'cards';
    }

    // Reihenfolge ist bewusst fest — erleichtert Vergleiche und lesbare Diffs.
    return array(
        'rootPage'      => isset($attrs['rootPage']) ? absint($attrs['rootPage']) : 0,
        'maxDepth'      => $zahl_im_bereich(isset($attrs['maxDepth']) ? $attrs['maxDepth'] : null, 1, 5, 2),
        'layout'        => $layout,
        'columns'       => $zahl_im_bereich(isset($attrs['columns']) ? $attrs['columns'] : null, 1, 4, 3),
        'collapsible'   => $wahrheitswert($attrs, 'collapsible', true),
        'openByDefault' => $wahrheitswert($attrs, 'openByDefault', false),
        'showSearch'    => $wahrheitswert($attrs, 'showSearch', true),
        'showCounts'    => $wahrheitswert($attrs, 'showCounts', false),
    );
}

/**
 * Liefert den Seitenbaum als schlanke Struktur.
 *
 * Zwei Abfragen, danach alles in PHP. Der Rückgabewert wird in einer
 * statischen Variablen gehalten, damit mehrere Blöcke auf derselben Seite
 * die Abfragen teilen.
 *
 * Bewusst NICHT über get_pages(): Das lädt vollständige WP_Post-Objekte
 * einschließlich post_content. Hier werden fünf Spalten geholt und alle
 * Pfade in einem Durchlauf von oben nach unten berechnet, statt je Seite
 * get_permalink() zu rufen (was die Elternkette jedes Mal neu auflöst).
 *
 * Aufbau des Rückgabewerts:
 *   array(
 *     'nodes'    => array( ID => array('id','parent','title','slug','uri','depth') ),
 *     'children' => array( ElternID => array(KindID, …) )   // bereits sortiert
 *   )
 *
 * @return array
 */
function simple_clean_page_index_daten() {
    static $daten = null;

    if ($daten !== null) {
        return $daten;
    }

    global $wpdb;

    // Keine Nutzereingaben in der Abfrage; $wpdb->posts kommt über die
    // Eigenschaft, nicht als zusammengebaute Zeichenkette.
    $zeilen = $wpdb->get_results(
        "SELECT ID, post_parent, post_title, post_name
         FROM {$wpdb->posts}
         WHERE post_type = 'page' AND post_status = 'publish'
         ORDER BY menu_order ASC, post_title ASC"
    );

    // Seiten, die per Meta aus dem Verzeichnis genommen wurden. Das Meta setzt
    // die Meta-Box (AP-4.4); bis dahin ist das Ergebnis leer — das ist so
    // gewollt, damit später keine Änderung an dieser Datei nötig ist.
    $ausgeschlossen = $wpdb->get_col(
        "SELECT post_id FROM {$wpdb->postmeta}
         WHERE meta_key = '_simple_clean_hide_from_index' AND meta_value = '1'"
    );
    // array_flip, damit die Prüfung unten ein isset() ist und keine Suche.
    $ausgeschlossen = array_flip(array_map('intval', (array) $ausgeschlossen));

    $roh    = array();
    $kinder = array();
    foreach ($zeilen as $zeile) {
        $id     = (int) $zeile->ID;
        $parent = (int) $zeile->post_parent;

        $roh[$id] = array(
            'id'     => $id,
            'parent' => $parent,
            'title'  => $zeile->post_title,
            'slug'   => $zeile->post_name,
        );
        // Reihenfolge der SQL-Sortierung bleibt erhalten.
        $kinder[$parent][] = $id;
    }

    // Baum von oben nach unten durchlaufen (Breitensuche ab Wurzel 0).
    //
    // Der Durchlauf leistet drei Dinge auf einmal:
    //  - Pfade: der Elternpfad ist bereits bekannt, wenn ein Kind an die Reihe
    //    kommt. Ein Anhängen genügt, kein erneutes Auflösen der Elternkette.
    //  - Verwaiste Knoten: Wessen Elternteil nicht im Ergebnis steht (etwa
    //    weil es ein Entwurf ist), wird von der Wurzel aus nie erreicht und
    //    fällt samt Unterbaum heraus. Das entspricht dem Verhalten von
    //    WordPress-Permalinks und verhindert unerreichbare Einträge.
    //  - Zyklen: Ein Ring aus Eltern-Kind-Beziehungen ist von der Wurzel aus
    //    ebenfalls nicht erreichbar. Die Besuchsliste sichert zusätzlich ab.
    $nodes     = array();
    $besucht   = array();
    $schlange  = array();
    $zeiger    = 0;
    $max_tiefe = 20;

    $wurzeln = isset($kinder[0]) ? $kinder[0] : array();
    foreach ($wurzeln as $id) {
        $schlange[] = array($id, 0, '');
    }

    while ($zeiger < count($schlange)) {
        list($id, $tiefe, $elternpfad) = $schlange[$zeiger];
        $zeiger++;

        if (isset($besucht[$id]) || !isset($roh[$id])) {
            continue;
        }
        // Ausgeschlossene Seiten samt Unterbaum überspringen: Der Knoten wird
        // nicht aufgenommen und seine Kinder gelangen nie in die Schlange.
        if (isset($ausgeschlossen[$id])) {
            continue;
        }
        if ($tiefe > $max_tiefe) {
            error_log(sprintf(
                'Seitenindex: Verschachtelung tiefer als %d Ebenen bei Seite %d - Zweig ausgelassen.',
                $max_tiefe,
                $id
            ));
            continue;
        }

        $besucht[$id] = true;

        $slug = $roh[$id]['slug'];
        $uri  = ('' === $elternpfad) ? $slug : $elternpfad . '/' . $slug;

        $nodes[$id] = array(
            'id'     => $id,
            'parent' => $roh[$id]['parent'],
            'title'  => $roh[$id]['title'],
            'slug'   => $slug,
            'uri'    => $uri,
            'depth'  => $tiefe,
        );

        if (isset($kinder[$id])) {
            foreach ($kinder[$id] as $kind_id) {
                $schlange[] = array($kind_id, $tiefe + 1, $uri);
            }
        }
    }

    // Kinderliste neu aufbauen — nur mit den Knoten, die den Durchlauf
    // überstanden haben. Die Reihenfolge bleibt korrekt, weil die
    // Breitensuche Geschwister eines Elternteils zusammenhängend und in der
    // ursprünglichen Sortierung abarbeitet.
    $kinder_gefiltert = array();
    foreach ($nodes as $id => $node) {
        $kinder_gefiltert[$node['parent']][] = $id;
    }

    $daten = array(
        'nodes'    => $nodes,
        'children' => $kinder_gefiltert,
    );

    return $daten;
}

/**
 * Baut die öffentliche URL eines Knotens.
 *
 * @param array $node Knoten aus simple_clean_page_index_daten().
 * @return string
 */
function simple_clean_page_index_url($node) {
    static $sprechend = null;
    if ($sprechend === null) {
        $sprechend = (bool) get_option('permalink_structure');
    }

    return $sprechend
        ? home_url('/' . $node['uri'] . '/')
        : home_url('/?page_id=' . $node['id']);
}

/**
 * Rendert eine Liste von Knoten samt Unterebenen.
 *
 * Ebene 0 sind die Kapitel, alles darunter sind Unterseiten. $ebene ist der
 * Index (0-basiert), $attrs['maxDepth'] die Anzahl der Ebenen — bei
 * maxDepth = 2 wird also Ebene 0 und Ebene 1 ausgegeben.
 *
 * @param array $ids     IDs der auszugebenden Knoten.
 * @param array $daten   Rückgabewert von simple_clean_page_index_daten().
 * @param array $attrs   Bereinigte Blockattribute.
 * @param int   $ebene   Aktuelle Ebene, 0-basiert.
 * @param array $besucht Referenz auf die Besuchsliste (Rekursionsschutz).
 * @return string HTML oder leere Zeichenkette.
 */
function simple_clean_page_index_liste($ids, $daten, $attrs, $ebene, &$besucht) {
    if (empty($ids) || $ebene >= $attrs['maxDepth']) {
        return '';
    }

    $ist_kapitel = (0 === $ebene);
    $listen_klasse = $ist_kapitel ? 'page-index__chapters' : 'page-index__pages';
    $eintrag_klasse = $ist_kapitel ? 'page-index__chapter' : 'page-index__page';
    $link_klasse = $ist_kapitel ? 'page-index__chapter-link' : 'page-index__page-link';

    $html = '<ul class="' . esc_attr($listen_klasse) . '">';

    foreach ($ids as $id) {
        if (!isset($daten['nodes'][$id]) || isset($besucht[$id])) {
            continue;
        }
        $besucht[$id] = true;

        $node = $daten['nodes'][$id];

        $html .= '<li class="' . esc_attr($eintrag_klasse) . '">';
        $html .= '<a class="' . esc_attr($link_klasse) . '" href="'
            . esc_url(simple_clean_page_index_url($node)) . '">'
            . esc_html($node['title']) . '</a>';

        $kind_ids = isset($daten['children'][$id]) ? $daten['children'][$id] : array();
        $unterliste = simple_clean_page_index_liste($kind_ids, $daten, $attrs, $ebene + 1, $besucht);

        if ('' !== $unterliste) {
            if ($attrs['collapsible']) {
                $beschriftung = $attrs['showCounts']
                    ? sprintf(
                        /* translators: %d: Anzahl der Unterseiten */
                        _n('%d Unterseite', '%d Unterseiten', count($kind_ids), 'fos-online-schulbuch'),
                        count($kind_ids)
                    )
                    : __('Unterseiten', 'fos-online-schulbuch');

                $html .= '<details class="page-index__sub"' . ($attrs['openByDefault'] ? ' open' : '') . '>';
                $html .= '<summary class="page-index__sub-toggle">' . esc_html($beschriftung) . '</summary>';
                $html .= $unterliste;
                $html .= '</details>';
            } else {
                $html .= $unterliste;
            }
        }

        $html .= '</li>';
    }

    $html .= '</ul>';

    return $html;
}

/**
 * Rendert den Block.
 *
 * Signatur entspricht dem, was WordPress einem render_callback übergibt.
 * Die Funktion GIBT ZURÜCK und gibt nichts direkt aus — ein render_callback,
 * der echo verwendet, platziert seine Ausgabe an der falschen Stelle im
 * Dokument.
 *
 * @param array    $attributes Blockattribute.
 * @param string   $content    Innerer Inhalt (hier ungenutzt).
 * @param WP_Block $block      Blockinstanz (hier ungenutzt).
 * @return string HTML.
 */
function simple_clean_render_page_index($attributes = array(), $content = '', $block = null) {
    $attrs = simple_clean_page_index_sanitize_attrs($attributes);
    $daten = simple_clean_page_index_daten();

    // Zeigt rootPage auf eine Seite, die es nicht (mehr) gibt, auf die oberste
    // Ebene zurückfallen statt eine leere Seite auszugeben.
    $wurzel = $attrs['rootPage'];
    if ($wurzel > 0 && !isset($daten['nodes'][$wurzel])) {
        $wurzel = 0;
    }

    $start_ids = isset($daten['children'][$wurzel]) ? $daten['children'][$wurzel] : array();

    $besucht = array();
    $liste   = simple_clean_page_index_liste($start_ids, $daten, $attrs, 0, $besucht);

    $inhalt = '';

    if ($attrs['showSearch'] && '' !== $liste) {
        // Kein name-Attribut und kein <form>: gefiltert wird rein im Browser
        // (Phase 4). Ohne JavaScript bleibt das Feld wirkungslos, die Liste
        // darunter aber vollständig nutzbar.
        $inhalt .= '<div class="page-index__search-wrap">';
        $inhalt .= '<input type="search" class="page-index__search"'
            . ' placeholder="' . esc_attr__('Seite suchen …', 'fos-online-schulbuch') . '"'
            . ' aria-label="' . esc_attr__('Inhaltsverzeichnis durchsuchen', 'fos-online-schulbuch') . '">';
        $inhalt .= '</div>';
    }

    $inhalt .= ('' !== $liste)
        ? $liste
        : '<p class="page-index__empty">' . esc_html__('Keine Seiten vorhanden.', 'fos-online-schulbuch') . '</p>';

    // Die Spaltenklasse nur bei "Mehrspaltig" ausgeben. Kapitelkarten stehen
    // bewusst immer untereinander (Begründung im CSS), und die einfache Liste
    // ist ohnehin einspaltig — dort wäre die Klasse irreführend.
    $klassen = array('page-index', 'page-index--' . $attrs['layout']);
    if ('columns' === $attrs['layout']) {
        $klassen[] = 'page-index--cols-' . $attrs['columns'];
    }

    if (function_exists('get_block_wrapper_attributes')) {
        $wrapper = get_block_wrapper_attributes(array('class' => implode(' ', $klassen)));
    } else {
        // Rückfall für WordPress vor 5.6
        $wrapper = 'class="' . esc_attr(implode(' ', $klassen)) . '"';
    }

    return '<nav ' . $wrapper . ' aria-label="'
        . esc_attr__('Inhaltsverzeichnis', 'fos-online-schulbuch') . '">'
        . $inhalt
        . '</nav>';
}

/**
 * Registriert den Block.
 *
 * Die Metadaten kommen aus blocks/inhaltsverzeichnis/block.json, das
 * Rendering über den render_callback in den Argumenten.
 *
 * ACHTUNG — bewusst NICHT über die block.json-Eigenschaft "render":
 * Die gibt es erst seit WordPress 6.1. Auf älteren Versionen würde sie
 * stillschweigend ignoriert, der Block gäbe schlicht nichts aus, und der
 * Fehler wäre schwer zu finden. Das Theme deklariert "Requires at least: 5.0".
 * register_block_type_from_metadata() mit render_callback funktioniert ab
 * WordPress 5.5 einheitlich.
 */
function simple_clean_register_page_index_block() {
    if (!function_exists('register_block_type_from_metadata')) {
        return;
    }

    register_block_type_from_metadata(
        get_template_directory() . '/blocks/inhaltsverzeichnis',
        array(
            'render_callback' => 'simple_clean_render_page_index',
        )
    );
}
add_action('init', 'simple_clean_register_page_index_block');

/**
 * Hängt das Editor-Script ein.
 *
 * OHNE DIESES SCRIPT ERSCHEINT DER BLOCK NICHT IM EINFÜGEN-MENÜ.
 * Die serverseitige Registrierung oben liefert Rendering, Block-Supports und
 * Metadaten — die Auffindbarkeit im Inserter entsteht aber erst durch
 * `registerBlockType()` im Editor. Beides gehört zusammen.
 *
 * Das Script wird nicht über die block.json-Eigenschaft `editorScript`
 * eingebunden, weil dort nur Pfade relativ zum Blockordner auflösbar sind;
 * die gebaute Datei liegt in dist/js/.
 *
 * Muster übernommen von simple_clean_glossar_editor_assets() in functions.php.
 */
function simple_clean_page_index_editor_assets() {
    $js_datei = get_template_directory() . '/dist/js/page-index-editor.js';

    if (!file_exists($js_datei)) {
        return;
    }

    wp_enqueue_script(
        'simple-clean-page-index-editor',
        get_template_directory_uri() . '/dist/js/page-index-editor.js',
        array(
            'wp-blocks',
            'wp-element',
            'wp-block-editor',
            'wp-components',
            'wp-data',
            'wp-server-side-render',
        ),
        filemtime($js_datei),
        true
    );
}
add_action('enqueue_block_editor_assets', 'simple_clean_page_index_editor_assets');

/**
 * Hängt Stylesheet und Frontend-Script ein — nur dort, wo der Block vorkommt.
 *
 * `has_block()` durchsucht den gespeicherten Beitragsinhalt und findet den
 * Block auch dann, wenn er in einem Container-Block des CDB-Plugins
 * verschachtelt ist: Der Blockkommentar steht in beiden Fällen im
 * `post_content`. Die zusätzliche Prüfung auf den Container ist trotzdem
 * eine sinnvolle Absicherung für den Fall, dass ein Container seinen Inhalt
 * einmal anders ablegt.
 */
function simple_clean_page_index_frontend_assets() {
    if (is_admin() || !is_singular()) {
        return;
    }

    $hat_block = has_block('fos/inhaltsverzeichnis')
        || has_block('container-block-designer/container');

    if (!$hat_block) {
        return;
    }

    $css_datei = get_template_directory() . '/dist/css/page-index-style.css';
    if (file_exists($css_datei)) {
        wp_enqueue_style(
            'simple-clean-page-index-style',
            get_template_directory_uri() . '/dist/css/page-index-style.css',
            array(),
            filemtime($css_datei)
        );
    }

    $js_datei = get_template_directory() . '/dist/js/page-index.js';
    if (file_exists($js_datei)) {
        wp_enqueue_script(
            'simple-clean-page-index',
            get_template_directory_uri() . '/dist/js/page-index.js',
            array(),
            filemtime($js_datei),
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'simple_clean_page_index_frontend_assets');
