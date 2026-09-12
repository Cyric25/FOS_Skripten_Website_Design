<?php
/**
 * Fehlermeldungen von Leserinnen und Lesern
 *
 * Ein Formular, mit dem jede Besucherin und jeder Besucher — angemeldet oder
 * nicht, also ausdrücklich auch Schülerinnen und Schüler im Klassenmodus —
 * einen Fehler auf einer Seite melden kann. Die Meldungen landen in einem
 * eigenen, nicht öffentlichen Inhaltstyp und werden im Admin unter
 * „Meldungen" bearbeitet (includes/admin/meldungen-admin.php).
 *
 * LEITGEDANKE: Gute Meldungen entstehen nicht durch ein größeres Textfeld,
 * sondern dadurch, dass das Formular die halbe Antwort schon selbst kennt.
 * Seite, Adresse, Klasse, Browser und — der wichtigste Teil bei einem
 * Schulbuch — die zuvor markierte Textstelle werden automatisch mitgeschickt.
 * Gefragt wird nur noch, was der Server nicht wissen kann.
 *
 * @package FOS_Online_Schulbuch
 * @since 1.5.112
 */

if (!defined('ABSPATH')) {
    exit;
}

/** Inhaltstyp und Zustände an einer Stelle, damit sie nicht auseinanderlaufen. */
const FOS_MELDUNG_CPT      = 'fos_meldung';
const FOS_MELDUNG_NEU      = 'fos_meldung_neu';
const FOS_MELDUNG_ARBEIT   = 'fos_meldung_arbeit';
const FOS_MELDUNG_ERLEDIGT = 'fos_meldung_erledigt';

/** Option, in der die Seite mit dem Formular-Shortcode vermerkt ist. */
const FOS_MELDUNG_SEITE_OPTION = 'fos_meldung_seite_id';

/**
 * Die Arten von Meldungen.
 *
 * Bewusst eine Whitelist wie bei den Sammelaktionen des Seitenmanagers: Der
 * Wert aus $_POST wird nur gegen diese Liste geprüft. Der Hilfstext steuert
 * den Platzhalter im Beschreibungsfeld — eine gezielte Frage bringt deutlich
 * brauchbarere Antworten als ein leeres Feld.
 *
 * @return array Schlüssel => ['label' => …, 'hilfe' => …]
 */
function simple_clean_meldung_arten() {
    return array(
        'inhalt' => array(
            'label' => 'Inhaltlicher Fehler',
            'hilfe' => 'Was steht da? Was müsste stattdessen dastehen?',
        ),
        'verstaendnis' => array(
            'label' => 'Unverständlich erklärt',
            'hilfe' => 'Welche Stelle ist unklar? Woran bist du hängengeblieben?',
        ),
        'technik' => array(
            'label' => 'Anzeige oder Technik',
            'hilfe' => 'Was hast du gemacht? Was ist passiert? Was hättest du erwartet?',
        ),
        'vorschlag' => array(
            'label' => 'Verbesserungsvorschlag',
            'hilfe' => 'Was würdest du ändern, und warum wäre das besser?',
        ),
    );
}

/** Die drei Bearbeitungszustände mit ihren Beschriftungen. */
function simple_clean_meldung_zustaende() {
    return array(
        FOS_MELDUNG_NEU      => 'Neu',
        FOS_MELDUNG_ARBEIT   => 'In Arbeit',
        FOS_MELDUNG_ERLEDIGT => 'Erledigt',
    );
}

/**
 * Inhaltstyp und Zustände anmelden.
 */
function simple_clean_meldungen_register() {
    register_post_type(FOS_MELDUNG_CPT, array(
        'labels' => array(
            'name'               => 'Meldungen',
            'singular_name'      => 'Meldung',
            'menu_name'          => 'Meldungen',
            'all_items'          => 'Alle Meldungen',
            'edit_item'          => 'Meldung ansehen',
            'view_item'          => 'Meldung ansehen',
            'search_items'       => 'Meldungen durchsuchen',
            'not_found'          => 'Keine Meldungen vorhanden',
            'not_found_in_trash' => 'Keine Meldungen im Papierkorb',
        ),
        // Nicht öffentlich: keine eigene Adresse, nicht in Suche, Verzeichnis,
        // Sitemap oder REST. Eine Fehlermeldung enthält oft den Namen der
        // Schülerin oder des Schülers — die gehört nirgends nach außen.
        'public'              => false,
        'publicly_queryable'  => false,
        'exclude_from_search' => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_rest'        => false,
        'menu_position'       => 27,          // direkt unter dem Seitenmanager
        'menu_icon'           => 'dashicons-megaphone',
        'capability_type'     => 'post',
        'map_meta_cap'        => true,
        'capabilities'        => array(
            // Meldungen entstehen ausschließlich über das Formular. Ein
            // „Neue Meldung"-Knopf im Admin wäre sinnlos und verwirrend.
            'create_posts' => 'do_not_allow',
        ),
        // Bewusst NUR 'title': Ohne 'editor'-Unterstützung öffnet WordPress
        // die klassische Bearbeitungsansicht statt des Blockeditors. Für einen
        // Meldungstext ist ein vollwertiger Blockeditor nicht nur überflüssig,
        // er würde den Text beim ersten Speichern auch in Blockauszeichnung
        // umschreiben. Der Text steht in einer eigenen, schreibgeschützten
        // Box (includes/admin/meldungen-admin.php).
        'supports'            => array('title'),
        'hierarchical'        => false,
        'has_archive'         => false,
        'rewrite'             => false,
        'query_var'           => false,
    ));

    // Eigene Zustände statt der WordPress-Standards. Sie erscheinen dadurch
    // von selbst als Filterzeile über der Liste („Neu (12) | In Arbeit (3)").
    $zaehler = array(
        FOS_MELDUNG_NEU      => array('Neu <span class="count">(%s)</span>', 'Neu <span class="count">(%s)</span>'),
        FOS_MELDUNG_ARBEIT   => array('In Arbeit <span class="count">(%s)</span>', 'In Arbeit <span class="count">(%s)</span>'),
        FOS_MELDUNG_ERLEDIGT => array('Erledigt <span class="count">(%s)</span>', 'Erledigt <span class="count">(%s)</span>'),
    );
    foreach (simple_clean_meldung_zustaende() as $schluessel => $label) {
        register_post_status($schluessel, array(
            'label'                     => $label,
            'public'                    => false,
            'internal'                  => false,
            'protected'                 => true,
            'show_in_admin_all_list'    => true,
            'show_in_admin_status_list' => true,
            'label_count'               => $zaehler[$schluessel],
        ));
    }
}
add_action('init', 'simple_clean_meldungen_register');

/**
 * Seite mit dem Formular-Shortcode merken.
 *
 * Der Link im Footer soll auch ohne JavaScript irgendwo hinführen. Statt bei
 * jedem Seitenaufruf nach dem Shortcode zu suchen, wird die ID beim Speichern
 * der betreffenden Seite einmal vermerkt.
 */
function simple_clean_meldung_seite_merken($post_id, $post) {
    if ($post->post_type !== 'page' || wp_is_post_revision($post_id)) {
        return;
    }
    $gemerkt = (int) get_option(FOS_MELDUNG_SEITE_OPTION, 0);
    if (has_shortcode((string) $post->post_content, 'fos_meldeformular')) {
        if ($gemerkt !== (int) $post_id) {
            update_option(FOS_MELDUNG_SEITE_OPTION, (int) $post_id);
        }
    } elseif ($gemerkt === (int) $post_id) {
        // Shortcode wurde entfernt — Vermerk aufräumen statt ins Leere zeigen.
        delete_option(FOS_MELDUNG_SEITE_OPTION);
    }
}
add_action('save_post', 'simple_clean_meldung_seite_merken', 10, 2);

/**
 * Adresse der Formularseite, sofern eine existiert und veröffentlicht ist.
 *
 * @return string Leerer String, wenn es keine gibt.
 */
function simple_clean_meldung_seiten_url() {
    $id = (int) get_option(FOS_MELDUNG_SEITE_OPTION, 0);
    if (!$id) {
        return '';
    }
    $seite = get_post($id);
    if (!$seite || $seite->post_status !== 'publish') {
        return '';
    }
    return (string) get_permalink($id);
}

/**
 * Der Link im Footer. Wird von footer.php aufgerufen.
 *
 * Ohne JavaScript führt er auf die Formularseite (falls angelegt); mit
 * JavaScript fängt meldungen.js den Klick ab und öffnet das Fenster direkt
 * auf der Seite, auf der man gerade liest. Genau das ist der Punkt: Wer
 * mitten im Text etwas findet, soll seinen Lesepunkt nicht verlieren.
 */
function simple_clean_meldung_footer_link() {
    if (is_admin()) {
        return;
    }
    $url = simple_clean_meldung_seiten_url();
    printf(
        '<a href="%s" class="admin-link fos-meldung-oeffnen" data-fos-meldung-oeffnen="1">%s</a>',
        esc_url($url ? $url : '#'),
        esc_html('Fehler melden')
    );
}

/**
 * Das Formular als HTML.
 *
 * Dieselbe Auszeichnung für beide Wege — Fenster im Footer und eigene Seite.
 * Ein zweites, abweichendes Formular wäre eine Fehlerquelle: Jede Änderung
 * müsste an zwei Stellen nachgezogen werden.
 *
 * @param string $kontext 'modal' oder 'seite'
 * @return string
 */
function simple_clean_meldung_formular($kontext = 'seite') {
    $arten = simple_clean_meldung_arten();
    $hilfen = array();
    foreach ($arten as $schluessel => $art) {
        $hilfen[$schluessel] = $art['hilfe'];
    }

    ob_start();
    ?>
    <form class="fos-meldung-formular" data-kontext="<?php echo esc_attr($kontext); ?>"
          data-hilfen="<?php echo esc_attr(wp_json_encode($hilfen)); ?>">

        <?php wp_nonce_field('fos_meldung', 'fos_meldung_nonce', false); ?>

        <p class="fos-meldung-feld">
            <label for="fos-meldung-art-<?php echo esc_attr($kontext); ?>">
                Was für eine Meldung ist es? <span class="fos-meldung-pflicht" aria-hidden="true">*</span>
            </label>
            <select id="fos-meldung-art-<?php echo esc_attr($kontext); ?>" name="art" required>
                <option value="">— bitte wählen —</option>
                <?php foreach ($arten as $schluessel => $art): ?>
                    <option value="<?php echo esc_attr($schluessel); ?>"><?php echo esc_html($art['label']); ?></option>
                <?php endforeach; ?>
            </select>
        </p>

        <p class="fos-meldung-feld">
            <label for="fos-meldung-titel-<?php echo esc_attr($kontext); ?>">
                Worum geht es? <span class="fos-meldung-pflicht" aria-hidden="true">*</span>
            </label>
            <input type="text" id="fos-meldung-titel-<?php echo esc_attr($kontext); ?>" name="titel"
                   maxlength="150" required placeholder="In einem Satz, z. B. „Formel im Abschnitt Veresterung stimmt nicht“" />
        </p>

        <p class="fos-meldung-feld">
            <label for="fos-meldung-text-<?php echo esc_attr($kontext); ?>">
                Beschreibung <span class="fos-meldung-pflicht" aria-hidden="true">*</span>
            </label>
            <textarea id="fos-meldung-text-<?php echo esc_attr($kontext); ?>" name="beschreibung"
                      rows="5" maxlength="5000" required
                      placeholder="Wähle oben zuerst die Art der Meldung."></textarea>
        </p>

        <div class="fos-meldung-auswahl" hidden>
            <span class="fos-meldung-auswahl-titel">Markierte Textstelle wird mitgeschickt:</span>
            <blockquote class="fos-meldung-auswahl-text"></blockquote>
            <button type="button" class="fos-meldung-auswahl-weg">Stelle nicht mitschicken</button>
        </div>

        <p class="fos-meldung-feld">
            <label for="fos-meldung-melder-<?php echo esc_attr($kontext); ?>">
                Dein Name oder deine Klasse <span class="fos-meldung-freiwillig">(freiwillig)</span>
            </label>
            <input type="text" id="fos-meldung-melder-<?php echo esc_attr($kontext); ?>" name="melder"
                   maxlength="100" placeholder="Damit man bei Rückfragen weiß, wen man fragen kann" />
        </p>

        <?php // Honigtopf: für Menschen unsichtbar, Bots füllen ihn aus. ?>
        <p class="fos-meldung-honigtopf" aria-hidden="true">
            <label for="fos-meldung-website-<?php echo esc_attr($kontext); ?>">Website</label>
            <input type="text" id="fos-meldung-website-<?php echo esc_attr($kontext); ?>"
                   name="website" tabindex="-1" autocomplete="off" />
        </p>

        <p class="fos-meldung-aktionen">
            <button type="submit" class="fos-meldung-senden">Meldung abschicken</button>
            <span class="fos-meldung-status" role="status" aria-live="polite"></span>
        </p>
    </form>
    <?php
    return (string) ob_get_clean();
}

/**
 * Shortcode für die eigene Seite: [fos_meldeformular]
 */
function simple_clean_meldung_shortcode() {
    return '<div class="fos-meldung-seite">' . simple_clean_meldung_formular('seite') . '</div>';
}
add_shortcode('fos_meldeformular', 'simple_clean_meldung_shortcode');

/**
 * Skript und Gestaltung laden — nur im Frontend.
 */
function simple_clean_meldung_assets() {
    if (is_admin()) {
        return;
    }

    $css = get_template_directory() . '/dist/css/meldungen-style.css';
    if (file_exists($css)) {
        wp_enqueue_style(
            'fos-meldungen',
            get_template_directory_uri() . '/dist/css/meldungen-style.css',
            array(),
            filemtime($css)
        );
    }

    $js = get_template_directory() . '/dist/js/meldungen.js';
    if (file_exists($js)) {
        wp_enqueue_script(
            'fos-meldungen',
            get_template_directory_uri() . '/dist/js/meldungen.js',
            array(),
            filemtime($js),
            true
        );
        wp_localize_script('fos-meldungen', 'fosMeldungDaten', array(
            'ajaxUrl'  => admin_url('admin-ajax.php'),
            'seiteId'  => (int) get_queried_object_id(),
            'texte'    => array(
                'senden'   => 'Wird gesendet …',
                'danke'    => 'Danke! Deine Meldung ist angekommen.',
                'fehler'   => 'Das hat leider nicht geklappt. Bitte versuche es später noch einmal.',
                'pflicht'  => 'Bitte fülle die drei Pflichtfelder aus.',
            ),
        ));
    }
}
add_action('wp_enqueue_scripts', 'simple_clean_meldung_assets');

/**
 * Das Fenster mit dem Formular in den Footer legen.
 *
 * Steht auf jeder Seite bereit, ist aber bis zum Klick verborgen — so muss
 * beim Melden nichts nachgeladen werden.
 */
function simple_clean_meldung_modal() {
    if (is_admin()) {
        return;
    }
    ?>
    <div class="fos-meldung-fenster" id="fos-meldung-fenster" hidden>
        <div class="fos-meldung-hintergrund" data-fos-meldung-schliessen="1"></div>
        <div class="fos-meldung-kasten" role="dialog" aria-modal="true" aria-labelledby="fos-meldung-ueberschrift">
            <div class="fos-meldung-kopf">
                <h2 id="fos-meldung-ueberschrift">Fehler melden</h2>
                <button type="button" class="fos-meldung-schliessen" data-fos-meldung-schliessen="1"
                        aria-label="Fenster schließen" title="Schließen">✕</button>
            </div>
            <p class="fos-meldung-einleitung">
                Danke, dass du mithilfst. Die Seite, auf der du gerade bist, wird automatisch
                mitgeschickt — du musst sie nicht beschreiben.
            </p>
            <?php echo simple_clean_meldung_formular('modal'); ?>
        </div>
    </div>
    <?php
}
add_action('wp_footer', 'simple_clean_meldung_modal');

/**
 * Begrenzung: wie viele Meldungen darf ein Absender in kurzer Zeit schicken?
 *
 * Dasselbe Muster wie beim Website-Passwort (Transient je IP). Die IP wird
 * dabei NUR gehasht als Schlüssel verwendet und nirgends gespeichert — an der
 * Meldung selbst hängt sie nicht.
 *
 * GEZÄHLT WIRD ERST BEIM ERFOLG, nicht beim Versuch. Sonst sperrt sich
 * jemand aus, der dreimal ein Pflichtfeld vergisst — und genau das passiert
 * erfahrungsgemäß den Leuten, die sich zum ersten Mal an so ein Formular
 * setzen. Für einen Bot ändert es nichts: Der kommt an den Pflichtfeldern
 * ohnehin nicht vorbei, und wenn doch, greift die Grenze ab der fünften
 * erfolgreichen Meldung.
 *
 * @return string Schlüssel des Zählers, oder '' wenn keine IP bekannt ist
 */
function simple_clean_meldung_zaehler_schluessel() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    return $ip === '' ? '' : 'fos_meldung_limit_' . md5($ip);
}

/** @return bool true, wenn noch gesendet werden darf */
function simple_clean_meldung_darf_senden() {
    $schluessel = simple_clean_meldung_zaehler_schluessel();
    if ($schluessel === '') {
        return true;
    }
    return ((int) get_transient($schluessel)) < 5;
}

/** Eine erfolgreich gespeicherte Meldung auf den Zähler nehmen. */
function simple_clean_meldung_verbrauchen() {
    $schluessel = simple_clean_meldung_zaehler_schluessel();
    if ($schluessel === '') {
        return;
    }
    set_transient($schluessel, ((int) get_transient($schluessel)) + 1, 15 * MINUTE_IN_SECONDS);
}

/**
 * Eine abgeschickte Meldung entgegennehmen.
 *
 * Erreichbar für Angemeldete UND nicht Angemeldete — Schülerinnen und Schüler
 * melden sich nie an, sie kommen über das Klassenpasswort. Genau sie finden
 * die meisten Fehler, weil sie den Text wirklich lesen.
 */
function simple_clean_meldung_speichern() {
    check_ajax_referer('fos_meldung', 'nonce');

    // Honigtopf: ausgefüllt heißt Bot. Nach außen sieht es aus wie Erfolg,
    // damit der Bot nichts über die Erkennung lernt.
    if (!empty($_POST['website'])) {
        wp_send_json_success(array('message' => 'Danke! Deine Meldung ist angekommen.'));
    }

    if (!simple_clean_meldung_darf_senden()) {
        wp_send_json_error(array(
            'message' => 'Es sind gerade sehr viele Meldungen von dir gekommen. Bitte versuche es in einer Viertelstunde noch einmal.',
        ));
    }

    $arten = simple_clean_meldung_arten();
    $art   = isset($_POST['art']) ? sanitize_key($_POST['art']) : '';
    if (!isset($arten[$art])) {
        wp_send_json_error(array('message' => 'Bitte wähle aus, um was für eine Meldung es geht.'));
    }

    $titel = isset($_POST['titel']) ? sanitize_text_field(wp_unslash($_POST['titel'])) : '';
    $text  = isset($_POST['beschreibung']) ? sanitize_textarea_field(wp_unslash($_POST['beschreibung'])) : '';
    $titel = mb_substr($titel, 0, 150);
    $text  = mb_substr($text, 0, 5000);

    if ($titel === '' || $text === '') {
        wp_send_json_error(array('message' => 'Bitte fülle die drei Pflichtfelder aus.'));
    }

    // Gemeldete Seite: Die ID wird gegen die Datenbank geprüft, die Adresse
    // nur übernommen, wenn sie auf diese Website zeigt. Ein fremder Wert
    // stünde sonst als anklickbarer Link in der Admin-Ansicht.
    $seite_id = isset($_POST['seite_id']) ? absint($_POST['seite_id']) : 0;
    if ($seite_id && !get_post($seite_id)) {
        $seite_id = 0;
    }
    $url = isset($_POST['url']) ? esc_url_raw(wp_unslash($_POST['url'])) : '';
    if ($url !== '') {
        $eigen = wp_parse_url(home_url(), PHP_URL_HOST);
        $fremd = wp_parse_url($url, PHP_URL_HOST);
        if (!$fremd || strtolower($fremd) !== strtolower((string) $eigen)) {
            $url = '';
        }
    }

    $meldung_id = wp_insert_post(array(
        'post_type'    => FOS_MELDUNG_CPT,
        'post_status'  => FOS_MELDUNG_NEU,
        'post_title'   => $titel,
        'post_content' => $text,
        'post_author'  => get_current_user_id(),
    ), true);

    if (is_wp_error($meldung_id)) {
        wp_send_json_error(array('message' => 'Die Meldung konnte nicht gespeichert werden.'));
    }

    $melder   = isset($_POST['melder']) ? mb_substr(sanitize_text_field(wp_unslash($_POST['melder'])), 0, 100) : '';
    $auswahl  = isset($_POST['auswahl']) ? mb_substr(sanitize_textarea_field(wp_unslash($_POST['auswahl'])), 0, 1000) : '';
    $klasse   = isset($_POST['klasse']) ? mb_substr(sanitize_text_field(wp_unslash($_POST['klasse'])), 0, 50) : '';
    $viewport = isset($_POST['viewport']) ? mb_substr(sanitize_text_field(wp_unslash($_POST['viewport'])), 0, 30) : '';
    $browser  = isset($_SERVER['HTTP_USER_AGENT'])
        ? mb_substr(sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])), 0, 255)
        : '';

    update_post_meta($meldung_id, '_fos_art', $art);
    update_post_meta($meldung_id, '_fos_seite_id', $seite_id);
    update_post_meta($meldung_id, '_fos_url', $url);
    update_post_meta($meldung_id, '_fos_melder', $melder);
    update_post_meta($meldung_id, '_fos_auswahl', $auswahl);
    update_post_meta($meldung_id, '_fos_klasse', $klasse);
    update_post_meta($meldung_id, '_fos_viewport', $viewport);
    update_post_meta($meldung_id, '_fos_browser', $browser);
    update_post_meta($meldung_id, '_fos_angemeldet', is_user_logged_in() ? '1' : '0');

    simple_clean_meldung_verbrauchen();

    wp_send_json_success(array('message' => 'Danke! Deine Meldung ist angekommen.'));
}
add_action('wp_ajax_fos_meldung', 'simple_clean_meldung_speichern');
add_action('wp_ajax_nopriv_fos_meldung', 'simple_clean_meldung_speichern');
