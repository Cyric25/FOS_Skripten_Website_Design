<?php
/**
 * Standalone-Harness für die Lehrpersonen-Kennzeichnung im Inhaltsverzeichnis.
 *
 * Prüfling: includes/page-index.php (AP-2.1 aus PLAN-Inhaltsverzeichnisse.md).
 * Nachgewiesen werden die beiden Fälle, auf die es sicherheitstechnisch
 * ankommt:
 *
 *   simple_clean_ist_lehrperson() === false  -> die gesperrte Seite fehlt
 *                                               vollständig in 'nodes', ihr
 *                                               Titel kommt im gerenderten
 *                                               HTML nirgends vor, und es gibt
 *                                               keinen Toggle-Button.
 *   simple_clean_ist_lehrperson() === true   -> die gesperrte Seite steht in
 *                                               'nodes' UND in
 *                                               'lehrer_gesperrt', ihr <li>
 *                                               trägt die --lehrer-only-Klasse,
 *                                               und der Button ist da.
 *
 * Aufbau nach dem Muster von tools/test-sichtbarkeit.php (Stubs statt
 * WordPress). Ein Unterschied: simple_clean_page_index_daten() hält sein
 * Ergebnis in einer statischen Variablen und hat keine Cache-Leeren-Funktion.
 * Beide Fälle laufen deshalb in je einem eigenen PHP-Prozess; der Aufruf ohne
 * Argument startet sich selbst zweimal und fasst zusammen.
 *
 * Aufruf:  php tools/test-page-index-lehrer-toggle.php
 *
 * Diese Datei liegt bewusst unter tools/ und ist damit NICHT im
 * Verteilungs-ZIP enthalten (create-theme-zip.js gibt tools/ nicht frei).
 *
 * @package SimpleCleanTheme
 */

if (PHP_SAPI !== 'cli') {
    exit("Nur über die Kommandozeile aufrufen.\n");
}

// --- Elternlauf: beide Fälle in getrennten Prozessen ----------------------

if (!isset($argv[1])) {
    $fehler = 0;
    foreach (array('gast', 'lehrperson', 'lehrperson-frei') as $fall) {
        $befehl = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__FILE__) . ' ' . $fall;
        $ausgabe = array();
        $status  = 0;
        exec($befehl, $ausgabe, $status);
        echo implode("\n", $ausgabe) . "\n";
        if (0 !== $status) {
            $fehler++;
        }
    }
    echo $fehler > 0
        ? "\nERGEBNIS: FEHLGESCHLAGEN\n"
        : "\nERGEBNIS: alle Pruefungen bestanden\n";
    exit($fehler > 0 ? 1 : 0);
}

$fall            = $argv[1];
$fall_lehrperson = ('gast' !== $fall);

define('ABSPATH', '/');

// --- Testbaum -------------------------------------------------------------
//   10 Kapitel A            (frei)
//    └── 20 Loesungen       (Meta _simple_clean_nur_lehrpersonen = '1')
//   30 Kapitel B            (frei)
$GLOBALS['test_seiten'] = array(
    10 => array('parent' => 0,  'title' => 'Kapitel A',            'slug' => 'kapitel-a'),
    20 => array('parent' => 10, 'title' => 'Loesungen Kapitel A',  'slug' => 'loesungen-kapitel-a'),
    30 => array('parent' => 0,  'title' => 'Kapitel B',            'slug' => 'kapitel-b'),
);
// Fall "lehrperson-frei": angemeldete Lehrperson, aber KEINE gesperrte Seite
// im Baum — dann darf auch kein Toggle-Button erscheinen.
$GLOBALS['test_gesperrt']   = ('lehrperson-frei' === $fall) ? array() : array(20 => true);
$GLOBALS['test_lehrperson'] = $fall_lehrperson;

// --- Stubs: Sichtbarkeitslogik (includes/sichtbarkeit.php) -----------------
// Bewusst als Stub und nicht durch Einbinden der echten Datei: Geprüft wird
// hier ausschliesslich, wie page-index.php diese beiden Funktionen NUTZT.
// Ihr eigenes Verhalten deckt tools/test-sichtbarkeit.php ab.

function simple_clean_ist_lehrperson() {
    return (bool) $GLOBALS['test_lehrperson'];
}

function simple_clean_gesperrte_seiten() {
    return $GLOBALS['test_gesperrt'];
}

// --- Stubs: WordPress ------------------------------------------------------

function add_action($tag, $callback, $priority = 10, $accepted_args = 1) { return true; }
function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) { return true; }
function absint($wert) { return abs((int) $wert); }
function esc_attr($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_html($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function esc_url($url) { return htmlspecialchars($url, ENT_QUOTES, 'UTF-8'); }
function esc_attr__($text, $domain = '') { return esc_attr($text); }
function esc_html__($text, $domain = '') { return esc_html($text); }
function _n($einzahl, $mehrzahl, $anzahl, $domain = '') { return 1 === (int) $anzahl ? $einzahl : $mehrzahl; }
function home_url($pfad = '') { return 'http://example.test' . $pfad; }
function user_trailingslashit($url, $typ = '') { return $url; }
function get_template_directory() { return str_replace('\\', '/', dirname(__DIR__)); }
function get_template_directory_uri() { return 'http://example.test/theme'; }

/** Minimales $wpdb-Doppel: liefert den Testbaum und die Meta-Abfragen. */
class Test_Page_Index_WPDB {
    public $posts    = 'wp_posts';
    public $postmeta = 'wp_postmeta';

    public function get_results($sql) {
        $zeilen = array();
        foreach ($GLOBALS['test_seiten'] as $id => $seite) {
            $zeile              = new stdClass();
            $zeile->ID          = (string) $id;
            $zeile->post_parent = (string) $seite['parent'];
            $zeile->post_title  = $seite['title'];
            $zeile->post_name   = $seite['slug'];
            $zeilen[]           = $zeile;
        }
        return $zeilen;
    }

    public function get_col($sql) {
        // _simple_clean_hide_from_index und _simple_clean_nav_gesperrt sind in
        // diesem Testbaum nirgends gesetzt. Die Lehrpersonen-Sperre laeuft
        // ueber simple_clean_gesperrte_seiten() oben, nicht ueber diese
        // Abfrage.
        return array();
    }
}

$GLOBALS['wpdb'] = new Test_Page_Index_WPDB();

// --- Prüfling einbinden ---------------------------------------------------

$ziel = str_replace('\\', '/', dirname(__DIR__)) . '/includes/page-index.php';
if (!file_exists($ziel)) {
    echo "HINWEIS: $ziel existiert nicht.\n";
    exit(1);
}
require_once $ziel;

// --- Prüfgerüst -----------------------------------------------------------

$GLOBALS['fails'] = 0;

function check($label, $bedingung, $ist = null) {
    if ($bedingung) {
        echo "  OK   $label\n";
        return;
    }
    $GLOBALS['fails']++;
    echo "  FAIL $label" . (null !== $ist ? ' -> ' . var_export($ist, true) : '') . "\n";
}

$titel_gesperrt = $GLOBALS['test_seiten'][20]['title'];

$daten = simple_clean_page_index_daten();
$html  = simple_clean_render_page_index(array('maxDepth' => 3, 'showSearch' => true));

if ('lehrperson-frei' === $fall) {
    echo "== Fall C: angemeldet, aber keine gesperrte Seite im Baum ==\n";

    check(
        'C1 · lehrer_gesperrt ist leer',
        array() === $daten['lehrer_gesperrt'],
        $daten['lehrer_gesperrt']
    );
    check(
        'C2 · kein Toggle-Button im HTML',
        false === strpos($html, 'page-index__lehrer-toggle')
    );
    check(
        'C3 · keine --lehrer-only-Klasse im HTML',
        false === strpos($html, '--lehrer-only')
    );
    check(
        'C4 · Baum ist vollstaendig (alle drei Seiten)',
        isset($daten['nodes'][10]) && isset($daten['nodes'][20]) && isset($daten['nodes'][30]),
        array_keys($daten['nodes'])
    );
} elseif (!$fall_lehrperson) {
    echo "== Fall A: simple_clean_ist_lehrperson() === false (nicht angemeldet) ==\n";

    check(
        'A1 · gesperrte Seite fehlt in nodes',
        !isset($daten['nodes'][20]),
        array_keys($daten['nodes'])
    );
    check(
        'A2 · freie Seiten stehen weiterhin in nodes',
        isset($daten['nodes'][10]) && isset($daten['nodes'][30]),
        array_keys($daten['nodes'])
    );
    check(
        'A3 · lehrer_gesperrt ist leer',
        array() === $daten['lehrer_gesperrt'],
        $daten['lehrer_gesperrt']
    );
    check(
        'A4 · Titel der gesperrten Seite kommt im HTML NIRGENDS vor',
        false === strpos($html, $titel_gesperrt)
    );
    check(
        'A5 · keine --lehrer-only-Klasse im HTML',
        false === strpos($html, '--lehrer-only')
    );
    check(
        'A6 · kein Toggle-Button im HTML',
        false === strpos($html, 'page-index__lehrer-toggle')
    );
    check(
        'A7 · Suchfeld und freie Kapitel weiterhin vorhanden (kein Kollateralschaden)',
        false !== strpos($html, 'page-index__search')
            && false !== strpos($html, 'Kapitel A')
            && false !== strpos($html, 'Kapitel B')
    );
} else {
    echo "== Fall B: simple_clean_ist_lehrperson() === true (angemeldet) ==\n";

    check(
        'B1 · gesperrte Seite steht in nodes',
        isset($daten['nodes'][20]),
        array_keys($daten['nodes'])
    );
    check(
        'B2 · gesperrte Seite steht in lehrer_gesperrt',
        isset($daten['lehrer_gesperrt'][20]),
        $daten['lehrer_gesperrt']
    );
    check(
        'B3 · nur die gesperrte Seite steht in lehrer_gesperrt',
        array(20) === array_keys($daten['lehrer_gesperrt']),
        array_keys($daten['lehrer_gesperrt'])
    );
    check(
        'B4 · Titel der gesperrten Seite steht im HTML',
        false !== strpos($html, $titel_gesperrt)
    );
    check(
        'B5 · <li> der gesperrten Unterseite traegt page-index__page--lehrer-only',
        false !== strpos($html, '<li class="page-index__page page-index__page--lehrer-only">'),
        $html
    );
    check(
        'B6 · freie Kapitel tragen KEINE --lehrer-only-Klasse',
        false !== strpos($html, '<li class="page-index__chapter">'),
        $html
    );
    check(
        'B7 · Toggle-Button vorhanden, aria-pressed="false"',
        false !== strpos($html, '<button type="button" class="page-index__lehrer-toggle" aria-pressed="false">')
    );
    check(
        'B8 · Button steht nach dem Such-Wrapper und vor der Liste (Vertrag Abschnitt 4)',
        strpos($html, 'page-index__search-wrap') < strpos($html, 'page-index__lehrer-toggle')
            && strpos($html, 'page-index__lehrer-toggle') < strpos($html, 'page-index__chapters')
    );
    // Die Gegenprobe "kein Button ohne gesperrte Seite" laeuft als eigener
    // Prozess (Fall C) — simple_clean_page_index_daten() haelt sein Ergebnis
    // statisch, ein Umschalten innerhalb desselben Laufs wuerde nichts aendern.
}

echo $GLOBALS['fails'] > 0
    ? "  --> {$GLOBALS['fails']} Pruefung(en) fehlgeschlagen\n"
    : "  --> alle Pruefungen bestanden\n";

exit($GLOBALS['fails'] > 0 ? 1 : 0);
