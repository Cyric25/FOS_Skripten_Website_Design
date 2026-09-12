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

    document.addEventListener('DOMContentLoaded', function () {
        var texte = window.fosMeldungTexte || {};

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
