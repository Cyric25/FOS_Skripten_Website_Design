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
 * Rendert den Block.
 *
 * Signatur entspricht dem, was WordPress einem render_callback übergibt.
 * Die Funktion GIBT ZURÜCK und gibt nichts direkt aus — ein render_callback,
 * der echo verwendet, platziert seine Ausgabe an der falschen Stelle im
 * Dokument.
 *
 * Wird in AP-3.2 vollständig ausgearbeitet.
 *
 * @param array    $attributes Blockattribute.
 * @param string   $content    Innerer Inhalt (hier ungenutzt).
 * @param WP_Block $block      Blockinstanz (hier ungenutzt).
 * @return string HTML.
 */
function simple_clean_render_page_index($attributes = array(), $content = '', $block = null) {
    $attrs = simple_clean_page_index_sanitize_attrs($attributes);

    // AP-3.2 füllt hier die Baumausgabe ein. Bis dahin bewusst leer, damit
    // eine Seite mit dem Block weder bricht noch etwas Halbfertiges zeigt.
    unset($attrs);

    return '';
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
