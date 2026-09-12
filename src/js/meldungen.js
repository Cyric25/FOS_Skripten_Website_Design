/**
 * Fehlermeldungen melden
 *
 * Öffnet das Meldefenster, sammelt den Zusammenhang ein, den der Server nicht
 * kennen kann, und schickt das Formular ab. Gegenstück zu
 * includes/meldungen.php.
 *
 * Bewusst ohne jQuery — wie src/js/main.js. Das Skript läuft auf jeder Seite
 * des Auftritts und soll so wenig wie möglich mitbringen.
 *
 * @package FOS_Online_Schulbuch
 * @since 1.5.112
 */

(function () {
    'use strict';

    if (typeof fosMeldungDaten === 'undefined') {
        return;
    }

    var fenster = null;
    var vorigerFokus = null;

    /**
     * Die zuletzt markierte Textstelle.
     *
     * WARUM GEMERKT UND NICHT ERST BEIM KLICK GELESEN: Ein Klick auf den Link
     * im Footer hebt die Markierung im selben Moment auf — beim Auslesen wäre
     * sie schon weg. Deshalb wird jede nicht leere Markierung laufend
     * festgehalten und beim Öffnen des Fensters verwendet. Genau diese Angabe
     * macht bei einem Schulbuch den Unterschied zwischen „auf der Seite
     * stimmt was nicht" und einer Meldung, mit der sich sofort arbeiten lässt.
     */
    var letzteMarkierung = '';

    document.addEventListener('selectionchange', function () {
        var auswahl = window.getSelection();
        if (!auswahl) {
            return;
        }
        var text = String(auswahl).trim();
        if (text.length > 2) {
            letzteMarkierung = text.length > 1000 ? text.slice(0, 1000) + ' …' : text;
        }
    });

    /** Klassenkennung aus der Adresse (Klassenmodus des CDB-Plugins). */
    function klasseAusAdresse() {
        try {
            return new URLSearchParams(window.location.search).get('classroom') || '';
        } catch (e) {
            return '';
        }
    }

    function zeigeMarkierung(formular, text) {
        var kasten = formular.querySelector('.fos-meldung-auswahl');
        if (!kasten) {
            return;
        }
        if (text) {
            kasten.querySelector('.fos-meldung-auswahl-text').textContent = text;
            kasten.hidden = false;
            formular.dataset.auswahl = text;
        } else {
            kasten.hidden = true;
            formular.dataset.auswahl = '';
        }
    }

    function oeffne() {
        fenster = fenster || document.getElementById('fos-meldung-fenster');
        if (!fenster) {
            return false;
        }
        vorigerFokus = document.activeElement;
        fenster.hidden = false;
        document.body.classList.add('fos-meldung-offen');

        var formular = fenster.querySelector('.fos-meldung-formular');
        if (formular) {
            zeigeMarkierung(formular, letzteMarkierung);
            var erstes = formular.querySelector('select, input, textarea');
            if (erstes) {
                erstes.focus();
            }
        }
        return true;
    }

    function schliesse() {
        if (!fenster || fenster.hidden) {
            return;
        }
        fenster.hidden = true;
        document.body.classList.remove('fos-meldung-offen');
        if (vorigerFokus && typeof vorigerFokus.focus === 'function') {
            vorigerFokus.focus();
        }
    }

    /** Der Platzhalter im Beschreibungsfeld richtet sich nach der Art. */
    function hilfstextSetzen(formular) {
        var art = formular.querySelector('[name="art"]');
        var text = formular.querySelector('[name="beschreibung"]');
        if (!art || !text) {
            return;
        }
        var hilfen = {};
        try {
            hilfen = JSON.parse(formular.dataset.hilfen || '{}');
        } catch (e) {
            hilfen = {};
        }
        art.addEventListener('change', function () {
            text.placeholder = hilfen[art.value] || 'Beschreibe möglichst genau, was dir aufgefallen ist.';
        });
    }

    function absenden(formular, event) {
        event.preventDefault();

        var status = formular.querySelector('.fos-meldung-status');
        var knopf = formular.querySelector('.fos-meldung-senden');
        var texte = fosMeldungDaten.texte || {};

        var daten = new FormData();
        daten.append('action', 'fos_meldung');
        daten.append('nonce', (formular.querySelector('[name="fos_meldung_nonce"]') || {}).value || '');
        daten.append('art', (formular.querySelector('[name="art"]') || {}).value || '');
        daten.append('titel', (formular.querySelector('[name="titel"]') || {}).value || '');
        daten.append('beschreibung', (formular.querySelector('[name="beschreibung"]') || {}).value || '');
        daten.append('melder', (formular.querySelector('[name="melder"]') || {}).value || '');
        daten.append('website', (formular.querySelector('[name="website"]') || {}).value || '');
        daten.append('auswahl', formular.dataset.auswahl || '');
        daten.append('seite_id', fosMeldungDaten.seiteId || 0);
        daten.append('url', window.location.href);
        daten.append('klasse', klasseAusAdresse());
        // Nur mitschicken, wenn der Wert etwas taugt. In manchen Umgebungen
        // (ausgeblendetes Fenster, Vorschau) liefert der Browser 0 — dann ist
        // „—" in der Meldung ehrlicher als eine erfundene Bildschirmgröße.
        if (window.innerWidth > 0 && window.innerHeight > 0) {
            daten.append('viewport', window.innerWidth + '×' + window.innerHeight);
        }

        if (!daten.get('art') || !daten.get('titel').trim() || !daten.get('beschreibung').trim()) {
            status.textContent = texte.pflicht || 'Bitte fülle die drei Pflichtfelder aus.';
            status.className = 'fos-meldung-status fos-meldung-status--fehler';
            return;
        }

        knopf.disabled = true;
        status.textContent = texte.senden || 'Wird gesendet …';
        status.className = 'fos-meldung-status';

        fetch(fosMeldungDaten.ajaxUrl, { method: 'POST', body: daten, credentials: 'same-origin' })
            .then(function (antwort) { return antwort.json(); })
            .then(function (ergebnis) {
                if (ergebnis && ergebnis.success) {
                    formular.reset();
                    zeigeMarkierung(formular, '');
                    status.textContent = (ergebnis.data && ergebnis.data.message) || texte.danke;
                    status.className = 'fos-meldung-status fos-meldung-status--erfolg';
                    // Kurz stehen lassen, damit die Bestätigung gelesen wird.
                    window.setTimeout(schliesse, 2500);
                } else {
                    status.textContent = (ergebnis && ergebnis.data && ergebnis.data.message) || texte.fehler;
                    status.className = 'fos-meldung-status fos-meldung-status--fehler';
                }
            })
            .catch(function (fehler) {
                // Den echten Grund in der Konsole behalten — die Meldung an die
                // Leserin bleibt bewusst allgemein.
                console.error('Meldung konnte nicht gesendet werden:', fehler);
                status.textContent = texte.fehler || 'Das hat leider nicht geklappt.';
                status.className = 'fos-meldung-status fos-meldung-status--fehler';
            })
            .finally(function () {
                knopf.disabled = false;
            });
    }

    function start() {
        // Öffnen. Führt der Link auf eine echte Seite, bleibt er als Rückfall
        // erhalten — abgefangen wird nur, wenn das Fenster wirklich aufgeht.
        document.addEventListener('click', function (e) {
            var ausloeser = e.target.closest('[data-fos-meldung-oeffnen]');
            if (ausloeser && oeffne()) {
                e.preventDefault();
                return;
            }
            if (e.target.closest('[data-fos-meldung-schliessen]')) {
                e.preventDefault();
                schliesse();
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                schliesse();
            }
        });

        Array.prototype.forEach.call(document.querySelectorAll('.fos-meldung-formular'), function (formular) {
            hilfstextSetzen(formular);
            formular.addEventListener('submit', function (e) { absenden(formular, e); });
            var weg = formular.querySelector('.fos-meldung-auswahl-weg');
            if (weg) {
                weg.addEventListener('click', function () { zeigeMarkierung(formular, ''); });
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
