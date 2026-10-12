#!/usr/bin/env node
/**
 * Pruefharnisch fuer die Darkmode-Bildklassifizierung
 * (`src/js/darkmode-image-invert.js`, `window.fosDarkmodeKlassifiziere`)
 * - ohne Browser, ohne jsdom, ohne npm-Abhaengigkeit.
 *
 * Aufruf:   node tools/test-darkmode-klassifizierung.js [--tabelle]
 * Andere Datei pruefen (Mutationsproben):  FOS_DARKMODE_DATEI=<pfad> node tools/...
 *
 * Herkunft: PLAN-Rueckmeldungen-Blockverweis-iPad-Tafel-Darkmode-Formeln.md,
 * AP-3.1 (Meldung #7107 - Strukturformeln im Darkmode unsichtbar).
 *
 * VERFAHREN
 * ---------
 * Die AUSGELIEFERTE Quelldatei laeuft in einem `vm`-Kontext mit minimalem
 * window/document-Stub (Muster: Plugins/CDB-Designer/tools/test-klassenpuls-js.js).
 * Kein Nachbau der Logik: Es laeuft der Quelltext, der an den Browser geht.
 *
 * Die Pixel-Fixtures erzeugt `tools/erzeuge-darkmode-fixtures.py` aus echten
 * Mediathek-Bildern, je Bild in drei Verkleinerungsarten (BOX, BILINEAR,
 * NEAREST) auf die Abtastgroesse. Erwartung:
 *   - erwartet "invertieren":       in ALLEN drei Verfahren -> 'invertieren'
 *   - erwartet "nicht-invertieren": in ALLEN drei Verfahren -> nicht 'invertieren'
 *
 * Zusaetzlich: die Abtastgroesse im Script (MAX_SAMPLE_DIMENSION, ueber
 * window.fosDarkmodeAbtastgroesse) muss der Fixture-Abtastgroesse entsprechen,
 * sonst pruefen die Fixtures etwas anderes, als der Browser sieht.
 *
 * Hinweis: Theme/package.json hat "type": "module" - diese Datei ist ESM.
 */
import fs from 'node:fs';
import path from 'node:path';
import vm from 'node:vm';
import zlib from 'node:zlib';
import { fileURLToPath } from 'node:url';

const HIER = path.dirname( fileURLToPath( import.meta.url ) );
const THEME = path.resolve( HIER, '..' );
const DATEI = process.env.FOS_DARKMODE_DATEI
    ? path.resolve( process.env.FOS_DARKMODE_DATEI )
    : path.join( THEME, 'src', 'js', 'darkmode-image-invert.js' );
const FIXTURES = path.join( HIER, 'fixtures', 'darkmode' );
const TABELLE = process.argv.includes( '--tabelle' );

let fehler = 0;
let geprueft = 0;
function pruefe( bedingung, text ) {
    geprueft++;
    if ( bedingung ) {
        if ( ! TABELLE ) { /* still */ }
    } else {
        fehler++;
        console.log( '  FEHLER: ' + text );
    }
}

// --- Script im vm-Kontext laden -------------------------------------------
function ladeScript() {
    const quelle = fs.readFileSync( DATEI, 'utf8' );
    pruefe( ! /^\s*(import|export)\s/m.test( quelle ), 'Script enthaelt import/export (muss klassisches Script bleiben)' );
    const noop = function () {};
    const document = {
        readyState: 'loading',
        addEventListener: noop,
        removeEventListener: noop,
        querySelectorAll: function () { return []; },
        createElement: function () { throw new Error( 'createElement im Harnisch nicht vorgesehen' ); },
        body: null,
        documentElement: { classList: { contains: function () { return false; } } }
    };
    const window = {
        addEventListener: noop,
        removeEventListener: noop,
        setTimeout: function () { return 0; },
        clearTimeout: noop,
        document: document
    };
    const kontext = vm.createContext( {
        window: window,
        document: document,
        setTimeout: window.setTimeout,
        clearTimeout: noop,
        console: console,
        Math: Math
    } );
    vm.runInContext( quelle, kontext, { filename: DATEI } );
    return window;
}

const win = ladeScript();
const klassifiziere = win.fosDarkmodeKlassifiziere;
pruefe( typeof klassifiziere === 'function', 'window.fosDarkmodeKlassifiziere ist keine Funktion' );
if ( typeof klassifiziere !== 'function' ) {
    console.log( 'Abbruch: keine Klassifizierungsfunktion.' );
    process.exit( 1 );
}

const index = JSON.parse( fs.readFileSync( path.join( FIXTURES, 'index.json' ), 'utf8' ) );
pruefe( win.fosDarkmodeAbtastgroesse === index.abtastgroesse,
    'Abtastgroesse im Script (' + win.fosDarkmodeAbtastgroesse + ') != Fixture-Abtastgroesse (' + index.abtastgroesse + ')' );

// --- Grenzfaelle ohne Fixture ---------------------------------------------
( function () {
    const leer = new Uint8ClampedArray( 4 * 16 );
    const r = klassifiziere( leer, 4, 4 );
    pruefe( r && r.ergebnis === 'leer', 'vollstaendig transparentes Bild -> leer (ist: ' + ( r && r.ergebnis ) + ')' );
    const weiss = new Uint8ClampedArray( 4 * 16 ).fill( 255 );
    const r2 = klassifiziere( weiss, 4, 4 );
    pruefe( r2 && r2.ergebnis === 'deckend', 'deckend weisses Bild -> deckend (ist: ' + ( r2 && r2.ergebnis ) + ')' );
    const schwarz = new Uint8ClampedArray( 4 * 16 );
    for ( let i = 0; i < 16; i++ ) { schwarz[ i * 4 + 3 ] = 255; }
    const r3 = klassifiziere( schwarz, 4, 4 );
    pruefe( r3 && r3.ergebnis === 'deckend', 'deckend schwarzes Bild -> deckend (ist: ' + ( r3 && r3.ergebnis ) + ')' );
}() );

// --- Fixtures --------------------------------------------------------------
const proBild = {};
if ( TABELLE ) {
    console.log( 'id'.padEnd( 32 ) + 'verf.    ergebnis     transp  strich   wHell  erwartet' );
}
for ( const f of index.fixtures ) {
    const roh = zlib.gunzipSync( fs.readFileSync( path.join( FIXTURES, f.datei ) ) );
    const daten = new Uint8ClampedArray( roh.buffer, roh.byteOffset, roh.byteLength );
    pruefe( daten.length === f.breite * f.hoehe * 4, f.datei + ': Laenge passt nicht zu Breite x Hoehe' );
    const r = klassifiziere( daten, f.breite, f.hoehe ) || {};
    const invertiert = r.ergebnis === 'invertieren';
    const ok = f.erwartet === 'invertieren' ? invertiert : ! invertiert;
    pruefe( ok, f.id + ' [' + f.verfahren + '] erwartet ' + f.erwartet + ', ist ' + r.ergebnis );
    ( proBild[ f.id ] = proBild[ f.id ] || [] ).push( ok );
    if ( TABELLE ) {
        const zahl = function ( v, n ) { return typeof v === 'number' ? v.toFixed( n ) : '-'; };
        console.log(
            f.id.padEnd( 32 ) + f.verfahren.padEnd( 9 ) + String( r.ergebnis ).padEnd( 13 ) +
            zahl( r.anteilTransparent, 3 ).padStart( 6 ) + '  ' +
            String( r.strichPixel ).padStart( 6 ) + '  ' +
            zahl( r.helligkeit, 1 ).padStart( 6 ) + '  ' + f.erwartet + ( ok ? '' : '   <-- FALSCH' )
        );
    }
}

const bilder = Object.keys( proBild );
const falscheBilder = bilder.filter( function ( k ) { return proBild[ k ].indexOf( false ) !== -1; } );
console.log( '' );
console.log( 'Bilder: ' + bilder.length + ', davon falsch in mind. einem Verfahren: ' + falscheBilder.length +
    ( falscheBilder.length ? ' (' + falscheBilder.join( ', ' ) + ')' : '' ) );
console.log( 'Pruefungen: ' + geprueft + ', Fehler: ' + fehler );
console.log( fehler === 0 ? 'ERGEBNIS: GRUEN' : 'ERGEBNIS: ROT' );
process.exit( fehler === 0 ? 0 : 1 );
