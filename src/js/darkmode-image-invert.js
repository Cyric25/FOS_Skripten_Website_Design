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
 * Kalibriert gegen echte Mediathek-Bilder in drei Verkleinerungsarten:
 * tools/test-darkmode-klassifizierung.js + tools/fixtures/darkmode/
 * (neu erzeugen: python -I tools/erzeuge-darkmode-fixtures.py).
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
    // Laengste Kante, auf die ein Bild vor der Analyse herunterskaliert wird
    // (muss mit ABTASTGROESSE in tools/erzeuge-darkmode-fixtures.py
    // uebereinstimmen - der Harnisch prueft das)
    var MAX_SAMPLE_DIMENSION = 512;
    // CSS-Klasse, die style.css im Darkmode invertiert
    var MARKER_CLASS = 'fos-darkmode-invert';

    function luminance( r, g, b ) {
        return 0.2126 * r + 0.7152 * g + 0.0722 * b;
    }

    /**
     * Reine Klassifizierung der abgetasteten Pixel (RGBA, nicht vormultipliziert,
     * wie getImageData sie liefert). Ohne DOM-Zugriff, damit sie im Node-Harnisch
     * tools/test-darkmode-klassifizierung.js gegen Fixtures geprueft werden kann.
     *
     * @return {{ergebnis: string, anteilTransparent: number, strichPixel: number, helligkeit: number}}
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
            helligkeit: alphaSum ? weightedLumSum / alphaSum : 0
        };

        if ( kennzahlen.anteilTransparent < MIN_TRANSPARENT_RATIO ) {
            kennzahlen.ergebnis = 'deckend'; // Bild hat einen (weitgehend) deckenden Hintergrund
        } else if ( ! totalPixels || strokeCount / totalPixels < MIN_STROKE_RATIO ) {
            kennzahlen.ergebnis = 'leer'; // (fast) vollstaendig transparent, nichts zu invertieren
        } else {
            kennzahlen.ergebnis = kennzahlen.helligkeit < DARK_LUMINANCE_THRESHOLD ? 'invertieren' : 'hell';
        }
        return kennzahlen;
    }

    window.fosDarkmodeKlassifiziere = klassifiziere;
    window.fosDarkmodeAbtastgroesse = MAX_SAMPLE_DIMENSION;

    function analyzeImage( img ) {
        try {
            var scale = Math.min( 1, MAX_SAMPLE_DIMENSION / Math.max( img.naturalWidth, img.naturalHeight ) );
            var w = Math.max( 1, Math.round( img.naturalWidth * scale ) );
            var h = Math.max( 1, Math.round( img.naturalHeight * scale ) );

            var canvas = document.createElement( 'canvas' );
            canvas.width = w;
            canvas.height = h;
            var ctx = canvas.getContext( '2d' );
            // Einheitlich geglaettet verkleinern; die Messung (E10) ist so
            // gebaut, dass sie mit jedem Verfahren dasselbe Ergebnis liefert.
            ctx.imageSmoothingEnabled = true;
            ctx.imageSmoothingQuality = 'high';
            ctx.drawImage( img, 0, 0, w, h );

            var data = ctx.getImageData( 0, 0, w, h ).data;

            return klassifiziere( data, w, h ).ergebnis === 'invertieren';
        } catch ( e ) {
            // z.B. SecurityError bei fremdgehosteten Bildern ohne CORS-Freigabe
            return false;
        }
    }

    function processImage( img ) {
        if ( img.dataset.fosDarkmodeChecked ) return;
        img.dataset.fosDarkmodeChecked = '1';
        if ( analyzeImage( img ) ) {
            img.classList.add( MARKER_CLASS );
        }
    }

    function scanImages() {
        var images = document.querySelectorAll( '.entry-content img' );
        Array.prototype.forEach.call( images, function ( img ) {
            if ( img.complete && img.naturalWidth ) {
                processImage( img );
            } else {
                img.addEventListener( 'load', function () {
                    processImage( img );
                }, { once: true } );
            }
        } );
    }

    function boot() {
        if ( 'requestIdleCallback' in window ) {
            requestIdleCallback( scanImages, { timeout: 2000 } );
        } else {
            setTimeout( scanImages, 200 );
        }
    }

    if ( document.readyState === 'complete' ) {
        boot();
    } else {
        window.addEventListener( 'load', boot );
    }

}());
