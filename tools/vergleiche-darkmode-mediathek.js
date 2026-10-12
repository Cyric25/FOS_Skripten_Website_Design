#!/usr/bin/env node
/**
 * Regressionswaechter Darkmode-Bildinvertierung, Teil 2: Vergleich alt/neu
 * (PLAN-Rueckmeldungen-Blockverweis-iPad-Tafel-Darkmode-Formeln.md, AP-3.fix1).
 *
 * Vorher Teil 1 ausfuehren (Abtastung, nur lesend):
 *     python -I tools/vergleiche-darkmode-mediathek.py [UPLOADS] [ZWISCHENORDNER]
 * Dann:
 *     node tools/vergleiche-darkmode-mediathek.js [ZWISCHENORDNER] [--alle]
 *
 * Klassifiziert jedes abgetastete Bild in BOX/BILINEAR/NEAREST mit
 *   - ALT: der Logik von Theme 1.5.117 (200 px, Alpha >= 250, Luminanz < 100),
 *          als reine Funktion aus Commit 4149b08 (`git show`, unveraenderte
 *          Schwellen; dort fuer den Rot-Nachweis herausgeloest),
 *   - NEU: der aktuellen src/js/darkmode-image-invert.js (512 px)
 * - jeweils der ECHTE Quelltext im vm-Kontext, kein Nachbau.
 *
 * Ausgabe: jede Abweichung "alt invertiert -> neu nicht" und
 * "alt nicht -> neu invertiert" (je Verfahren verglichen), Bilder mit
 * uneinigen Verfahren, Summen. --alle listet jedes Bild.
 * Exit-Code immer 0 (Werkzeug, kein Test) - die Bewertung jeder Abweichung
 * steht in der Uebergabenotiz von AP-3.fix1.
 */
import fs from 'node:fs';
import os from 'node:os';
import path from 'node:path';
import vm from 'node:vm';
import zlib from 'node:zlib';
import { execFileSync } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const HIER = path.dirname( fileURLToPath( import.meta.url ) );
const THEME = path.resolve( HIER, '..' );
const argumente = process.argv.slice( 2 ).filter( function ( a ) { return a.indexOf( '--' ) !== 0; } );
const ZWISCHEN = argumente[ 0 ] || path.join( os.tmpdir(), 'fos-darkmode-mediathek' );
const ALLE = process.argv.includes( '--alle' );
const ALT_COMMIT = '4149b08';

function ladeKlassifizierer( quelle, name ) {
    const noop = function () {};
    const document = {
        readyState: 'loading', addEventListener: noop, removeEventListener: noop,
        querySelectorAll: function () { return []; }, body: null,
        createElement: function () { throw new Error( 'createElement nicht vorgesehen' ); },
        documentElement: { classList: { contains: function () { return false; } } }
    };
    const window = { addEventListener: noop, removeEventListener: noop, setTimeout: function () { return 0; }, clearTimeout: noop, document: document };
    vm.runInContext( quelle, vm.createContext( { window: window, document: document, setTimeout: window.setTimeout, clearTimeout: noop, console: console, Math: Math } ), { filename: name } );
    return { klassifiziere: window.fosDarkmodeKlassifiziere, kante: window.fosDarkmodeAbtastgroesse };
}

const alt = ladeKlassifizierer( execFileSync( 'git', [ 'show', ALT_COMMIT + ':src/js/darkmode-image-invert.js' ], { cwd: THEME, encoding: 'utf8' } ), 'alt-' + ALT_COMMIT );
const neu = ladeKlassifizierer( fs.readFileSync( path.join( THEME, 'src', 'js', 'darkmode-image-invert.js' ), 'utf8' ), 'src/js/darkmode-image-invert.js' );
const index = JSON.parse( fs.readFileSync( path.join( ZWISCHEN, 'index.json' ), 'utf8' ) );
const VERFAHREN = [ 'BOX', 'BILINEAR', 'NEAREST' ];
const KURZ = { invertieren: 'INV', hell: 'hell', deckend: 'deck', leer: 'leer' };

function klassifiziere( k, bild, verfahren ) {
    const a = bild.abtastungen.find( function ( x ) { return x.kante === k.kante && x.verfahren === verfahren; } );
    if ( ! a ) { throw new Error( bild.pfad + ': keine Abtastung ' + k.kante + '/' + verfahren + ' - Teil 1 erneut ausfuehren' ); }
    const roh = zlib.gunzipSync( fs.readFileSync( path.join( ZWISCHEN, a.datei ) ) );
    return k.klassifiziere( new Uint8ClampedArray( roh.buffer, roh.byteOffset, roh.byteLength ), a.breite, a.hoehe );
}

const listen = { altJaNeuNein: [], altNeinNeuJa: [], uneinig: [] };
const summe = { alt: 0, neu: 0, bilder: 0 };
const zeilen = [];
for ( const bild of index.bilder ) {
    const ra = VERFAHREN.map( function ( v ) { return klassifiziere( alt, bild, v ); } );
    const rn = VERFAHREN.map( function ( v ) { return klassifiziere( neu, bild, v ); } );
    const a = ra.map( function ( r ) { return r.ergebnis === 'invertieren'; } );
    const n = rn.map( function ( r ) { return r.ergebnis === 'invertieren'; } );
    summe.bilder++;
    if ( a.some( Boolean ) ) { summe.alt++; }
    if ( n.some( Boolean ) ) { summe.neu++; }
    const m = rn[ 1 ];
    const zeile = bild.pfad.padEnd( 62 ) +
        ' alt ' + ra.map( function ( r ) { return KURZ[ r.ergebnis ]; } ).join( '/' ).padEnd( 15 ) +
        ' neu ' + rn.map( function ( r ) { return KURZ[ r.ergebnis ]; } ).join( '/' ).padEnd( 15 ) +
        ' tr ' + m.anteilTransparent.toFixed( 2 ) +
        ' wH ' + rn.map( function ( r ) { return r.helligkeit.toFixed( 0 ); } ).join( '/' ) +
        ' fT‰ ' + rn.map( function ( r ) { return typeof r.freieTinte === 'number' ? ( r.freieTinte * 1000 ).toFixed( 1 ) : '-'; } ).join( '/' );
    zeilen.push( zeile );
    if ( a.some( function ( x, i ) { return x && ! n[ i ]; } ) ) { listen.altJaNeuNein.push( zeile ); }
    if ( a.some( function ( x, i ) { return ! x && n[ i ]; } ) ) { listen.altNeinNeuJa.push( zeile ); }
    if ( new Set( n ).size > 1 ) { listen.uneinig.push( zeile ); }
}
if ( ALLE ) { console.log( '=== ALLE BILDER ===' ); zeilen.forEach( function ( z ) { console.log( z ); } ); console.log( '' ); }
console.log( '=== ALT INVERTIERT -> NEU NICHT (je Verfahren) : ' + listen.altJaNeuNein.length + ' ===' );
listen.altJaNeuNein.forEach( function ( z ) { console.log( z ); } );
console.log( '\n=== ALT NICHT -> NEU INVERTIERT (je Verfahren) : ' + listen.altNeinNeuJa.length + ' ===' );
listen.altNeinNeuJa.forEach( function ( z ) { console.log( z ); } );
console.log( '\n=== NEU UNEINIG ZWISCHEN DEN VERFAHREN : ' + listen.uneinig.length + ' ===' );
listen.uneinig.forEach( function ( z ) { console.log( z ); } );
console.log( '\nBilder: ' + summe.bilder + ', alt invertiert (mind. 1 Verfahren): ' + summe.alt + ', neu invertiert: ' + summe.neu );
