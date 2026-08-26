/**
 * Frontend-Verhalten des Blocks fos/inhaltsverzeichnis.
 *
 * Filtert die bereits vorhandene Liste im Browser. Es findet KEINE
 * Netzwerkanfrage statt - die Eintraege stehen ohnehin vollstaendig im HTML.
 *
 * Ohne JavaScript bleibt die Liste vollstaendig nutzbar: Das Aufklappen
 * laeuft ueber natives <details>, nur das Filtern entfaellt.
 *
 * ACHTUNG: keine import/export-Anweisungen. Vite gibt ES-Module aus, die
 * Datei wird aber als klassisches Script eingehaengt. Ein einziges "import"
 * laesst den Browser die Datei bei Zeile 1 abbrechen.
 */

(function () {
	'use strict';

	var ENTPRELLUNG_MS = 150;

	function versteckKlasse(element) {
		return element.classList.contains('page-index__chapter')
			? 'page-index__chapter--hidden'
			: 'page-index__page--hidden';
	}

	/**
	 * Liefert die direkten Untereintraege eines Listenelements.
	 * Die Unterliste steht je nach Einstellung direkt im <li> oder in einem
	 * <details>.
	 */
	function unterEintraege(element) {
		return element.querySelectorAll(':scope > details > ul > li, :scope > ul > li');
	}

	/**
	 * Blendet einen Eintrag und seine Nachfahren passend zum Suchtext ein
	 * oder aus. Rekursiv, damit auch tiefere Ebenen mitgefiltert werden.
	 *
	 * Regel: Passt der Eintrag selbst, bleiben ALLE seine Untereintraege
	 * sichtbar - man sucht ein Kapitel und will dann dessen Inhalt sehen.
	 * Umgesetzt, indem an die Nachfahren ein leerer Suchtext weitergereicht
	 * wird; auf den passt jeder Eintrag.
	 *
	 * @return {boolean} true, wenn der Eintrag sichtbar bleibt.
	 */
	function filtereEintrag(element, suchtext) {
		// Der Titel steckt an zwei moeglichen Stellen: bei einem aufklappbaren
		// Kapitel im <summary> (dort per Klasse gesucht, sonst traefe der
		// Ausdruck auch die Anzahl-Anzeige .page-index__chapter-count), sonst
		// direkt als Kind im <li>. Die span-Varianten decken gesperrte Seiten
		// ab (AP-3): dort steht statt eines <a> ein <span> mit dem Titel.
		var summary = element.querySelector(':scope > details > summary');
		var link = summary
			? summary.querySelector('.page-index__chapter-link, .page-index__chapter-title')
			: element.querySelector(':scope > a, :scope > span');
		var text = link ? link.textContent.toLowerCase() : '';
		var selbstTreffer = suchtext === '' || text.indexOf(suchtext) !== -1;

		var kindTreffer = false;
		var kinder = unterEintraege(element);
		for (var i = 0; i < kinder.length; i++) {
			if (filtereEintrag(kinder[i], selbstTreffer ? '' : suchtext)) {
				kindTreffer = true;
			}
		}

		var sichtbar = selbstTreffer || kindTreffer;
		element.classList.toggle(versteckKlasse(element), !sichtbar);

		return sichtbar;
	}

	function zaehleSichtbareSeiten(wurzel) {
		var alle = wurzel.querySelectorAll('.page-index__page');
		var n = 0;
		for (var i = 0; i < alle.length; i++) {
			if (!alle[i].classList.contains('page-index__page--hidden')) {
				n++;
			}
		}
		return n;
	}

	function richteEin(wurzel) {
		// Lehrpersonen-Toggle (AP-2.2, Vertrag siehe PLAN-Inhaltsverzeichnisse.md,
		// Abschnitt 4). Unabhaengig vom Suchfeld-Fruehausstieg weiter unten
		// platziert, da der Toggle auch funktionieren muss, wenn showSearch fuer
		// den Block deaktiviert ist.
		var lehrerToggle = wurzel.querySelector('.page-index__lehrer-toggle');
		if (lehrerToggle) {
			lehrerToggle.addEventListener('click', function () {
				var aktiv = wurzel.classList.toggle('page-index--zeige-lehreransicht');
				lehrerToggle.setAttribute('aria-pressed', aktiv ? 'true' : 'false');
				lehrerToggle.textContent = aktiv
					? 'Nur Schüleransicht zeigen'
					: 'Lehrpersonen-Seiten anzeigen';
			});
		}

		var feld = wurzel.querySelector('.page-index__search');
		if (!feld) {
			return;
		}

		var kapitel = wurzel.querySelectorAll(':scope > .page-index__chapters > .page-index__chapter');
		if (!kapitel.length) {
			return;
		}

		// Ausgangszustand der Aufklappebenen merken, um ihn beim Leeren des
		// Feldes exakt wiederherzustellen.
		var details = wurzel.querySelectorAll('details.page-index__sub');
		var warOffen = [];
		for (var i = 0; i < details.length; i++) {
			warOffen.push(details[i].open);
		}

		// Meldebereich fuer Screenreader. Sichtbar ist er nicht (CSS-Klasse
		// page-index__status), aber Vorlesesoftware bekommt die Trefferzahl
		// mit - sonst waere das Filtern fuer sie unbemerkbar.
		var status = document.createElement('p');
		status.className = 'page-index__status';
		status.setAttribute('aria-live', 'polite');
		wurzel.appendChild(status);

		var keineTreffer = null;

		function zeigeKeineTreffer(anzeigen) {
			if (anzeigen && !keineTreffer) {
				keineTreffer = document.createElement('p');
				keineTreffer.className = 'page-index__no-results';
				keineTreffer.textContent = 'Keine Treffer.';
				wurzel.appendChild(keineTreffer);
			} else if (!anzeigen && keineTreffer) {
				keineTreffer.parentNode.removeChild(keineTreffer);
				keineTreffer = null;
			}
		}

		function anwenden() {
			var suchtext = feld.value.trim().toLowerCase();

			if (suchtext === '') {
				// Ausgangszustand wiederherstellen.
				var versteckte = wurzel.querySelectorAll(
					'.page-index__chapter--hidden, .page-index__page--hidden'
				);
				for (var v = 0; v < versteckte.length; v++) {
					versteckte[v].classList.remove(
						'page-index__chapter--hidden',
						'page-index__page--hidden'
					);
				}
				for (var d = 0; d < details.length; d++) {
					details[d].open = warOffen[d];
				}
				zeigeKeineTreffer(false);
				status.textContent = '';
				return;
			}

			var sichtbareKapitel = 0;
			for (var k = 0; k < kapitel.length; k++) {
				if (filtereEintrag(kapitel[k], suchtext)) {
					sichtbareKapitel++;
				}
			}

			// Bei aktiver Suche alles aufklappen, sonst blieben Treffer hinter
			// zugeklappten Ebenen verborgen.
			for (var o = 0; o < details.length; o++) {
				details[o].open = true;
			}

			zeigeKeineTreffer(sichtbareKapitel === 0);

			var seiten = zaehleSichtbareSeiten(wurzel);
			status.textContent =
				sichtbareKapitel === 0
					? 'Keine Treffer.'
					: sichtbareKapitel + ' Kapitel, ' + seiten + ' Seiten gefunden.';
		}

		var zeitgeber = null;
		feld.addEventListener('input', function () {
			window.clearTimeout(zeitgeber);
			zeitgeber = window.setTimeout(anwenden, ENTPRELLUNG_MS);
		});

		// Escape leert das Feld - schneller als markieren und loeschen.
		feld.addEventListener('keydown', function (ereignis) {
			if (ereignis.key === 'Escape' && feld.value !== '') {
				ereignis.preventDefault();
				feld.value = '';
				anwenden();
			}
		});
	}

	function start() {
		// Mehrere Verzeichnisse auf einer Seite sind moeglich.
		var alle = document.querySelectorAll('.page-index');
		for (var i = 0; i < alle.length; i++) {
			richteEin(alle[i]);
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', start);
	} else {
		start();
	}
})();
