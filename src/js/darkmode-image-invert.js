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
 * Siehe PLAN-Darkmode-Bildinvertierung.md, AP-1.1.
 */
(function () {
    'use strict';

    // Alpha-Wert (0-255): darunter gilt ein Pixel als "nennenswert transparent"
    var TRANSPARENT_ALPHA_THRESHOLD = 250;
    // Anteil der abgetasteten Pixel, der mindestens transparent sein muss,
    // damit das Bild als "ohne Hintergrund" gilt
    var MIN_TRANSPARENT_RATIO = 0.05;
    // Mittlere Helligkeit (0-255) der undurchsichtigen Pixel, unterhalb derer
    // ein Bild als "dunkel" gilt
    var DARK_LUMINANCE_THRESHOLD = 100;
    // Laengste Kante, auf die ein Bild vor der Analyse herunterskaliert wird
    var MAX_SAMPLE_DIMENSION = 200;
    // CSS-Klasse, die style.css im Darkmode invertiert
    var MARKER_CLASS = 'fos-darkmode-invert';

    function luminance( r, g, b ) {
        return 0.2126 * r + 0.7152 * g + 0.0722 * b;
    }

    function analyzeImage( img ) {
        try {
            var scale = Math.min( 1, MAX_SAMPLE_DIMENSION / Math.max( img.naturalWidth, img.naturalHeight ) );
            var w = Math.max( 1, Math.round( img.naturalWidth * scale ) );
            var h = Math.max( 1, Math.round( img.naturalHeight * scale ) );

            var canvas = document.createElement( 'canvas' );
            canvas.width = w;
            canvas.height = h;
            var ctx = canvas.getContext( '2d' );
            ctx.drawImage( img, 0, 0, w, h );

            var data = ctx.getImageData( 0, 0, w, h ).data;

            var totalPixels = w * h;
            var transparentCount = 0;
            var opaqueLumSum = 0;
            var opaqueCount = 0;

            for ( var i = 0; i < data.length; i += 4 ) {
                var alpha = data[ i + 3 ];
                if ( alpha < TRANSPARENT_ALPHA_THRESHOLD ) {
                    transparentCount++;
                } else {
                    opaqueLumSum += luminance( data[ i ], data[ i + 1 ], data[ i + 2 ] );
                    opaqueCount++;
                }
            }

            if ( transparentCount / totalPixels < MIN_TRANSPARENT_RATIO ) {
                return false; // Bild hat einen (weitgehend) deckenden Hintergrund
            }
            if ( opaqueCount === 0 ) {
                return false; // vollstaendig transparent, nichts zu invertieren
            }

            return ( opaqueLumSum / opaqueCount ) < DARK_LUMINANCE_THRESHOLD;
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
