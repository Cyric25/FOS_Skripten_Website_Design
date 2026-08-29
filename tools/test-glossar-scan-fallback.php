<?php
/**
 * Standalone-Harness für den Glossar-Scan-Rückfall — ohne WordPress.
 *
 * Prüft die beiden Funktionen aus functions.php, die den frueheren
 * Datenverlust-Fund beheben (Theme/CLAUDE.md, Abschnitt "Glossar-System"):
 *
 * 1. simple_clean_ensure_glossar_scanned() — holt den Scan fuer eine nie
 *    gescannte Seite einmalig nach, speichert ihn und liefert ihn bei
 *    erneutem Aufruf unveraendert aus der Meta zurueck (kein zweiter Scan).
 * 2. simple_clean_process_glossar_links_optimized() — verliert keinen Text
 *    mehr, wenn preg_replace_callback() an einem zu grossen Alternations-
 *    Pattern scheitert (PREG_INTERNAL_ERROR / "regular expression is too
 *    large").
 *
 * Die Funktionen werden aus der echten functions.php extrahiert (nicht
 * neu abgetippt), damit der Test den tatsaechlich ausgelieferten Code
 * prueft und nicht nur eine Kopie davon.
 *
 * Aufruf:  php tools/test-glossar-scan-fallback.php
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
define('HOUR_IN_SECONDS', 3600);

// --- Funktionen aus der echten functions.php extrahieren -------------------

function glossar_test_extrahiere_funktion($quelltext, $name) {
    if (!preg_match('/function\s+' . preg_quote($name, '/') . '\s*\(/', $quelltext, $treffer, PREG_OFFSET_CAPTURE)) {
        fwrite(STDERR, "Funktion $name nicht gefunden.\n");
        exit(1);
    }
    $start = $treffer[0][1];
    $klammer_auf = strpos($quelltext, '{', $start);
    $tiefe = 0;
    $ende = null;
    for ($i = $klammer_auf; $i < strlen($quelltext); $i++) {
        if ($quelltext[$i] === '{') {
            $tiefe++;
        } elseif ($quelltext[$i] === '}') {
            $tiefe--;
            if ($tiefe === 0) {
                $ende = $i;
                break;
            }
        }
    }
    if ($ende === null) {
        fwrite(STDERR, "Ende von $name nicht gefunden (unbalancierte Klammern?).\n");
        exit(1);
    }
    return substr($quelltext, $start, $ende - $start + 1);
}

$functions_php = str_replace('\\', '/', dirname(__DIR__)) . '/functions.php';
$quelltext = file_get_contents($functions_php);
if ($quelltext === false) {
    fwrite(STDERR, "Konnte $functions_php nicht lesen.\n");
    exit(1);
}

$benoetigte_funktionen = array(
    'simple_clean_extract_text_from_blocks',
    'simple_clean_scan_glossar_candidates',
    'simple_clean_ensure_glossar_scanned',
    'simple_clean_get_glossar_terms_by_ids',
    'simple_clean_build_glossar_pattern_for_terms',
    'simple_clean_process_glossar_links_optimized',
);

foreach ($benoetigte_funktionen as $name) {
    eval(glossar_test_extrahiere_funktion($quelltext, $name));
}

// --- Testzustand & Stubs ----------------------------------------------------

$GLOBALS['test_meta']        = array(); // post_id => array(meta_key => wert)
$GLOBALS['test_posts']       = array(); // post_id => post_content
$GLOBALS['test_alle_terms']  = array(); // von simple_clean_get_glossar_terms() geliefert
$GLOBALS['test_scan_aufrufe'] = 0;

function get_post_meta($post_id, $key, $single = false) {
    $post_id = (int) $post_id;
    if (isset($GLOBALS['test_meta'][$post_id][$key])) {
        return $GLOBALS['test_meta'][$post_id][$key];
    }
    return $single ? '' : array();
}

function update_post_meta($post_id, $key, $value) {
    $GLOBALS['test_meta'][(int) $post_id][$key] = $value;
    return true;
}

function get_post($post_id) {
    $post_id = (int) $post_id;
    if (!isset($GLOBALS['test_posts'][$post_id])) {
        return null;
    }
    return (object) array(
        'ID'            => $post_id,
        'post_content'  => $GLOBALS['test_posts'][$post_id],
        'post_title'    => '',
        'post_excerpt'  => '',
    );
}

function parse_blocks($content) {
    // Vereinfachtes Stand-in: ein einziger "Block" mit dem Rohcontent als
    // innerHTML - reicht, um simple_clean_extract_text_from_blocks() zu
    // durchlaufen, ohne den echten Gutenberg-Parser nachzubauen.
    return array(
        array(
            'blockName'   => 'core/paragraph',
            'attrs'       => array(),
            'innerHTML'   => $content,
            'innerBlocks' => array(),
        ),
    );
}

function wp_strip_all_tags($string) {
    return trim(strip_tags($string));
}

function wp_cache_get($key, $group = '') {
    return false; // immer Cache-Miss - erzwingt den Aufruf von simple_clean_get_glossar_terms()
}

function wp_cache_set($key, $value, $group = '', $ttl = 0) {
    return true;
}

function current_time($type) {
    return '2026-08-29 12:00:00';
}

function get_option($name, $default = false) {
    $werte = array(
        'glossar_case_sensitive' => '0',
        'glossar_first_only'     => '1',
    );
    return isset($werte[$name]) ? $werte[$name] : $default;
}

function esc_attr($value) {
    return htmlspecialchars($value, ENT_QUOTES);
}

// Von den Testfällen befüllt, bevor sie die Extraktions-/Scan-Funktionen
// aufrufen - steht für simple_clean_get_glossar_terms_by_ids() UND
// simple_clean_scan_glossar_candidates() bereit (beide rufen dieselbe
// Funktion auf).
function simple_clean_get_glossar_terms() {
    $GLOBALS['test_scan_aufrufe']++;
    return $GLOBALS['test_alle_terms'];
}

// --- Kleine Testinfrastruktur ------------------------------------------------

$fehlgeschlagen = 0;
$gesamt = 0;

function pruefe($beschreibung, $bedingung) {
    global $fehlgeschlagen, $gesamt;
    $gesamt++;
    if ($bedingung) {
        echo "  OK   $beschreibung\n";
    } else {
        echo "  FAIL $beschreibung\n";
        $fehlgeschlagen++;
    }
}

// =============================================================================
// Test 1: simple_clean_ensure_glossar_scanned() holt den Scan einmalig nach
// =============================================================================

echo "Test 1: Scan-Nachholung fuer eine nie gescannte Seite\n";

$GLOBALS['test_meta']  = array();
$GLOBALS['test_posts'] = array(
    42 => 'Dies ist ein Text ueber Photosynthese und Mitochondrien.',
);
$GLOBALS['test_alle_terms'] = array(
    array('id' => 1, 'term' => 'Photosynthese', 'definition' => 'Def 1', 'permalink' => '/g/1'),
    array('id' => 2, 'term' => 'Mitochondrien', 'definition' => 'Def 2', 'permalink' => '/g/2'),
    array('id' => 3, 'term' => 'Ribosom',       'definition' => 'Def 3', 'permalink' => '/g/3'),
);
$GLOBALS['test_scan_aufrufe'] = 0;

pruefe(
    'Vor dem Scan ist kein _glossar_scan_version gesetzt',
    empty(get_post_meta(42, '_glossar_scan_version', true))
);

$kandidaten = simple_clean_ensure_glossar_scanned(42);

pruefe('Scan findet Photosynthese (ID 1)', in_array(1, $kandidaten, true));
pruefe('Scan findet Mitochondrien (ID 2)', in_array(2, $kandidaten, true));
pruefe('Scan findet NICHT Ribosom (ID 3, kommt im Text nicht vor)', !in_array(3, $kandidaten, true));
pruefe(
    '_glossar_scan_version wurde auf 1 gesetzt',
    get_post_meta(42, '_glossar_scan_version', true) === 1
);
pruefe(
    '_glossar_term_candidates wurde persistiert',
    get_post_meta(42, '_glossar_term_candidates', true) === $kandidaten
);
pruefe('_glossar_last_scanned wurde gesetzt', !empty(get_post_meta(42, '_glossar_last_scanned', true)));

$scans_nach_erstem_aufruf = $GLOBALS['test_scan_aufrufe'];

// Zweiter Aufruf fuer dieselbe (jetzt gescannte) Seite: Es soll kein
// weiterer Scan laufen und dieselben Kandidaten zurückkommen, auch wenn
// sich der Universum an Begriffen inzwischen geaendert hat.
$GLOBALS['test_alle_terms'] = array(); // wuerde einen echten Rescan sofort auffallen lassen
$kandidaten_zweiter_aufruf = simple_clean_ensure_glossar_scanned(42);

pruefe(
    'Zweiter Aufruf scannt NICHT erneut (liest aus der Meta)',
    $kandidaten_zweiter_aufruf === $kandidaten
);

// =============================================================================
// Test 2: Eine bereits gescannte, aber leere Kandidatenliste bleibt leer
// =============================================================================

echo "\nTest 2: Bereits gescannt, aber keine Begriffe gefunden\n";

$GLOBALS['test_meta'] = array(
    43 => array(
        '_glossar_term_candidates' => array(),
        '_glossar_scan_version'    => 1,
    ),
);
$GLOBALS['test_scan_aufrufe'] = 0;
$GLOBALS['test_alle_terms'] = array(
    array('id' => 1, 'term' => 'Photosynthese', 'definition' => 'Def 1', 'permalink' => '/g/1'),
);

$kandidaten_leer = simple_clean_ensure_glossar_scanned(43);

pruefe('Leere, aber gültige Kandidatenliste bleibt leer', $kandidaten_leer === array());
pruefe('Kein Scan ausgelöst (Meta war bereits maßgeblich)', $GLOBALS['test_scan_aufrufe'] === 0);

// =============================================================================
// Test 3: preg_replace_callback()-Fehlschlag verliert keinen Text mehr
// =============================================================================

echo "\nTest 3: Sehr grosses Pattern (PREG_INTERNAL_ERROR) verliert keinen Text\n";

// Genug lange, eindeutige Fake-Begriffe, um auf dieser PHP/PCRE-Version
// zuverlaessig "regular expression is too large" auszuloesen (empirisch
// geprueft: ab ca. 3000 Alternativen von je 20 Zeichen).
$viele_terms = array();
for ($i = 0; $i < 4000; $i++) {
    $viele_terms[] = array(
        'id'         => $i + 1,
        'term'       => str_repeat(chr(97 + ($i % 26)), 20) . $i,
        'definition' => 'x',
        'permalink'  => '/g/' . ($i + 1),
    );
}
$GLOBALS['test_alle_terms'] = $viele_terms;
$viele_kandidaten = range(1, 4000);

$original_content = '<p>Hallo Welt, dies ist ein ganz normaler Testabsatz mit mehreren Woertern.</p>'
    . '<p>Ein zweiter Absatz, der ebenfalls vollstaendig erhalten bleiben muss.</p>';

$ergebnis = simple_clean_process_glossar_links_optimized($original_content, $viele_kandidaten);

pruefe(
    'Trotz PCRE-Fehler bleibt der Text vollständig erhalten',
    $ergebnis['content'] === $original_content
);
pruefe(
    'Text ist NICHT leer (der urspruengliche Fehlerzustand)',
    trim($ergebnis['content']) !== ''
);

// Kontrolle: Mit einer normalen, kleinen Kandidatenliste funktioniert die
// Verlinkung weiterhin ganz normal (der Fix darf den Normalfall nicht
// kaputt machen).
echo "\nKontrolle: normale Verlinkung funktioniert weiterhin\n";

$GLOBALS['test_alle_terms'] = array(
    array('id' => 1, 'term' => 'Testabsatz', 'definition' => 'Def', 'permalink' => '/g/1'),
);
$normales_ergebnis = simple_clean_process_glossar_links_optimized(
    '<p>Ein Testabsatz zum Ausprobieren.</p>',
    array(1)
);

pruefe(
    'Normaler Begriff wird verlinkt (span mit data-glossar-id)',
    strpos($normales_ergebnis['content'], 'data-glossar-id="1"') !== false
);
pruefe('terms_found enthält ID 1', in_array(1, $normales_ergebnis['terms_found'], true));

// --- Zusammenfassung ---------------------------------------------------------

echo "\n" . str_repeat('-', 60) . "\n";
if ($fehlgeschlagen === 0) {
    echo "Alle $gesamt Prüfungen erfolgreich.\n";
    exit(0);
}
echo "$fehlgeschlagen von $gesamt Prüfungen fehlgeschlagen.\n";
exit(1);
