/**
 * Simple Clean Theme - Main JavaScript
 */

// ── Custom Lightbox (CLB) ────────────────────────────────────────────────────
//
// FLIP animation: image zooms FROM its position on the page TO the center,
// and zooms back on close. Triggered by data-clb-src on <img> elements.
// ----------------------------------------------------------------------------
(function () {
    'use strict';

    var overlay, clbImg, closeBtn;
    var currentTrigger = null;

    // ── Build overlay DOM (once) ─────────────────────────────────────────────

    function buildOverlay() {
        overlay  = document.createElement( 'div' );
        overlay.id = 'clb-overlay';
        overlay.setAttribute( 'role', 'dialog' );
        overlay.setAttribute( 'aria-modal', 'true' );
        overlay.setAttribute( 'aria-label', 'Vergrößerte Ansicht' );

        closeBtn = document.createElement( 'button' );
        closeBtn.id = 'clb-close';
        closeBtn.setAttribute( 'aria-label', 'Schließen' );
        closeBtn.textContent = '×';

        clbImg = document.createElement( 'img' );
        clbImg.id = 'clb-img';
        clbImg.alt = '';

        overlay.appendChild( closeBtn );
        overlay.appendChild( clbImg );
        document.body.appendChild( overlay );

        overlay.addEventListener( 'click', function ( e ) {
            if ( e.target === overlay ) closeLightbox();
        } );
        closeBtn.addEventListener( 'click', closeLightbox );
        clbImg.addEventListener( 'click', closeLightbox );

        document.addEventListener( 'keydown', function ( e ) {
            if ( e.key === 'Escape' && overlay.classList.contains( 'clb-active' ) ) {
                closeLightbox();
            }
        } );
    }

    // ── Size calculation ─────────────────────────────────────────────────────

    function fitClbImg() {
        var nw = clbImg.naturalWidth;
        var nh = clbImg.naturalHeight;
        if ( ! nw || ! nh ) return;

        var adminBar = document.getElementById( 'wpadminbar' );
        var abH      = adminBar ? adminBar.offsetHeight : 0;
        var maxW     = window.innerWidth  * 0.90;
        var maxH     = ( window.innerHeight - abH ) * 0.90;
        var scale    = Math.min( maxW / nw, maxH / nh, 1 );
        var w        = Math.round( nw * scale );
        var h        = Math.round( nh * scale );

        clbImg.style.setProperty( 'width',      w + 'px', 'important' );
        clbImg.style.setProperty( 'height',     h + 'px', 'important' );
        clbImg.style.setProperty( 'max-width',  'none',   'important' );
        clbImg.style.setProperty( 'max-height', 'none',   'important' );
    }

    // ── FLIP: animate from triggerRect to final center position ──────────────

    function flipOpen( triggerRect ) {
        if ( ! triggerRect ) return;
        var imgRect = clbImg.getBoundingClientRect();
        if ( ! imgRect.width ) return;

        var dx = ( triggerRect.left + triggerRect.width  / 2 ) - ( imgRect.left + imgRect.width  / 2 );
        var dy = ( triggerRect.top  + triggerRect.height / 2 ) - ( imgRect.top  + imgRect.height / 2 );
        var sx = triggerRect.width  / imgRect.width;
        var sy = triggerRect.height / imgRect.height;

        // Instantly jump to trigger position (no transition)
        clbImg.style.transition = 'none';
        clbImg.style.transform  = 'translate(' + dx + 'px,' + dy + 'px) scale(' + sx + ',' + sy + ')';

        // Force reflow, then animate to center
        clbImg.offsetHeight;
        clbImg.style.transition = 'transform 0.32s cubic-bezier(0.2, 0, 0, 1)';
        clbImg.style.transform  = '';
    }

    // ── Open / close ─────────────────────────────────────────────────────────

    function openLightbox( src, triggerEl ) {
        currentTrigger = triggerEl || null;

        // Reset previous size + transform
        clbImg.style.removeProperty( 'width' );
        clbImg.style.removeProperty( 'height' );
        clbImg.style.removeProperty( 'max-width' );
        clbImg.style.removeProperty( 'max-height' );
        clbImg.style.transition = 'none';
        clbImg.style.transform  = '';

        var triggerRect = triggerEl ? triggerEl.getBoundingClientRect() : null;

        overlay.classList.add( 'clb-active' );
        document.body.style.overflow = 'hidden';

        function afterLoad() {
            fitClbImg();
            flipOpen( triggerRect );
        }

        if ( typeof clbImg.decode === 'function' ) {
            clbImg.src = src;
            clbImg.decode()
                .then( afterLoad )
                .catch( afterLoad );
        } else {
            clbImg.onload = afterLoad;
            clbImg.src = src;
            if ( clbImg.complete && clbImg.naturalWidth ) afterLoad();
        }
    }

    function closeLightbox() {
        var triggerEl = currentTrigger;
        currentTrigger = null;

        // Animate back to original image position
        if ( triggerEl && triggerEl.isConnected ) {
            var triggerRect = triggerEl.getBoundingClientRect();
            var imgRect     = clbImg.getBoundingClientRect();
            var dx = ( triggerRect.left + triggerRect.width  / 2 ) - ( imgRect.left + imgRect.width  / 2 );
            var dy = ( triggerRect.top  + triggerRect.height / 2 ) - ( imgRect.top  + imgRect.height / 2 );
            var sx = triggerRect.width  / ( imgRect.width  || 1 );
            var sy = triggerRect.height / ( imgRect.height || 1 );
            clbImg.style.transition = 'transform 0.25s cubic-bezier(0.4, 0, 1, 1)';
            clbImg.style.transform  = 'translate(' + dx + 'px,' + dy + 'px) scale(' + sx + ',' + sy + ')';
        }

        overlay.classList.remove( 'clb-active' );
        document.body.style.overflow = '';

        overlay.addEventListener( 'transitionend', function clearSrc() {
            overlay.removeEventListener( 'transitionend', clearSrc );
            clbImg.src = '';
            clbImg.style.removeProperty( 'transform' );
            clbImg.style.removeProperty( 'transition' );
        } );
    }

    // ── Click delegation ─────────────────────────────────────────────────────

    function initCustomLightbox() {
        buildOverlay();

        document.addEventListener( 'click', function ( e ) {
            var trigger = e.target.closest( '[data-clb-src]' );
            if ( ! trigger ) return;
            e.preventDefault();
            var src        = trigger.getAttribute( 'data-clb-src' ) || trigger.src;
            var triggerImg = trigger.tagName === 'IMG' ? trigger : trigger.querySelector( 'img' );
            if ( src ) openLightbox( src, triggerImg || trigger );
        } );

        // Refit on resize (without re-triggering FLIP)
        window.addEventListener( 'resize', function () {
            if ( overlay.classList.contains( 'clb-active' ) ) {
                clbImg.style.removeProperty( 'transform' );
                clbImg.style.removeProperty( 'transition' );
                fitClbImg();
            }
        } );
    }

    // ── Boot ─────────────────────────────────────────────────────────────────

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', initCustomLightbox );
    } else {
        initCustomLightbox();
    }

}());
