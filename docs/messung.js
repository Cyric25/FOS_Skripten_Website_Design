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

  const RE = /<!--\s*SC-PERF\s+queries=(\d+)\s+time=([\d.]+)s\s+peak=(.+?)\s*-->/;
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

  if (!CONFIG.inhaltsverzeichnis) {
    // Nur Seiten der OBERSTEN Ebene kommen als Inhaltsverzeichnis in Frage.
    // Eine tief verschachtelte Seite namens "Übersicht" ist etwas anderes.
    const oberste = seiten.filter(s => s.parent === 0);
    const passend = oberste.filter(s => /inhalt|verzeichnis|übersicht|ubersicht|skripten/i.test(titelVon(s)));

    if (passend.length === 1) {
      CONFIG.inhaltsverzeichnis = pfadVon(passend[0]);
      log(`Inhaltsverzeichnis erkannt: "${titelVon(passend[0])}" -> ${CONFIG.inhaltsverzeichnis}`);
    } else {
      warn('Inhaltsverzeichnis konnte nicht eindeutig bestimmt werden.');
      if (passend.length > 1) {
        warn('Mehrere Kandidaten auf oberster Ebene:');
        console.table(passend.map(s => ({ Titel: titelVon(s), Pfad: pfadVon(s) })));
      }
      warn('Alle Seiten der obersten Ebene:');
      console.table(oberste.map(s => ({ Titel: titelVon(s), Pfad: pfadVon(s) })));
      warn('Bitte oben bei CONFIG.inhaltsverzeichnis den Pfad eintragen und erneut ausfuehren.');
      return;
    }
  }

  if (!CONFIG.skriptenseite) {
    const kandidat = seiten.find(s => s.parent > 0 && pfadVon(s) !== CONFIG.inhaltsverzeichnis);
    if (!kandidat) { warn('Keine Unterseite gefunden. Bitte CONFIG.skriptenseite eintragen.'); return; }
    CONFIG.skriptenseite = pfadVon(kandidat);
    log(`Skriptenseite gewaehlt: "${titelVon(kandidat)}" -> ${CONFIG.skriptenseite}`);
  }

  // ===================================================================
  // SCHRITT 3 - Messung
  // ===================================================================
  const median = (werte) => [...werte].sort((a, b) => a - b)[Math.floor(werte.length / 2)];

  async function miss(name, pfad) {
    const queries = [], zeiten = [], groessen = [];
    let peak = '?';
    for (let i = 0; i < CONFIG.durchlaeufe; i++) {
      const r = await hole(pfad);
      if (r.status !== 200) { warn(`${name}: HTTP ${r.status} bei ${pfad}`); return null; }
      if (!r.treffer) { warn(`${name}: kein SC-PERF bei ${pfad} (Seite laedt, Ausgabe fehlt)`); return null; }
      queries.push(parseInt(r.treffer[1], 10));
      zeiten.push(parseFloat(r.treffer[2]));
      peak = r.treffer[3];
      groessen.push(r.bytes);
    }
    const e = {
      Seite: name,
      Pfad: pfad,
      Queries: median(queries),
      'Zeit (s)': median(zeiten),
      Speicher: peak,
      'Groesse (KB)': (median(groessen) / 1024).toFixed(1),
      Einzelwerte: `q:[${queries}] t:[${zeiten}]`,
    };
    log(`${name}: ${e.Queries} Queries, ${e['Zeit (s)']} s, ${peak}, ${e['Groesse (KB)']} KB`);
    return e;
  }

  log(`Messe je ${CONFIG.durchlaeufe} Durchlaeufe …`);
  const zeilen = [];
  for (const [name, pfad] of [
    ['a) Inhaltsverzeichnis', CONFIG.inhaltsverzeichnis],
    ['b) Skriptenseite',      CONFIG.skriptenseite],
    ['c) Startseite',         CONFIG.startseite],
  ]) {
    const r = await miss(name, pfad);
    if (r) zeilen.push(r);
  }
  if (!zeilen.length) { warn('Keine Messwerte erhalten. Abbruch.'); return; }

  // Gegenprobe: ohne Parameter darf nichts ausgegeben werden
  const ohne = await hole(CONFIG.inhaltsverzeichnis, false);
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
      `time=${String(z['Zeit (s)']).padStart(7)}s  peak=${z.Speicher}  ` +
      `groesse=${z['Groesse (KB)']} KB   (${z.Einzelwerte})`),
    '',
    'Pfad Inhaltsverzeichnis: ' + CONFIG.inhaltsverzeichnis,
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
