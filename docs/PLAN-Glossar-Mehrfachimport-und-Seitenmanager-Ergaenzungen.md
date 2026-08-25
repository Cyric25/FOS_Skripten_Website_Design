# Projektplan: Glossar-Mehrfachimport (Auto-Scan) und Seitenmanager-Ergänzungen

_Erstellt am: 2026-08-25 · Letzte Aktualisierung: 2026-08-25 (Phase 1 abgeschlossen)_

## 0. Anweisungen für den ausführenden Agenten

Du arbeitest nach diesem Plan. Er ist die einzige Wahrheitsquelle – du hast
keinen Zugriff auf das Gespräch, in dem er entstand. Halte dich an diese Regeln:

**Rollen und Modelle:**
A. Wird die Abarbeitung von einem Orchestrator koordiniert (Opus), gilt:
   Der Orchestrator delegiert APs an Subagenten und implementiert NIEMALS
   selbst. Er gibt jedem Subagenten nur dessen AP-Text plus die Abschnitte
   0–3 dieses Plans als Kontext, prüft jede Rückmeldung gegen die
   Akzeptanzkriterien des APs, bevor er abhängige APs freigibt, und pflegt
   die Statustabelle.
B. Jedes AP nennt sein Ausführungsmodell (**Modell:** sonnet | opus).
   Subagenten mit genau diesem Modell starten – Sonnet für klar
   vorgezeichnete Umsetzung, Opus wo Urteilsvermögen gefragt ist.
C. Unabhängige APs derselben Phase (keine gemeinsamen Abhängigkeiten,
   disjunkte Dateien) dürfen parallel bearbeitet werden – in Claude Code
   idealerweise in getrennten Git-Worktrees mit je eigenem Branch.
   APs, die dieselben Dateien ändern, nie parallel ausführen. **In diesem
   Plan gilt das für alle APs beider Phasen: Phase 1 ändert ausschließlich
   `Theme/functions.php`, Phase 2 ausschließlich
   `Theme/includes/admin/page-manager.php` — innerhalb jeder Phase daher
   strikt sequenziell arbeiten, nicht parallelisieren.**

**Arbeitsweise:**
1. Bearbeite genau EIN Arbeitspaket (AP) pro Auftrag, sofern nicht anders beauftragt.
2. Prüfe vor Beginn die Abhängigkeiten deines APs in der Statustabelle
   (Abschnitt 8). Sind sie nicht ☑, brich ab und melde das.
3. Setze deinen AP-Status auf ◐ (in Arbeit), bevor du beginnst.
4. Bleibe strikt im Scope des APs. Fällt dir Verbesserungspotenzial außerhalb
   auf, notiere es in der Übergabenotiz – setze es nicht um.
5. Beachte die Nicht-Ziele (Abschnitt 2) und Constraints (Abschnitt 3).

**Tests (Pflicht, ein AP ohne bestandene Tests ist nicht fertig):**
6. Nach Abschluss: alle Akzeptanzkriterien einzeln nachweisen + die im AP
   definierten Tests durchführen.
7. Sieht das AP TDD vor: Tests zuerst schreiben, Fehlschlag bestätigen,
   rote Tests committen, dann implementieren bis grün. **Tests niemals
   abändern, damit sie bestehen.** Hältst du einen Test für inhaltlich
   falsch, dokumentiere das in der Übergabenotiz und stoppe – die
   Entscheidung liegt beim Nutzer/Orchestrator. (Dieser Plan enthält keine
   TDD-APs — reines WordPress-Admin-PHP ohne vorhandenes Testframework,
   siehe Abschnitt 3.)
8. Ergebnis ins Testprotokoll (Abschnitt 9) eintragen.
9. Erst dann Status auf ☑. Bei Fehlschlag: Status ✗ (blockiert), Ursache in
   die Übergabenotiz, nicht mit abhängigen APs weitermachen.
10. Nach dem letzten Implementierungs-AP einer Phase zusätzlich:
    Integrationstest der Phase + Regressionscheck aller vorherigen Phasen
    (deren „lauffähiger Endzustand" muss weiterhin funktionieren).
    Eintrag ins Testprotokoll.
11. Danach folgt das Review-AP (`AP-<N>.rev`): Es wird von einem frischen
    Agenten ausgeführt, der KEINES der APs dieser Phase implementiert hat.
    Der Review-Agent arbeitet ausschließlich lesend und verändert keine
    Datei. Kritische Befunde führen zu Korrektur-APs (siehe Regel 15);
    die Phase ist erst danach abgeschlossen.

**Übergabe:**
12. Fülle die Übergabenotiz deines APs aus: was geändert wurde, getroffene
    Entscheidungen, was für Folge-APs relevant ist.
13. Hat dein AP Dateien angelegt, verschoben oder wesentlich geändert:
    aktualisiere deren Zeilen in `Theme/reference_file_map.md` (Datei |
    Zweck | wichtige Funktionen | Abhängigkeiten). Die umfassende
    Projektdokumentation wird im Dokumentations-AP am Phasenende
    (`AP-<N>.doc`) nachgezogen.
14. Aktualisiere „Letzte Aktualisierung" im Dateikopf dieses Plans.
15. Git: mindestens ein Commit mit AP-ID im Text, z. B.
    `AP-1.2: Auto-Scan nach Import gekoppelt`. Nach jedem abgeschlossenen
    AP den Phasen-Branch zum Remote pushen (`git push -u origin <branch>`)
    – das Remote (`https://github.com/Cyric25/FOS_Skripten_Website_Design`)
    ist das Backup des Fortschritts. Phasen-Branches erst nach bestandenem
    Integrationstest UND Review in `main` mergen, danach ebenfalls pushen.
16. **Vor jedem Abschluss (AP, Review, Merge):** Syntax-Check aller
    geänderten PHP-Dateien mit `php -l <datei>` — Projektstandard laut
    `Theme/CLAUDE.md`. Kein Merge nach `main` mit fehlgeschlagenem
    Syntax-Check.

**Umplanung:**
17. Zeigt sich während der Ausführung, dass der Plan nicht trägt (Review-
    Befunde, blockierte APs, falsche Annahmen), werden Korrektur-APs mit
    fortlaufender Nummer ergänzt (`AP-<N>.fix1`, …) und in Statustabelle
    und Testprotokoll aufgenommen. Bestehende APs und Übergabenotizen
    werden nie gelöscht, nur ergänzt – der Plan bleibt nachvollziehbare
    Historie.

## 1. Projektziel

Der Glossar-CSV-Import im Theme „FOS Online Schulbuch" akzeptiert mehrere
gleichzeitig ausgewählte CSV-Dateien statt nur einer; nach Abschluss aller
Importe läuft der bestehende Bulk-Scan automatisch und ohne Rückfrage genau
einmal. Zusätzlich hängt der Seitenmanager neu angelegte Seiten immer ans
Ende ihrer Geschwisterseiten an, und eine neue Bulk-Aktion macht die
bestehende Einzelseiten-Funktion „Für Navigation sperren" für mehrere
Seiten gleichzeitig nutzbar.

## 2. Nicht-Ziele

- Kein Umbau der bestehenden Einzeldatei-Importlogik (Zeilenverarbeitung,
  Duplikaterkennung) – sie wird wiederverwendet, nicht neu geschrieben.
- Kein neuer Bulk-Scan-Mechanismus – der bestehende AJAX-Batch-Ablauf
  (`glossar_bulk_scan` / `glossar_bulk_scan_batch`) wird unverändert
  wiederverwendet, nur der Auslöser ändert sich.
- Der bestehende manuelle Button „Alle Seiten jetzt scannen" (inkl.
  `confirm()`-Dialog) wird NICHT entfernt und bleibt unverändert nutzbar.
- Keine Änderung an `_simple_clean_hide_navigation` (Bulk-Aktionen
  `hide_nav`/`show_nav`) und keine Bulk-Aktion für
  `_simple_clean_hide_from_sidebar` (fünfte Checkbox „Nicht in der
  Seitenleiste anzeigen") – beide bleiben unangetastet, sie sind NICHT
  Gegenstand dieses Plans.
- Kein Eingriff in `ajax_update_order()` (Drag&Drop-Sortierung) – nur die
  Neuanlage in `ajax_create_page()` wird geändert.
- Keine neuen Fremdbibliotheken, kein neuer Build-Schritt, keine
  Datenbank-Migration (keine Schemaänderung, nur bestehende Post-Meta-Felder
  und `menu_order`).
- Kein automatisiertes Testframework wird neu eingeführt.

## 3. Kontext & Constraints

- **Umgebung:** WordPress-Theme „FOS Online Schulbuch"
  (`Theme/`), PHP 7.4+, WordPress 5.0+. Kein Build-Schritt für reine
  PHP-Änderungen nötig; `npm run build` nur falls JS/CSS geändert wird
  (in diesem Plan nicht der Fall — Teil A ändert nur inline-`<script>` in
  `functions.php`, kein Vite-Bundle betroffen).
- **Bestehende Konventionen:** `Theme/CLAUDE.md` (Funktionsübersicht
  Glossar-System, Admin-Werkzeuge) und `Theme/reference_file_map.md`
  (Datei-Map) sind maßgeblich und aktuell zu halten. Namensschema
  `simple_clean_*`. Nonce-Pattern: `check_admin_referer()`/
  `wp_nonce_field()` für klassische POST-Formulare,
  `check_ajax_referer()` für AJAX. Rechteprüfung `current_user_can()`
  wie im jeweiligen bestehenden Kontext (`manage_options` für die
  Glossar-Einstellungsseite, `edit_pages`/`edit_page($id)` im
  Seitenmanager). Fehler werden gesammelt statt beim ersten Fehler
  abzubrechen (bestehendes Muster, siehe `page-manager.php`). Bei
  Bulk-Aktionen: Werte aus `$_POST` werden NIE in einen Methodennamen
  übersetzt, sondern nur gegen eine Whitelist geprüft
  (`bulk_aktionen()`).
- **Harte Grenzen:** Keine externen Libraries. PHP-Server-Limits
  (`upload_max_filesize`, `post_max_size`, `max_file_uploads`) begrenzen
  Anzahl/Größe gleichzeitig hochladbarer CSV-Dateien serverseitig – das ist
  eine bekannte, akzeptierte Einschränkung, kein Bug.
- **Testumgebung:** Kein automatisierter Test-Harness für Glossar-Import
  oder Seitenmanager vorhanden (nur `Theme/tools/test-sichtbarkeit.php`
  für ein anderes Subsystem, hier nicht relevant). Tests laufen manuell:
  primär `php -l` (Syntax) lokal, funktionale Prüfung auf dem lokalen
  Testserver `fos.localhost:8080` (WordPress-Installation mit beiden
  Plugins als Kopie), falls dieser erreichbar ist. Ist der Testserver in
  der Ausführungsumgebung nicht erreichbar, sind die im AP formulierten
  Code-Prüfschritte (Syntax-Check, Logiknachvollzug am Quelltext,
  `php -r`-Kurzskripte für isolierte Funktionslogik) durchzuführen und das
  Fehlen des Live-Tests explizit im Testprotokoll zu vermerken.
- **Git-Strategie:** Branch pro Phase (`phase-1-glossar-mehrfachimport`,
  `phase-2-seitenmanager-ergaenzungen`), Commit pro AP mit AP-ID im Text.
  Nach jedem AP Push zum Remote. Merge nach `main` erst nach bestandenem
  Phasen-Integrationstest und Review.
- **Remote-Repository:** `https://github.com/Cyric25/FOS_Skripten_Website_Design.git`
  (bereits verbunden, Branch `main`, Stand beim Planungszeitpunkt: sauberer
  Arbeitsbaum, letzter Commit `94e315d`). Keine Einrichtung nötig.

## 4. Architekturentscheidungen

| Entscheidung | Begründung | Verworfene Alternative |
|---|---|---|
| Mehrfachdatei-Import: pro Datei ein „virtuelles" `$_FILES`-Element rekonstruieren und die bestehende Ein-Datei-Logik aus `simple_clean_handle_glossar_import()` unverändert je Datei aufrufen | Kleinster Eingriff, maximale Wiederverwendung der bereits korrekten Zeilenverarbeitung/Duplikaterkennung/Fehlerbehandlung; das Verhalten bei genau einer Datei bleibt exakt identisch zum Ist-Zustand | Die Funktion komplett auf eine neue, generische Multi-Datei-Signatur umschreiben — höheres Regressionsrisiko für den (häufigsten) Einzeldatei-Fall, kein zusätzlicher Nutzen |
| `$existing_posts`-Array wird über alle Dateien einer Import-Sitzung hinweg weitergereicht (einmal vor der Schleife geladen, nach jeder Datei um neu angelegte Posts ergänzt) | Verhindert, dass derselbe Begriff aus zwei verschiedenen CSV-Dateien im selben Durchlauf doppelt angelegt wird — Fortsetzung des bereits innerhalb einer Datei bestehenden Musters | Für jede Datei einen frischen `get_posts()`-Aufruf — unnötige zusätzliche Datenbankabfragen und lässt dateiübergreifende Duplikate durchrutschen |
| Auto-Scan wird durch dieselbe bestehende JS-Funktion `startBulkScan()` ausgelöst, nur der Aufrufweg ändert sich (automatisch statt nur per Klick), Bedingung: `imported + updated > 0` über alle Dateien summiert | Wiederverwendung des bereits funktionierenden, gebatchten AJAX-Ablaufs samt Fortschrittsanzeige; unnötiger Scan-Lauf ohne echte Änderung wird vermieden | Eigenen zweiten Scan-Trigger-Pfad bauen — Code-Duplikat, zwei parallel zu pflegende Abläufe |
| Neue Seiten (Teil B): `menu_order` wird vor `wp_insert_post()` per gezielter `$wpdb`-Abfrage auf den höchsten vorhandenen Wert unter den Geschwistern + 1 gesetzt, statt hartcodiert 0 — einheitlich für Unterseiten und Seiten auf oberster Ebene (`parent_id = 0` ist nur ein Sonderfall desselben Codepfads) | Ein einziger Codepfad für beide Fälle vermeidet Sonderbehandlung und ist konsistent mit dem bestätigten Nutzerwunsch | Getrennte Logik für Unterseiten vs. oberste Ebene — unnötige Verzweigung für identisches gewünschtes Verhalten |
| Neue Bulk-Aktion „Für Navigation sperren" (Teil C) exakt nach dem Muster von `hide_index`/`show_index`: Whitelist-Eintrag, `switch`-Fall, `<option>` in bestehender Optgroup „Sichtbarkeit" | Strukturell identische Aufgabe zu vier bereits vorhandenen Aktionspaaren — Musterübernahme statt neuer Lösung, keine JS-Änderung nötig | Eigene UI/eigenen Endpunkt bauen — unnötig, das generische Bulk-Aktions-Muster deckt den Fall bereits ab |

## 5. Risiken & Rollback

| Risiko | Wahrscheinlichkeit | Auswirkung | Gegenmaßnahme / Rollback |
|---|---|---|---|
| `$_FILES`-Array-Struktur bei `multiple`-Attribut unterscheidet sich strukturell von der bisherigen (verschachtelte Arrays statt Skalare) und wird falsch rekonstruiert | mittel | hoch (Import könnte fehlschlagen oder falsche Datei verarbeiten) | AP-1.1 verlangt explizit einen Test mit genau einer UND mit mehreren Dateien vor Abschluss; bei Fehlschlag Status ✗ und Ursache dokumentieren, nicht mit AP-1.2 weitermachen |
| Auto-Scan startet doppelt (einmal automatisch, einmal weil der Nutzer zusätzlich den manuellen Button klickt) | gering | mittel (unnötige doppelte Serverlast, kein Datenverlust da Scan idempotent) | AP-1.2 verlangt als Akzeptanzkriterium, dass der automatische Trigger die Scan-Controls (`#bulk-scan-controls`) während des Laufs ausblendet, genau wie der manuelle Pfad es bereits tut |
| Reihenfolge-Fix bei `ajax_create_page()` verwendet eine Race-Condition-anfällige Max-Abfrage bei sehr schneller Doppelanlage (zwei Anfragen gleichzeitig lesen denselben Max-Wert) | gering | gering (im Zweifel identischer `menu_order`-Wert bei zwei Seiten, keine Datenkorruption, per Drag&Drop im Seitenmanager jederzeit korrigierbar) | Als bekannte, akzeptierte Einschränkung im Doku-AP vermerken, kein Blocker für diesen Plan |
| Neue Bulk-Aktion verwechselt `_simple_clean_nav_gesperrt` mit dem strukturell ähnlichen, aber anderen Meta `_simple_clean_hide_navigation` | mittel | hoch (falsches Meta würde eine andere, bereits produktive Funktion verändern) | AP-2.2 nennt den exakten Meta-Key `_simple_clean_nav_gesperrt` wörtlich und verweist auf die Abgrenzung in Abschnitt 2 (Nicht-Ziele); AP-2.rev prüft diesen Punkt explizit |

**Generelle Rollback-Strategie:** Branch pro Phase, Commit pro AP. Bei
Fehlschlag eines APs: `git checkout -- <geänderte Datei>` bzw. `git revert`
des betreffenden Commits, nie direkt auf `main` arbeiten. Da keine
Datenbank-Schemaänderung stattfindet, ist kein DB-Dump nötig — im
Zweifel genügt das Zurücksetzen des PHP-Codes.

## 6. Phasenübersicht

Jede Phase endet mit `AP-<N>.rev` (unabhängiges Review) und `AP-<N>.doc` (Dokumentation) – in dieser Reihenfolge nach den Implementierungs-APs.

| Phase | Ziel | Lauffähiger Endzustand | APs |
|---|---|---|---|
| 1 | Glossar-CSV-Import akzeptiert mehrere Dateien, Bulk-Scan läuft danach automatisch einmal | Admin lädt 1..n CSV-Dateien gleichzeitig hoch, alle werden importiert (Fehler je Datei gemeldet, andere trotzdem verarbeitet), danach läuft der Scan automatisch ohne Klick; der manuelle Scan-Button funktioniert weiterhin unverändert | AP-1.1, AP-1.2, AP-1.rev, AP-1.doc |
| 2 | Seitenmanager: neue Seiten landen zuverlässig am Ende, „Für Navigation sperren" ist als Bulk-Aktion nutzbar | Neu angelegte Seite (egal ob oberste Ebene oder Unterseite) erscheint immer als letztes Geschwister; mehrere ausgewählte Seiten lassen sich per Bulk-Aktion für die Navigation sperren/entsperren | AP-2.1, AP-2.2, AP-2.rev, AP-2.doc |

## 7. Arbeitspakete

### Phase 1: Glossar-Mehrfachimport mit Auto-Scan

### AP-1.1: Mehrfachdatei-CSV-Import im Backend

**Status:** ☑ erledigt
**Umfang:** M
**Modell:** opus
**Abhängigkeiten:** keine

**Ziel & Kontext:**
Der Glossar-CSV-Import in `Theme/functions.php` verarbeitet aktuell genau
eine Datei pro Formular-Absendung. Die Funktion
`simple_clean_glossar_settings_page()` (Zeile ~3666) rendert das
Import-Formular mit `<input type="file" name="glossar_csv" accept=".csv" required>`
(ohne `multiple`, Zeile ~3808) und ruft bei `isset($_POST['glossar_import'])`
(Zeile ~3673) einmalig `simple_clean_handle_glossar_import()` (Zeile ~1136)
auf, die `$_FILES['glossar_csv']` als Skalar liest (`$_FILES['glossar_csv']['error']`,
`['name']`, `['tmp_name']`) und ein einzelnes Ergebnis-Array
(`success`, `imported`, `updated`, `skipped`, `errors`) zurückgibt. Diese
Funktion lädt vor der Zeilenverarbeitung einmalig alle bestehenden
Glossar-Posts in `$existing_posts` (Zeile ~1180-1185) und reicht dieses
Array an `simple_clean_glossar_term_exists_or_similar($term, $existing_posts)`
weiter; neu angelegte Posts werden dem Array direkt angehängt
(Zeile ~1285-1288), damit Duplikate auch innerhalb derselben Datei erkannt
werden. Ziel: Das Datei-Input akzeptiert mehrere gleichzeitig ausgewählte
CSV-Dateien; das Backend verarbeitet sie nacheinander, wobei Duplikate auch
DATEIÜBERGREIFEND erkannt werden (dasselbe `$existing_posts`-Array läuft
über alle Dateien der Sitzung weiter), und meldet das Ergebnis je Datei.
Fehlerhafte einzelne Dateien (falsches Format, Lesefehler) werden
übersprungen, die übrigen Dateien werden trotzdem importiert.

**Betroffene Dateien:**
- `Theme/functions.php` (ändern)

**Vorgehen:**
1. In `simple_clean_glossar_settings_page()`: Am `<input type="file" ...>`
   (Zeile ~3808) das Attribut `multiple` ergänzen, damit der Browser
   Mehrfachauswahl erlaubt. Name bleibt `glossar_csv` — PHP liefert dann
   `$_FILES['glossar_csv']['name']`, `['tmp_name']`, `['error']`,
   `['size']`, `['type']` jeweils als indiziertes Array (ein Eintrag pro
   hochgeladener Datei) statt als Skalar.
2. Neue Funktion `simple_clean_handle_glossar_import_multi()` in
   `functions.php` anlegen (z. B. direkt vor
   `simple_clean_handle_glossar_import()`), die:
   a. Die Berechtigungsprüfung (`current_user_can('manage_options')`)
      einmal zentral durchführt (wie am Anfang der bestehenden
      Ein-Datei-Funktion) und bei fehlender Berechtigung sofort
      `array('success' => false, 'message' => '...')` zurückgibt.
   b. Prüft, ob `$_FILES['glossar_csv']` gesetzt ist und mindestens ein
      Element enthält; sonst Fehlermeldung wie im Ist-Zustand
      („Keine Datei hochgeladen oder Upload-Fehler.").
   c. Die bestehenden `$existing_posts` EINMAL vor der Schleife lädt
      (identische `get_posts()`-Query wie in der bestehenden Funktion,
      Zeile ~1181-1185).
   d. Für jeden Index `$i` in `$_FILES['glossar_csv']['name']` ein
      "virtuelles" Einzeldatei-`$_FILES`-Element zusammenbaut:
      `array('name' => $_FILES['glossar_csv']['name'][$i], 'type' => ...[$i], 'tmp_name' => ...[$i], 'error' => ...[$i], 'size' => ...[$i])`.
   e. Die bestehende Funktion `simple_clean_handle_glossar_import()` NICHT
      komplett neu schreiben, sondern so umbauen, dass sie zwei zusätzliche,
      optionale Parameter akzeptiert: `simple_clean_handle_glossar_import(array $file = null, array &$existing_posts = null)`.
      Ist `$file` `null`, verhält sie sich exakt wie bisher (liest aus dem
      globalen `$_FILES['glossar_csv']`, lädt `$existing_posts` selbst) —
      das sichert Rückwärtskompatibilität für den Fall, dass die Funktion
      anderswo unverändert aufgerufen wird. Ist `$file` gesetzt, verwendet
      sie dieses Array anstelle von `$_FILES['glossar_csv']`, und ist
      `$existing_posts` als Referenz übergeben, nutzt und erweitert sie
      dieses Array statt es selbst neu zu laden (die bestehende
      Anhänge-Logik in Zeile ~1285-1288 bleibt dabei erhalten, wirkt jetzt
      nur auf die durchgereichte Referenz).
   f. Für jede Datei `simple_clean_handle_glossar_import($virtuellesFile, $existing_posts)`
      aufruft und das Einzelergebnis unter dem ursprünglichen Dateinamen
      (`$_FILES['glossar_csv']['name'][$i]`) in einem Sammel-Array
      `$ergebnisse` ablegt. Schlägt eine Datei komplett fehl (z. B. falsches
      Format, `success => false`), wird das im Sammel-Array vermerkt und mit
      der nächsten Datei fortgefahren (nicht abbrechen).
   g. Am Ende ein Gesamtergebnis zurückgibt:
      `array('success' => true, 'dateien' => $ergebnisse, 'imported_gesamt' => ..., 'updated_gesamt' => ..., 'skipped_gesamt' => ..., 'sollScannen' => ($imported_gesamt + $updated_gesamt) > 0)`
      — die Summen werden aus den Einzelergebnissen aller Dateien
      aufaddiert.
3. Die Aufrufstelle in `simple_clean_glossar_settings_page()`
   (Zeile ~3673-3697) auf `simple_clean_handle_glossar_import_multi()`
   umstellen. Die `admin_notices`-Ausgabe erweitern: pro Datei eine Zeile
   mit Dateiname, importiert/aktualisiert/übersprungen-Zahlen und ggf.
   Fehlerliste dieser Datei (bestehendes Layout als Vorlage nehmen, nur je
   Datei wiederholen statt einmal global).
4. Das Gesamtergebnis (`sollScannen`, `imported_gesamt`, `updated_gesamt`)
   muss für AP-1.2 im selben Seitenaufruf verfügbar sein — als PHP-Variable
   im Scope von `simple_clean_glossar_settings_page()` ablegen (z. B.
   `$glossar_import_ergebnis`), aus der AP-1.2 später den Auto-Scan-Trigger
   in den `<script>`-Block einspeist. In diesem AP nur die Variable
   bereitstellen, den JS-Teil NICHT anfassen (das ist AP-1.2).

**Akzeptanzkriterien:**
- [ ] `php -l Theme/functions.php` läuft ohne Fehler.
- [ ] Das Datei-Input trägt das Attribut `multiple`.
- [ ] Upload von genau EINER CSV-Datei über das Formular liefert exakt das
      gleiche Ergebnis (importierte/aktualisierte/übersprungene Anzahl) wie
      vor der Änderung.
- [ ] Upload von ZWEI CSV-Dateien gleichzeitig, bei denen ein Begriff in
      beiden Dateien identisch vorkommt, führt dazu, dass der Begriff nur
      EINMAL angelegt wird (dateiübergreifende Duplikaterkennung
      funktioniert).
- [ ] Upload von zwei Dateien, von denen eine ein ungültiges Format hat
      (z. B. `.txt` umbenannt in `.csv` mit kaputtem Inhalt) oder eine leere
      Datei ist, führt dazu, dass die andere, gültige Datei trotzdem
      vollständig importiert wird und der Fehler nur für die betroffene
      Datei gemeldet wird.
- [ ] Die zurückgegebene Struktur enthält `sollScannen` korrekt: `true`,
      wenn über alle Dateien hinweg mindestens ein Begriff importiert oder
      aktualisiert wurde, sonst `false`.

**Tests:**
- Smoke-Test: Ist der lokale Testserver `fos.localhost:8080` erreichbar,
  dort als Administrator zu „Glossar → Einstellungen" navigieren, zwei
  kleine Test-CSV-Dateien (je 2-3 Zeilen, UTF-8, Semikolon-getrennt, Spalten
  `Begriff;Definition;Slug;Status`) hochladen und die
  `admin_notices`-Meldung prüfen: pro Datei eine eigene Zusammenfassung,
  keine PHP-Fatal-Errors/Warnings im Seitenquelltext oder `debug.log`.
- Ist der Testserver nicht erreichbar: `php -r`-Kurzskript schreiben, das
  `simple_clean_handle_glossar_import_multi` NICHT direkt aufrufen kann
  (WordPress-Funktionen fehlen ohne WP-Bootstrap) — stattdessen den
  Code-Pfad manuell am Quelltext nachvollziehen (jede Vorgehens-Unterzeile
  1-4 einzeln gegen den geänderten Code abgleichen) und das Ergebnis in der
  Übergabenotiz festhalten; den fehlenden Live-Test explizit im
  Testprotokoll vermerken.
- Regressionsrelevanz: Einzeldatei-Import ist der heute produktiv genutzte
  Weg — muss unverändert funktionieren, siehe drittes Akzeptanzkriterium.

**Übergabenotiz:**
Umgesetzt wie geplant, mit einer Abweichung von der Vorgehensbeschreibung:
`simple_clean_handle_glossar_import()` behält ihre Signatur nicht per
separatem Default-Parameter bei, sondern wurde direkt um zwei optionale
Parameter erweitert: `function simple_clean_handle_glossar_import($file = null, &$existing_posts = null)`.
Ohne Argumente verhält sie sich exakt wie vorher (liest aus
`$_FILES['glossar_csv']`, lädt `$existing_posts` selbst). Der interne
Dateihandle wurde von `$file` in `$handle` umbenannt, da `$file` jetzt der
Parametername für das `$_FILES`-Array ist (Namenskollision vermieden).
`simple_clean_handle_glossar_import_multi()` baut pro Datei ein virtuelles
`$_FILES`-Element und reicht `$existing_posts` per Referenz durch alle
Dateien weiter. `$glossar_import_ergebnis` ist als lokale Variable in
`simple_clean_glossar_settings_page()` verfügbar (`sollScannen`,
`imported_gesamt`/`updated_gesamt`/`skipped_gesamt`, `dateien`-Array je
Datei) — direkt nutzbar für AP-1.2 im selben Funktions-Scope.

**Testnachweis (kein Zugriff auf Admin-Login des lokalen Testservers in
dieser Session, daher Stub-Harness statt Browser-Test — laut PLAN.md
Abschnitt 3 zulässiger Fallback):** `php -l functions.php` fehlerfrei.
Zusätzlich ein Stub-Harness-Testskript geschrieben, das die vier
betroffenen Funktionen wörtlich aus `functions.php` extrahiert und gegen
gestubbte WordPress-Funktionen (`get_posts`, `wp_insert_post`,
`wp_update_post`, `get_post`, `current_user_can`, `is_wp_error`) ausführt
— damit wird der ECHTE, geänderte Code ausgeführt, nicht nur gelesen.
Vier Testfälle, alle bestanden: (1) Einzeldatei-Import liefert identisches
Ergebnis wie vor der Änderung (2 importiert, 0 Fehler); (2) zwei Dateien
mit einem dateiübergreifenden Duplikat („Proton" in beiden CSVs) legen den
Begriff nur einmal an (`imported_gesamt=3`, `skipped_gesamt=1`,
`sollScannen=true`); (3) eine leere/fehlerhafte Datei neben einer gültigen
lässt die gültige trotzdem vollständig importieren
(`imported_gesamt=1`, `success=true`); (4) ein Import, der ausschließlich
einen bereits vorhandenen Begriff als Duplikat überspringt, liefert
`sollScannen=false`. Live-UI-Test auf `fos.localhost:8080` (Formular,
`admin_notices`-Darstellung) konnte in dieser Session mangels
WP-Admin-Zugangsdaten nicht durchgeführt werden — als offene Lücke
vermerkt, sollte vor Produktiv-Deployment nachgeholt werden.

---

### AP-1.2: Automatischer Bulk-Scan nach Mehrfach-Import

**Status:** ☑ erledigt
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-1.1 (liefert `$glossar_import_ergebnis['sollScannen']`
im PHP-Scope von `simple_clean_glossar_settings_page()`)

**Ziel & Kontext:**
`Theme/functions.php` enthält in `simple_clean_glossar_settings_page()`
einen inline `<script>`-Block (Zeile ~3867-3974), der u. a. die Funktion
`startBulkScan()` definiert (ruft AJAX-Aktion `glossar_bulk_scan` auf, dann
gebatcht `glossar_bulk_scan_batch` bis fertig, mit Fortschrittsbalken
`#bulk-scan-progress`/`#progress-bar`). Ausgelöst wird `startBulkScan()`
bisher NUR durch den Klick-Handler auf `#start-bulk-scan`
(Zeile ~3873-3890), der zuerst einen `confirm()`-Dialog zeigt. Ziel: Direkt
nach einem erfolgreichen Mehrfach-Import (AP-1.1 liefert dafür
`$glossar_import_ergebnis['sollScannen'] === true`) soll `startBulkScan()`
automatisch aufgerufen werden — OHNE den `confirm()`-Dialog zu durchlaufen
und ohne dass der Nutzer klicken muss. Der bestehende manuelle Button samt
`confirm()` bleibt zusätzlich unverändert nutzbar.

**Betroffene Dateien:**
- `Theme/functions.php` (ändern)

**Vorgehen:**
1. Im PHP-Teil von `simple_clean_glossar_settings_page()`: Direkt vor dem
   `<script>`-Block (oder an geeigneter Stelle davor) eine JavaScript-Variable
   ausgeben, die den Auto-Scan-Wunsch aus PHP nach JS transportiert, z. B.:
   ```php
   <?php $auto_scan = (isset($glossar_import_ergebnis) && !empty($glossar_import_ergebnis['sollScannen'])) ? 'true' : 'false'; ?>
   <script>
   var glossarAutoScan = <?php echo $auto_scan; ?>;
   </script>
   ```
   (Platzierung: vor dem bestehenden `jQuery(document).ready(...)`-Block,
   damit die Variable dort verfügbar ist.)
2. Im bestehenden `jQuery(document).ready(function($) { ... })`-Block
   (Zeile ~3868): nach der Definition von `startBulkScan()`,
   `processBatch()`, `showResult()`, `resetUI()` (diese Funktionen NICHT
   verändern) am Ende des `ready()`-Blocks ergänzen:
   ```javascript
   if (glossarAutoScan) {
       $('#bulk-scan-controls').hide();
       $('#bulk-scan-progress').show();
       $('#bulk-scan-result').hide();
       $('#progress-bar').css('width', '0%');
       startBulkScan();
   }
   ```
   Das ist bewusst dieselbe UI-Vorbereitung wie im bestehenden Klick-Handler
   (Zeile ~3878-3889), nur ohne den vorangestellten `confirm()`-Aufruf und
   ohne Bindung an ein Klick-Event — der Code läuft beim Laden der Seite
   sofort, wenn `glossarAutoScan === true`.
3. Sicherstellen, dass bei `glossarAutoScan === true` der manuelle
   Klick-Handler auf `#start-bulk-scan` nicht zusätzlich ausgelöst wird
   (er ist ohnehin nur an ein `click`-Event gebunden, es besteht daher kein
   Konflikt — als Prüfschritt trotzdem in den Tests unten verifizieren).

**Akzeptanzkriterien:**
- [ ] `php -l Theme/functions.php` läuft ohne Fehler.
- [ ] Nach einem Import, bei dem mindestens ein Begriff neu angelegt oder
      aktualisiert wurde, startet der Bulk-Scan automatisch beim
      Neuladen der Einstellungsseite (Fortschrittsbalken erscheint ohne
      Klick, kein `confirm()`-Dialog erscheint).
- [ ] Nach einem Import, bei dem ausschließlich Zeilen als Duplikate
      übersprungen wurden (kein `imported`/`updated` über alle Dateien),
      startet KEIN automatischer Scan.
- [ ] Ohne vorherigen Import (normaler Aufruf der Einstellungsseite) läuft
      kein automatischer Scan.
- [ ] Der manuelle Button „🚀 Alle Seiten jetzt scannen" funktioniert
      weiterhin unverändert inkl. `confirm()`-Dialog, wenn er unabhängig
      von einem Import angeklickt wird.

**Tests:**
- Smoke-Test (auf `fos.localhost:8080`, falls erreichbar): Eine CSV-Datei
  mit einem neuen Begriff importieren → Seite lädt neu → Fortschrittsbalken
  erscheint automatisch, kein Klick nötig, kein `confirm()`-Popup; nach
  Abschluss zeigt `#bulk-scan-result` die Erfolgsmeldung, Seite lädt nach
  3 Sekunden erneut (bestehendes Verhalten aus `showResult()`).
- Zweiter Testlauf: Dieselbe CSV-Datei ein zweites Mal importieren (jetzt
  alles Duplikate, kein Overwrite aktiviert) → kein automatischer
  Scan-Start, `#bulk-scan-controls` bleibt sichtbar mit dem manuellen
  Button.
- Dritter Testlauf: Auf der Einstellungsseite (ohne Import) manuell auf
  „🚀 Alle Seiten jetzt scannen" klicken → `confirm()`-Dialog erscheint wie
  bisher, nach Bestätigung startet der Scan wie bisher.
- Ist der Testserver nicht erreichbar: Den JS-Code am Quelltext gegen alle
  vier Akzeptanzkriterien durchgehen (insbesondere: `glossarAutoScan` wird
  korrekt aus PHP befüllt, der `if`-Block ruft exakt dieselbe
  UI-Vorbereitung wie der Klick-Handler auf, aber ohne `confirm()`) und das
  Ergebnis in der Übergabenotiz sowie im Testprotokoll als
  Code-Nachvollzug statt Live-Test kennzeichnen.

**Übergabenotiz:**
Umgesetzt exakt wie im Vorgehen beschrieben: `glossarAutoScan`-Variable
direkt vor dem bestehenden `<script>`-Block ausgegeben, Auto-Start-Block
als letzte Anweisung im `jQuery(document).ready()`-Callback (nach
`resetUI()`, vor dem schließenden `});`), ruft dieselbe UI-Vorbereitung
wie der Klick-Handler auf, aber ohne `confirm()` und ruft `startBulkScan()`
direkt auf statt ein Klick-Event zu simulieren.

**Testnachweis (kein Admin-Login verfügbar, daher kein Live-Browser-Test –
laut PLAN.md Abschnitt 3 zulässiger Fallback):** `php -l functions.php`
fehlerfrei. Die PHP-Ausdrucksformel für `$auto_scan` wurde isoliert mit
`php -r` für alle drei relevanten Fälle ausgeführt: `sollScannen=true` →
`glossarAutoScan = true`; `sollScannen=false` (nur Duplikate) →
`glossarAutoScan = false`; `$glossar_import_ergebnis` nicht gesetzt (kein
Import stattgefunden) → `glossarAutoScan = false`. Der gesamte
`<script>`-Block wurde zusätzlich per Node.js (`new Function(js)` nach
Ersetzen der `<?php ... ?>`-Tags durch Platzhalter) auf reine
JavaScript-Syntaxfehler geprüft — fehlerfrei. Der bestehende
Klick-Handler auf `#start-bulk-scan` wurde nicht verändert (nur Code nach
`resetUI()` ergänzt) — Diff bestätigt, dass sein `confirm()`-Aufruf
unangetastet blieb. Live-UI-Test (tatsächliches Auslösen im Browser ohne
Klick, Zusammenspiel mit dem manuellen Button in derselben Sitzung)
konnte mangels WP-Admin-Zugangsdaten nicht durchgeführt werden — als
offene Lücke vermerkt, siehe auch AP-1.1.

---

### AP-1.fix1: Kritischer Fund aus AP-1.rev beheben — fehlendes `[]` am Datei-Feldnamen

**Status:** ☑ erledigt
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-1.1, AP-1.2

**Ziel & Kontext:**
Das unabhängige Review (AP-1.rev) fand einen kritischen, empirisch
bestätigten Fehler: Das Datei-Input in
`simple_clean_glossar_settings_page()` trug nach AP-1.1 das Attribut
`multiple`, aber der `name`-Attributwert blieb `glossar_csv` ohne die für
PHP-Mehrfach-Uploads erforderliche `[]`-Notation
(`name="glossar_csv[]"`). Ohne diese Notation liefert PHP
`$_FILES['glossar_csv']['name']` bei EINER ausgewählten Datei als
Skalar-String und bei MEHREREN Dateien überschreibt der Browser das Feld
so, dass am Ende ebenfalls kein Array ankommt. Der Guard in
`simple_clean_handle_glossar_import_multi()`
(`!is_array($_FILES['glossar_csv']['name'])`) griff dadurch bei JEDER
echten Formular-Absendung, auch bei genau einer Datei — der komplette
CSV-Import war dadurch unbenutzbar, ein vollständiger Rückschritt
gegenüber dem Vor-AP-1.1-Zustand. Der Fund war nur deshalb im
Stub-Harness-Test (AP-1.1) nicht aufgefallen, weil dort direkt
handgebaute, bereits array-förmige `$file`-Strukturen an die Funktionen
übergeben wurden, statt den echten Weg über ein HTML-Formular und PHPs
`$_FILES`-Befüllung zu durchlaufen.

**Betroffene Dateien:**
- `Theme/functions.php` (ändern)

**Vorgehen:**
1. In `simple_clean_glossar_settings_page()`, am Datei-Input: `name="glossar_csv"` durch `name="glossar_csv[]"` ersetzen (Attribut `multiple` bleibt erhalten).
2. Keine weiteren Code-Änderungen nötig — die Rekonstruktions-Schleife in `simple_clean_handle_glossar_import_multi()` und die `$existing_posts`-Referenzweitergabe waren laut Review bereits korrekt und werden durch die Namensänderung erstmals tatsächlich erreicht.
3. Verifikation ausdrücklich NICHT wieder nur mit handgebauten `$_FILES`-Fixtures durchführen, sondern über einen echten HTTP-Request (`php -S` + `curl -F "glossar_csv[]=@datei.csv"`), damit die reale PHP-`$_FILES`-Befüllung mitgetestet wird — genau der Teil, der den Fehler in AP-1.1 verdeckt hatte.

**Akzeptanzkriterien:**
- [ ] `php -l Theme/functions.php` läuft ohne Fehler.
- [ ] Ein echter HTTP-Multipart-POST mit genau einer Datei unter dem Feldnamen `glossar_csv[]` liefert ein `$_FILES['glossar_csv']['name']`-Array mit einem Element (nicht mehr Skalar) und der Import gelingt (`success: true`, Begriffe importiert).
- [ ] Derselbe Test mit zwei Dateien liefert ein Array mit zwei Elementen, beide Dateien werden verarbeitet, dateiübergreifende Duplikate werden weiterhin nur einmal angelegt.
- [ ] Derselbe Test mit einer Datei mit ungültiger Endung neben einer gültigen Datei: die ungültige Datei liefert `success: false` für sich, die gültige Datei wird trotzdem importiert (Gesamtergebnis `success: true`).
- [ ] Alle bereits in AP-1.1 formulierten Akzeptanzkriterien sind jetzt über einen ECHTEN HTTP-Request nachgewiesen, nicht nur über den Stub-Harness mit synthetischen Arrays.

**Tests:**
- Genuiner HTTP-Test: Ein kleiner PHP-Endpunkt (stubt dieselben WordPress-Funktionen wie der AP-1.1-Stub-Harness, lädt aber die vier betroffenen Funktionen wörtlich aus `functions.php` und ruft `simple_clean_handle_glossar_import_multi()` direkt auf Basis des echten `$_FILES` aus dem eingehenden Request auf) wird mit `php -S 127.0.0.1:<port> <endpunkt>.php` gestartet. Gegen ihn werden per `curl -F "glossar_csv[]=@a.csv" ...` echte multipart/form-data-Requests geschickt — für (a) eine Datei, (b) zwei Dateien mit einem dateiübergreifenden Duplikat, (c) eine Datei mit falscher Endung neben einer gültigen. Die JSON-Antwort wird gegen die Akzeptanzkriterien geprüft.
- Nach bestandenem Test: erneuter kurzer Blick auf `simple_clean_handle_glossar_import()`s Fallback-Zweig (`$file === null` → liest direkt `$_FILES['glossar_csv']`): Dieser Zweig wird im Theme nirgends mehr ohne Argumente aufgerufen (einziger verbleibender Aufruf ist `simple_clean_handle_glossar_import_multi()`, immer mit `$virtuelle_datei`) — der Fallback bleibt als Absicherung bestehen, ist aber toter Code im aktuellen Aufrufgraphen; das ist bewusst so belassen (Rückwärtskompatibilität für künftige direkte Aufrufe) und kein neuer Fehler, da niemand ihn mit der neuen `[]`-Feldform aufruft.

**Übergabenotiz:**
Fix angewendet: `name="glossar_csv"` → `name="glossar_csv[]"` in
`simple_clean_glossar_settings_page()` (Zeile mit dem `<input
type="file">`). Keine weiteren Code-Änderungen nötig.

Verifiziert über einen echten HTTP-Endpunkt (`php -S` + `curl -F`, nicht
nur Stub-Arrays): (a) eine Datei per `curl -F "glossar_csv[]=@einzel.csv"`
→ `$_FILES['glossar_csv']['name']` korrekt als einelementiges Array,
Import gelingt (`imported: 2`); (b) zwei Dateien mit demselben Begriff
(„Proton") in beiden → `imported_gesamt: 3`, `skipped_gesamt: 1`,
Duplikat dateiübergreifend erkannt; (c) eine Datei mit `.txt`-Endung neben
einer gültigen `.csv`-Datei → die `.txt`-Datei liefert `success: false`
für sich, die gültige Datei wird trotzdem mit `imported: 2` verarbeitet,
Gesamtergebnis `success: true`. Alle drei Fälle bestanden. `php -l`
weiterhin fehlerfrei.

Der Fallback-Zweig in `simple_clean_handle_glossar_import()` für
`$file === null` bleibt unverändert bestehen; er wird im aktuellen
Theme-Code nirgends mehr direkt (ohne Argumente) aufgerufen, ist also
totes Absicherungscode — bewusst nicht entfernt (Rückwärtskompatibilität
für künftige Aufrufer), aber als Hinweis für AP-1.doc festgehalten.

---

### AP-1.rev: Unabhängiges Review Phase 1

**Status:** ☑ erledigt
**Umfang:** M
**Modell:** opus
**Abhängigkeiten:** AP-1.1, AP-1.2 (inkl. Phasen-Integrationstest)

**Ziel & Kontext:**
Unabhängige Qualitätsprüfung der Phase 1 (Glossar-Mehrfachimport +
Auto-Scan) durch einen Agenten, der an keiner Implementierung beteiligt
war. Nur lesend arbeiten (Read/Grep/Glob bzw. Dateien ansehen) – KEINE
Datei verändern.

**Vorgehen:**
1. `Theme/functions.php` lesen: `simple_clean_handle_glossar_import()`,
   `simple_clean_handle_glossar_import_multi()`,
   `simple_clean_glossar_settings_page()` (inkl. des `<script>`-Blocks).
2. Gegen jedes Akzeptanzkriterium von AP-1.1 und AP-1.2 prüfen (Stichproben
   im Quelltext, nicht nur die Übergabenotizen glauben): insbesondere ob
   der Einzeldatei-Fall wirklich unverändert bleibt, ob
   `$existing_posts` korrekt als Referenz über alle Dateien weiterläuft, ob
   `sollScannen` korrekt aus den Summen berechnet wird, ob der
   `confirm()`-Dialog beim Auto-Trigger tatsächlich nicht erscheint.
3. Sicherheitscheck: Ist die Berechtigungsprüfung
   (`current_user_can('manage_options')`) in
   `simple_clean_handle_glossar_import_multi()`/der angepassten
   `simple_clean_handle_glossar_import()` weiterhin vorhanden und wird sie
   in jedem Codepfad erreicht? Wird `$_FILES`-Input weiterhin korrekt
   validiert (Dateiendung, Upload-Error-Code) für JEDE Datei der
   Mehrfachauswahl, nicht nur die erste?
4. Scope-Check: Wurde `_simple_clean_hide_navigation`,
   `_simple_clean_hide_from_sidebar` oder der manuelle Scan-Button entgegen
   den Nicht-Zielen (Abschnitt 2) verändert? Wurde
   `glossar_bulk_scan`/`glossar_bulk_scan_batch` selbst angetastet (sollte
   unverändert sein)?
5. Befunde als Review-Bericht in die Übergabenotiz: je Befund Schweregrad
   (kritisch / mittel / gering), betroffenes AP, Datei und Fundstelle
   (Zeilennummer).

**Akzeptanzkriterien:**
- [ ] AP-1.1 und AP-1.2 wurden jeweils gegen ihre Akzeptanzkriterien
      geprüft.
- [ ] Alle Befunde mit Schweregrad, Datei und Fundstelle dokumentiert.
- [ ] Keine Datei wurde verändert.

**Tests:**
- entfällt (Review-AP; das Ergebnis ist der Bericht).

**Übergabenotiz:**
Review durch einen unabhängigen, ausschließlich lesenden Agenten
durchgeführt (Explore-Subagent ohne Schreibrechte, keine Datei
verändert). `php -l functions.php` zur Kontrolle erneut ausgeführt:
fehlerfrei.

**Kritischer Befund (1):** Das Datei-Input in
`simple_clean_glossar_settings_page()` trug nach AP-1.1 zwar `multiple`,
aber der Feldname blieb `glossar_csv` ohne `[]`. PHP befüllt
`$_FILES['glossar_csv']['name']` dadurch bei jeder echten
Formular-Absendung als Skalar, nie als Array — der Guard in
`simple_clean_handle_glossar_import_multi()` griff dadurch IMMER, auch
bei genau einer Datei. **Kompletter Rückschritt gegenüber dem
Vor-AP-1.1-Zustand**, empirisch bestätigt (nicht nur plausibel vermutet)
durch einen echten `php -S`-Testserver mit multipart/form-data-POSTs.
Dies ist exakt das in Abschnitt 5 der PLAN.md vorab benannte Risiko
(„`$_FILES`-Array-Struktur … wird falsch rekonstruiert“). Ursache, warum
es im AP-1.1-Stub-Harness nicht auffiel: Dort wurden `$file`-Arrays
direkt handgebaut und übergeben, statt den echten Weg über ein
HTML-Formular und PHPs eigene `$_FILES`-Befüllung zu durchlaufen — der
eigentliche Fehler lag genau in diesem ungetesteten Übergang.
→ Korrektur-AP **AP-1.fix1** angelegt und abgeschlossen (siehe oben).

**Verifiziert, kein Fehler:** `$existing_posts`-Referenzweitergabe über
mehrere Dateien hinweg korrekt implementiert (Zeile ~1346, ~1370,
~1198-1204). Rechteprüfung `current_user_can('manage_options')` auf
jedem erreichbaren Codepfad vorhanden. Datei-Endungs-/Upload-Error-Prüfung
läuft pro Datei einzeln (nicht nur für die erste). `glossar_bulk_scan`/
`glossar_bulk_scan_batch`, `_simple_clean_hide_navigation`,
`_simple_clean_hide_from_sidebar` und der manuelle `confirm()`-Button
wurden nicht angetastet — kein Scope-Verstoß.

**Geringfügiger, nicht blockierender Hinweis:** Die Hilfetexte auf der
Einstellungsseite nennen an einer vorbestehenden (nicht von diesem Plan
verursachten) Stelle „Komma“ als Trennzeichen, während der Parser fest
Semikolon verwendet — vorbestehende Doku-Ungenauigkeit, nicht Teil des
Scopes von AP-1.1/1.2, nur zur Vollständigkeit vermerkt.

**Ergebnis:** Ein kritischer Befund, behoben in AP-1.fix1 (siehe dort für
den vollständigen, über echte HTTP-Requests geführten Verifikationsnachweis).
Kein weiterer Korrektur-Bedarf.

---

### AP-1.doc: Dokumentation Phase 1 aktualisieren

**Status:** ☑ erledigt
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-1.rev

**Ziel & Kontext:**
`Theme/CLAUDE.md` und `Theme/reference_file_map.md` auf den Stand nach
Phase 1 bringen, damit das Projekt ohne Kenntnis dieses Plans erweiterbar
bleibt. Root-`DOKUMENTATION.md` wird bewusst NICHT in diesem AP geändert
(erfolgt gesammelt für Phase 1+2 in AP-2.doc, siehe dort) — sie führt pro
abgeschlossenem Vorhaben genau einen Eintrag, dieser Plan ist ein
Vorhaben.

**Betroffene Dateien:**
- `Theme/CLAUDE.md` (ändern)
- `Theme/reference_file_map.md` (ändern)

**Vorgehen:**
1. In `Theme/CLAUDE.md`, Abschnitt „Glossar-System" (unter
   „Funktionsübersicht functions.php"), den Satz zu
   „Admin-Seite `simple_clean_glossar_settings_page()` ... mit
   CSV-Import/-Export und Bulk-Scan" ergänzen: CSV-Import akzeptiert seit
   diesem Plan mehrere gleichzeitig ausgewählte Dateien
   (`simple_clean_handle_glossar_import_multi()`), Duplikate werden
   dateiübergreifend erkannt, fehlerhafte Einzeldateien werden übersprungen
   statt den gesamten Import abzubrechen. Ergänzen, dass der Bulk-Scan
   nach einem Import mit mindestens einer echten Änderung automatisch und
   ohne Rückfrage einmalig läuft (Bedingung: `imported + updated > 0`
   über alle Dateien), der manuelle Button weiterhin unverändert
   funktioniert.
2. In `Theme/reference_file_map.md`, Zeile zu `functions.php`
   (aktuell Zeile 12): den Beschreibungstext um einen Satz zum
   Mehrfachimport (neue Funktion
   `simple_clean_handle_glossar_import_multi()`) und zur
   Auto-Scan-Kopplung (`glossarAutoScan`-Variable im inline-Script)
   ergänzen.
3. „Stand"-Datum in `Theme/reference_file_map.md` (Zeile 3) auf das
   Datum dieses APs aktualisieren.
4. „Letzte Aktualisierung" im Kopf dieser `PLAN.md` aktualisieren.

**Akzeptanzkriterien:**
- [ ] `Theme/CLAUDE.md` beschreibt den Mehrfachimport und die
      Auto-Scan-Kopplung im Abschnitt „Glossar-System" korrekt und aktuell.
- [ ] `Theme/reference_file_map.md`-Zeile zu `functions.php` erwähnt
      `simple_clean_handle_glossar_import_multi()` und die
      Auto-Scan-Kopplung.
- [ ] Kein Verweis in der aktualisierten Dokumentation zeigt auf nicht mehr
      existierende Funktionen (Stichprobe: `simple_clean_handle_glossar_import()`
      existiert weiterhin, wird korrekt als Basis für Einzeldatei UND
      Mehrfachimport beschrieben).

**Tests:**
- Stichprobe: Die neu beschriebene Funktion
  `simple_clean_handle_glossar_import_multi()` im tatsächlichen
  `functions.php`-Quelltext suchen und Signatur/Verhalten gegen die
  Dokumentationsbeschreibung abgleichen.

**Übergabenotiz:**
`Theme/CLAUDE.md`, Abschnitt „Glossar-System": Nach der Zeile zur
Admin-Seite `simple_clean_glossar_settings_page()` zwei neue Absätze
ergänzt — Mehrfachdatei-Import (`simple_clean_handle_glossar_import_multi()`,
Pflicht-Feldname `glossar_csv[]`, dateiübergreifende Duplikaterkennung,
Hinweis auf den in AP-1.fix1 behobenen kritischen Fehler) und
Auto-Scan-Kopplung (`sollScannen`, `glossarAutoScan`, manueller Button
bleibt bestehen). `Theme/reference_file_map.md`: Zeile zu `functions.php`
um denselben Sachverhalt kompakt ergänzt, „Stand"-Datum auf 2026-08-25 und
Theme-Version auf 1.5.87 aktualisiert (beides war vorher auf
2026-08-23/1.5.81 stehen geblieben).

Stichprobe: `simple_clean_handle_glossar_import_multi()` im Quelltext
gesucht — existiert mit der beschriebenen Signatur (keine Parameter,
liest `$_FILES['glossar_csv']`), Verhalten entspricht der Beschreibung.

### AP-2.1: Neue Seiten immer ans Ende der Geschwisterseiten anhängen

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** keine

**Ziel & Kontext:**
In `Theme/includes/admin/page-manager.php`, Methode
`Simple_Clean_Page_Manager::ajax_create_page()` (Zeile ~534-575), wird eine
neue Seite per `wp_insert_post()` mit hartcodiertem `'menu_order' => 0`
angelegt (Zeile ~558-564) — unabhängig davon, wie viele Geschwisterseiten
unter demselben `post_parent` bereits existieren. Da
`Simple_Clean_Page_Manager::render_admin_page()` die Seiten mit
`get_pages(['sort_column' => 'menu_order, post_title', ...])` sortiert
(Zeile ~132-135), landet eine neue Seite mit `menu_order = 0` je nach Titel
irgendwo unter ihren Geschwistern statt zuverlässig am Ende. Ziel:
Vor dem `wp_insert_post()`-Aufruf den höchsten vorhandenen
`menu_order`-Wert unter den Geschwistern des Ziel-`$parent_id` ermitteln
und die neue Seite mit `menu_order` = dieser Wert + 1 anlegen. Gilt
einheitlich für Unterseiten (`parent_id > 0`) UND Seiten auf oberster
Ebene (`parent_id === 0`) — derselbe Codepfad, keine Fallunterscheidung.

**Betroffene Dateien:**
- `Theme/includes/admin/page-manager.php` (ändern)

**Vorgehen:**
1. In `ajax_create_page()`, nach der bestehenden Prüfung „Verify parent
   exists if specified" (Zeile ~549-555) und vor dem `wp_insert_post()`-Aufruf
   (Zeile ~557-564), folgende Ermittlung ergänzen:
   ```php
   global $wpdb;
   $max_order = $wpdb->get_var($wpdb->prepare(
       "SELECT MAX(menu_order) FROM {$wpdb->posts}
        WHERE post_type = 'page' AND post_parent = %d
        AND post_status IN ('publish', 'draft', 'pending', 'private', 'trash')",
       $parent_id
   ));
   $neuer_menu_order = ($max_order === null) ? 0 : ((int) $max_order + 1);
   ```
   `post_status` bewusst inklusive `'trash'`, damit eine neue Seite nicht
   denselben `menu_order`-Wert wie eine (noch wiederherstellbare)
   papierkorb-Seite erhält und beim Wiederherstellen kollidiert — bewusst
   defensiv, kostet nichts an Korrektheit für den Normalfall.
2. Im `wp_insert_post()`-Aufruf `'menu_order' => 0` durch
   `'menu_order' => $neuer_menu_order` ersetzen.

**Akzeptanzkriterien:**
- [ ] `php -l Theme/includes/admin/page-manager.php` läuft ohne Fehler.
- [ ] Neuanlage einer Unterseite unter einer Elternseite, die bereits
      Geschwister mit `menu_order` 0, 1, 2 hat, ergibt für die neue Seite
      `menu_order = 3`.
- [ ] Neuanlage einer Seite auf oberster Ebene (`parent_id = 0`) verhält
      sich identisch: `menu_order` = höchster vorhandener Wert unter allen
      obersten Seiten + 1.
- [ ] Neuanlage der ERSTEN Seite unter einer bisher kinderlosen Elternseite
      (kein Geschwister vorhanden) ergibt `menu_order = 0` (Verhalten bei
      leerer Menge unverändert zum Ist-Zustand).
- [ ] Nach der Neuanlage erscheint die neue Seite im Seitenmanager-Baum
      (`render_admin_page()`, sortiert nach `menu_order, post_title`) als
      LETZTES Element unter ihren Geschwistern, unabhängig von ihrem Titel.

**Tests:**
- Smoke-Test (auf `fos.localhost:8080`, falls erreichbar): Im
  Seitenmanager eine Elternseite mit mind. zwei vorhandenen Unterseiten
  wählen, über „Unterseite erstellen" (Button `.create-child-page`) eine
  neue Seite mit einem alphabetisch früh einsortierenden Titel (z. B.
  „AAA Test") anlegen → nach dem automatischen Reload muss sie trotz des
  Titels als LETZTE Unterseite erscheinen, nicht an erster Stelle.
- Zweiter Testlauf: „Neue Seite" auf oberster Ebene mit ebenfalls
  alphabetisch früh einsortierendem Titel anlegen → muss als letzte Seite
  auf oberster Ebene erscheinen.
- Dritter Testlauf: Eine neue, bisher leere Elternseite anlegen und direkt
  danach deren erste Unterseite → `menu_order` dieser ersten Unterseite ist
  0 (per Datenbank-Blick, z. B. `wp_posts.menu_order` in phpMyAdmin, oder
  indirekt: sie erscheint korrekt einsortiert, da sie die einzige ist).
- Ist der Testserver nicht erreichbar: Den geänderten Code gegen die vier
  Akzeptanzkriterien am Quelltext nachvollziehen (insbesondere den
  `$wpdb->prepare()`-Aufruf auf korrekte Platzhalter und den
  `null`-Fallback bei leerer Geschwistermenge prüfen) und das Fehlen des
  Live-Tests im Testprotokoll vermerken.
- Regressionsrelevanz: `ajax_update_order()` (Drag&Drop) und
  `ajax_bulk_action()` bleiben von dieser Änderung unberührt — nur die
  Neuanlage ändert sich.

**Übergabenotiz:**
(leer – wird vom ausführenden Agenten nach Abschluss ausgefüllt)

---

### AP-2.2: Neue Bulk-Aktion „Für Navigation sperren"

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-2.1 (gleiche Datei — sequenziell nach AP-2.1
bearbeiten, nicht parallel, um Merge-Konflikte in
`Theme/includes/admin/page-manager.php` zu vermeiden; inhaltlich
unabhängig von AP-2.1)

**Ziel & Kontext:**
In `Theme/functions.php` existiert bereits eine Einzelseiten-Checkbox
„Für Navigation sperren" in der Meta-Box „Navigation, Verzeichnis &
Zugriff" (`simple_clean_navigation_meta_box_callback()`, Zeile ~683-708),
die das Post-Meta `_simple_clean_nav_gesperrt` setzt (gespeichert in
`simple_clean_save_navigation_meta()`, Zeile ~807-811). Ist dieses Meta
`'1'`, ist die Seite in Inhaltsverzeichnis und Seitenleiste nicht mehr
anklickbar (ausgewertet in `includes/page-index.php` und `sidebar.php`),
bleibt aber über ihre Adresse erreichbar — KEIN Zugriffsschutz. Für dieses
Meta existiert aktuell KEINE Bulk-Aktion im Seitenmanager
(`Theme/includes/admin/page-manager.php`). Ziel: Ein neues
Bulk-Aktionspaar `lock_nav` (setzt `_simple_clean_nav_gesperrt`) /
`unlock_nav` (löscht es) nach exakt demselben Muster wie das bereits
vorhandene Paar `hide_index`/`show_index` (Meta
`_simple_clean_hide_from_index`) ergänzen.

**WICHTIG — Abgrenzung, nicht verwechseln:**
- `_simple_clean_nav_gesperrt` (dieses AP) ist NICHT dasselbe wie
  `_simple_clean_hide_navigation` (bereits vorhandene Bulk-Aktionen
  `hide_nav`/`show_nav`, Zeile ~820-828 in `page-manager.php` — dieses
  Meta steuert, ob eine Seite BEIM ANSEHEN ihre EIGENE Sidebar zeigt, nicht
  ob sie in fremden Sidebar-Bäumen anklickbar ist).
- `_simple_clean_nav_gesperrt` ist auch NICHT
  `_simple_clean_hide_from_sidebar` (die fünfte Checkbox „Nicht in der
  Seitenleiste anzeigen", nimmt die Seite komplett aus dem Baum — dafür
  wird in diesem Plan explizit KEINE Bulk-Aktion gebaut, siehe Nicht-Ziele
  in Abschnitt 2 der PLAN.md).
- Beide bestehenden, ähnlich klingenden Aktionen `hide_nav`/`show_nav`
  bleiben unverändert. Es werden ausschließlich zwei NEUE Schlüssel
  `lock_nav`/`unlock_nav` ergänzt, keine bestehenden geändert.

**Betroffene Dateien:**
- `Theme/includes/admin/page-manager.php` (ändern)

**Vorgehen:**
1. In `bulk_aktionen()` (Zeile ~45-58) nach dem Eintrag
   `'show_nav' => 'Wieder in Seitenleiste aufnehmen',` zwei neue Einträge
   ergänzen:
   ```php
   'lock_nav'       => 'Für Navigation sperren',
   'unlock_nav'     => 'Wieder für Navigation freigeben',
   ```
2. In `ajax_bulk_action()`, im `switch ($aktion)`-Block
   (Zeile ~739-844), nach dem bestehenden `case 'show_nav':`-Block
   (Zeile ~825-828) zwei neue `case`-Blöcke ergänzen, exakt nach dem Muster
   von `hide_index`/`show_index` (Zeile ~810-818):
   ```php
   case 'lock_nav':
       update_post_meta($id, '_simple_clean_nav_gesperrt', '1');
       $geaendert++;
       break;

   case 'unlock_nav':
       delete_post_meta($id, '_simple_clean_nav_gesperrt');
       $geaendert++;
       break;
   ```
3. In `render_admin_page()`, im `<select id="page-bulk-action">`
   (Zeile ~182-204), innerhalb der bestehenden
   `<optgroup label="Sichtbarkeit">` (Zeile ~191-196), nach der Option
   `<option value="show_nav">Wieder in Seitenleiste aufnehmen</option>`
   zwei neue Optionen ergänzen:
   ```html
   <option value="lock_nav">Für Navigation sperren</option>
   <option value="unlock_nav">Wieder für Navigation freigeben</option>
   ```
4. Die `$reload`-Zeile (Zeile ~848,
   `in_array($aktion, ['status_publish', 'status_draft', 'trash', 'set_parent'], true)`)
   NICHT ändern — `lock_nav`/`unlock_nav` sollen wie `hide_index`/
   `show_index` NICHT in dieser Liste stehen (reine Meta-Aktion, kein
   sichtbarer Baum-Wechsel, der ein Neuladen braucht).
5. Keine Änderung an `Theme/src/js/page-manager.js` nötig — die Bulk-Aktion
   läuft über das bereits vorhandene generische
   `#page-bulk-action`-Select/AJAX-Muster (`fuehreBulkAus()`), das jeden
   Wert aus der Whitelist automatisch unterstützt.

**Akzeptanzkriterien:**
- [ ] `php -l Theme/includes/admin/page-manager.php` läuft ohne Fehler.
- [ ] `bulk_aktionen()` enthält die zwei neuen Schlüssel `lock_nav` und
      `unlock_nav` mit den angegebenen Beschriftungen.
- [ ] Das `<select id="page-bulk-action">` zeigt in der Optgroup
      „Sichtbarkeit" zusätzlich die zwei neuen Optionen.
- [ ] Eine Bulk-Aktion `lock_nav` auf mehrere ausgewählte Seiten setzt bei
      JEDER ausgewählten Seite das Post-Meta `_simple_clean_nav_gesperrt`
      auf `'1'` (nachprüfbar z. B. per `get_post_meta()` oder direkt in
      der Datenbank).
- [ ] Eine Bulk-Aktion `unlock_nav` löscht dieses Meta bei allen
      ausgewählten Seiten wieder.
- [ ] Die bestehenden Aktionen `hide_nav`/`show_nav` (Meta
      `_simple_clean_hide_navigation`) sind durch diese Änderung NICHT
      betroffen — beide Metas bleiben streng getrennt.
- [ ] Nach `lock_nav` ist die betroffene Seite auf der tatsächlichen
      Website im Inhaltsverzeichnis-Block bzw. in der Seitenleiste nicht
      mehr anklickbar, aber weiterhin über ihre direkte Adresse erreichbar
      (Verhalten identisch zur bereits vorhandenen Einzelseiten-Checkbox).

**Tests:**
- Smoke-Test (auf `fos.localhost:8080`, falls erreichbar): Im
  Seitenmanager zwei Seiten per Checkbox auswählen, Bulk-Aktion
  „Für Navigation sperren" wählen und ausführen → Erfolgsmeldung
  erscheint, kein automatisches Neuladen (da nicht in `$reload`-Liste).
  Danach eine der beiden Seiten im Frontend aufrufen (direkte Adresse
  funktioniert weiterhin) und die Seitenleiste/das Inhaltsverzeichnis auf
  einer anderen Seite prüfen: Der Eintrag ist dort nicht mehr anklickbar
  (reiner Text/`<span>` statt Link), Unterbaum bleibt bedienbar
  (Verhalten wie bei der bereits vorhandenen Einzelseiten-Checkbox, siehe
  `Theme/functions.php` Zeile ~693-696).
- Zweiter Testlauf: Dieselben zwei Seiten mit „Wieder für Navigation
  freigeben" zurücksetzen → Einträge sind wieder normal anklickbar.
- Dritter Testlauf: Eine der beiden Seiten hat zusätzlich `hide_nav`
  aktiviert (aus „Aus Seitenleiste ausnehmen") — nach `lock_nav` bleibt
  dieser unabhängige Zustand unverändert (die Seite selbst zeigt weiterhin
  keine eigene Sidebar UND ist zusätzlich nicht anklickbar) — bestätigt,
  dass beide Metas unabhängig voneinander funktionieren.
- Ist der Testserver nicht erreichbar: Code-Nachvollzug aller sechs
  Akzeptanzkriterien am Quelltext (insbesondere: exakter Meta-Key-String
  `_simple_clean_nav_gesperrt` an beiden Stellen — `case`-Block und der
  bereits bestehende Referenzcode in `functions.php` — muss
  zeichengleich sein) und Vermerk im Testprotokoll.
- Regressionsrelevanz: `hide_index`/`show_index`, `hide_nav`/`show_nav`,
  `lock_teacher`/`unlock_teacher`, `set_parent`, `status_publish`,
  `status_draft`, `trash` müssen alle unverändert weiter funktionieren
  (reine Ergänzung im `switch`, keine bestehenden `case`-Blöcke
  angefasst).

**Übergabenotiz:**
(leer – wird vom ausführenden Agenten nach Abschluss ausgefüllt)

---

### AP-2.rev: Unabhängiges Review Phase 2

**Status:** ☐ offen
**Umfang:** M
**Modell:** opus
**Abhängigkeiten:** AP-2.1, AP-2.2 (inkl. Phasen-Integrationstest)

**Ziel & Kontext:**
Unabhängige Qualitätsprüfung der Phase 2 (Seitenmanager-Ergänzungen) durch
einen Agenten, der an keiner Implementierung beteiligt war. Nur lesend
arbeiten (Read/Grep/Glob bzw. Dateien ansehen) – KEINE Datei verändern.

**Vorgehen:**
1. `Theme/includes/admin/page-manager.php` vollständig lesen, insbesondere
   `ajax_create_page()`, `bulk_aktionen()`, `ajax_bulk_action()` und den
   `<select id="page-bulk-action">`-Block in `render_admin_page()`.
2. Gegen jedes Akzeptanzkriterium von AP-2.1 und AP-2.2 prüfen (Stichproben
   im Quelltext, nicht nur Übergabenotizen glauben).
3. **Besonders kritisch prüfen (siehe Risiko in Abschnitt 5 der PLAN.md):**
   Ist der Meta-Key in AP-2.2 wirklich `_simple_clean_nav_gesperrt` und
   NICHT versehentlich `_simple_clean_hide_navigation`? Zeichengleicher
   Abgleich mit `Theme/functions.php`, Zeile ~684-708 (Checkbox-Definition)
   und Zeile ~807-811 (Speicherlogik) nötig.
4. Scope-Check: Wurden `hide_nav`/`show_nav`,
   `_simple_clean_hide_from_sidebar`, `ajax_update_order()` oder andere
   bestehende `case`-Blöcke in `ajax_bulk_action()` entgegen den
   Nicht-Zielen (Abschnitt 2) verändert?
5. Qualitäts-Check: `$wpdb->prepare()` in AP-2.1 korrekt parametrisiert
   (kein String-Concat mit `$parent_id`)? Rechteprüfung je Einzelseite in
   `ajax_bulk_action()` weiterhin vor dem neuen `switch`-Fall vorhanden
   (Zeile ~734-737, gilt für alle `case`-Blöcke gemeinsam, nicht pro Fall
   wiederholt — das ist im Ist-Zustand bereits so und korrekt)?
6. Befunde als Review-Bericht in die Übergabenotiz: je Befund Schweregrad
   (kritisch / mittel / gering), betroffenes AP, Datei und Fundstelle.

**Akzeptanzkriterien:**
- [ ] AP-2.1 und AP-2.2 wurden jeweils gegen ihre Akzeptanzkriterien
      geprüft.
- [ ] Der Meta-Key-Abgleich aus Vorgehen Punkt 3 wurde durchgeführt und
      das Ergebnis dokumentiert.
- [ ] Alle Befunde mit Schweregrad, Datei und Fundstelle dokumentiert.
- [ ] Keine Datei wurde verändert.

**Tests:**
- entfällt (Review-AP; das Ergebnis ist der Bericht).

**Übergabenotiz:**
(leer – wird vom ausführenden Review-Agenten nach Abschluss ausgefüllt:
Review-Bericht mit Befunden je Schweregrad, Datei und Fundstelle,
insbesondere das Ergebnis des Meta-Key-Abgleichs aus Vorgehen Punkt 3)

---

### AP-2.doc: Dokumentation Phase 2 aktualisieren (und Gesamt-Vorhaben)

**Status:** ☐ offen
**Umfang:** S
**Modell:** sonnet
**Abhängigkeiten:** AP-2.rev

**Ziel & Kontext:**
`Theme/CLAUDE.md` und `Theme/reference_file_map.md` auf den Stand nach
Phase 2 bringen. Zusätzlich, da dies das letzte AP des GESAMTEN Plans ist
(Phase 1 + Phase 2), einen zusammenfassenden Eintrag für das gesamte
Vorhaben in der Root-`DOKUMENTATION.md`
(`C:\Users\mtnhu\OneDrive - Bildungsdirektion\#Unterricht\Website\DOKUMENTATION.md`)
ergänzen — dort werden abgeschlossene Vorhaben mit Verweis auf ihre
PLAN-Datei gelistet (siehe bestehende Einträge dort, z. B. „Vorhaben
‚PDF-Export- und Tafelmodus-Fixes'" als Formatvorbild).

**Betroffene Dateien:**
- `Theme/CLAUDE.md` (ändern)
- `Theme/reference_file_map.md` (ändern)
- `C:\Users\mtnhu\OneDrive - Bildungsdirektion\#Unterricht\Website\DOKUMENTATION.md` (ändern)

**Vorgehen:**
1. In `Theme/CLAUDE.md`, Abschnitt „Admin-Werkzeuge (includes/admin/)",
   Unterpunkt zu `page-manager.php`: Die Zahl „acht Aktionen als
   Whitelist" (bzw. den zwischenzeitlich in `reference_file_map.md`
   bereits auf „zehn" aktualisierten Stand, siehe dort Zeile 28) auf
   „zwölf" korrigieren und `lock_nav`/`unlock_nav` (Meta
   `_simple_clean_nav_gesperrt`, „Für Navigation sperren") in der Liste
   ergänzen. Dabei ausdrücklich festhalten, dass dieses Meta eigenständig
   ist und NICHT mit `_simple_clean_hide_navigation` (hinter
   `hide_nav`/`show_nav`) verwechselt werden darf (Formulierung wie in der
   Abgrenzung von AP-2.2 oben).
   Im selben Abschnitt, bei der Beschreibung von `ajax_create_page()`
   (falls dort erwähnt) bzw. als neuer Satz ergänzen: Neue Seiten erhalten
   seit diesem Plan ihren `menu_order` automatisch als höchsten Wert der
   Geschwister + 1, landen also immer als letztes Geschwister — statt
   vorher hartcodiert 0.
2. In `Theme/reference_file_map.md`, Zeile zu
   `includes/admin/page-manager.php` (aktuell Zeile 28, „zehn" Aktionen):
   auf „zwölf" Aktionen aktualisieren, `lock_nav`/`unlock_nav` ergänzen,
   sowie einen Satz zur `menu_order`-Berechnung in `ajax_create_page()`
   ergänzen.
3. „Stand"-Datum in `Theme/reference_file_map.md` erneut aktualisieren.
4. In der Root-`DOKUMENTATION.md`: Am Ende der Datei (nach dem letzten
   bestehenden „Vorhaben"-Absatz) einen neuen Absatz nach demselben Muster
   wie die vorhandenen Einträge ergänzen: Titel „Vorhaben
   ‚Glossar-Mehrfachimport und Seitenmanager-Ergänzungen'" (abgeschlossen,
   Datum dieses APs), Verweis auf
   `Theme/docs/PLAN-Glossar-Mehrfachimport-und-Seitenmanager-Ergaenzungen.md`,
   kurze Zusammenfassung aller drei Teile (Mehrfach-CSV-Import mit
   Auto-Scan, Seiten ans Ende anhängen, Bulk-Aktion „Für Navigation
   sperren") in 3-5 Sätzen, analog zum Detailgrad der bestehenden
   Einträge in dieser Datei.
5. „Letzte Aktualisierung" im Kopf der `PLAN.md` aktualisieren, Status
   aller APs in Abschnitt 8 auf ☑ prüfen.

**Akzeptanzkriterien:**
- [ ] `Theme/CLAUDE.md` beschreibt `lock_nav`/`unlock_nav` inkl. der
      Abgrenzung zu `hide_nav`/`show_nav` sowie das neue
      `menu_order`-Verhalten bei Seitenneuanlage.
- [ ] `Theme/reference_file_map.md`-Zeile zu `page-manager.php` nennt
      „zwölf" Aktionen inkl. der beiden neuen Schlüssel.
- [ ] Root-`DOKUMENTATION.md` enthält einen neuen Absatz für dieses
      Vorhaben mit Verweis auf die PLAN-Datei.
- [ ] Kein Verweis in der aktualisierten Dokumentation zeigt auf nicht mehr
      existierende Funktionen oder falsche Meta-Keys (Stichprobe:
      `_simple_clean_nav_gesperrt` korrekt geschrieben, nicht verwechselt).

**Tests:**
- Stichprobe: Zwei zufällige geänderte Zeilen der Datei-Map
  (`functions.php` aus AP-1.doc und `page-manager.php` aus diesem AP)
  gegen den tatsächlichen Quelltext prüfen (Zweck und Funktionen stimmen).

**Übergabenotiz:**
(leer – wird vom ausführenden Agenten nach Abschluss ausgefüllt)

## 8. Status

Legende: ☐ offen · ◐ in Arbeit · ☑ erledigt · ✗ blockiert

| AP | Titel | Modell | Status | Abhängig von | Notiz |
|---|---|---|---|---|---|
| AP-1.1 | Mehrfachdatei-CSV-Import im Backend | opus | ☑ | – | Verifiziert per Stub-Harness (echter Code), Live-UI-Test offen mangels Admin-Login |
| AP-1.2 | Automatischer Bulk-Scan nach Mehrfach-Import | sonnet | ☑ | AP-1.1 | JS-Syntax + PHP-Logik verifiziert, Live-UI-Test offen mangels Admin-Login |
| AP-1.fix1 | Korrektur: fehlendes `[]` am Datei-Feldnamen | sonnet | ☑ | AP-1.1, AP-1.2 | Kritischer Fund aus AP-1.rev, per echtem HTTP-Test verifiziert behoben |
| AP-1.rev | Review Phase 1 | opus | ☑ | AP-1.1, AP-1.2 | 1 kritischer Fund → AP-1.fix1; Kurz-Review nach Fix bestätigt Behebung |
| AP-1.doc | Doku Phase 1 | sonnet | ☑ | AP-1.rev, AP-1.fix1 | |
| AP-2.1 | Neue Seiten ans Ende anhängen | sonnet | ☐ | – | |
| AP-2.2 | Bulk-Aktion „Für Navigation sperren" | sonnet | ☐ | AP-2.1 (gleiche Datei) | |
| AP-2.rev | Review Phase 2 | opus | ☐ | AP-2.1, AP-2.2 | |
| AP-2.doc | Doku Phase 2 + Gesamt-Vorhaben | sonnet | ☐ | AP-2.rev | |

## 9. Testprotokoll

Wird während der Ausführung gepflegt. Ein Eintrag pro abgeschlossenem AP und pro Phasenabschluss.

| Datum | AP / Phase | Getestet | Ergebnis | Getestet von |
|---|---|---|---|---|
| 2026-08-25 | AP-1.1 | `php -l`; Stub-Harness mit vier Fällen (Einzeldatei-Regression, dateiübergreifendes Duplikat, eine ungültige Datei blockiert die andere nicht, reine Duplikat-Skips ergeben `sollScannen=false`) | Bestanden (4/4), Live-UI-Test mangels Admin-Zugang offen | Stub-Harness (Code-Ausführung) |
| 2026-08-25 | AP-1.2 | `php -l`; `$auto_scan`-Formel isoliert für alle drei Fälle per `php -r`; `<script>`-Block per Node.js auf JS-Syntaxfehler geprüft; Diff-Kontrolle, dass der manuelle `confirm()`-Klick-Handler unverändert blieb | Bestanden, Live-UI-Test mangels Admin-Zugang offen | Code-/Syntaxprüfung |
| 2026-08-25 | Phase 1 Integrationstest | End-to-End-Kette `simple_clean_handle_glossar_import_multi()`-Ergebnis → `$auto_scan` → `glossarAutoScan` für „Änderung vorhanden" und „nur Duplikate" nachvollzogen | Bestanden (beide Fälle liefern das erwartete Flag). Live-Browser-Integrationstest (echter Mehrfach-Upload im Admin-UI) mangels Admin-Zugang offen — als bekannte Testlücke in AP-1.doc zu vermerken | Code-Integrationsprüfung |
| 2026-08-25 | AP-1.rev | Unabhängiges Review gegen alle Akzeptanzkriterien von AP-1.1/1.2, Sicherheits- und Scope-Check | 1 kritischer Fund (fehlendes `[]` am Feldnamen, kompletter Rückschritt gegenüber Vor-AP-1.1-Zustand, empirisch per `php -S`+curl bestätigt) → AP-1.fix1 angelegt | unabhängiger Explore-Subagent (read-only) |
| 2026-08-25 | AP-1.fix1 | Echter HTTP-Multipart-POST (`php -S` + `curl -F "glossar_csv[]=@datei"`) für: eine Datei, zwei Dateien mit dateiübergreifendem Duplikat, eine Datei mit falscher Endung neben gültiger Datei | Bestanden (3/3), `php -l` fehlerfrei | Genuiner HTTP-Test (kein Stub-Fixture) |
| 2026-08-25 | AP-1.rev Kurz-Review (nach AP-1.fix1) | Feldname, Guard-Logik in `simple_clean_handle_glossar_import_multi()`, `php -l`, Reichweiten-Check auf verbleibende Annahmen des alten Skalar-Formats | Bestanden, keine weiteren Befunde | unabhängiger Explore-Subagent (read-only) |

## 10. Dokumentation

- **Projektdokumentation:** `Theme/CLAUDE.md` (Abschnitte „Glossar-System"
  und „Admin-Werkzeuge (includes/admin/)") sowie der zusammenfassende
  Absatz in der Root-`DOKUMENTATION.md`. Wird je Phase im `AP-<N>.doc`
  aktualisiert.
- **Datei-Map:** `Theme/reference_file_map.md` – Zeilen zu
  `functions.php` und `includes/admin/page-manager.php`. Wird von jedem
  AP gepflegt, das diese Dateien wesentlich ändert.
