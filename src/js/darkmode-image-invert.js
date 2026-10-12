/**
 * Simple Clean Theme - Darkmode-Bildinvertierung
 *
 * Erkennt Bilder in .entry-content ohne (deckenden) Hintergrund - z.B.
 * selbstgezeichnete Strukturbilder mit transparentem Hintergrund und dunklen
 * Linien - und markiert sie mit der Klasse fos-darkmode-invert. Die
 * eigentliche Invertierung passiert per CSS (style.css,
 * :root[data-theme="dark"] img.fos-darkmode-invert), abhaengig vom jeweils
 * aktuellen Darkmode-Zustand.
 *
 * Siehe PLAN-Darkmode-Bildinvertierung.md, AP-1.1, und
 * PLAN-Rueckmeldungen-Blockverweis-iPad-Tafel-Darkmode-Formeln.md, Phase 3
 * (Meldung #7107).
 *
 * MESSUNG (seit #7107, Entscheidung E10 - bitte nicht auf "nur deckende
 * Pixel zaehlen" zurueckbauen):
 * Feine schwarze Linien grosser Formel-PNGs werden beim Verkleinern auf die
 * Abtastgroesse HALBTRANSPARENT. Die alte Messung zaehlte nur Pixel mit
 * Alpha >= 250 als Strich - bei flaechenmittelndem/bilinearem Verkleinern
 * (WebKit, Chrome 'high') blieben dann 0 Strichpixel uebrig und das Bild
 * wurde nie invertiert. Jetzt:
 *   - transparent   = Alpha < 16
 *   - Strich        = Alpha >= 32 (auch halbtransparente Kantenpixel)
 *   - Helligkeit    = alpha-gewichtet (Summe a*L / Summe a) ueber alle
 *                     Strichpixel - unabhaengig vom Verkleinerungsverfahren
 *   - freie Tinte   (AP-3.fix1) = Anteil dunkler, (nahezu) deckender Pixel,
 *                     die an transparenten bzw. durchscheinend hellen Grund
 *                     grenzen, aber NICHT an eine deckend helle Flaeche.
 * Entscheidung: deckend -> nie; Helligkeit < 110 -> invertieren; sonst
 * invertieren, wenn freie Tinte >= 1,6 Promille der Abtastflaeche.
 * WARUM DIE FREIE TINTE (Befund K1, Betreiber-Leitlinie 2026-10-12: "schwarze
 * Linien/Schrift auf transparentem Grund -> invertieren; Norm-Piktogramme mit
 * deckend heller Flaeche -> nicht"): Halbtransparente Pastell-Markerflaechen
 * oder grosse helle Farbflaechen heben die mittlere Helligkeit ueber jede
 * Schwelle, obwohl daneben schwarze Schrift auf transparentem Grund steht, die
 * im Darkmode unsichtbar wird. Die Helligkeit allein trennt das nicht von
 * GHS-Piktogrammen (schwarzes Symbol auf deckend weisser Raute) - die
 * Nachbarschaft der dunklen Pixel schon. Bitte nicht durch eine hoehere
 * Helligkeitsschwelle ersetzen: Lipid-Grafiken (wl 131-155) und
 * Malat-Aspartat-Shuttle (166) liegen ueber GHS03 (165).
 * Kalibriert gegen echte Mediathek-Bilder in drei Verkleinerungsarten:
 * tools/test-darkmode-klassifizierung.js + tools/fixtures/darkmode/
 * (neu erzeugen: python -I tools/erzeuge-darkmode-fixtures.py).
 *
 * DOM-ANBINDUNG (AP-3.2, E11):
 *   - geprueft werden img in .entry-content und im Blockverweis-Modal
 *     (.cbd-block-reference-modal), auch nachtraeglich eingefuegte
 *     (MutationObserver, gedrosselt ueber requestIdleCallback/setTimeout)
 *   - data-fos-darkmode-checked traegt den GRUND und wird erst NACH der
 *     Analyse gesetzt: invertiert|hell|deckend|leer|fehler|ohne-groesse
 *   - SVG nur mit viewBox (complete, aber naturalWidth 0): decode()
 *     abwarten, dann mit angezeigter Groesse analysieren; ist auch die 0,
 *     erneut bei load bzw. sobald das Bild eine Flaeche hat
 *     (IntersectionObserver). Lehnt decode() ab und das Bild steht mit 0x0
 *     im Layout, ist es defekt -> 'fehler' (AP-3.fix2: keine Endlosschleife)
 *   - Analyse in Leerlauf-Haeppchen; Messwerte in window.fosDarkmodeStatistik
 *
 * AUTOREN-KLASSEN (AP-3.4): .fos-darkmode-no-invert (Bild oder Vorfahre,
 * z. B. <figure> des Bild-Blocks) -> Grund 'ausgenommen', Klasse entfernt;
 * .fos-darkmode-invert an einem Vorfahren -> 'erzwungen', Klasse auch am
 * <img>. Keine Analyse in beiden Faellen. Doku fuer Autoren: CLAUDE.md.
 *
 * KEIN import/export: wird als klassisches Script eingebunden.
 */
(function () {
    'use strict';

    // Alpha-Wert (0-255): darunter gilt ein Pixel als transparent (Hintergrund)
    var TRANSPARENT_ALPHA_THRESHOLD = 16;
    // Alpha-Wert (0-255): ab hier zaehlt ein Pixel als Strich (geht in die
    // Helligkeit ein). Zwischen 16 und 31 liegen Pixel, die weder als
    // Hintergrund noch als Strich zaehlen (Rauschen am Linienrand).
    var STROKE_ALPHA_THRESHOLD = 32;
    // Anteil der abgetasteten Pixel, der mindestens transparent sein muss,
    // damit das Bild als "ohne Hintergrund" gilt
    var MIN_TRANSPARENT_RATIO = 0.05;
    // Mindestanteil an Strichpixeln; darunter gilt das Bild als leer
    var MIN_STROKE_RATIO = 0.0005;
    // Alpha-gewichtete mittlere Helligkeit (0-255) der Strichpixel, unterhalb
    // derer ein Bild als "dunkel" gilt
    var DARK_LUMINANCE_THRESHOLD = 110;
    // --- freie Tinte (AP-3.fix1) ---
    // relative Leuchtdichte (linear, 0-1) unterhalb derer ein Pixel "dunkle
    // Tinte" ist (~ sRGB-Grau 63; Kontrast zu #121212 < ~1,8). Linear statt
    // L(0-255), damit gesaettigtes Rot (GHS-Rahmen, Y 0,21) NICHT als dunkel zaehlt.
    var TINTE_LEUCHTDICHTE = 0.05;
    // ab dieser Leuchtdichte ist ein deckendes Pixel "helle Flaeche" (~ sRGB 160)
    var FLAECHE_LEUCHTDICHTE = 0.35;
    // Tinte muss nahezu deckend sein (weiche Schatten zaehlen nicht)
    var TINTE_MIN_ALPHA = 128;
    // ab hier gilt ein Pixel als deckend (darunter: durchscheinend, z. B. Textmarker)
    var DECKEND_ALPHA = 224;
    // Nachbarschaft in Abtastpixeln (Quadrat 2*r+1)
    var UMGEBUNG_RADIUS = 2;
    // Mindestanteil freier Tinte an der Abtastflaeche (1,6 Promille);
    // kalibriert: darf nicht max. 1,1 (Einstabmesskette), muss min. 2,2
    // (kompetitiver Inhibitor), siehe Plan AP-3.fix1
    var MIN_FREIE_TINTE = 0.0016;
    // Laengste Kante, auf die ein Bild vor der Analyse herunterskaliert wird
    // (muss mit ABTASTGROESSE in tools/erzeuge-darkmode-fixtures.py
    // uebereinstimmen - der Harnisch prueft das)
    var MAX_SAMPLE_DIMENSION = 512;
    // CSS-Klasse, die style.css im Darkmode invertiert
    var MARKER_CLASS = 'fos-darkmode-invert';

    function luminance( r, g, b ) {
        return 0.2126 * r + 0.7152 * g + 0.0722 * b;
    }

    // sRGB 0-255 -> linear 0-1 (einmalig als Tabelle)
    var LINEAR = ( function () {
        var t = new Float64Array( 256 );
        for ( var i = 0; i < 256; i++ ) {
            var c = i / 255;
            t[ i ] = c <= 0.04045 ? c / 12.92 : Math.pow( ( c + 0.055 ) / 1.055, 2.4 );
        }
        return t;
    }() );

    function leuchtdichte( r, g, b ) {
        return 0.2126 * LINEAR[ r ] + 0.7152 * LINEAR[ g ] + 0.0722 * LINEAR[ b ];
    }

    // Maske um r Pixel ausdehnen (Quadrat), separabel mit gleitender Summe: O(w*h)
    function dehne( maske, w, h, r ) {
        var n = w * h;
        var zeile = new Uint8Array( n );
        var aus = new Uint8Array( n );
        var x, y, c, b;
        for ( y = 0; y < h; y++ ) {
            c = 0;
            b = y * w;
            for ( x = -r; x < w; x++ ) {
                if ( x + r < w ) { c += maske[ b + x + r ]; }
                if ( x - r - 1 >= 0 ) { c -= maske[ b + x - r - 1 ]; }
                if ( x >= 0 ) { zeile[ b + x ] = c > 0 ? 1 : 0; }
            }
        }
        for ( x = 0; x < w; x++ ) {
            c = 0;
            for ( y = -r; y < h; y++ ) {
                if ( y + r < h ) { c += zeile[ ( y + r ) * w + x ]; }
                if ( y - r - 1 >= 0 ) { c -= zeile[ ( y - r - 1 ) * w + x ]; }
                if ( y >= 0 ) { aus[ y * w + x ] = c > 0 ? 1 : 0; }
            }
        }
        return aus;
    }

    /**
     * Anteil "freier Tinte" an der Flaeche (0-1): dunkle, (nahezu) deckende
     * Pixel mit transparentem/durchscheinend hellem Grund in der Naehe und
     * OHNE deckend helle Flaeche in der Naehe. Schwarze Schrift auf
     * transparentem Grund zaehlt, das schwarze Symbol eines GHS-Piktogramms
     * auf weisser Raute und Umrisslinien heller Flaechen nicht.
     */
    function freieTinte( data, w, h ) {
        var n = w * h;
        if ( ! n || data.length < n * 4 ) { return 0; }
        var grund = new Uint8Array( n );
        var flaeche = new Uint8Array( n );
        var tinte = new Uint8Array( n );
        var p, i, a, y;
        for ( p = 0, i = 0; p < n; p++, i += 4 ) {
            a = data[ i + 3 ];
            if ( a < TRANSPARENT_ALPHA_THRESHOLD ) { grund[ p ] = 1; continue; }
            y = leuchtdichte( data[ i ], data[ i + 1 ], data[ i + 2 ] );
            if ( a < DECKEND_ALPHA ) {
                if ( y >= TINTE_LEUCHTDICHTE ) { grund[ p ] = 1; } // durchscheinend hell (Textmarker)
            } else if ( y >= FLAECHE_LEUCHTDICHTE ) {
                flaeche[ p ] = 1;
            }
            if ( a >= TINTE_MIN_ALPHA && y < TINTE_LEUCHTDICHTE ) { tinte[ p ] = 1; }
        }
        var nahGrund = dehne( grund, w, h, UMGEBUNG_RADIUS );
        var nahFlaeche = dehne( flaeche, w, h, UMGEBUNG_RADIUS );
        var frei = 0;
        for ( p = 0; p < n; p++ ) {
            if ( tinte[ p ] && nahGrund[ p ] && ! nahFlaeche[ p ] ) { frei++; }
        }
        return frei / n;
    }

    /**
     * Reine Klassifizierung der abgetasteten Pixel (RGBA, nicht vormultipliziert,
     * wie getImageData sie liefert). Ohne DOM-Zugriff, damit sie im Node-Harnisch
     * tools/test-darkmode-klassifizierung.js gegen Fixtures geprueft werden kann.
     *
     * @return {{ergebnis: string, anteilTransparent: number, strichPixel: number, helligkeit: number, freieTinte: number}}
     *         ergebnis: 'invertieren' | 'hell' | 'deckend' | 'leer'
     */
    function klassifiziere( data, w, h ) {
        var totalPixels = w * h;
        var transparentCount = 0;
        var strokeCount = 0;
        var alphaSum = 0;
        var weightedLumSum = 0;

        for ( var i = 0; i < data.length; i += 4 ) {
            var alpha = data[ i + 3 ];
            if ( alpha < TRANSPARENT_ALPHA_THRESHOLD ) {
                transparentCount++;
            } else if ( alpha >= STROKE_ALPHA_THRESHOLD ) {
                strokeCount++;
                alphaSum += alpha;
                weightedLumSum += alpha * luminance( data[ i ], data[ i + 1 ], data[ i + 2 ] );
            }
        }

        var kennzahlen = {
            ergebnis: '',
            anteilTransparent: totalPixels ? transparentCount / totalPixels : 0,
            strichPixel: strokeCount,
            helligkeit: alphaSum ? weightedLumSum / alphaSum : 0,
            freieTinte: 0
        };

        if ( kennzahlen.anteilTransparent < MIN_TRANSPARENT_RATIO ) {
            kennzahlen.ergebnis = 'deckend'; // Bild hat einen (weitgehend) deckenden Hintergrund
        } else if ( ! totalPixels || strokeCount / totalPixels < MIN_STROKE_RATIO ) {
            kennzahlen.ergebnis = 'leer'; // (fast) vollstaendig transparent, nichts zu invertieren
        } else if ( kennzahlen.helligkeit < DARK_LUMINANCE_THRESHOLD ) {
            kennzahlen.ergebnis = 'invertieren'; // ueberwiegend dunkel
        } else {
            // hell im Mittel - aber steht dunkle Tinte frei auf transparentem Grund?
            kennzahlen.freieTinte = freieTinte( data, w, h );
            kennzahlen.ergebnis = kennzahlen.freieTinte >= MIN_FREIE_TINTE ? 'invertieren' : 'hell';
        }
        return kennzahlen;
    }

    window.fosDarkmodeKlassifiziere = klassifiziere;
    window.fosDarkmodeAbtastgroesse = MAX_SAMPLE_DIMENSION;


    // ------------------------------------------------------------------
    // Ab hier: Anbindung an das DOM (AP-3.2, E11)
    // ------------------------------------------------------------------

    // Wo Bilder geprueft werden: Seiteninhalt und das Blockverweis-Modal des
    // CDB-Designers (blocks/block-reference/view.js haengt
    // div.cbd-block-reference-modal direkt an <body>, also ausserhalb von
    // .entry-content).
    var BEREICHE = '.entry-content, .cbd-block-reference-modal';
    var BILD_SELEKTOR = '.entry-content img, .cbd-block-reference-modal img';

    // Diagnose-Merkmal data-fos-darkmode-checked: Wert = Grund, gesetzt erst
    // NACH der Analyse. Im Web-Inspector am Geraet ablesbar.
    //   invertiert | hell | deckend | leer | fehler | ohne-groesse
    //   (+ AP-3.4: erzwungen | ausgenommen)
    var GRUND = { invertieren: 'invertiert', hell: 'hell', deckend: 'deckend', leer: 'leer' };

    // Messwerte fuer Tests (Browser-Konsole). gesamtMs/maxMs = Zeit auf dem
    // Hauptthread (Zeichnen der verkleinerten Fassung, getImageData,
    // Klassifizierung); das Dekodieren/Verkleinern per createImageBitmap
    // laeuft ausserhalb und ist nicht enthalten.
    var statistik = { anzahl: 0, gesamtMs: 0, maxMs: 0, zuletztMs: 0 };
    window.fosDarkmodeStatistik = statistik;

    function jetzt() {
        return ( window.performance && performance.now ) ? performance.now() : Date.now();
    }

    /**
     * Liest die verkleinerte Fassung aus und klassifiziert sie.
     * quelle: das <img> selbst oder ein bereits verkleinertes ImageBitmap.
     * @return {string} Grund fuer das Diagnose-Merkmal
     */
    function leseUndKlassifiziere( quelle, w, h ) {
        var start = jetzt();
        try {
            var canvas = document.createElement( 'canvas' );
            canvas.width = w;
            canvas.height = h;
            // willReadFrequently: Canvas im Hauptspeicher statt auf der GPU -
            // getImageData() muss dann nichts von der Grafikkarte zuruecklesen
            // (gemessen in Chrome: Rueckleseweg war der groesste Kostenblock).
            var ctx = canvas.getContext( '2d', { willReadFrequently: true } );
            // Einheitlich geglaettet verkleinern; die Messung (E10) ist so
            // gebaut, dass sie mit jedem Verfahren dasselbe Ergebnis liefert.
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            // Immer mit Zielgroesse zeichnen - auch ein Bitmap, falls ein
            // Browser die resize-Optionen von createImageBitmap ignoriert.
            ctx.drawImage( quelle, 0, 0, w, h );

            var data = ctx.getImageData( 0, 0, w, h ).data;

            return GRUND[ klassifiziere( data, w, h ).ergebnis ] || 'fehler';
        } catch ( e ) {
            // z.B. SecurityError bei fremdgehosteten Bildern ohne CORS-Freigabe,
            // InvalidStateError bei defekten Bildern
            return 'fehler';
        } finally {
            var dauer = jetzt() - start;
            statistik.anzahl++;
            statistik.gesamtMs += dauer;
            if ( dauer > statistik.maxMs ) statistik.maxMs = dauer;
            statistik.zuletztMs = jetzt(); // ms seit Seitenstart: letzte Analyse
        }
    }

    /**
     * Verkleinert das Bild auf die Abtastgroesse und klassifiziert es; ruft
     * fertig(grund) genau einmal auf.
     *
     * Bevorzugt createImageBitmap mit resize-Optionen: Dekodieren und
     * Verkleinern grosser PNGs laufen dann NICHT auf dem Hauptthread (gemessen,
     * Chrome, 42 Bilder mit kaltem Cache: Hauptthread 41 ms statt ~470 ms beim
     * direkten drawImage). Rueckfall auf drawImage(img), wenn die Funktion fehlt
     * oder ablehnt (z. B. SVG ohne eigene Groesse).
     */
    function analysiere( img, breite, hoehe, fertig ) {
        var scale = Math.min( 1, MAX_SAMPLE_DIMENSION / Math.max( breite, hoehe ) );
        var w = Math.max( 1, Math.round( breite * scale ) );
        var h = Math.max( 1, Math.round( hoehe * scale ) );

        if ( typeof window.createImageBitmap === 'function' && img.naturalWidth ) {
            var bitmapVersuch;
            try {
                bitmapVersuch = window.createImageBitmap( img, {
                    resizeWidth: w,
                    resizeHeight: h,
                    resizeQuality: 'high'
                } );
            } catch ( e ) {
                bitmapVersuch = null;
            }
            if ( bitmapVersuch && typeof bitmapVersuch.then === 'function' ) {
                bitmapVersuch.then( function ( bitmap ) {
                    var grund = leseUndKlassifiziere( bitmap, w, h );
                    if ( bitmap.close ) bitmap.close();
                    // Gemessen (Chrome): Fuer SVG liefert createImageBitmap mit
                    // resize ein LEERES Bitmap. 'leer' wird deshalb nie aus dem
                    // Bitmap-Weg uebernommen, sondern direkt gegengeprueft.
                    if ( grund === 'leer' ) {
                        grund = leseUndKlassifiziere( img, w, h );
                    }
                    fertig( grund );
                }, function () {
                    fertig( leseUndKlassifiziere( img, w, h ) );
                } );
                return;
            }
        }
        fertig( leseUndKlassifiziere( img, w, h ) );
    }

    function setzeErgebnis( img, grund ) {
        img.dataset.fosDarkmodeChecked = grund;
        // 'erzwungen': Klasse auch am <img>, damit die Lightbox (main.js
        // prueft nur das ausloesende <img>) die Invertierung uebernimmt.
        if ( grund === 'invertiert' || grund === 'erzwungen' ) {
            img.classList.add( MARKER_CLASS );
        } else {
            img.classList.remove( MARKER_CLASS );
        }
    }

    // --- Bilder ohne messbare Groesse erneut versuchen ---------------------
    // Einfachste tragfaehige Loesung: ein gemeinsamer IntersectionObserver
    // (falls vorhanden) meldet, wenn ein bisher groessenloses Bild sichtbar
    // wird - dann hat es ein Layout und getBoundingClientRect() liefert Masse.
    // Zusaetzlich loest ein spaeteres 'load' die Pruefung erneut aus. Kein
    // Intervall, kein Vollscan.
    //
    // AP-3.fix2 (M1, #7107): Ein defektes Bild mit alt="" hat 0x0 und meldet
    // dem IntersectionObserver trotzdem isIntersecting = true (kantenbuendig
    // zaehlt). Frueher: unobserve -> pruefeBild -> wieder 'ohne-groesse' ->
    // observe ... einmal je Frame (Listener-Leck, Dauerlayout). Deshalb:
    //   - der Rueckruf prueft nur bei Flaeche > 0 erneut, sonst bleibt das
    //     Bild einfach beobachtet (KEIN unobserve/observe im Wechsel)
    //   - load-Listener und Beobachtung je Bild hoechstens einmal (Merker)
    //   - nach abgelehntem decode() ohne Groesse: 'fehler', keine Beobachtung
    var sichtbarBeobachter = null;

    function hatFlaeche( rect ) {
        return !! rect && rect.width > 0 && rect.height > 0;
    }

    // Wiederholung ueber die Warteschlange (Zeitbudget, MAX_PARALLEL) statt direkt
    function einreihenUndPlanen( img ) {
        einreihen( img );
        planeAbarbeitung( true );
    }

    // Hoechstens EIN load-Listener je Bild (fuer einen spaeteren src-Wechsel
    // bzw. nachgeladene Bilder). Ein bisheriges 'fehler' wird dann verworfen.
    function horcheAufLoad( img ) {
        if ( img._fosDarkmodeLoadHorcht ) return;
        img._fosDarkmodeLoadHorcht = true;
        img.addEventListener( 'load', function () {
            img._fosDarkmodeLoadHorcht = false;
            if ( img.dataset.fosDarkmodeChecked === 'fehler' ) {
                delete img.dataset.fosDarkmodeChecked;
            }
            einreihenUndPlanen( img );
        }, { once: true } );
    }

    function spaeterErneut( img ) {
        horcheAufLoad( img );
        if ( typeof window.IntersectionObserver === 'function' ) {
            if ( ! sichtbarBeobachter ) {
                sichtbarBeobachter = new IntersectionObserver( function ( eintraege ) {
                    eintraege.forEach( function ( eintrag ) {
                        // Ohne Flaeche (0x0, display:none) nichts tun und weiter
                        // beobachten: der Browser meldet erneut, sobald sich
                        // Schnitt oder Groesse aendern.
                        if ( eintrag.isIntersecting && hatFlaeche( eintrag.boundingClientRect ) ) {
                            sichtbarBeobachter.unobserve( eintrag.target );
                            eintrag.target._fosDarkmodeBeobachtet = false;
                            einreihenUndPlanen( eintrag.target );
                        }
                    } );
                } );
            }
            if ( ! img._fosDarkmodeBeobachtet ) {
                img._fosDarkmodeBeobachtet = true;
                sichtbarBeobachter.observe( img );
            }
        }
    }

    /**
     * @param {boolean} decodeAbgelehnt decode() ist fehlgeschlagen - dann ist
     *        ein 'leer' (nichts gezeichnet) in Wahrheit ein defektes Bild
     *        (z. B. src="" bei .cbd-number-icon). Die Analyse laeuft trotzdem,
     *        weil WebKit decode() fuer manche SVG ablehnt, die sich zeichnen lassen.
     */
    function analysiereMitGroesse( img, fertig, decodeAbgelehnt ) {
        var breite = img.naturalWidth;
        var hoehe = img.naturalHeight;
        if ( ! breite || ! hoehe ) {
            // SVG nur mit viewBox: keine natuerliche Groesse -> angezeigte Groesse
            var rect = img.getBoundingClientRect();
            breite = rect.width;
            hoehe = rect.height;
        }
        if ( ! breite || ! hoehe ) {
            img._fosDarkmodeLaeuft = false;
            if ( decodeAbgelehnt && img.getClientRects().length ) {
                // AP-3.fix2: decode() abgelehnt, keine Groesse, aber im Layout
                // (0x0-Box) -> defektes Bild, endgueltig. Kein erneutes Pruefen
                // per IntersectionObserver (Endlosschleife); hoechstens ein
                // load-Listener fuer einen spaeteren src-Wechsel.
                setzeErgebnis( img, 'fehler' );
                horcheAufLoad( img );
            } else {
                // SVG nur mit viewBox (oder nicht im Layout, z. B. display:none):
                // sobald das Bild sichtbar wird bzw. ein 'load' kommt, erneut.
                setzeErgebnis( img, 'ohne-groesse' );
                spaeterErneut( img );
            }
            fertig();
            return;
        }
        analysiere( img, breite, hoehe, function ( grund ) {
            img._fosDarkmodeLaeuft = false;
            if ( decodeAbgelehnt && grund === 'leer' ) grund = 'fehler';
            setzeErgebnis( img, grund );
            fertig();
        } );
    }

    function nichts() {}

    /**
     * Autoren-Klassen (AP-3.4, Notausgang fuer Fehleinordnungen der
     * Automatik). Im Bild-Block landet die "Zusaetzliche CSS-Klasse" am
     * <figure>, deshalb zaehlen Vorfahren mit:
     *   .fos-darkmode-no-invert (am Bild oder Vorfahren) -> 'ausgenommen'
     *   .fos-darkmode-invert    (am Bild oder Vorfahren) -> 'erzwungen'
     * "nein" gewinnt immer - genauso wie in style.css, wo die no-invert-Regel
     * bei gleicher Spezifitaet nach der invert-Regel steht.
     * Wird VOR jeder Analyse geprueft (pruefeBild laesst bereits gepruefte
     * Bilder aus, die vom Script selbst gesetzte Klasse wird hier also nie
     * fuer eine Autorenvorgabe gehalten).
     */
    function autorenVorgabe( img ) {
        if ( ! img.closest ) return '';
        if ( img.closest( '.fos-darkmode-no-invert' ) ) return 'ausgenommen';
        if ( img.closest( '.' + MARKER_CLASS ) ) return 'erzwungen';
        return '';
    }

    /**
     * Prueft ein Bild genau einmal (Ausnahme: 'ohne-groesse' darf erneut).
     * Das interne Merkmal _fosDarkmodeLaeuft verhindert Doppelpruefungen,
     * solange auf load/decode/createImageBitmap gewartet wird.
     * fertig() wird genau einmal aufgerufen, sobald dieser Aufruf nichts mehr
     * tut (auch wenn er nur einen load-Listener angemeldet hat).
     */
    function pruefeBild( img, fertig ) {
        fertig = fertig || nichts;
        var bisher = img.dataset.fosDarkmodeChecked;
        if ( ( bisher && bisher !== 'ohne-groesse' ) || img._fosDarkmodeLaeuft ) {
            fertig();
            return;
        }
        var vorgabe = autorenVorgabe( img );
        if ( vorgabe ) {
            setzeErgebnis( img, vorgabe );
            fertig();
            return;
        }
        img._fosDarkmodeLaeuft = true;

        if ( ! img.complete ) {
            img.addEventListener( 'load', function () {
                img._fosDarkmodeLaeuft = false;
                einreihenUndPlanen( img ); // ueber die Warteschlange (AP-3.fix2, G2)
            }, { once: true } );
            img.addEventListener( 'error', function () {
                img._fosDarkmodeLaeuft = false;
                setzeErgebnis( img, 'fehler' );
            }, { once: true } );
            fertig();
            return;
        }
        if ( ! img.naturalWidth && typeof img.decode === 'function' ) {
            // complete && naturalWidth === 0: SVG ohne Groesse (oder defekt).
            // Nach decode() steht ggf. doch eine Groesse bereit; bei Ablehnung
            // (defektes Bild) wird trotzdem gemessen, 'leer' dann als 'fehler'.
            img.decode().then( function () {
                analysiereMitGroesse( img, fertig, false );
            }, function () {
                analysiereMitGroesse( img, fertig, true );
            } );
            return;
        }
        analysiereMitGroesse( img, fertig );
    }

    // --- Warteschlange (R7) ------------------------------------------------
    // Hoechstens MAX_PARALLEL Bilder gleichzeitig in Arbeit, damit nicht
    // Dutzende grosse PNGs auf einmal dekodiert werden. Je Durchgang hoechstens
    // ZEITBUDGET_MS Hauptthread-Arbeit (wichtig fuer den synchronen Rueckfall
    // ohne createImageBitmap), danach Abgabe an den Browser.
    // Erster Start im Leerlauf (requestIdleCallback); Folgedurchgaenge per
    // setTimeout(0) - mit requestIdleCallback je Bild dauerte eine Seite mit
    // 42 Bildern waehrend des Ladens ueber 5 s (gemessen), weil jeder Aufruf
    // bis zu seinem Timeout auf Leerlauf wartete.
    var MAX_PARALLEL = 2;
    var ZEITBUDGET_MS = 8;
    var warteschlange = [];
    var geplant = false;
    var inArbeit = 0;

    function planeAbarbeitung( sofort ) {
        if ( geplant || inArbeit >= MAX_PARALLEL || ! warteschlange.length ) return;
        geplant = true;
        if ( ! sofort && 'requestIdleCallback' in window ) {
            requestIdleCallback( arbeiteAb, { timeout: 1000 } );
        } else {
            // WebKit (Safari/Edge iOS) kennt requestIdleCallback nicht
            setTimeout( arbeiteAb, sofort ? 0 : 16 );
        }
    }

    function einFertig() {
        inArbeit--;
        planeAbarbeitung( true );
    }

    function arbeiteAb() {
        geplant = false;
        var start = jetzt();
        while ( inArbeit < MAX_PARALLEL && warteschlange.length && ( jetzt() - start ) < ZEITBUDGET_MS ) {
            inArbeit++;
            pruefeBild( warteschlange.shift(), einFertig );
        }
        planeAbarbeitung( true );
    }

    function einreihen( img ) {
        var bisher = img.dataset.fosDarkmodeChecked;
        if ( bisher && bisher !== 'ohne-groesse' ) return;
        if ( warteschlange.indexOf( img ) === -1 ) warteschlange.push( img );
    }

    function scanImages() {
        Array.prototype.forEach.call( document.querySelectorAll( BILD_SELEKTOR ), einreihen );
        planeAbarbeitung();
    }

    // --- Nachtraeglich eingefuegte Bilder (Blockverweis-Modal, AJAX) -------
    function beobachteNeueBilder() {
        if ( typeof window.MutationObserver !== 'function' || ! document.body ) return;
        new MutationObserver( function ( mutationen ) {
            for ( var i = 0; i < mutationen.length; i++ ) {
                var knoten = mutationen[ i ].addedNodes;
                for ( var j = 0; j < knoten.length; j++ ) {
                    var k = knoten[ j ];
                    if ( k.nodeType !== 1 ) continue;
                    if ( k.tagName === 'IMG' ) {
                        if ( k.closest && k.closest( BEREICHE ) ) einreihen( k );
                    } else if ( k.getElementsByTagName ) {
                        var bilder = k.getElementsByTagName( 'img' );
                        for ( var b = 0; b < bilder.length; b++ ) {
                            if ( bilder[ b ].closest( BEREICHE ) ) einreihen( bilder[ b ] );
                        }
                    }
                }
            }
            // Gedrosselt: Abarbeitung erst im naechsten Leerlauf; mehrere
            // Mutationen in Folge landen in derselben Warteschlange.
            planeAbarbeitung();
        } ).observe( document.body, { childList: true, subtree: true } );
    }

    function boot() {
        scanImages();
        beobachteNeueBilder();
    }

    if ( document.readyState === 'complete' ) {
        boot();
    } else {
        window.addEventListener( 'load', boot );
    }

}());
