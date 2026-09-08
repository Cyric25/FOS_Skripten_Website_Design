/**
 * Editor-Werkzeug "Kapitellink"
 * (PLAN-Summary-PDF-und-Content-Links.md, Phase 3, AP-3.4)
 *
 * Fuegt der Inline-Werkzeugleiste des Block-Editors einen Knopf hinzu, der
 * markierten Text in einen Link auf eine bestimmte Kapitelkarte eines
 * fos/inhaltsverzeichnis-Blocks verwandelt. Die Auswahl ist zweistufig -
 * erst die Zielseite, dann das Kapitel dieser Seite (Architekturentscheidung
 * A5; eine automatische Ermittlung der Zielseite ist ausdrueckliches
 * Nicht-Ziel, weil mehrere Verzeichnisbloecke mit unterschiedlichem rootPage
 * erlaubt sind und die Zuordnung deshalb mehrdeutig waere).
 *
 * Ergebnis ist ein ganz gewoehnlicher Link:
 *   <a href="<Permalink der Seite>#page-index-kapitel-<Kapitel-ID>">Text</a>
 * Ankerschema aus AP-3.1, Sprungverhalten aus AP-3.2, Auswahldaten aus den
 * beiden REST-Routen aus AP-3.3.
 *
 * ACHTUNG: keine import/export-Anweisungen. Vite gibt ES-Module aus, die
 * Datei wird aber als klassisches Script eingehaengt. Ein einziges "import"
 * laesst den Browser die Datei bei Zeile 1 abbrechen. Zugriff deshalb ueber
 * die wp.*-Globalen - dasselbe Muster wie in glossar-editor.js und
 * page-index-editor.js.
 */

(function (wp) {
	'use strict';

	if (!wp || !wp.richText || !wp.blockEditor || !wp.element || !wp.components) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var useState = wp.element.useState;
	var useEffect = wp.element.useEffect;

	var RichTextToolbarButton = wp.blockEditor.RichTextToolbarButton;
	var Popover = wp.components.Popover;
	var SelectControl = wp.components.SelectControl;
	var Button = wp.components.Button;
	var Spinner = wp.components.Spinner;

	var applyFormat = wp.richText.applyFormat;
	var registerFormatType = wp.richText.registerFormatType;
	var create = wp.richText.create;
	var insert = wp.richText.insert;

	// Muss Zeichen fuer Zeichen zum Schema aus AP-3.1 passen
	// (id="page-index-kapitel-<post_id>") und zur Regex in AP-3.2.
	var ANKER_PRAEFIX = '#page-index-kapitel-';

	// ------------------------------------------------------------------
	// Datenzugriff
	// ------------------------------------------------------------------

	function holeSeiten() {
		return wp.apiFetch({ path: '/simple-clean/v1/inhaltsverzeichnis-seiten' });
	}

	function holeKapitel(seitenId) {
		return wp.apiFetch({
			path: '/simple-clean/v1/inhaltsverzeichnis-kapitel?seite=' + encodeURIComponent(seitenId)
		});
	}

	/**
	 * Permalink der Zielseite.
	 *
	 * Die beiden Kapitellink-Routen liefern bewusst nur id und title; die
	 * Adresse kommt aus der Kern-Route, damit hier keine zweite Stelle
	 * entsteht, die Permalinks zusammenbaut (das Theme hat mit
	 * simple_clean_page_index_url() bereits eine, und deren Kopfkommentar
	 * warnt ausdruecklich davor, Adressen an weiteren Stellen selbst zu
	 * bilden).
	 */
	function holePermalink(seitenId) {
		return wp.apiFetch({ path: '/wp/v2/pages/' + encodeURIComponent(seitenId) })
			.then(function (seite) {
				return seite && seite.link ? seite.link : '';
			});
	}

	// ------------------------------------------------------------------
	// Oberflaeche
	// ------------------------------------------------------------------

	function KapitellinkEdit(props) {
		var offenZustand = useState(false);
		var offen = offenZustand[0];
		var setOffen = offenZustand[1];

		// Der Wert MIT Auswahlbereich, festgehalten im Moment des Klicks auf
		// den Werkzeugknopf. Sobald der Fokus in die Auswahlfelder wandert,
		// ist auf den laufend hereingereichten props.value kein Verlass mehr -
		// das Einfuegen liefe dann ins Leere oder an die falsche Stelle.
		var gemerkterWertZustand = useState(null);
		var gemerkterWert = gemerkterWertZustand[0];
		var setGemerkterWert = gemerkterWertZustand[1];

		var seitenZustand = useState([]);
		var seiten = seitenZustand[0];
		var setSeiten = seitenZustand[1];

		var kapitelZustand = useState([]);
		var kapitel = kapitelZustand[0];
		var setKapitel = kapitelZustand[1];

		var seiteZustand = useState('');
		var gewaehlteSeite = seiteZustand[0];
		var setGewaehlteSeite = seiteZustand[1];

		var kapitelWahlZustand = useState('');
		var gewaehltesKapitel = kapitelWahlZustand[0];
		var setGewaehltesKapitel = kapitelWahlZustand[1];

		var ladenZustand = useState(false);
		var laedt = ladenZustand[0];
		var setLaedt = ladenZustand[1];

		var fehlerZustand = useState('');
		var fehler = fehlerZustand[0];
		var setFehler = fehlerZustand[1];

		// Seitenliste erst beim Oeffnen holen - beim Laden des Editors waere
		// es eine Anfrage, die die meisten Bearbeitungen nie brauchen.
		useEffect(function () {
			if (!offen) {
				return;
			}
			setLaedt(true);
			setFehler('');
			holeSeiten().then(function (liste) {
				setSeiten(liste || []);
				setLaedt(false);
				if (!liste || !liste.length) {
					setFehler('Keine Seite mit einem Inhaltsverzeichnis-Block gefunden.');
				}
			}).catch(function () {
				setLaedt(false);
				setFehler('Die Seitenliste konnte nicht geladen werden.');
			});
		}, [offen]);

		// Zweite Stufe: Kapitel der gewaehlten Seite.
		useEffect(function () {
			if (!offen || !gewaehlteSeite) {
				setKapitel([]);
				setGewaehltesKapitel('');
				return;
			}
			setLaedt(true);
			setFehler('');
			holeKapitel(gewaehlteSeite).then(function (liste) {
				setKapitel(liste || []);
				setGewaehltesKapitel(liste && liste.length ? String(liste[0].id) : '');
				setLaedt(false);
				if (!liste || !liste.length) {
					setFehler('Diese Seite zeigt keine Kapitel an.');
				}
			}).catch(function () {
				setLaedt(false);
				setFehler('Die Kapitelliste konnte nicht geladen werden.');
			});
		}, [offen, gewaehlteSeite]);

		function schliessen() {
			setOffen(false);
			setFehler('');
			setGemerkterWert(null);
		}

		function oeffnen() {
			// Auswahlbereich sichern, BEVOR der Fokus in das Popover wandert.
			setGemerkterWert(props.value);
			setOffen(true);
		}

		function einfuegen() {
			if (!gewaehlteSeite || !gewaehltesKapitel) {
				return;
			}

			setLaedt(true);
			holePermalink(gewaehlteSeite).then(function (permalink) {
				setLaedt(false);

				if (!permalink) {
					setFehler('Die Adresse der Zielseite konnte nicht ermittelt werden.');
					return;
				}

				var url = permalink + ANKER_PRAEFIX + gewaehltesKapitel;
				var wert = gemerkterWert || props.value;

				// BEWUSST core/link statt eines eigenen Formats, siehe
				// Kopfkommentar von registerFormatType() unten: Das Ergebnis
				// ist ein gewoehnlicher Link, der mit der Standard-Link-
				// Oberflaeche weiterbearbeitet und entfernt werden kann - und
				// er traegt keine eigene Klasse, folgt also der Linkfarbregel
				// aus AP-2.1 wie jeder andere Inhalts-Link.
				var linkFormat = { type: 'core/link', attributes: { url: url } };

				var neuerWert;
				if (wert.start === wert.end) {
					// Keine Textauswahl: Kapiteltitel als neuen, verlinkten
					// Text einsetzen (so im AP-Text vorgesehen).
					var titel = '';
					for (var i = 0; i < kapitel.length; i++) {
						if (String(kapitel[i].id) === String(gewaehltesKapitel)) {
							titel = kapitel[i].title;
							break;
						}
					}
					if (!titel) {
						titel = 'Kapitel';
					}
					var eingefuegt = applyFormat(create({ text: titel }), linkFormat, 0, titel.length);
					neuerWert = insert(wert, eingefuegt);
				} else {
					neuerWert = applyFormat(wert, linkFormat);
				}

				props.onChange(neuerWert);
				schliessen();
			}).catch(function () {
				setLaedt(false);
				setFehler('Die Adresse der Zielseite konnte nicht ermittelt werden.');
			});
		}

		var seitenOptionen = [{ value: '', label: '— Seite wählen —' }].concat(
			seiten.map(function (s) {
				return { value: String(s.id), label: s.title };
			})
		);

		var kapitelOptionen = kapitel.map(function (k) {
			return { value: String(k.id), label: k.title };
		});

		return el(
			Fragment,
			null,
			el(RichTextToolbarButton, {
				icon: 'admin-links',
				title: 'Kapitellink',
				onClick: oeffnen
			}),
			offen && el(
				Popover,
				{
					className: 'fos-kapitellink-popover',
					placement: 'bottom-start',
					onClose: schliessen,
					// Ohne diesen Fokusfang wandert der Fokus sofort in das
					// Popover und der Auswahlbereich im Text geht sichtbar
					// verloren; gemerkterWert traegt ihn ohnehin.
					focusOnMount: 'firstElement'
				},
				el(
					'div',
					{ style: { padding: '12px', minWidth: '280px' } },
					el('p', { style: { margin: '0 0 8px', fontWeight: 600 } }, 'Kapitellink einfügen'),
					el(SelectControl, {
						label: 'Seite',
						value: gewaehlteSeite,
						options: seitenOptionen,
						onChange: function (wert) {
							setGewaehlteSeite(wert);
						}
					}),
					gewaehlteSeite && kapitelOptionen.length
						? el(SelectControl, {
							label: 'Kapitel',
							value: gewaehltesKapitel,
							options: kapitelOptionen,
							onChange: function (wert) {
								setGewaehltesKapitel(wert);
							}
						})
						: null,
					laedt ? el(Spinner) : null,
					fehler
						? el('p', { style: { color: '#cc1818', margin: '8px 0 0' } }, fehler)
						: null,
					el(
						'div',
						{ style: { marginTop: '12px', display: 'flex', gap: '8px' } },
						el(
							Button,
							{
								variant: 'primary',
								disabled: !gewaehlteSeite || !gewaehltesKapitel || laedt,
								onClick: einfuegen
							},
							'Einfügen'
						),
						el(Button, { variant: 'tertiary', onClick: schliessen }, 'Abbrechen')
					)
				)
			)
		);
	}

	/**
	 * Registrierung.
	 *
	 * ABWEICHUNG VOM PLAN-TEXT, bewusst (siehe Uebergabenotiz zu AP-3.4):
	 * Der Plan schlaegt className: null vor. Ein Format mit tagName 'a' UND
	 * className null beansprucht in Gutenberg das nackte <a>-Element fuer
	 * sich - genau das, was der Kern-Formattyp core/link bereits tut
	 * (getFormatTypeForBareElement() nimmt den ERSTEN passenden Typ). Zwei
	 * Bewerber um dasselbe Element sind eine unnoetige Fehlerquelle, und der
	 * eingefuegte Link liesse sich dann nicht mehr mit der gewohnten
	 * Link-Oberflaeche bearbeiten.
	 *
	 * Dieser Formattyp ist deshalb reiner Traeger des Werkzeugknopfes: Er
	 * wird NIE auf Text angewendet (die Klasse unten kommt in keinem Inhalt
	 * vor), und das Einfuegen wendet core/link an. Das Ergebnis ist ein
	 * gewoehnlicher Link - genau das, was das Vorhaben will.
	 */
	registerFormatType('fos/kapitellink', {
		title: 'Kapitellink',
		tagName: 'a',
		className: 'fos-kapitellink-werkzeug',
		edit: KapitellinkEdit
	});
})(window.wp);
