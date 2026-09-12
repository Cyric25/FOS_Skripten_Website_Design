<?php
/**
 * Admin-Ansicht für die Fehlermeldungen
 *
 * Gegenstück zu includes/meldungen.php: Dort entstehen die Meldungen, hier
 * werden sie gesichtet und abgearbeitet. Bewusst keine eigene Oberfläche,
 * sondern die vertraute WordPress-Listenansicht — mit passenden Spalten,
 * einem Filter nach Art und den drei Zuständen als Filterzeile darüber.
 *
 * @package FOS_Online_Schulbuch
 * @since 1.5.112
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ------------------------------------------------- Meldung als Arbeitsauftrag */

/**
 * Eine Meldung als Textblock, der sich direkt weiterverwenden lässt.
 *
 * ZWECK: Eine Meldung nützt erst, wenn sich daraus ohne Rückfragen eine
 * Fehlerbehebung ableiten lässt. Der Block enthält deshalb nicht nur den
 * Meldungstext, sondern alles, was zum Auffinden und Einordnen der Stelle
 * nötig ist — Seite mit ID und Bearbeitungsadresse, die markierte Textstelle
 * als Suchschlüssel, dazu Browser und Versionsstände für Anzeigefehler.
 *
 * Markdown, weil es sowohl für Menschen lesbar ist als auch von
 * Coding-Agenten zuverlässig verstanden wird.
 *
 * EINZIGE QUELLE des Textes — das JavaScript legt ihn nur in die
 * Zwischenablage und baut ihn nicht selbst zusammen.
 *
 * @param int $post_id
 * @return string
 */
function simple_clean_meldung_als_text($post_id) {
    $post = get_post($post_id);
    if (!$post || $post->post_type !== FOS_MELDUNG_CPT) {
        return '';
    }

    $arten     = simple_clean_meldung_arten();
    $zustaende = simple_clean_meldung_zustaende();
    $art       = (string) get_post_meta($post_id, '_fos_art', true);
    $art_label = isset($arten[$art]) ? $arten[$art]['label'] : 'unbekannt';
    $zustand   = isset($zustaende[$post->post_status]) ? $zustaende[$post->post_status] : $post->post_status;

    $seite_id = (int) get_post_meta($post_id, '_fos_seite_id', true);
    $seite    = $seite_id ? get_post($seite_id) : null;
    $url      = (string) get_post_meta($post_id, '_fos_url', true);
    $auswahl  = (string) get_post_meta($post_id, '_fos_auswahl', true);
    $melder   = (string) get_post_meta($post_id, '_fos_melder', true);
    $klasse   = (string) get_post_meta($post_id, '_fos_klasse', true);
    $notiz    = (string) get_post_meta($post_id, '_fos_notiz', true);
    $theme    = wp_get_theme();

    $z = array();
    $z[] = '## Fehlermeldung #' . $post_id . ' — ' . $art_label;
    $z[] = '';
    $z[] = '**' . $post->post_title . '**';
    $z[] = '';
    $z[] = '- Gemeldet: ' . get_the_time('d.m.Y H:i', $post) . ' · Status: ' . $zustand;

    $wer = $melder !== '' ? $melder : 'ohne Namensangabe';
    $wer .= get_post_meta($post_id, '_fos_angemeldet', true) === '1' ? ' (angemeldet)' : ' (nicht angemeldet)';
    if ($klasse !== '') {
        $wer .= ', Klassenmodus ' . $klasse;
    }
    $z[] = '- Melder: ' . $wer;

    $z[] = '';
    $z[] = '### Beschreibung';
    $z[] = '';
    $z[] = $post->post_content !== '' ? $post->post_content : '(keine)';

    if ($auswahl !== '') {
        $z[] = '';
        $z[] = '### Markierte Textstelle auf der Seite';
        $z[] = '';
        foreach (preg_split('/\r\n|\r|\n/', $auswahl) as $zeile) {
            $z[] = '> ' . $zeile;
        }
    }

    $z[] = '';
    $z[] = '### Betroffene Seite';
    $z[] = '';
    if ($seite) {
        $z[] = '- Titel: ' . $seite->post_title . ' (Seiten-ID ' . $seite_id . ')';
        $z[] = '- Bearbeiten: ' . admin_url('post.php?post=' . $seite_id . '&action=edit');
    } else {
        $z[] = '- Seite nicht mehr vorhanden oder nicht übermittelt';
    }
    if ($url !== '') {
        $z[] = '- Adresse: ' . $url;
    }

    $z[] = '';
    $z[] = '### Technisch';
    $z[] = '';
    $browser = (string) get_post_meta($post_id, '_fos_browser', true);
    $z[] = '- Browser: ' . ($browser !== '' ? $browser : 'unbekannt');
    $viewport = (string) get_post_meta($post_id, '_fos_viewport', true);
    $z[] = '- Bildschirm: ' . ($viewport !== '' ? $viewport : 'unbekannt');
    $z[] = '- Theme: ' . $theme->get('Name') . ' ' . $theme->get('Version') . ' · WordPress ' . get_bloginfo('version');

    if ($notiz !== '') {
        $z[] = '';
        $z[] = '### Notiz des Betreibers';
        $z[] = '';
        $z[] = $notiz;
    }

    return implode("\n", $z);
}

/* ------------------------------------------------------------------ Liste */

/**
 * Spalten der Übersicht.
 *
 * Der Autor fällt weg (Meldungen kommen fast immer von nicht Angemeldeten),
 * dafür kommen Art, betroffene Seite und Melder dazu.
 */
function simple_clean_meldung_spalten($spalten) {
    $neu = array();
    foreach ($spalten as $schluessel => $label) {
        if ($schluessel === 'author') {
            continue;
        }
        $neu[$schluessel] = $label;
        if ($schluessel === 'title') {
            $neu['fos_art']    = 'Art';
            $neu['fos_seite']  = 'Seite';
            $neu['fos_melder'] = 'Melder';
        }
    }
    return $neu;
}
add_filter('manage_' . FOS_MELDUNG_CPT . '_posts_columns', 'simple_clean_meldung_spalten');

/**
 * Inhalt der eigenen Spalten.
 */
function simple_clean_meldung_spalte_inhalt($spalte, $post_id) {
    switch ($spalte) {
        case 'fos_art':
            $arten = simple_clean_meldung_arten();
            $art   = (string) get_post_meta($post_id, '_fos_art', true);
            echo isset($arten[$art]) ? esc_html($arten[$art]['label']) : '—';
            break;

        case 'fos_seite':
            $seite_id = (int) get_post_meta($post_id, '_fos_seite_id', true);
            $url      = (string) get_post_meta($post_id, '_fos_url', true);
            $seite    = $seite_id ? get_post($seite_id) : null;
            if ($seite) {
                printf(
                    '<a href="%s">%s</a>',
                    esc_url((string) get_edit_post_link($seite_id)),
                    esc_html(mb_substr($seite->post_title, 0, 60))
                );
                if ($url) {
                    printf(
                        ' <a href="%s" target="_blank" rel="noopener" title="Seite ansehen" aria-label="Seite ansehen">↗</a>',
                        esc_url($url)
                    );
                }
            } elseif ($url) {
                printf('<a href="%s" target="_blank" rel="noopener">%s</a>', esc_url($url), esc_html($url));
            } else {
                echo '—';
            }
            $klasse = (string) get_post_meta($post_id, '_fos_klasse', true);
            if ($klasse !== '') {
                echo '<br /><span class="description">Klassenmodus: ' . esc_html($klasse) . '</span>';
            }
            break;

        case 'fos_melder':
            $melder = (string) get_post_meta($post_id, '_fos_melder', true);
            $ang    = get_post_meta($post_id, '_fos_angemeldet', true) === '1';
            echo $melder !== '' ? esc_html($melder) : '<span class="description">ohne Angabe</span>';
            echo '<br /><span class="description">' . ($ang ? 'angemeldet' : 'nicht angemeldet') . '</span>';
            break;
    }
}
add_action('manage_' . FOS_MELDUNG_CPT . '_posts_custom_column', 'simple_clean_meldung_spalte_inhalt', 10, 2);

/**
 * Zeilenaktion „Kopieren" unter jedem Titel.
 */
function simple_clean_meldung_zeilenaktion($aktionen, $post) {
    if ($post->post_type !== FOS_MELDUNG_CPT) {
        return $aktionen;
    }
    $aktionen['fos_kopieren'] = sprintf(
        '<a href="#" class="fos-meldung-kopieren" data-id="%d">Kopieren</a>',
        (int) $post->ID
    );
    return $aktionen;
}
add_filter('post_row_actions', 'simple_clean_meldung_zeilenaktion', 10, 2);

/**
 * Knopf über der Liste: alle angehakten Meldungen auf einmal kopieren.
 */
function simple_clean_meldung_kopierknopf($welche) {
    global $typenow;
    if ($typenow !== FOS_MELDUNG_CPT || $welche !== 'top') {
        return;
    }
    echo '<button type="button" class="button" id="fos-meldung-kopieren-auswahl" '
       . 'style="margin-left:8px;">Ausgewählte kopieren</button>';
}
add_action('manage_posts_extra_tablenav', 'simple_clean_meldung_kopierknopf');

/**
 * Filter nach Art über der Liste.
 */
function simple_clean_meldung_filter() {
    global $typenow;
    if ($typenow !== FOS_MELDUNG_CPT) {
        return;
    }
    $aktuell = isset($_GET['fos_art']) ? sanitize_key($_GET['fos_art']) : '';
    echo '<select name="fos_art"><option value="">Alle Arten</option>';
    foreach (simple_clean_meldung_arten() as $schluessel => $art) {
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($schluessel),
            selected($aktuell, $schluessel, false),
            esc_html($art['label'])
        );
    }
    echo '</select>';
}
add_action('restrict_manage_posts', 'simple_clean_meldung_filter');

/**
 * Den Filter auf die Abfrage anwenden.
 */
function simple_clean_meldung_filter_anwenden($query) {
    global $pagenow;
    if (!is_admin() || $pagenow !== 'edit.php' || !$query->is_main_query()) {
        return;
    }
    if (($query->get('post_type') !== FOS_MELDUNG_CPT) || empty($_GET['fos_art'])) {
        return;
    }
    $arten = simple_clean_meldung_arten();
    $art   = sanitize_key($_GET['fos_art']);
    if (!isset($arten[$art])) {
        return;
    }
    $query->set('meta_query', array(
        array('key' => '_fos_art', 'value' => $art),
    ));
}
add_action('pre_get_posts', 'simple_clean_meldung_filter_anwenden');

/**
 * Sammelaktionen für die Zustände.
 *
 * Der häufigste Handgriff beim Sichten: mehrere Meldungen auf einmal auf
 * „Erledigt" setzen. Dieselbe Überlegung wie bei den Sammelaktionen des
 * Seitenmanagers.
 */
function simple_clean_meldung_sammelaktionen($aktionen) {
    foreach (simple_clean_meldung_zustaende() as $schluessel => $label) {
        $aktionen['fos_setze_' . $schluessel] = 'Status: ' . $label;
    }
    return $aktionen;
}
add_filter('bulk_actions-edit-' . FOS_MELDUNG_CPT, 'simple_clean_meldung_sammelaktionen');

function simple_clean_meldung_sammelaktion_ausfuehren($umleitung, $aktion, $ids) {
    $zustaende = simple_clean_meldung_zustaende();
    $ziel = '';
    foreach ($zustaende as $schluessel => $label) {
        if ($aktion === 'fos_setze_' . $schluessel) {
            $ziel = $schluessel;
            break;
        }
    }
    if ($ziel === '') {
        return $umleitung;
    }

    $anzahl = 0;
    foreach ($ids as $id) {
        if (!current_user_can('edit_post', $id)) {
            continue;
        }
        if (simple_clean_meldung_zustand_setzen((int) $id, $ziel)) {
            $anzahl++;
        }
    }
    return add_query_arg('fos_geaendert', $anzahl, $umleitung);
}
add_filter('handle_bulk_actions-edit-' . FOS_MELDUNG_CPT, 'simple_clean_meldung_sammelaktion_ausfuehren', 10, 3);

function simple_clean_meldung_sammelaktion_hinweis() {
    if (empty($_GET['fos_geaendert'])) {
        return;
    }
    printf(
        '<div class="notice notice-success is-dismissible"><p>%d Meldung(en) geändert.</p></div>',
        (int) $_GET['fos_geaendert']
    );
}
add_action('admin_notices', 'simple_clean_meldung_sammelaktion_hinweis');

/**
 * Zustand einer Meldung setzen.
 *
 * Über $wpdb statt wp_update_post — dasselbe Muster wie beim post_parent im
 * Seitenmanager. Der Aufruf kommt hier aus einem save_post-Haken; ein
 * wp_update_post() darin würde denselben Haken erneut auslösen.
 *
 * @return bool true, wenn sich etwas geändert hat
 */
function simple_clean_meldung_zustand_setzen($post_id, $zustand) {
    if (!isset(simple_clean_meldung_zustaende()[$zustand])) {
        return false;
    }
    $post = get_post($post_id);
    if (!$post || $post->post_type !== FOS_MELDUNG_CPT || $post->post_status === $zustand) {
        return false;
    }

    global $wpdb;
    $ergebnis = $wpdb->update($wpdb->posts, array('post_status' => $zustand), array('ID' => $post_id), array('%s'), array('%d'));
    if ($ergebnis === false) {
        return false;
    }
    clean_post_cache($post_id);
    return true;
}

/* -------------------------------------------------------- Einzelansicht */

/**
 * Die Boxen der Einzelansicht.
 */
function simple_clean_meldung_boxen() {
    add_meta_box('fos-meldung-text', 'Die Meldung', 'simple_clean_meldung_box_text', FOS_MELDUNG_CPT, 'normal', 'high');
    add_meta_box('fos-meldung-bearbeitung', 'Bearbeitung', 'simple_clean_meldung_box_bearbeitung', FOS_MELDUNG_CPT, 'side', 'high');
    add_meta_box('fos-meldung-kopieren', 'Für die Fehlerbehebung kopieren', 'simple_clean_meldung_box_kopieren', FOS_MELDUNG_CPT, 'normal', 'default');
    add_meta_box('fos-meldung-kontext', 'Automatisch erfasst', 'simple_clean_meldung_box_kontext', FOS_MELDUNG_CPT, 'side', 'default');
}
add_action('add_meta_boxes_' . FOS_MELDUNG_CPT, 'simple_clean_meldung_boxen');

/**
 * Meldungstext — schreibgeschützt.
 *
 * Der Inhaltstyp unterstützt bewusst kein 'editor' (siehe meldungen.php):
 * Ein Blockeditor würde den eingegangenen Text beim ersten Speichern in
 * Blockauszeichnung umschreiben. Hier steht er unverändert.
 */
function simple_clean_meldung_box_text($post) {
    $arten = simple_clean_meldung_arten();
    $art   = (string) get_post_meta($post->ID, '_fos_art', true);
    $auswahl = (string) get_post_meta($post->ID, '_fos_auswahl', true);
    ?>
    <p><strong>Art:</strong> <?php echo isset($arten[$art]) ? esc_html($arten[$art]['label']) : '—'; ?></p>

    <?php if ($auswahl !== ''): ?>
        <p><strong>Markierte Textstelle auf der Seite:</strong></p>
        <blockquote style="margin:0 0 1em;padding:8px 12px;border-left:4px solid #e24614;background:#f5ede9;">
            <?php echo nl2br(esc_html($auswahl)); ?>
        </blockquote>
    <?php endif; ?>

    <p><strong>Beschreibung:</strong></p>
    <div style="padding:8px 12px;background:#f6f7f7;border:1px solid #dcdcde;border-radius:4px;white-space:pre-wrap;">
        <?php echo esc_html($post->post_content); ?>
    </div>
    <?php
}

/**
 * Der fertige Textblock zum Herauskopieren.
 *
 * Das Textfeld steht bewusst sichtbar da und nicht nur hinter dem Knopf: So
 * ist auf einen Blick zu sehen, was mitgeht — und wer lieber von Hand
 * markiert, kann das tun.
 */
function simple_clean_meldung_box_kopieren($post) {
    $text = simple_clean_meldung_als_text($post->ID);
    ?>
    <p class="description" style="margin-top:0;">
        Dieser Block enthält alles, was zum Beheben nötig ist — Meldungstext,
        markierte Stelle, Seite mit Bearbeitungslink und die Versionsstände.
        Einfach in eine Sitzung mit dem Coding-Agenten einfügen.
    </p>
    <p>
        <button type="button" class="button button-primary" id="fos-meldung-kopieren-einzeln">
            In die Zwischenablage kopieren
        </button>
    </p>
    <textarea id="fos-meldung-kopiertext" readonly rows="16"
              style="width:100%;font-family:Consolas,Monaco,monospace;font-size:12px;"
              onclick="this.select();"><?php echo esc_textarea($text); ?></textarea>
    <?php
}

/**
 * Zustand und eigene Notiz.
 */
function simple_clean_meldung_box_bearbeitung($post) {
    wp_nonce_field('fos_meldung_speichern', 'fos_meldung_admin_nonce');
    $notiz = (string) get_post_meta($post->ID, '_fos_notiz', true);
    ?>
    <p>
        <label for="fos-meldung-zustand"><strong>Status</strong></label><br />
        <select name="fos_meldung_zustand" id="fos-meldung-zustand" style="width:100%;">
            <?php foreach (simple_clean_meldung_zustaende() as $schluessel => $label): ?>
                <option value="<?php echo esc_attr($schluessel); ?>" <?php selected($post->post_status, $schluessel); ?>>
                    <?php echo esc_html($label); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <p>
        <label for="fos-meldung-notiz"><strong>Notiz</strong> (nur für dich)</label><br />
        <textarea name="fos_meldung_notiz" id="fos-meldung-notiz" rows="5" style="width:100%;"><?php
            echo esc_textarea($notiz);
        ?></textarea>
    </p>
    <?php
}

/**
 * Alles, was das Formular selbst mitgeschickt hat.
 */
function simple_clean_meldung_box_kontext($post) {
    $seite_id = (int) get_post_meta($post->ID, '_fos_seite_id', true);
    $seite    = $seite_id ? get_post($seite_id) : null;
    $zeilen   = array(
        'Seite'      => $seite
            ? '<a href="' . esc_url((string) get_edit_post_link($seite_id)) . '">' . esc_html($seite->post_title) . '</a>'
            : '—',
        'Adresse'    => ($u = (string) get_post_meta($post->ID, '_fos_url', true))
            ? '<a href="' . esc_url($u) . '" target="_blank" rel="noopener">' . esc_html(mb_substr($u, 0, 60)) . '</a>'
            : '—',
        'Melder'     => ($m = (string) get_post_meta($post->ID, '_fos_melder', true)) ? esc_html($m) : 'ohne Angabe',
        'Angemeldet' => get_post_meta($post->ID, '_fos_angemeldet', true) === '1' ? 'ja' : 'nein',
        'Klasse'     => ($k = (string) get_post_meta($post->ID, '_fos_klasse', true)) ? esc_html($k) : '—',
        'Bildschirm' => ($v = (string) get_post_meta($post->ID, '_fos_viewport', true)) ? esc_html($v) : '—',
        'Eingegangen' => esc_html(get_the_time('d.m.Y H:i', $post)),
    );
    echo '<table class="widefat striped" style="border:0;">';
    foreach ($zeilen as $label => $wert) {
        printf('<tr><td style="width:35%%;"><strong>%s</strong></td><td>%s</td></tr>', esc_html($label), $wert);
    }
    echo '</table>';
    $browser = (string) get_post_meta($post->ID, '_fos_browser', true);
    if ($browser !== '') {
        echo '<p class="description" style="margin-top:8px;word-break:break-all;">' . esc_html($browser) . '</p>';
    }
}

/**
 * Zustand und Notiz speichern.
 */
function simple_clean_meldung_admin_speichern($post_id, $post) {
    if ($post->post_type !== FOS_MELDUNG_CPT) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!isset($_POST['fos_meldung_admin_nonce'])
        || !wp_verify_nonce(sanitize_key($_POST['fos_meldung_admin_nonce']), 'fos_meldung_speichern')) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['fos_meldung_notiz'])) {
        update_post_meta($post_id, '_fos_notiz', sanitize_textarea_field(wp_unslash($_POST['fos_meldung_notiz'])));
    }
    if (isset($_POST['fos_meldung_zustand'])) {
        simple_clean_meldung_zustand_setzen($post_id, sanitize_key($_POST['fos_meldung_zustand']));
    }
}
add_action('save_post', 'simple_clean_meldung_admin_speichern', 10, 2);

/**
 * Die Status- und Sichtbarkeitszeilen der Veröffentlichen-Box ausblenden.
 *
 * Der Zustand wird über die eigene Box oben gesetzt. Die Standardzeilen
 * kennen die eigenen Zustände nicht und zeigen dort Unsinn an; der
 * „Aktualisieren"-Knopf der Box wird aber gebraucht und bleibt.
 */
function simple_clean_meldung_admin_css($hook) {
    global $typenow;
    if ($typenow !== FOS_MELDUNG_CPT || !in_array($hook, array('post.php', 'post-new.php'), true)) {
        return;
    }
    echo '<style>#minor-publishing-actions,#misc-publishing-actions{display:none;}</style>';
}
add_action('admin_head', 'simple_clean_meldung_admin_css');

/**
 * Das Kopier-Skript laden — nur auf den beiden Meldungs-Bildschirmen.
 *
 * Auf der Liste kommen zusätzlich die fertigen Textblöcke aller angezeigten
 * Meldungen mit, damit „Ausgewählte kopieren" ohne weitere Anfrage auskommt.
 */
function simple_clean_meldung_admin_skript($hook) {
    global $typenow;
    if ($typenow !== FOS_MELDUNG_CPT || !in_array($hook, array('edit.php', 'post.php'), true)) {
        return;
    }

    $js = get_template_directory() . '/dist/js/meldungen-admin.js';
    if (!file_exists($js)) {
        return;
    }
    wp_enqueue_script(
        'fos-meldungen-admin',
        get_template_directory_uri() . '/dist/js/meldungen-admin.js',
        array(),
        filemtime($js),
        true
    );

    $texte = array();
    if ($hook === 'edit.php') {
        global $wp_query;
        if (!empty($wp_query->posts)) {
            foreach ($wp_query->posts as $eintrag) {
                $texte[(string) $eintrag->ID] = simple_clean_meldung_als_text($eintrag->ID);
            }
        }
    }
    wp_localize_script('fos-meldungen-admin', 'fosMeldungTexte', $texte);
}
add_action('admin_enqueue_scripts', 'simple_clean_meldung_admin_skript');

/**
 * Zahl der neuen Meldungen am Menüpunkt — wie bei den Kommentaren.
 *
 * So ist ohne Klick zu sehen, ob etwas hereingekommen ist.
 */
function simple_clean_meldung_menue_zaehler() {
    global $menu;
    if (!is_array($menu)) {
        return;
    }
    $anzahl = (int) wp_count_posts(FOS_MELDUNG_CPT)->{FOS_MELDUNG_NEU};
    if ($anzahl < 1) {
        return;
    }
    $slug = 'edit.php?post_type=' . FOS_MELDUNG_CPT;
    foreach ($menu as $position => $eintrag) {
        if (isset($eintrag[2]) && $eintrag[2] === $slug) {
            $menu[$position][0] .= sprintf(
                ' <span class="awaiting-mod"><span class="pending-count">%d</span></span>',
                $anzahl
            );
            break;
        }
    }
}
add_action('admin_menu', 'simple_clean_meldung_menue_zaehler', 99);
