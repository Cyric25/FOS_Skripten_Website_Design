/**
 * Meldungen im Admin herauskopieren
 *
 * Erzeugt aus einer oder mehreren Meldungen einen Textblock, der sich direkt
 * als Arbeitsauftrag in eine Sitzung mit einem Coding-Agenten einfügen lässt.
 * Den Text baut PHP (simple_clean_meldung_als_text() in
 * includes/admin/meldungen-admin.php) — hier wird er nur in die Zwischenablage
 * gelegt. Eine zweite Textfassung im JavaScript wäre eine Fehlerquelle:
 * Änderungen müssten an zwei Stellen nachgezogen werden.
 *
 * @package FOS_Online_Schulbuch
 * @since 1.5.113
 */

(function () {
    'use strict';

    /**
     * In die Zwischenablage legen.
     *
     * navigator.clipboard gibt es nur in einem sicheren Kontext (https oder
     * localhost). Im Admin ist das praktisch immer gegeben — aber eben nicht
     * garantiert, etwa bei einer Installation, die über http läuft. Deshalb
     * der alte Weg als Rückfall.
     */
    function inZwischenablage(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function (erfuellen, ablehnen) {
            var feld = document.createElement('textarea');
            feld.value = text;
            feld.setAttribute('readonly', '');
            feld.style.position = 'fixed';
            feld.style.left = '-9999px';
            document.body.appendChild(feld);
            feld.select();
            try {
                document.execCommand('copy') ? erfuellen() : ablehnen();
            } catch (e) {
                ablehnen(e);
            } finally {
                document.body.removeChild(feld);
            }
        });
    }

    /** Kurze Rückmeldung am Knopf selbst — ohne Dialog, ohne Umweg. */
    function rueckmeldung(knopf, text) {
        var vorher = knopf.textContent;
        knopf.textContent = text;
        knopf.disabled = true;
        window.setTimeout(function () {
            knopf.textContent = vorher;
            knopf.disabled = false;
        }, 1600);
    }

    function kopiere(knopf, text) {
        if (!text) {
            rueckmeldung(knopf, 'Nichts ausgewählt');
            return;
        }
        inZwischenablage(text).then(
            function () { rueckmeldung(knopf, '✓ Kopiert'); },
            function (fehler) {
                console.error('Kopieren fehlgeschlagen:', fehler);
                rueckmeldung(knopf, 'Fehlgeschlagen');
            }
        );
    }

    /* ------------------------------------------- Status direkt in der Liste */

    /**
     * Erledigte Zeilen in der Ansicht „Alle" unter ein Trennband stellen.
     *
     * Der Server sortiert sie bereits ans Ende (posts_orderby in
     * meldungen-admin.php); hier kommt nur das Band dazu — und nach einem
     * Statuswechsel wird die Zeile in die richtige Gruppe verschoben, ohne
     * die Seite neu zu laden. Die Reihenfolge innerhalb der Gruppen bleibt.
     */
    function ordneErledigte(admin) {
        var liste = document.getElementById('the-list');
        if (!liste || !admin.gruppieren) {
            return;
        }
        var alterTrenner = liste.querySelector('tr.fos-meldung-trenner');
        if (alterTrenner) {
            liste.removeChild(alterTrenner);
        }
        var offen = [];
        var erledigt = [];
        Array.prototype.forEach.call(liste.querySelectorAll('tr[id^="post-"]'), function (zeile) {
            (zeile.classList.contains('status-' + admin.erledigt) ? erledigt : offen).push(zeile);
        });
        offen.forEach(function (zeile) { liste.appendChild(zeile); });
        if (!erledigt.length) {
            return;
        }
        var tabelle = liste.closest('table');
        var spalten = tabelle ? tabelle.querySelectorAll('thead tr > *').length : 1;
        var trenner = document.createElement('tr');
        trenner.className = 'fos-meldung-trenner';
        var zelle = document.createElement('td');
        zelle.colSpan = spalten;
        zelle.textContent = 'Erledigt (' + erledigt.length + ' auf dieser Seite)';
        trenner.appendChild(zelle);
        liste.appendChild(trenner);
        erledigt.forEach(function (zeile) { liste.appendChild(zeile); });
    }

    /** Zähler in der Filterzeile („Offen (3)") und die Blase am Menüpunkt nachführen. */
    function aktualisiereZaehler(admin, anzahl) {
        Object.keys(anzahl).forEach(function (zustand) {
            var zahl = document.querySelector('.subsubsub li.' + zustand + ' .count');
            if (zahl) {
                zahl.textContent = '(' + anzahl[zustand] + ')';
            }
        });
        var blase = document.querySelector('#adminmenu a[href$="post_type=fos_meldung"] .awaiting-mod');
        if (blase) {
            var offen = anzahl[admin.neu] || 0;
            var inhalt = blase.querySelector('.pending-count');
            if (inhalt) {
                inhalt.textContent = offen;
            }
            blase.style.display = offen > 0 ? '' : 'none';
        }
    }

    function setzeKnoepfe(gruppe, zustand) {
        Array.prototype.forEach.call(gruppe.querySelectorAll('.fos-meldung-status__knopf'), function (knopf) {
            var aktiv = knopf.dataset.zustand === zustand;
            knopf.classList.toggle('is-aktiv', aktiv);
            knopf.setAttribute('aria-pressed', aktiv ? 'true' : 'false');
        });
    }

    function setzeZeilenstatus(zeile, zustand) {
        Array.prototype.slice.call(zeile.classList).forEach(function (klasse) {
            if (klasse.indexOf('status-') === 0) {
                zeile.classList.remove(klasse);
            }
        });
        zeile.classList.add('status-' + zustand);
    }

    function zeigeFehler(gruppe, text) {
        var hinweis = gruppe.querySelector('.fos-meldung-status__fehler');
        if (!hinweis) {
            hinweis = document.createElement('span');
            hinweis.className = 'fos-meldung-status__fehler';
            gruppe.appendChild(hinweis);
        }
        hinweis.textContent = text;
    }

    function wechsleStatus(admin, texte, knopf) {
        var gruppe = knopf.closest('.fos-meldung-status');
        var zeile = knopf.closest('tr');
        var zustand = knopf.dataset.zustand;
        if (!gruppe || !zeile || knopf.classList.contains('is-aktiv')) {
            return;
        }
        var daten = new FormData();
        daten.append('action', 'fos_meldung_zustand');
        daten.append('nonce', admin.nonce);
        daten.append('id', gruppe.dataset.id);
        daten.append('zustand', zustand);

        gruppe.classList.add('is-beschaeftigt');
        fetch(admin.ajaxUrl, { method: 'POST', body: daten, credentials: 'same-origin' })
            .then(function (antwort) { return antwort.json(); })
            .then(function (ergebnis) {
                if (!ergebnis || !ergebnis.success) {
                    throw new Error(ergebnis && ergebnis.data && ergebnis.data.message ? ergebnis.data.message : 'unbekannt');
                }
                var fehler = gruppe.querySelector('.fos-meldung-status__fehler');
                if (fehler) {
                    gruppe.removeChild(fehler);
                }
                setzeKnoepfe(gruppe, ergebnis.data.zustand);
                setzeZeilenstatus(zeile, ergebnis.data.zustand);
                aktualisiereZaehler(admin, ergebnis.data.anzahl || {});
                if (ergebnis.data.text) {
                    texte[gruppe.dataset.id] = ergebnis.data.text;
                }
                ordneErledigte(admin);
            })
            .catch(function (fehler) {
                console.error('Status ändern fehlgeschlagen:', fehler);
                zeigeFehler(gruppe, 'Nicht gespeichert – bitte Seite neu laden.');
            })
            .then(function () {
                gruppe.classList.remove('is-beschaeftigt');
            });
    }

    /**
     * Konfiguration aus dem Markup lesen (data-Attribute an der ersten
     * Knopfgruppe, gesetzt in simple_clean_meldung_spalte_inhalt()). Bewusst
     * nicht per wp_localize_script(): Ein Inline-Skript blockiert eine strenge
     * Content-Security-Policy. Keine Gruppe → keine Liste → nichts zu tun.
     */
    function leseKonfiguration() {
        var gruppe = document.querySelector('.fos-meldung-status[data-nonce]');
        if (!gruppe) {
            return null;
        }
        return {
            ajaxUrl: gruppe.dataset.ajax,
            nonce: gruppe.dataset.nonce,
            erledigt: gruppe.dataset.erledigt,
            neu: gruppe.dataset.offen,
            gruppieren: gruppe.dataset.gruppieren === '1'
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Die Kopiertexte kommen weiterhin per wp_localize_script (Inline-
        // Skript). Unter strenger CSP fehlen sie — dann bleibt nur das
        // Kopieren leer, die Statusknöpfe funktionieren trotzdem.
        var texte = window.fosMeldungTexte || {};
        var admin = leseKonfiguration();

        if (admin) {
            ordneErledigte(admin);
            document.addEventListener('click', function (e) {
                var knopf = e.target.closest('.fos-meldung-status__knopf');
                if (!knopf) {
                    return;
                }
                e.preventDefault();
                wechsleStatus(admin, texte, knopf);
            });
        }

        // Einzelansicht: Knopf über dem Textfeld
        var einzeln = document.getElementById('fos-meldung-kopieren-einzeln');
        if (einzeln) {
            einzeln.addEventListener('click', function () {
                var feld = document.getElementById('fos-meldung-kopiertext');
                kopiere(einzeln, feld ? feld.value : '');
            });
        }

        // Liste: eine Zeile
        document.addEventListener('click', function (e) {
            var zeile = e.target.closest('.fos-meldung-kopieren');
            if (!zeile) {
                return;
            }
            e.preventDefault();
            kopiere(zeile, texte[zeile.dataset.id] || '');
        });

        // Liste: alle angehakten Zeilen auf einmal — so lässt sich ein ganzer
        // Schwung Meldungen in einem Rutsch abarbeiten.
        var mehrere = document.getElementById('fos-meldung-kopieren-auswahl');
        if (mehrere) {
            mehrere.addEventListener('click', function () {
                var gewaehlt = document.querySelectorAll('#the-list input[name="post[]"]:checked');
                var block = [];
                Array.prototype.forEach.call(gewaehlt, function (kaestchen) {
                    if (texte[kaestchen.value]) {
                        block.push(texte[kaestchen.value]);
                    }
                });
                kopiere(mehrere, block.join('\n\n---\n\n'));
            });
        }
    });
})();
