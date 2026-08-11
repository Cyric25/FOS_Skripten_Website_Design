<?php
/**
 * Seiten-Sichtbarkeit: „Nur für Lehrpersonen"
 *
 * Einzelne Seiten lassen sich über das Meta `_simple_clean_nur_lehrpersonen`
 * sperren. Für nicht angemeldete Besucher verschwinden sie aus Seitenleiste,
 * Inhaltsverzeichnis, Menü, Suche, REST und Sitemap; der direkte Aufruf endet
 * auf einer Hinweisseite (siehe AP-1.3).
 *
 * WARUM EINE EIGENE DATEI
 * Die functions.php hat rund 3900 Zeilen und ruft beim Laden Dutzende
 * WordPress-Funktionen auf — sie lässt sich nicht ohne WordPress ausführen.
 * Diese Datei dagegen kommt mit einer Handvoll Stubs aus und wird deshalb von
 * `tools/test-sichtbarkeit.php` direkt geprüft.
 *
 * WO DAS META SONST NOCH GESCHRIEBEN WIRD
 * - `functions.php`, Meta-Box „Navigation, Verzeichnis & Zugriff"
 * - `includes/admin/page-manager.php`, Sammelaktionen `lock_teacher` /
 *   `unlock_teacher`
 * Beide schreiben den String `'1'` und löschen das Meta per
 * `delete_post_meta()` — eine abweichende Schreibweise würde hier nicht
 * erkannt.
 *
 * KEINE function_exists-GUARDS, UND DAS IST ABSICHT
 * Bedingt deklarierte Funktionen werden von PHP nicht gehoistet. In
 * `sidebar.php` musste deshalb einmal die Reihenfolge gerettet werden
 * (v1.5.57→58, siehe CLAUDE.md). Diese Datei wird per `require_once` genau
 * einmal geladen; die Deklarationen stehen unbedingt da und werden gehoistet.
 * Aufrufer aus anderen Dateien sichern sich ihrerseits mit `function_exists()`
 * ab, falls diese Datei einmal fehlen sollte.
 *
 * @package SimpleCleanTheme
 * @since 1.5.77
 */

// Sicherheit: Direkten Zugriff verhindern
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Zählwerk für den Zwischenspeicher der Funktionen unten.
 *
 * Die Ergebnisse werden je Seitenaufbau festgehalten. Wird das Zählwerk
 * erhöht, gelten alle gehaltenen Ergebnisse als veraltet. Gebraucht wird das
 * von den Tests und von langlaufenden Prozessen (WP-CLI, Importe), die
 * innerhalb eines Aufrufs Metas ändern.
 *
 * @param bool $erhoehen Zählwerk erhöhen und damit den Zwischenspeicher verwerfen.
 * @return int
 */
function simple_clean_sichtbarkeit_generation($erhoehen = false) {
    static $generation = 0;

    if ($erhoehen) {
        $generation++;
    }

    return $generation;
}

/**
 * Zwischenspeicher der Sichtbarkeitsfunktionen verwerfen.
 *
 * @return void
 */
function simple_clean_sichtbarkeit_cache_leeren() {
    simple_clean_sichtbarkeit_generation(true);
}

/**
 * Gilt der aktuelle Besucher als Lehrperson?
 *
 * DIES IST DIE EINZIGE STELLE, AN DER „LEHRPERSON" DEFINIERT IST.
 *
 * Derzeit heißt das schlicht „angemeldet". Das trägt, solange es auf dieser
 * Installation ausschließlich Lehrer-Konten gibt — Schülerinnen und Schüler
 * melden sich nie an, sie kommen über das Klassenpasswort des CDB-Plugins.
 *
 * ACHTUNG: Sobald ein Konto existiert, das keine Lehrperson ist (ein Abonnent,
 * ein Testkonto, ein späterer Schülerzugang), öffnet sich die Sperre still.
 * Verschärft wird dann genau hier — etwa auf
 * `current_user_can('cbd_edit_blocks')`, was Administratoren und die Rolle
 * „Block-Redakteur" umfasst. Alle übrigen Fundstellen bleiben unberührt,
 * weil sie ausschließlich diese Funktion befragen.
 *
 * @return bool
 */
function simple_clean_ist_lehrperson() {
    return (bool) apply_filters('simple_clean_ist_lehrperson', is_user_logged_in());
}

/**
 * IDs aller Seiten, an denen das Meta gesetzt ist — ohne Unterbau.
 *
 * Eine Abfrage, Ergebnis als `array(ID => true)`, damit die Prüfung beim
 * Aufrufer ein `isset()` ist und keine Suche. Dasselbe Muster nutzt
 * `simple_clean_page_index_daten()` in `includes/page-index.php` für den
 * Ausschluss aus dem Inhaltsverzeichnis.
 *
 * @return array<int,bool>
 */
function simple_clean_gesperrte_seiten() {
    static $cache = null;
    static $generation = -1;

    $aktuell = simple_clean_sichtbarkeit_generation();
    if (null !== $cache && $generation === $aktuell) {
        return $cache;
    }

    global $wpdb;

    // Keine Nutzereingaben in der Abfrage; $wpdb->postmeta kommt über die
    // Eigenschaft, nicht als zusammengebaute Zeichenkette.
    $ids = $wpdb->get_col(
        "SELECT post_id FROM {$wpdb->postmeta}
         WHERE meta_key = '_simple_clean_nur_lehrpersonen' AND meta_value = '1'"
    );

    $cache      = array_fill_keys(array_map('intval', (array) $ids), true);
    $generation = $aktuell;

    return $cache;
}

/**
 * IDs aller gesperrten Seiten **einschließlich ihres gesamten Unterbaums**.
 *
 * Gebraucht wird das überall dort, wo Seiten in einer FLACHEN Liste
 * erscheinen — Menü, Suche, REST, Sitemap. In den beiden Baumdarstellungen
 * (Seitenleiste, Inhaltsverzeichnis) ist das nicht nötig: Beide laufen von
 * der Wurzel abwärts, ein entfernter Knoten nimmt seinen Unterbaum von selbst
 * mit.
 *
 * Ist keine einzige Seite gesperrt, entfällt der Baumaufbau vollständig —
 * die Funktion kostet dann nur die eine Abfrage aus
 * `simple_clean_gesperrte_seiten()`.
 *
 * @return array<int,bool>
 */
function simple_clean_gesperrte_seiten_mit_unterbaum() {
    static $cache = null;
    static $generation = -1;

    $aktuell = simple_clean_sichtbarkeit_generation();
    if (null !== $cache && $generation === $aktuell) {
        return $cache;
    }

    $gesperrt = simple_clean_gesperrte_seiten();

    // Der häufige Fall: nichts gesperrt, also nichts aufzubauen.
    if (empty($gesperrt)) {
        $cache      = array();
        $generation = $aktuell;
        return $cache;
    }

    global $wpdb;

    $zeilen = $wpdb->get_results(
        "SELECT ID, post_parent FROM {$wpdb->posts}
         WHERE post_type = 'page' AND post_status != 'trash'"
    );

    $kinder = array();
    foreach ((array) $zeilen as $zeile) {
        $kinder[(int) $zeile->post_parent][] = (int) $zeile->ID;
    }

    // Breitensuche von jeder gesperrten Seite abwärts. Die Ergebnisliste ist
    // zugleich die Besuchsliste — dadurch terminiert der Durchlauf auch bei
    // einem Ring aus Eltern-Kind-Beziehungen.
    $ergebnis = array();
    $schlange = array_keys($gesperrt);
    $zeiger   = 0;

    while ($zeiger < count($schlange)) {
        $id = (int) $schlange[$zeiger];
        $zeiger++;

        if (isset($ergebnis[$id])) {
            continue;
        }
        $ergebnis[$id] = true;

        if (isset($kinder[$id])) {
            foreach ($kinder[$id] as $kind) {
                if (!isset($ergebnis[$kind])) {
                    $schlange[] = $kind;
                }
            }
        }
    }

    $cache      = $ergebnis;
    $generation = $aktuell;

    return $cache;
}

/**
 * Ist diese Seite gesperrt — selbst oder über einen Vorfahren?
 *
 * Die Sperre vererbt sich bewusst auf den gesamten Unterbaum, wie das Meta
 * `_simple_clean_hide_from_index`. Eine Seite „Lösungen" mit einer Unterseite
 * je Kapitel ist der wahrscheinliche Aufbau; ein vergessenes Häkchen an einer
 * Unterseite wäre ein Leck.
 *
 * @param int $post_id
 * @return bool
 */
function simple_clean_seite_nur_lehrpersonen($post_id) {
    $post_id = (int) $post_id;
    if ($post_id <= 0) {
        return false;
    }

    if ('1' === get_post_meta($post_id, '_simple_clean_nur_lehrpersonen', true)) {
        return true;
    }

    $gesperrt = simple_clean_gesperrte_seiten();
    if (empty($gesperrt)) {
        return false;
    }

    foreach ((array) get_post_ancestors($post_id) as $vorfahr) {
        if (isset($gesperrt[(int) $vorfahr])) {
            return true;
        }
    }

    return false;
}

/**
 * Die Gesamtentscheidung: Darf diese Seite ausgeliefert werden?
 *
 * Der Filter `simple_clean_lehrerseite_freigeben` ist die Naht, an der sich
 * das Plugin „Container Block Designer" einhängt: Es gibt eine gesperrte
 * Seite frei, wenn eine gültige Klassensitzung vorliegt und die Seite für
 * diese Klasse als „behandelt" markierte Container enthält.
 *
 * SEIN STANDARDWERT IST `false` UND MUSS ES BLEIBEN. Fehlt das Plugin, ist es
 * abgeschaltet oder greift der Filter nicht, bleibt die Seite gesperrt. Ein
 * Fehler in der Naht zeigt damit zu wenig, nie zu viel.
 *
 * @param int $post_id
 * @return bool
 */
function simple_clean_seite_sichtbar($post_id) {
    if (simple_clean_ist_lehrperson()) {
        return true;
    }

    $post_id = (int) $post_id;

    if (!simple_clean_seite_nur_lehrpersonen($post_id)) {
        return true;
    }

    return (bool) apply_filters('simple_clean_lehrerseite_freigeben', false, $post_id);
}

// ===================================================================
// DURCHSETZUNG BEIM SEITENAUFRUF
// ===================================================================

/**
 * Gesperrte Seiten beim direkten Aufruf abfangen.
 *
 * REIHENFOLGE AUF template_redirect — die zählt:
 *
 *   Priorität  1  simple_clean_block_ai_user_agents()     403 für AI-Crawler
 *   Priorität 10  simple_clean_password_protection_check() Passwort der Website
 *   Priorität 20  diese Funktion                           Lehrersperre
 *
 * Die Lehrersperre kommt zuletzt. Sonst käme ein Besucher, der das
 * Website-Passwort nicht kennt, über die Hinweisseite an der Passwortabfrage
 * vorbei — und wüsste damit, dass es die Seite gibt.
 *
 * @return void
 */
function simple_clean_lehrerseite_pruefen() {
    // Nur die öffentliche Anzeige einzelner Seiten betrifft das hier.
    if (is_admin()
        || (defined('DOING_AJAX') && DOING_AJAX)
        || (defined('DOING_CRON') && DOING_CRON)
        || (defined('REST_REQUEST') && REST_REQUEST)
        || is_feed()
        || !is_singular('page')) {
        return;
    }

    $seiten_id = (int) get_queried_object_id();
    if ($seiten_id <= 0) {
        return;
    }

    if (simple_clean_seite_sichtbar($seiten_id)) {
        return;
    }

    simple_clean_lehrerhinweis_ausgeben($seiten_id);
    exit;
}
add_action('template_redirect', 'simple_clean_lehrerseite_pruefen', 20);

/**
 * Die Hinweisseite „Nur für Lehrpersonen".
 *
 * DER TITEL DER GESPERRTEN SEITE WIRD NIRGENDS AUSGEGEBEN — weder sichtbar
 * noch im <title>. Er verriete, wie die Lösung heißt, und damit genau das,
 * was die Sperre verbergen soll.
 *
 * HTTP 403 statt 200: Für Suchmaschinen, Prüfwerkzeuge und Caches ist das die
 * ehrliche Antwort. `nocache_headers()` verhindert, dass ein Cache diese
 * Antwort später einer angemeldeten Lehrperson vorsetzt (oder umgekehrt).
 *
 * @param int $seiten_id
 * @return void
 */
function simple_clean_lehrerhinweis_ausgeben($seiten_id) {
    $seiten_id = (int) $seiten_id;

    status_header(403);
    nocache_headers();

    // Muss VOR get_header() gesetzt werden — dort läuft wp_head().
    add_filter('wp_robots', 'wp_robots_no_robots');
    add_filter('pre_get_document_title', 'simple_clean_lehrerhinweis_titel', 99);

    // Ziel für „zurück": die nächste sichtbare Elternseite, sonst die
    // Startseite. Eine gesperrte Elternseite darf hier nicht auftauchen.
    $zurueck_url  = home_url('/');
    $zurueck_text = __('Zur Startseite', 'simple-clean-theme');

    $eltern_id = (int) wp_get_post_parent_id($seiten_id);
    if ($eltern_id > 0 && simple_clean_seite_sichtbar($eltern_id)) {
        $zurueck_url  = get_permalink($eltern_id);
        $zurueck_text = get_the_title($eltern_id);
    }

    $anmelde_url = wp_login_url(get_permalink($seiten_id));

    get_header();
    ?>
    <main class="site-main">
        <div class="container">
            <div class="sc-lehrerhinweis">
                <h1><?php esc_html_e('Nur für Lehrpersonen', 'simple-clean-theme'); ?></h1>
                <p>
                    <?php esc_html_e('Diese Seite ist nur für angemeldete Lehrpersonen sichtbar.', 'simple-clean-theme'); ?>
                </p>
                <p>
                    <a class="sc-lehrerhinweis__anmelden" href="<?php echo esc_url($anmelde_url); ?>">
                        <?php esc_html_e('Anmelden', 'simple-clean-theme'); ?>
                    </a>
                </p>
                <p class="sc-lehrerhinweis__zurueck">
                    <a href="<?php echo esc_url($zurueck_url); ?>"><?php echo esc_html($zurueck_text); ?></a>
                </p>
            </div>
        </div>
    </main>
    <?php
    get_footer();
}

/**
 * Dokumenttitel der Hinweisseite. Ersetzt den Seitentitel vollständig.
 *
 * @param string $titel
 * @return string
 */
function simple_clean_lehrerhinweis_titel($titel) {
    return __('Nur für Lehrpersonen', 'simple-clean-theme') . ' – ' . get_bloginfo('name');
}