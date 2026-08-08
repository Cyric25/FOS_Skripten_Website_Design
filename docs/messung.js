/* ===================================================================
   Messskript fuer AP-1.2 / AP-1.6 / AP-5.2  (PLAN-Seitenindex.md)

   ANWENDUNG
   1. Als Administrator auf der Website anmelden.
   2. Irgendeine Seite der Website oeffnen (wichtig: gleiche Domain).
   3. Entwicklerwerkzeuge oeffnen (F12) -> Reiter "Konsole".
   4. Dieses gesamte Skript hineinkopieren und mit Enter ausfuehren.

   Das Skript prueft zuerst die Voraussetzungen (Theme-Version, Anmeldung)
   und sagt genau, woran es liegt, wenn etwas fehlt. Danach ruft es jede
   Seite dreimal ohne Cache ab, liest den SC-PERF-Kommentar aus und legt das
   Ergebnis als fertigen Textblock in die Zwischenablage.
   =================================================================== */

(async () => {
  // --- Konfiguration -----------------------------------------------
  // Leer lassen = das Skript schlaegt Seiten vor und listet sie auf.
  // Nach dem ersten Lauf hier die gewuenschten Pfade eintragen.
  const CONFIG = {
    inhaltsverzeichnis: '',   // z. B. '/inhaltsverzeichnis/'
    skriptenseite:      '',   // eine gewoehnliche Seite mit Fließtext
    startseite:         '/',
    durchlaeufe:        3,
  };
  // -----------------------------------------------------------------

  // Das Muster muss BEIDE Schreibweisen der Zeit erkennen:
  //   time=1.873s  -> ab Theme v1.5.70, bewusst mit Punkt formatiert
  //   time=1,873s  -> v1.5.69, dort kam die Zahl aus timer_stop() und damit
  //                   aus number_format_i18n(), das in einer deutschen
  //                   Installation ein Komma setzt.
  // Ebenso beim Speicher: "50331648" (Bytes, neu) oder "48 MB" (alt).
  const RE = /<!--\s*SC-PERF\s+queries=(\d+)\s+time=([\d.,]+)s\s+peak=(.+?)\s*-->/;

  // Zweite Zeile ab Theme v1.5.70: Aufschluesselung der Glossar-Verlinkung.
  const RE_G = /<!--\s*SC-GLOSSAR\s+aufrufe=(\d+)\s+kandidaten=(-?\d+)\s+fallback=(\d+)\s+begriffe=(\d+)\s+zeit=([\d.,]+)s\s*-->/;

  // "1,873" -> 1.873 ; "1.873" -> 1.873 ; "1.234,567" -> 1234.567
  // Enthaelt die Zahl ein Komma, ist das Komma das Dezimaltrennzeichen und
  // Punkte sind Tausendertrennzeichen. Sonst ist der Punkt das Dezimalzeichen.
  const zahl = (s) => {
    s = String(s);
    return s.includes(',')
      ? parseFloat(s.replace(/\./g, '').replace(',', '.'))
      : parseFloat(s);
  };

  // "50331648" -> "48 MB" ; "48 MB" bleibt "48 MB"
  const speicher = (s) => /^\d+$/.test(s)
    ? (parseInt(s, 10) / 1048576).toFixed(0) + ' MB'
    : s;
  const log  = (...a) => console.log('%c[Messung]', 'color:#e24614;font-weight:bold', ...a);
  const warn = (...a) => console.log('%c[Messung]', 'color:#b00;font-weight:bold', ...a);
  const ok   = (...a) => console.log('%c[Messung]', 'color:#0a0;font-weight:bold', ...a);

  const url = (pfad, mitParam) => {
    const u = new URL(pfad, location.origin);
    if (mitParam) u.searchParams.set('sc_perf', '1');
    u.searchParams.set('_cb', Math.random().toString(36).slice(2));
    return u.toString();
  };

  async function hole(pfad, mitParam = true) {
    const angefordert = url(pfad, mitParam);
    const antwort = await fetch(angefordert, {
      cache: 'no-store',
      credentials: 'same-origin',
    });
    const text = await antwort.text();
    return {
      status: antwort.status,
      text,
      angefordert,
      endgueltig: antwort.url,          // nach eventuellen Weiterleitungen
      weitergeleitet: antwort.redirected,
      bytes: new TextEncoder().encode(text).length,
      treffer: text.match(RE),
      glossar: text.match(RE_G),
    };
  }

  // ===================================================================
  // SCHRITT 1 - Diagnose der Voraussetzungen
  // ===================================================================
  log('Pruefe Voraussetzungen …');

  const start = await hole(CONFIG.startseite);
  if (start.status !== 200) {
    warn(`Startseite antwortet mit HTTP ${start.status}. Abbruch.`);
    return;
  }

  // a) Ist die Sitzung als angemeldeter Benutzer erkannt?
  //    WordPress setzt bei angemeldeten Benutzern die Body-Klasse "logged-in".
  const angemeldet = /<body[^>]*\bclass="[^"]*\blogged-in\b/.test(start.text);

  // b) Welche Theme-Version laeuft wirklich?
  //    Der Stylesheet-Link wird aus dem HTML gelesen (robust gegen einen
  //    abweichenden Ordnernamen), die Datei selbst dann direkt geholt.
  //    Statische Dateien liefert der Webserver aus, ohne WordPress.
  let themeVersion = 'unbekannt', themePfad = '?';
  const linkTreffer = start.text.match(/href=["']([^"']*\/themes\/[^"']*style\.css[^"']*)["']/i);
  if (linkTreffer) {
    themePfad = linkTreffer[1].split('?')[0];
    try {
      const cssAntwort = await fetch(themePfad, { cache: 'no-store' });
      const css = await cssAntwort.text();
      const v = css.match(/^\s*Version:\s*([\d.]+)/mi);
      if (v) themeVersion = v[1];
    } catch (e) { /* egal, bleibt unbekannt */ }
  }

  // c) Ist die Messfunktion aktiv?
  const messfunktionAktiv = !!start.treffer;

  console.table([{
    'Angemeldet erkannt': angemeldet ? 'ja' : 'NEIN',
    'Theme-Version live': themeVersion,
    'SC-PERF aktiv':      messfunktionAktiv ? 'ja' : 'NEIN',
  }]);

  // Versionen numerisch vergleichen. Ein Stringvergleich waere hier falsch:
  // '1.5.7' < '1.5.69' ergaebe false, obwohl 1.5.7 die aeltere Version ist.
  const vergleiche = (a, b) => {
    const za = a.split('.').map(Number), zb = b.split('.').map(Number);
    for (let i = 0; i < Math.max(za.length, zb.length); i++) {
      const d = (za[i] || 0) - (zb[i] || 0);
      if (d !== 0) return d;
    }
    return 0;
  };

  if (!messfunktionAktiv) {
    warn('Die Messfunktion antwortet nicht. Diagnose:');
    if (themeVersion !== 'unbekannt' && vergleiche(themeVersion, '1.5.69') < 0) {
      warn(`  -> Auf der Website laeuft Theme v${themeVersion}.`);
      warn('     Die Messfunktion kam erst mit v1.5.69 dazu.');
      warn('     ABHILFE: Theme/dist/fos-online-schulbuch.zip hochladen');
      warn('     (WP-Admin -> Design -> Themes -> Theme hochladen -> Ersetzen).');
    } else if (!angemeldet) {
      warn('  -> Die Sitzung wird nicht als angemeldet erkannt.');
      warn('     ABHILFE: In DIESEM Browserfenster als Administrator anmelden');
      warn('     und das Skript erneut ausfuehren.');
    } else if (themeVersion !== 'unbekannt' && vergleiche(themeVersion, '1.5.69') >= 0) {
      warn(`  -> Theme v${themeVersion} laeuft und du bist angemeldet.`);
      warn('     Grenze die Ursache jetzt ein …');

      // Probe 1: Werden HTML-Kommentare ueberhaupt ausgeliefert?
      // Viele Optimierungs-Plugins (LiteSpeed, WP Rocket, Autoptimize)
      // entfernen sie beim Minifizieren - dann kann SC-PERF nicht ankommen.
      const kommentare = (start.text.match(/<!--/g) || []).length;

      // Probe 2: Hat das Konto wirklich manage_options?
      // /wp-admin/options-general.php ist genau daran gebunden und laesst
      // sich mit dem Anmelde-Cookie ohne Nonce abrufen.
      let rechte = 'unklar';
      try {
        const a = await fetch('/wp-admin/options-general.php', {
          cache: 'no-store', credentials: 'same-origin',
        });
        const t = await a.text();
        const verweigert = /nicht die Berechtigung|not allowed to access|Sie haben keine ausreichende/i.test(t);
        if (a.status === 200 && !verweigert)      rechte = 'vorhanden';
        else if (a.status === 403 || verweigert)  rechte = 'FEHLT';
        else                                      rechte = `unklar (HTTP ${a.status})`;
      } catch (e) { rechte = 'nicht pruefbar'; }

      // Probe 3: Fingerabdruecke bekannter Optimierer im HTML
      const spuren = ['litespeed', 'wp-rocket', 'wprocket', 'autoptimize', 'wp-fastest-cache',
                      'w3-total-cache', 'wp-super-cache', 'hummingbird', 'swift-performance']
        .filter(n => start.text.toLowerCase().includes(n));

      // Probe 4: Lief wp_footer ueberhaupt?
      // NICHT ueber "wpadminbar" pruefen - WordPress gibt schon im wp_head
      // ein <style id="admin-bar-inline-css"> mit #wpadminbar aus, der
      // Treffer beweist also nichts. Aussagekraeftig ist </body>: das steht
      // in footer.php direkt NACH wp_footer(). Ist es da, lief wp_footer
      // vollstaendig durch.
      const footerLief = /<\/body>/i.test(start.text);

      // Probe 6 (die entscheidende): Laeuft wirklich die NEUE functions.php?
      // Theme/CLAUDE.md haelt fest: bis v1.5.63 haengte functions.php das
      // Stylesheet mit fester Version '1.0' ein, seit v1.5.64 mit
      // filemtime(). Steht in der URL also style.css?ver=1.0, laeuft eine
      // ALTE functions.php - unabhaengig davon, welche Version in der
      // style.css selbst steht. Genau dieser Fall trennt "Datei nicht
      // aktualisiert" von "Bedingung greift nicht".
      const verTreffer = start.text.match(/\/themes\/[^"']*style\.css\?ver=([^"'&]+)/i);
      const cssVer = verTreffer ? verTreffer[1] : '(nicht gefunden)';
      const cssVerIstZeitstempel = /^\d{9,}$/.test(cssVer);

      // Probe 5: Kommt der Parameter am Ziel an, oder wird er unterwegs
      // entfernt? fetch folgt Weiterleitungen still - antwort.url zeigt,
      // wo die Anfrage tatsaechlich gelandet ist.
      const paramAngekommen = start.endgueltig.includes('sc_perf');

      console.table([{
        'HTML-Kommentare in der Seite': kommentare,
        'Berechtigung manage_options':  rechte,
        'Gefundene Optimierer':         spuren.length ? spuren.join(', ') : 'keine',
        'wp_footer lief (</body> da)':  footerLief ? 'ja' : 'NEIN',
        'weitergeleitet':               start.weitergeleitet ? 'ja' : 'nein',
        'sc_perf in der Ziel-URL':      paramAngekommen ? 'ja' : 'NEIN',
        'style.css?ver=':               cssVer,
        'functions.php aktuell?':       cssVerIstZeitstempel ? 'ja (filemtime)' : 'NEIN (feste Version)',
      }]);
      log('  angefordert: ' + start.angefordert);
      log('  gelandet bei: ' + start.endgueltig);

      if (rechte === 'FEHLT') {
        warn('  URSACHE: Dein Konto hat kein manage_options.');
        warn('  ABHILFE: Mit einem Administratorkonto anmelden.');
      } else if (kommentare === 0) {
        warn('  URSACHE: In der ausgelieferten Seite steht KEIN einziger HTML-Kommentar.');
        warn('  Etwas entfernt sie - vermutlich ein Optimierungs-/Cache-Plugin' +
             (spuren.length ? ` (${spuren.join(', ')})` : '') + '.');
      } else if (!paramAngekommen) {
        warn('  URSACHE: Der Parameter sc_perf geht unterwegs verloren.');
        warn('  Die Anfrage landet ohne ihn am Ziel - eine Weiterleitung oder');
        warn('  eine Server-Regel entfernt die Abfragezeichenfolge.');
        warn('  ABHILFE: Melde mir das, ich stelle den Ausloeser auf etwas um,');
        warn('  das eine Weiterleitung uebersteht.');
      } else if (!footerLief) {
        warn('  URSACHE: Die Ausgabe bricht vor </body> ab - wp_footer wird nicht');
        warn('  vollstaendig durchlaufen. Bitte melde mir diese Tabelle.');
      } else if (!cssVerIstZeitstempel) {
        warn(`  URSACHE: Es laeuft eine ALTE functions.php (style.css?ver=${cssVer}).`);
        warn('  Seit v1.5.64 haengt functions.php das Stylesheet mit filemtime() ein,');
        warn('  die Version muesste also ein Unix-Zeitstempel sein. Die style.css');
        warn('  wurde ersetzt, die functions.php aber nicht - oder sie wird noch');
        warn('  aus einem PHP-Zwischenspeicher (OPcache) bedient.');
        warn('  ABHILFE: Theme erneut hochladen; bringt das nichts, im All-Inkl-KAS');
        warn('  den PHP-Zwischenspeicher leeren bzw. kurz die PHP-Version umstellen');
        warn('  und zurueckstellen - das verwirft den OPcache.');
      } else {
        warn('  Alle Voraussetzungen sind erfuellt: Parameter kommt an, wp_footer');
        warn(`  laeuft, Rechte stimmen, functions.php ist aktuell (ver=${cssVer}),`);
        warn('  Kommentare werden nicht entfernt.');
        warn('  Damit bleibt ein Seiten-Cache als wahrscheinlichste Ursache.');
        warn('  ABHILFE: Cache leeren, dann erneut ausfuehren. Bleibt es dabei,');
        warn('  melde mir bitte diese Tabelle.');
      }
      warn(`  Zum Nachsehen von Hand: ${url(CONFIG.startseite, true)} (Strg+U, nach SC-PERF suchen)`);
    } else {
      warn(`  -> Theme-Version nicht ermittelbar (Stylesheet-Pfad: ${themePfad}).`);
      warn('     Bitte pruefen, ob v1.5.69 hochgeladen wurde.');
    }
    return;
  }

  ok(`Voraussetzungen erfuellt (Theme v${themeVersion}, angemeldet).`);

  // ===================================================================
  // SCHRITT 2 - Seiten bestimmen
  // ===================================================================
  async function restZaehler(typ, extra = '') {
    try {
      const a = await fetch(`/wp-json/wp/v2/${typ}?per_page=1&${extra}`, { credentials: 'same-origin' });
      return a.headers.get('X-WP-Total') || '?';
    } catch (e) { return '?'; }
  }

  async function alleSeiten() {
    const gesammelt = [];
    for (let seite = 1; seite <= 10; seite++) {
      const a = await fetch(
        `/wp-json/wp/v2/pages?per_page=100&page=${seite}&status=publish&_fields=id,link,title,parent`,
        { credentials: 'same-origin' });
      if (!a.ok) break;
      const teil = await a.json();
      gesammelt.push(...teil);
      const gesamt = parseInt(a.headers.get('X-WP-TotalPages') || '1', 10);
      if (seite >= gesamt) break;
    }
    return gesammelt;
  }

  const seiten = await alleSeiten();
  const pfadVon = (s) => new URL(s.link).pathname;
  const titelVon = (s) => (s.title?.rendered || '').replace(/&#\d+;/g, m =>
    String.fromCharCode(parseInt(m.slice(2, -1), 10)));

  // Die Inhaltsverzeichnisseite wird NICHT ueber den Titel geraten - das ging
  // schief und lieferte einmal eine beliebige Kapitelseite, einmal die
  // Startseite. Gesucht wird stattdessen nach dem Merkmal, auf das es
  // ankommt: Der Core-Block core/page-list rendert mit der CSS-Klasse
  // wp-block-page-list. Welche Seite die ist, steht damit fest.
  async function findePageListSeiten(kandidaten) {
    const gefunden = [];
    for (let i = 0; i < kandidaten.length; i += 20) {
      const teil = kandidaten.slice(i, i + 20).map(s => s.id);
      const a = await fetch(
        `/wp-json/wp/v2/pages?include=${teil.join(',')}&per_page=20&_fields=id,link,title,content`,
        { credentials: 'same-origin' });
      if (!a.ok) continue;
      const daten = await a.json();
      for (const p of daten) {
        const html = p.content?.rendered || '';
        if (/wp-block-page-list/.test(html)) {
          gefunden.push({
            id: p.id,
            titel: titelVon(p),
            pfad: new URL(p.link).pathname,
            links: (html.match(/<a\b/g) || []).length,
          });
        }
      }
    }
    return gefunden;
  }

  // Es kann MEHRERE Verzeichnisseiten geben - auf dieser Website sind es drei
  // Kapiteluebersichten statt eines Gesamtverzeichnisses. Dann werden alle
  // gemessen, statt eine auszuwaehlen: der serverseitige Aufwand ist bei
  // core/page-list naemlich auf allen gleich gross. Der Core laedt ueber
  // get_pages() immer ALLE Seiten der Website und filtert erst danach nach
  // parentPageID. Eine Uebersicht mit 26 sichtbaren Links kostet den Server
  // also genauso viel wie eine mit 128.
  let verzeichnisse = [];
  if (CONFIG.inhaltsverzeichnis) {
    verzeichnisse = [{ titel: '(konfiguriert)', pfad: CONFIG.inhaltsverzeichnis }];
  } else {
    log('Suche Seiten mit dem Block "Seitenliste" (core/page-list) …');
    const oberste = seiten.filter(s => s.parent === 0);
    const rest    = seiten.filter(s => s.parent !== 0);
    verzeichnisse = await findePageListSeiten(oberste);
    const weitere = await findePageListSeiten(rest);
    verzeichnisse = verzeichnisse.concat(weitere);

    if (!verzeichnisse.length) {
      warn('Keine Seite mit dem Block "Seitenliste" gefunden.');
      warn('Moeglich: das Verzeichnis steckt in einem Container-Block, der');
      warn('den Inhalt anders ablegt, oder es wurde anders gebaut.');
      warn('Bitte oben bei CONFIG.inhaltsverzeichnis den Pfad von Hand eintragen.');
      console.table(oberste.map(s => ({ Titel: titelVon(s), Pfad: pfadVon(s) })));
      return;
    }
    log(`${verzeichnisse.length} Verzeichnisseite(n) gefunden:`);
    console.table(verzeichnisse);
  }

  if (!CONFIG.skriptenseite) {
    const verzPfade = verzeichnisse.map(v => v.pfad);
    const kandidat = seiten.find(s => s.parent > 0 && !verzPfade.includes(pfadVon(s)));
    if (!kandidat) { warn('Keine Unterseite gefunden. Bitte CONFIG.skriptenseite eintragen.'); return; }
    CONFIG.skriptenseite = pfadVon(kandidat);
    log(`Skriptenseite gewaehlt: "${titelVon(kandidat)}" -> ${CONFIG.skriptenseite}`);
  }

  // ===================================================================
  // SCHRITT 3 - Messung
  // ===================================================================
  const median = (werte) => [...werte].sort((a, b) => a - b)[Math.floor(werte.length / 2)];

  async function miss(name, pfad) {
    const queries = [], zeiten = [], groessen = [], glossarZeiten = [];
    let peak = '?', links = 0, elemente = 0, g = null;
    for (let i = 0; i < CONFIG.durchlaeufe; i++) {
      const r = await hole(pfad);
      if (r.status !== 200) { warn(`${name}: HTTP ${r.status} bei ${pfad}`); return null; }
      if (!r.treffer) { warn(`${name}: kein SC-PERF bei ${pfad} (Seite laedt, Ausgabe fehlt)`); return null; }
      queries.push(parseInt(r.treffer[1], 10));
      zeiten.push(zahl(r.treffer[2]));
      peak = speicher(r.treffer[3]);
      groessen.push(r.bytes);
      if (r.glossar) {
        g = { kandidaten: +r.glossar[2], fallback: +r.glossar[3], begriffe: +r.glossar[4] };
        glossarZeiten.push(zahl(r.glossar[5]));
      }
      // DOM-Umfang: bei einem Inhaltsverzeichnis oft der eigentliche
      // Bremsklotz - der Server ist schnell fertig, der Browser nicht.
      links    = (r.text.match(/<a\b/gi) || []).length;
      elemente = (r.text.match(/<[a-z][a-z0-9]*\b/gi) || []).length;
    }
    const gZeit = glossarZeiten.length ? median(glossarZeiten) : null;
    const gesamt = median(zeiten);
    const e = {
      Seite: name,
      Pfad: pfad,
      Queries: median(queries),
      'Zeit (s)': gesamt,
      'davon Glossar (s)': gZeit === null ? '?' : gZeit,
      'Glossar %': gZeit === null ? '?' : (gZeit / gesamt * 100).toFixed(0) + '%',
      Begriffe: g ? g.begriffe : '?',
      Kandidaten: g ? g.kandidaten : '?',
      Fallback: g ? (g.fallback > 0 ? 'JA' : 'nein') : '?',
      Speicher: peak,
      'Groesse (KB)': (median(groessen) / 1024).toFixed(1),
      Links: links,
      'HTML-Elemente': elemente,
      Einzelwerte: `q:[${queries}] t:[${zeiten}]`,
    };
    log(`${name}: ${e.Queries} Queries, ${gesamt} s` +
        (gZeit === null ? '' : ` (davon Glossar ${gZeit} s = ${e['Glossar %']}, ` +
         `${e.Begriffe} Begriffe, Fallback ${e.Fallback})`) +
        `, ${peak}, ${e['Groesse (KB)']} KB, ${links} Links, ${elemente} Elemente`);
    return e;
  }

  log(`Messe je ${CONFIG.durchlaeufe} Durchlaeufe …`);
  const zumMessen = verzeichnisse.map((v, i) =>
    [`a${verzeichnisse.length > 1 ? i + 1 : ''}) Verz. ${v.titel}`, v.pfad]);
  zumMessen.push(['b) Skriptenseite', CONFIG.skriptenseite]);
  zumMessen.push(['c) Startseite',    CONFIG.startseite]);

  const zeilen = [];
  for (const [name, pfad] of zumMessen) {
    const r = await miss(name, pfad);
    if (r) zeilen.push(r);
  }
  if (!zeilen.length) { warn('Keine Messwerte erhalten. Abbruch.'); return; }

  // Gegenprobe: ohne Parameter darf nichts ausgegeben werden
  const ohne = await hole(verzeichnisse[0].pfad, false);
  const gegenprobe = ohne.treffer
    ? 'FEHLGESCHLAGEN - SC-PERF erscheint auch OHNE ?sc_perf=1'
    : 'bestanden (ohne ?sc_perf=1 keine Ausgabe)';
  (ohne.treffer ? warn : ok)('Gegenprobe: ' + gegenprobe);

  const seitenZahl  = await restZaehler('pages', 'status=publish');
  const glossarZahl = await restZaehler('glossar');

  console.table(zeilen.map(({ Einzelwerte, ...rest }) => rest));

  const block = [
    '--- MESSWERTE ---',
    'Zeitpunkt: ' + new Date().toLocaleString('de-DE'),
    'Theme-Version: ' + themeVersion,
    '',
    ...zeilen.map(z =>
      `${z.Seite.padEnd(24)} queries=${String(z.Queries).padStart(4)}  ` +
      `time=${String(z['Zeit (s)']).padStart(7)}s  glossar=${String(z['davon Glossar (s)']).padStart(7)}s` +
      ` (${z['Glossar %']}, ${z.Begriffe} Begriffe, Fallback ${z.Fallback})  ` +
      `peak=${z.Speicher}  groesse=${z['Groesse (KB)']} KB  links=${z.Links}  ` +
      `elemente=${z['HTML-Elemente']}   (${z.Einzelwerte})`),
    '',
    'Verzeichnisseiten: ' + verzeichnisse.map(v => `${v.titel} (${v.pfad})`).join(' | '),
    'Pfad Skriptenseite:      ' + CONFIG.skriptenseite,
    'Veroeffentlichte Seiten: ' + seitenZahl,
    'Glossareintraege:        ' + glossarZahl,
    'Gegenprobe ohne Parameter: ' + gegenprobe,
    '--- ENDE ---',
  ].join('\n');

  console.log(block);
  try {
    await navigator.clipboard.writeText(block);
    ok('In die Zwischenablage kopiert - du kannst es direkt einfuegen.');
  } catch (e) {
    log('Zwischenablage nicht verfuegbar. Bitte den Textblock oben markieren und kopieren.');
  }
})();
