<?php
/**
 * Standalone-Harness für die Seiten-Sichtbarkeit — ohne WordPress.
 *
 * Geprüft wird die zentrale Logik aus includes/sichtbarkeit.php:
 * Wer gilt als Lehrperson, welche Seiten sind gesperrt, wie vererbt sich
 * die Sperre auf den Unterbaum, und wann darf eine Seite ausgeliefert
 * werden.
 *
 * Aufruf:  php tools/test-sichtbarkeit.php
 *
 * Diese Datei liegt bewusst unter tools/ und ist damit NICHT im
 * Verteilungs-ZIP enthalten (create-theme-zip.js gibt tools/ nicht frei).
 *
 * @package SimpleCleanTheme
 */

if (PHP_SAPI !== 'cli') {
    exit("Nur über die Kommandozeile aufrufen.\n");
}

define('ABSPATH', '/');

// --- Testzustand ----------------------------------------------------------
// Wird von jedem Testfall über zustand_setzen() neu belegt.
$GLOBALS['test_angemeldet'] = false;
$GLOBALS['test_meta']       = array();   // post_id => array(meta_key => wert)
$GLOBALS['test_seiten']     = array();   // post_id => post_parent
$GLOBALS['test_abfragen']   = 0;         // Zähler für Datenbankabfragen
$GLOBALS['test_filter']     = array();   // tag => array(callback, …)

function zustand_setzen($angemeldet, array $meta, array $seiten) {
    $GLOBALS['test_angemeldet'] = $angemeldet;
    $GLOBALS['test_meta']       = $meta;
    $GLOBALS['test_seiten']     = $seiten;
    $GLOBALS['test_abfragen']   = 0;
    $GLOBALS['test_filter']     = array();
    // Die Funktionen halten ihre Ergebnisse zwischen; für jeden Testfall
    // muss dieser Zwischenspeicher verworfen werden.
    if (function_exists('simple_clean_sichtbarkeit_cache_leeren')) {
        simple_clean_sichtbarkeit_cache_leeren();
    }
}

// --- Stubs ----------------------------------------------------------------

function is_user_logged_in() {
    return (bool) $GLOBALS['test_angemeldet'];
}

function get_post_meta($post_id, $key, $single = false) {
    $post_id = (int) $post_id;
    if (isset($GLOBALS['test_meta'][$post_id][$key])) {
        return $GLOBALS['test_meta'][$post_id][$key];
    }
    return $single ? '' : array();
}

/**
 * Elternkette von unten nach oben, wie get_post_ancestors() in WordPress.
 * Die Besuchsliste schützt vor Zyklen — genau wie der WordPress-Kern.
 */
function get_post_ancestors($post_id) {
    $post_id   = (int) $post_id;
    $ancestors = array();
    $besucht   = array();
    $aktuell   = isset($GLOBALS['test_seiten'][$post_id]) ? (int) $GLOBALS['test_seiten'][$post_id] : 0;

    while ($aktuell > 0 && !isset($besucht[$aktuell])) {
        $besucht[$aktuell] = true;
        $ancestors[]       = $aktuell;
        $aktuell = isset($GLOBALS['test_seiten'][$aktuell]) ? (int) $GLOBALS['test_seiten'][$aktuell] : 0;
    }

    return $ancestors;
}

function add_filter($tag, $callback, $priority = 10, $accepted_args = 1) {
    $GLOBALS['test_filter'][$tag][] = $callback;
    return true;
}

function add_action($tag, $callback, $priority = 10, $accepted_args = 1) {
    return true;
}

function apply_filters($tag, $value) {
    $weitere = array_slice(func_get_args(), 2);
    if (empty($GLOBALS['test_filter'][$tag])) {
        return $value;
    }
    foreach ($GLOBALS['test_filter'][$tag] as $callback) {
        $args  = array_merge(array($value), $weitere);
        $value = call_user_func_array($callback, $args);
    }
    return $value;
}

/**
 * Minimales $wpdb-Doppel. Zählt jede Abfrage mit, damit die Tests
 * nachweisen können, dass ohne gesperrte Seiten keine zweite Abfrage läuft.
 */
class Test_WPDB {
    public $posts    = 'wp_posts';
    public $postmeta = 'wp_postmeta';

    public function get_col($sql) {
        $GLOBALS['test_abfragen']++;
        $treffer = array();
        if (strpos($sql, '_simple_clean_nur_lehrpersonen') !== false) {
            foreach ($GLOBALS['test_meta'] as $post_id => $meta) {
                if (isset($meta['_simple_clean_nur_lehrpersonen'])
                    && '1' === $meta['_simple_clean_nur_lehrpersonen']) {
                    $treffer[] = (string) $post_id;
                }
            }
        }
        return $treffer;
    }

    public function get_results($sql) {
        $GLOBALS['test_abfragen']++;
        $zeilen = array();
        foreach ($GLOBALS['test_seiten'] as $id => $parent) {
            $zeile              = new stdClass();
            $zeile->ID          = (string) $id;
            $zeile->post_parent = (string) $parent;
            $zeilen[]           = $zeile;
        }
        return $zeilen;
    }
}

$GLOBALS['wpdb'] = new Test_WPDB();

// --- Prüfling einbinden ---------------------------------------------------

$ziel = str_replace('\\', '/', dirname(__DIR__)) . '/includes/sichtbarkeit.php';
if (!file_exists($ziel)) {
    echo "HINWEIS: $ziel existiert noch nicht — alle Pruefungen schlagen fehl.\n\n";
} else {
    require_once $ziel;
}

// --- Prüfgerüst -----------------------------------------------------------

$GLOBALS['fails'] = 0;

function check($label, $condition, $actual = null) {
    if ($condition) {
        echo "  OK   $label\n";
        return;
    }
    $GLOBALS['fails']++;
    echo "  FAIL $label" . (null !== $actual ? ' -> ' . var_export($actual, true) : '') . "\n";
}

/** Ruft eine Funktion auf, die es vielleicht noch nicht gibt (roter Lauf). */
function ruf($name, $args = array()) {
    if (!function_exists($name)) {
        return '### FUNKTION FEHLT: ' . $name;
    }
    return call_user_func_array($name, $args);
}

// Ein wiederverwendbarer Seitenbaum:
//   10 (Wurzel)
//    └── 20 (gesperrt in den meisten Faellen)
//         ├── 30
//         │    └── 40
//         └── 50
//   60 (eigenstaendig, nie gesperrt)
$baum = array(10 => 0, 20 => 10, 30 => 20, 40 => 30, 50 => 20, 60 => 0);
$sperre_20 = array(20 => array('_simple_clean_nur_lehrpersonen' => '1'));

echo "== Wer ist Lehrperson ==\n";

zustand_setzen(false, array(), $baum);
check('1 · nicht angemeldet -> false', false === ruf('simple_clean_ist_lehrperson'), ruf('simple_clean_ist_lehrperson'));

zustand_setzen(true, array(), $baum);
check('2 · angemeldet -> true', true === ruf('simple_clean_ist_lehrperson'), ruf('simple_clean_ist_lehrperson'));

zustand_setzen(true, array(), $baum);
add_filter('simple_clean_ist_lehrperson', function () { return false; });
check(
    '3 · Filter erzwingt false trotz Anmeldung',
    false === ruf('simple_clean_ist_lehrperson'),
    ruf('simple_clean_ist_lehrperson')
);

echo "\n== Ist eine Seite gesperrt ==\n";

zustand_setzen(false, array(), $baum);
check('4 · Seite ohne Meta -> false', false === ruf('simple_clean_seite_nur_lehrpersonen', array(30)));

zustand_setzen(false, $sperre_20, $baum);
check('5 · Seite mit Meta 1 -> true', true === ruf('simple_clean_seite_nur_lehrpersonen', array(20)));

zustand_setzen(false, $sperre_20, $baum);
check('6 · Kind einer gesperrten Seite -> true (Vererbung)', true === ruf('simple_clean_seite_nur_lehrpersonen', array(30)));

zustand_setzen(false, $sperre_20, $baum);
check('7 · Enkel einer gesperrten Seite -> true', true === ruf('simple_clean_seite_nur_lehrpersonen', array(40)));

zustand_setzen(false, array(20 => array('_simple_clean_nur_lehrpersonen' => '')), $baum);
check('8a · Meta leer -> false', false === ruf('simple_clean_seite_nur_lehrpersonen', array(20)));
zustand_setzen(false, array(20 => array('_simple_clean_nur_lehrpersonen' => '0')), $baum);
check('8b · Meta 0 -> false', false === ruf('simple_clean_seite_nur_lehrpersonen', array(20)));

zustand_setzen(false, $sperre_20, $baum);
check('8c · Geschwisterzweig bleibt frei', false === ruf('simple_clean_seite_nur_lehrpersonen', array(60)));

echo "\n== Liste der gesperrten Seiten ==\n";

zustand_setzen(false, array(), $baum);
$leer = ruf('simple_clean_gesperrte_seiten');
check('9a · ohne gesperrte Seiten -> leeres Array', array() === $leer, $leer);
$GLOBALS['test_abfragen'] = 0;
$leer2 = ruf('simple_clean_gesperrte_seiten_mit_unterbaum');
check('9b · Unterbaum-Liste ebenfalls leer', array() === $leer2, $leer2);
check(
    '9c · dafuer laeuft hoechstens EINE Abfrage (kein Baumaufbau ohne Not)',
    $GLOBALS['test_abfragen'] <= 1,
    $GLOBALS['test_abfragen']
);

zustand_setzen(false, $sperre_20, $baum);
$mit = ruf('simple_clean_gesperrte_seiten_mit_unterbaum');
$erwartet = array(20, 30, 40, 50);
$haben = is_array($mit) ? array_keys($mit) : $mit;
if (is_array($haben)) {
    sort($haben);
}
check('10 · gesperrte Seite plus gesamter Unterbaum', $erwartet === $haben, $haben);

zustand_setzen(false, array(70 => array('_simple_clean_nur_lehrpersonen' => '1')), array(70 => 80, 80 => 70));
$zyklus = ruf('simple_clean_gesperrte_seiten_mit_unterbaum');
check('11 · Zyklus im Seitenbaum terminiert', is_array($zyklus), $zyklus);

echo "\n== Die Gesamtentscheidung ==\n";

zustand_setzen(true, $sperre_20, $baum);
check('12 · angemeldet sieht gesperrte Seite', true === ruf('simple_clean_seite_sichtbar', array(20)));

zustand_setzen(false, $sperre_20, $baum);
add_filter('simple_clean_lehrerseite_freigeben', function ($frei, $post_id) { return true; }, 10, 2);
check('13 · Filter gibt gesperrte Seite frei', true === ruf('simple_clean_seite_sichtbar', array(20)));

zustand_setzen(false, $sperre_20, $baum);
check('14 · ohne Filter bleibt gesperrt', false === ruf('simple_clean_seite_sichtbar', array(20)));

zustand_setzen(false, $sperre_20, $baum);
check('15 · nicht gesperrte Seite ist sichtbar', true === ruf('simple_clean_seite_sichtbar', array(60)));

echo "\n== Die Naht faellt geschlossen aus ==\n";

zustand_setzen(false, $sperre_20, $baum);
add_filter('simple_clean_lehrerseite_freigeben', function ($frei, $post_id) { return $frei; }, 10, 2);
check(
    '16 · ein Filter, der nur durchreicht, gibt NICHT frei',
    false === ruf('simple_clean_seite_sichtbar', array(20)),
    ruf('simple_clean_seite_sichtbar', array(20))
);

zustand_setzen(false, $sperre_20, $baum);
check('17 · Kind einer gesperrten Seite ist ebenfalls unsichtbar', false === ruf('simple_clean_seite_sichtbar', array(40)));

$fails = $GLOBALS['fails'];
echo "\n" . (0 === $fails ? "ALLE TESTS BESTANDEN\n" : "$fails FEHLER\n");
exit(0 === $fails ? 0 : 1);