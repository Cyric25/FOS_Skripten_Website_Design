<?php
/**
 * Clipboard Uploader – Bild aus Zwischenablage in Mediathek hochladen
 *
 * Registriert "Medien → Aus Zwischenablage" im WordPress-Admin.
 * Unterstützt Upload als PNG (Rasterbild) oder SVG (Vektorgrafik via ImageTracer.js).
 *
 * @package FOS_Online_Schulbuch
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Simple_Clean_Clipboard_Uploader {

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'add_admin_menu' ] );
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_scripts' ] );
    }

    public static function add_admin_menu() {
        add_submenu_page(
            'upload.php',
            'Aus Zwischenablage hochladen',
            'Aus Zwischenablage',
            'upload_files',
            'clipboard-media-upload',
            [ __CLASS__, 'render_page' ]
        );
    }

    public static function enqueue_scripts( $hook ) {
        if ( strpos( $hook, 'clipboard-media-upload' ) === false ) return;

        // ImageTracer.js must load in <head> (false) so it is available
        // when the inline script in render_page() executes.
        if ( defined( 'MODULAR_BLOCKS_PLUGIN_PATH' ) &&
             file_exists( MODULAR_BLOCKS_PLUGIN_PATH . 'assets/js/vendor/imagetracer.js' ) ) {
            wp_enqueue_script( 'imagetracer', MODULAR_BLOCKS_PLUGIN_URL . 'assets/js/vendor/imagetracer.js', [], '1.2.6', false );
        } else {
            wp_enqueue_script( 'imagetracer', 'https://cdn.jsdelivr.net/npm/imagetracerjs@1.2.6/imagetracer_v1.2.6.js', [], '1.2.6', false );
        }
    }

    public static function render_page() {
        if ( ! current_user_can( 'upload_files' ) ) wp_die( 'Zugriff verweigert.' );

        $nonce    = wp_create_nonce( 'wp_rest' );
        $api_root = esc_url( rest_url() );
        ?>
<div class="wrap cu-wrap">
<h1 class="wp-heading-inline">Bild aus Zwischenablage in Mediathek hochladen</h1>
<p class="cu-subtitle">Kopiere eine Zeichnung in OneNote (<kbd>Strg+C</kbd>), öffne dieses Fenster und drücke <kbd>Strg+V</kbd> – oder ziehe eine Bilddatei in das Feld.</p>

<div id="cu-paste-zone" tabindex="0">
    <div class="cu-paste-icon">⎘</div>
    <p class="cu-paste-main">Strg+V drücken oder Bild hineinziehen</p>
    <p class="cu-paste-sub">PNG, JPG, GIF, BMP, WebP werden akzeptiert</p>
</div>

<div id="cu-editor" style="display:none">
    <div class="cu-preview-wrap">
        <canvas id="cu-canvas"></canvas>
        <p id="cu-dimensions" class="cu-meta"></p>
    </div>

    <div class="cu-controls">
        <div class="cu-field">
            <label for="cu-filename"><strong>Dateiname</strong></label>
            <div class="cu-filename-row">
                <input type="text" id="cu-filename" value="zeichnung" class="regular-text" />
                <span id="cu-ext-badge" class="cu-ext">.svg</span>
            </div>
        </div>

        <div class="cu-field">
            <strong>Format</strong>
            <div class="cu-format-grid">
                <label class="cu-format-card" data-val="png">
                    <input type="radio" name="cu_format" value="png" hidden>
                    <span class="cu-format-icon">🖼</span>
                    <span class="cu-format-name">PNG</span>
                    <span class="cu-format-desc">Rasterbild<br>Verlustfrei</span>
                </label>
                <label class="cu-format-card cu-active" data-val="svg-embed">
                    <input type="radio" name="cu_format" value="svg-embed" checked hidden>
                    <span class="cu-format-icon">✦</span>
                    <span class="cu-format-name">SVG eingebettet</span>
                    <span class="cu-format-desc">Identische Qualität<br>Skalierbar ✓</span>
                </label>
                <label class="cu-format-card" data-val="svg-trace">
                    <input type="radio" name="cu_format" value="svg-trace" hidden>
                    <span class="cu-format-icon">✐</span>
                    <span class="cu-format-name">SVG vektorisiert</span>
                    <span class="cu-format-desc">Echte Pfade<br>Für einfache Linien</span>
                </label>
            </div>
            <p class="description" style="margin-top:6px">
                <strong>SVG eingebettet</strong> empfohlen für OneNote-Zeichnungen: originalgetreue Qualität, vollständig skalierbar.<br>
                <strong>SVG vektorisiert</strong> nur für sehr einfache einfarbige Strichzeichnungen.
            </p>
        </div>

        <div id="cu-svg-opts" style="display:none">
            <div class="cu-field">
                <label for="cu-preset"><strong>Vektorisierungs-Modus</strong></label>
                <select id="cu-preset" class="regular-text">
                    <option value="mono">Strichzeichnung – Tinte auf weiß</option>
                    <option value="auto">Automatisch (8 Farben)</option>
                    <option value="color">Farbig (16 Farben)</option>
                </select>
            </div>
            <button type="button" id="cu-preview-svg" class="button">SVG Vorschau</button>
            <div id="cu-svg-preview-wrap" style="display:none">
                <p class="cu-meta">SVG-Vorschau:</p>
                <div id="cu-svg-preview" class="cu-svg-preview-box"></div>
            </div>
        </div>

        <div class="cu-actions">
            <button type="button" id="cu-upload-btn" class="button button-primary button-large">
                ↑ In Mediathek hochladen
            </button>
            <button type="button" id="cu-reset-btn" class="button">Anderes Bild wählen</button>
        </div>

        <div id="cu-progress" style="display:none">
            <span class="spinner is-active"></span> Wird hochgeladen…
        </div>

        <div id="cu-error" style="display:none" class="notice notice-error"><p></p></div>
    </div>
</div>

<div id="cu-success" style="display:none" class="cu-success-box">
    <div class="cu-success-icon">✓</div>
    <h2>Erfolgreich hochgeladen!</h2>
    <p id="cu-success-name" class="cu-meta"></p>
    <img id="cu-success-thumb" src="" alt="" style="display:none" class="cu-success-thumb" />
    <div class="cu-success-actions">
        <a id="cu-media-link" href="#" target="_blank" class="button button-primary">In Mediathek ansehen</a>
        <button type="button" id="cu-upload-another" class="button">Weiteres Bild hochladen</button>
    </div>
</div>

</div>

<style>
.cu-wrap { max-width: 820px; }
.cu-subtitle { color: #666; margin: 4px 0 20px; }

kbd {
    display: inline-block;
    padding: 1px 6px;
    font-family: monospace;
    font-size: 12px;
    background: #f0f0f0;
    border: 1px solid #ccc;
    border-radius: 3px;
}

#cu-paste-zone {
    border: 3px dashed #c3c4c7;
    border-radius: 8px;
    padding: 60px 40px;
    text-align: center;
    cursor: pointer;
    transition: border-color .15s, background .15s;
    background: #fafafa;
    outline: none;
}
#cu-paste-zone:hover,
#cu-paste-zone.cu-drag-over {
    border-color: #0073aa;
    background: #f0f6fc;
}
#cu-paste-zone:focus { border-color: #0073aa; box-shadow: 0 0 0 2px #c2daf0; }
.cu-paste-icon { font-size: 48px; margin-bottom: 12px; opacity: .5; }
.cu-paste-main { font-size: 18px; font-weight: 600; color: #1d2327; margin: 0 0 6px; }
.cu-paste-sub  { color: #646970; margin: 0; }

#cu-editor { display: flex; gap: 32px; align-items: flex-start; margin-top: 24px; }

.cu-preview-wrap { flex: 0 0 auto; }
#cu-canvas {
    display: block;
    max-width: 340px;
    max-height: 340px;
    width: auto;
    height: auto;
    border: 1px solid #ddd;
    border-radius: 4px;
    background: repeating-conic-gradient(#e0e0e0 0% 25%, #f5f5f5 0% 50%) 0 0 / 16px 16px;
}
.cu-meta { font-size: 12px; color: #888; margin: 6px 0 0; }

.cu-controls { flex: 1; min-width: 0; }
.cu-field { margin-bottom: 20px; }
.cu-field label, .cu-field strong { display: block; margin-bottom: 6px; font-weight: 600; }

.cu-filename-row { display: flex; align-items: center; gap: 4px; }
.cu-ext {
    display: inline-block;
    padding: 3px 8px;
    background: #e0e0e0;
    border-radius: 3px;
    font-family: monospace;
    font-size: 12px;
    white-space: nowrap;
}

.cu-format-grid { display: flex; gap: 12px; }
.cu-format-card {
    flex: 1;
    border: 2px solid #ddd;
    border-radius: 6px;
    padding: 14px 12px;
    cursor: pointer;
    text-align: center;
    transition: border-color .15s, background .15s;
    user-select: none;
}
.cu-format-card:hover  { border-color: #0073aa; background: #f0f6fc; }
.cu-format-card.cu-active { border-color: #0073aa; background: #f0f6fc; }
.cu-format-icon { display: block; font-size: 24px; margin-bottom: 6px; }
.cu-format-name { display: block; font-weight: 600; font-size: 14px; }
.cu-format-desc { display: block; font-size: 11px; color: #888; margin-top: 3px; line-height: 1.4; }

.cu-svg-preview-box {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 8px;
    margin-top: 8px;
    background: #fff;
    max-height: 300px;
    overflow: auto;
}
.cu-svg-preview-box svg { max-width: 100%; height: auto; display: block; }

.cu-actions { display: flex; gap: 10px; align-items: center; margin-top: 24px; }
#cu-progress { display: flex; align-items: center; gap: 8px; margin-top: 12px; color: #666; }
#cu-error { margin-top: 12px; }

.cu-success-box {
    text-align: center;
    padding: 48px 40px;
    border: 1px solid #c3e6cb;
    border-radius: 8px;
    background: #f0fff4;
    margin-top: 24px;
}
.cu-success-icon { font-size: 56px; color: #46b450; margin-bottom: 12px; }
.cu-success-box h2 { margin: 0 0 8px; }
.cu-success-thumb {
    display: block;
    max-width: 240px;
    max-height: 240px;
    margin: 16px auto;
    border: 1px solid #ddd;
    border-radius: 4px;
}
.cu-success-actions { display: flex; gap: 12px; justify-content: center; margin-top: 20px; }
</style>

<script>
(function () {
    'use strict';

    const cfg = {
        nonce:   '<?php echo esc_js( $nonce ); ?>',
        apiRoot: '<?php echo esc_js( $api_root ); ?>'
    };

    let sourceCanvas = null;

    // ---- ImageTracer Presets ----------------------------------------
    const PRESETS = {
        mono: {
            // Best for OneNote ink on white background
            colorsampling:  0,
            numberofcolors: 2,
            pal: [ { r:255, g:255, b:255, a:255 }, { r:0, g:0, b:0, a:255 } ],
            pathomit:       6,
            blurradius:     1,
            ltres:          1,
            qtres:          1,
        },
        auto: {
            numberofcolors: 8,
            pathomit:       4,
            blurradius:     0,
        },
        color: {
            numberofcolors: 16,
            pathomit:       2,
            blurradius:     1,
        },
    };

    // ---- UI elements -------------------------------------------------
    const pasteZone   = document.getElementById( 'cu-paste-zone' );
    const editorBox   = document.getElementById( 'cu-editor' );
    const successBox  = document.getElementById( 'cu-success' );
    const canvas      = document.getElementById( 'cu-canvas' );
    const dimLabel    = document.getElementById( 'cu-dimensions' );
    const filenameIn  = document.getElementById( 'cu-filename' );
    const extBadge    = document.getElementById( 'cu-ext-badge' );
    extBadge.textContent = '.svg'; // default is svg-embed
    const svgOpts     = document.getElementById( 'cu-svg-opts' );
    const presetSel   = document.getElementById( 'cu-preset' );
    const previewBtn  = document.getElementById( 'cu-preview-svg' );
    const svgPrevWrap = document.getElementById( 'cu-svg-preview-wrap' );
    const svgPrevBox  = document.getElementById( 'cu-svg-preview' );
    const uploadBtn   = document.getElementById( 'cu-upload-btn' );
    const resetBtn    = document.getElementById( 'cu-reset-btn' );
    const progressEl  = document.getElementById( 'cu-progress' );
    const errorEl     = document.getElementById( 'cu-error' );
    const formatCards = document.querySelectorAll( '.cu-format-card' );
    const formatRadios = document.querySelectorAll( 'input[name="cu_format"]' );

    sourceCanvas = canvas;

    // ---- Paste zone focus & click -----------------------------------
    pasteZone.addEventListener( 'click', () => pasteZone.focus() );

    // ---- Format selection -------------------------------------------
    formatCards.forEach( card => {
        card.addEventListener( 'click', () => {
            const val = card.dataset.val;
            formatCards.forEach( c => c.classList.remove( 'cu-active' ) );
            card.classList.add( 'cu-active' );
            card.querySelector( 'input' ).checked = true;
            extBadge.textContent = val === 'png' ? '.png' : '.svg';
            svgOpts.style.display = val === 'svg-trace' ? 'block' : 'none';
            svgPrevWrap.style.display = 'none';
        } );
    } );

    // ---- Load image from Blob/File ----------------------------------
    function loadImageFromBlob( blob ) {
        const reader = new FileReader();
        reader.onload = ev => {
            const img = new Image();
            img.onload = () => {
                // Scale to max 1400px to keep tracing fast
                let w = img.width, h = img.height;
                if ( w > 1400 ) { h = Math.round( h * 1400 / w ); w = 1400; }
                if ( h > 1400 ) { w = Math.round( w * 1400 / h ); h = 1400; }

                canvas.width  = w;
                canvas.height = h;
                canvas.getContext( '2d' ).drawImage( img, 0, 0, w, h );

                dimLabel.textContent = img.naturalWidth + ' × ' + img.naturalHeight +
                    ' px (dargestellt: ' + w + ' × ' + h + ')';

                pasteZone.style.display   = 'none';
                editorBox.style.display   = 'flex';
                successBox.style.display  = 'none';
                svgPrevWrap.style.display = 'none';
                errorEl.style.display     = 'none';
            };
            img.src = ev.target.result;
        };
        reader.readAsDataURL( blob );
    }

    // ---- Paste (Ctrl+V) --------------------------------------------
    document.addEventListener( 'paste', e => {
        const items = Array.from( e.clipboardData?.items || [] );
        for ( const item of items ) {
            if ( item.type.startsWith( 'image/' ) ) {
                e.preventDefault();
                loadImageFromBlob( item.getAsFile() );
                return;
            }
        }
    } );

    // ---- Drag & Drop ------------------------------------------------
    pasteZone.addEventListener( 'dragover', e => {
        e.preventDefault();
        pasteZone.classList.add( 'cu-drag-over' );
    } );
    pasteZone.addEventListener( 'dragleave', () => pasteZone.classList.remove( 'cu-drag-over' ) );
    pasteZone.addEventListener( 'drop', e => {
        e.preventDefault();
        pasteZone.classList.remove( 'cu-drag-over' );
        const file = e.dataTransfer.files[ 0 ];
        if ( file && file.type.startsWith( 'image/' ) ) loadImageFromBlob( file );
    } );

    // Also accept drop on whole page
    document.addEventListener( 'dragover', e => e.preventDefault() );
    document.addEventListener( 'drop', e => {
        e.preventDefault();
        const file = e.dataTransfer.files[ 0 ];
        if ( file && file.type.startsWith( 'image/' ) ) loadImageFromBlob( file );
    } );

    // ---- SVG Live Preview -------------------------------------------
    previewBtn.addEventListener( 'click', () => {
        if ( ! canvas.width ) return;
        previewBtn.disabled  = true;
        previewBtn.textContent = 'Berechne…';
        setTimeout( () => {
            try {
                const preset  = PRESETS[ presetSel.value ] || PRESETS.auto;
                const imgData = canvas.getContext( '2d' ).getImageData( 0, 0, canvas.width, canvas.height );
                const svgStr  = ImageTracer.imagedataToSVG( imgData, preset );
                svgPrevBox.innerHTML  = svgStr;
                svgPrevWrap.style.display = 'block';
            } catch ( err ) {
                showError( 'SVG-Vorschau fehlgeschlagen: ' + err.message );
            } finally {
                previewBtn.disabled    = false;
                previewBtn.textContent = 'SVG Vorschau';
            }
        }, 30 );
    } );

    // ---- Reset -------------------------------------------------------
    resetBtn.addEventListener( 'click', reset );
    document.getElementById( 'cu-upload-another' ).addEventListener( 'click', reset );

    function reset() {
        pasteZone.style.display  = 'block';
        editorBox.style.display  = 'none';
        successBox.style.display = 'none';
        errorEl.style.display    = 'none';
        svgPrevWrap.style.display = 'none';
        canvas.getContext( '2d' ).clearRect( 0, 0, canvas.width, canvas.height );
        pasteZone.focus();
    }

    // ---- Upload -----------------------------------------------------
    uploadBtn.addEventListener( 'click', async () => {
        const format   = document.querySelector( 'input[name="cu_format"]:checked' ).value;
        const filename = ( filenameIn.value.trim() || 'zeichnung' )
                            .replace( /[^a-z0-9_\-äöüÄÖÜß]/gi, '_' );

        errorEl.style.display    = 'none';
        progressEl.style.display = 'flex';
        uploadBtn.disabled       = true;

        try {
            let blob, mimeType, ext;

            if ( format === 'png' ) {
                blob     = await new Promise( r => canvas.toBlob( r, 'image/png' ) );
                mimeType = 'image/png';
                ext      = 'png';
            } else if ( format === 'svg-embed' ) {
                // Embed PNG as <image> inside SVG – identical quality, fully scalable
                const dataUrl = canvas.toDataURL( 'image/png' );
                const w = canvas.width, h = canvas.height;
                const svgStr = '<svg xmlns="http://www.w3.org/2000/svg" ' +
                    'xmlns:xlink="http://www.w3.org/1999/xlink" ' +
                    'viewBox="0 0 ' + w + ' ' + h + '" ' +
                    'width="' + w + '" height="' + h + '">' +
                    '<image href="' + dataUrl + '" width="' + w + '" height="' + h + '" ' +
                    'preserveAspectRatio="xMidYMid meet"/>' +
                    '</svg>';
                blob     = new Blob( [ svgStr ], { type: 'image/svg+xml' } );
                mimeType = 'image/svg+xml';
                ext      = 'svg';
            } else {
                // svg-trace: raster-to-vector via ImageTracer.js
                const preset  = PRESETS[ presetSel.value ] || PRESETS.auto;
                const imgData = canvas.getContext( '2d' ).getImageData( 0, 0, canvas.width, canvas.height );
                const svgStr  = ImageTracer.imagedataToSVG( imgData, preset );
                blob     = new Blob( [ svgStr ], { type: 'image/svg+xml' } );
                mimeType = 'image/svg+xml';
                ext      = 'svg';
            }

            const result = await uploadToMedia( blob, filename + '.' + ext, mimeType );
            showSuccess( result, format );

        } catch ( err ) {
            showError( err.message || 'Unbekannter Fehler' );
        } finally {
            progressEl.style.display = 'none';
            uploadBtn.disabled       = false;
        }
    } );

    // ---- WordPress REST API upload ----------------------------------
    async function uploadToMedia( blob, filename, mimeType ) {
        const resp = await fetch( cfg.apiRoot + 'wp/v2/media', {
            method: 'POST',
            headers: {
                'Content-Disposition': 'attachment; filename="' + encodeURIComponent( filename ) + '"',
                'Content-Type': mimeType,
                'X-WP-Nonce': cfg.nonce,
            },
            body: blob,
        } );

        if ( ! resp.ok ) {
            const body = await resp.json().catch( () => ({}) );
            throw new Error( body.message || 'HTTP ' + resp.status + ' – ' + resp.statusText );
        }
        return resp.json();
    }

    // ---- Success / Error states ------------------------------------
    function showSuccess( att, format ) {
        editorBox.style.display  = 'none';
        successBox.style.display = 'block';

        const name = att.slug ? att.slug + ( format === 'svg' ? '.svg' : '.png' ) : att.title?.rendered || '';
        document.getElementById( 'cu-success-name' ).textContent = name;

        const link = document.getElementById( 'cu-media-link' );
        link.href = att.link || ( '<?php echo esc_url( admin_url( 'upload.php' ) ); ?>' );

        const thumb = document.getElementById( 'cu-success-thumb' );
        if ( att.source_url ) {
            thumb.src             = att.source_url;
            thumb.style.display   = 'block';
        }
    }

    function showError( msg ) {
        errorEl.querySelector( 'p' ).textContent = 'Fehler: ' + msg;
        errorEl.style.display = 'block';
    }

    // ---- Shortcut hint on paste zone --------------------------------
    pasteZone.addEventListener( 'keydown', e => {
        if ( ( e.key === 'v' || e.key === 'V' ) && ( e.ctrlKey || e.metaKey ) ) {
            // Browser will fire the paste event automatically
        }
    } );


})();
</script>
        <?php
    }
}

