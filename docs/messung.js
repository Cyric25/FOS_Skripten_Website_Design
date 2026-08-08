/* ===================================================================
   Messskript fuer AP-1.2 / AP-1.6 / AP-5.2  (PLAN-Seitenindex.md)

   ANWENDUNG
   1. Als Administrator auf der Website anmelden.
   2. Irgendeine Seite der Website oeffnen (wichtig: gleiche Domain).
   3. Entwicklerwerkzeuge oeffnen (F12) -> Reiter "Konsole".
   4. Dieses gesamte Skript hineinkopieren und mit Enter ausfuehren.

   Das Skript ruft jede Seite dreimal ohne Cache ab, liest den
   SC-PERF-Kommentar aus und gibt am Ende einen fertigen Textblock aus,
   den du mir einfach zurueckschicken kannst.
   =================================================================== */

(async () => {
  // --- Konfiguration -----------------------------------------------
  // Pfade eintragen. Leer lassen = das Skript versucht, sie selbst zu finden.
  const CONFIG = {
    inhaltsverzeichnis: '',   // z. B. '/inhaltsverzeichnis/'
    skriptenseite:      '',   // eine gewoehnliche Seite mit Fließtext
    startseite:         '/',
    durchlaeufe:        3,
  };
  // -----------------------------------------------------------------

  const RE = /<!--\s*SC-PERF\s+queries=(\d+)\s+time=([\d.]+)s\s+peak=(.+?)\s*-->/;
  const log = (...a) => console.log('%c[Messung]', 'color:#e24614;font-weight:bold', ...a);

  const url = (pfad, mitParam) => {
    const u = new URL(pfad, location.origin);
    if (mitParam) u.searchParams.set('sc_perf', '1');
    u.searchParams.set('_cb', Math.random().toString(36).slice(2));
    return u.toString();
  };

  async function hole(pfad, mitParam = true) {
    const antwort = await fetch(url(pfad, mitParam), {
      cache: 'no-store',
      credentials: 'same-origin',
    });
    const text = await antwort.text();
    return {
      status: antwort.status,
      bytes: new TextEncoder().encode(text).length,
      treffer: text.match(RE),
    };
  }

  const median = (werte) => [...werte].sort((a, b) => a - b)[Math.floor(werte.length / 2)];

  async function miss(name, pfad) {
    const queries = [], zeiten = [], groessen = [];
    let peak = '?';
    for (let i = 0; i < CONFIG.durchlaeufe; i++) {
      const r = await hole(pfad);
      if (r.status !== 200) { log(`FEHLER ${name}: HTTP ${r.status} bei ${pfad}`); return null; }
      if (!r.treffer) {
        log(`FEHLER ${name}: kein SC-PERF gefunden.`);
        log('  Moegliche Ursachen: Theme v1.5.69 noch nicht hochgeladen, ' +
            'nicht als Administrator angemeldet, oder ein Cache liefert die Seite aus.');
        return null;
      }
      queries.push(parseInt(r.treffer[1], 10));
      zeiten.push(parseFloat(r.treffer[2]));
      peak = r.treffer[3];
      groessen.push(r.bytes);
    }
    const ergebnis = {
      Seite: name,
      Pfad: pfad,
      Queries: median(queries),
      'Zeit (s)': median(zeiten),
      Speicher: peak,
      'Groesse (KB)': (median(groessen) / 1024).toFixed(1),
      Einzelwerte: `q:[${queries}] t:[${zeiten}]`,
    };
    log(`${name}: ${ergebnis.Queries} Queries, ${ergebnis['Zeit (s)']} s, ${peak}, ${ergebnis['Groesse (KB)']} KB`);
    return ergebnis;
  }

  // --- Pfade bestimmen ---------------------------------------------
  async function restZaehler(typ, extra = '') {
    try {
      const a = await fetch(`/wp-json/wp/v2/${typ}?per_page=1&${extra}`, { credentials: 'same-origin' });
      return a.headers.get('X-WP-Total') || '?';
    } catch (e) { return '?'; }
  }

  async function findeSeiten() {
    const a = await fetch('/wp-json/wp/v2/pages?per_page=100&status=publish&_fields=id,link,title,parent',
                          { credentials: 'same-origin' });
    if (!a.ok) return [];
    return a.json();
  }

  log('Starte. Ermittle Seiten …');
  const seiten = await findeSeiten();

  if (!CONFIG.inhaltsverzeichnis) {
    const kandidat = seiten.find(s => /inhalt|verzeichnis|übersicht|ubersicht/i.test(s.title?.rendered || ''));
    if (kandidat) {
      CONFIG.inhaltsverzeichnis = new URL(kandidat.link).pathname;
      log(`Inhaltsverzeichnis automatisch erkannt: "${kandidat.title.rendered}" -> ${CONFIG.inhaltsverzeichnis}`);
    } else {
      log('Inhaltsverzeichnis NICHT gefunden. Bitte oben bei CONFIG.inhaltsverzeichnis den Pfad eintragen.');
      log('Verfuegbare Seiten (erste 20):');
      console.table(seiten.slice(0, 20).map(s => ({ Titel: s.title?.rendered, Pfad: new URL(s.link).pathname })));
      return;
    }
  }

  if (!CONFIG.skriptenseite) {
    // Eine tief liegende Seite nehmen - die hat erfahrungsgemaess echten Fließtext.
    const kandidat = seiten.find(s => s.parent > 0 && new URL(s.link).pathname !== CONFIG.inhaltsverzeichnis);
    if (kandidat) {
      CONFIG.skriptenseite = new URL(kandidat.link).pathname;
      log(`Skriptenseite automatisch gewaehlt: "${kandidat.title.rendered}" -> ${CONFIG.skriptenseite}`);
    } else {
      log('Keine Unterseite gefunden. Bitte CONFIG.skriptenseite eintragen.');
      return;
    }
  }

  // --- Messung ------------------------------------------------------
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

  if (!zeilen.length) { log('Keine Messwerte erhalten. Abbruch.'); return; }

  // --- Gegenprobe: ohne Parameter darf nichts ausgegeben werden -----
  const ohne = await hole(CONFIG.inhaltsverzeichnis, false);
  const gegenprobe = ohne.treffer
    ? 'FEHLGESCHLAGEN - SC-PERF erscheint auch OHNE ?sc_perf=1'
    : 'bestanden (ohne ?sc_perf=1 keine Ausgabe)';
  log('Gegenprobe: ' + gegenprobe);

  // --- Zaehler ------------------------------------------------------
  const seitenZahl  = await restZaehler('pages', 'status=publish');
  const glossarZahl = await restZaehler('glossar');

  // --- Ausgabe ------------------------------------------------------
  console.table(zeilen.map(({ Einzelwerte, ...rest }) => rest));

  const block = [
    '--- MESSWERTE ---',
    'Zeitpunkt: ' + new Date().toLocaleString('de-DE'),
    'Theme-Version: ' + (document.querySelector('link[href*="style.css?ver="]')?.href.split('ver=')[1] || 'unbekannt'),
    '',
    ...zeilen.map(z =>
      `${z.Seite.padEnd(24)} queries=${String(z.Queries).padStart(4)}  ` +
      `time=${String(z['Zeit (s)']).padStart(7)}s  peak=${z.Speicher}  ` +
      `groesse=${z['Groesse (KB)']} KB   (${z.Einzelwerte})`),
    '',
    'Veroeffentlichte Seiten: ' + seitenZahl,
    'Glossareintraege:        ' + glossarZahl,
    'Gegenprobe ohne Parameter: ' + gegenprobe,
    '--- ENDE ---',
  ].join('\n');

  console.log(block);
  try {
    await navigator.clipboard.writeText(block);
    log('In die Zwischenablage kopiert - du kannst es direkt einfuegen.');
  } catch (e) {
    log('Zwischenablage nicht verfuegbar. Bitte den Textblock oben markieren und kopieren.');
  }
})();
