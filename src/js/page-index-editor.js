/**
 * Editor-Integration fuer den Block fos/inhaltsverzeichnis.
 *
 * ACHTUNG - KEINE import/export-Anweisungen in dieser Datei!
 * Vite gibt ES-Module aus, diese Datei wird aber als klassisches Script
 * eingehaengt. Ein einziges "import" oder "export" laesst den Browser mit
 * "Cannot use import statement outside a module" abbrechen - und dann wird
 * der Block NICHT registriert und erscheint nicht im Einfuegen-Menue.
 * Zugriff deshalb ausschliesslich ueber die wp.*-Globalen, genau wie in
 * src/js/glossar-editor.js.
 *
 * WARUM DIESE DATEI UEBERHAUPT NOETIG IST
 * Eine rein serverseitige Registrierung (register_block_type_from_metadata in
 * includes/page-index.php) liefert Rendering, Block-Supports und Metadaten -
 * aber sie macht den Block NICHT im Inserter sichtbar. Die Auffindbarkeit
 * entsteht erst durch registerBlockType() hier im Editor.
 *
 * Titel, Kategorie und Attribute kommen aus blocks/inhaltsverzeichnis/
 * block.json: WordPress reicht die serverseitigen Blockdefinitionen an den
 * Editor durch, bevor dieses Script laeuft. block.json bleibt damit die
 * massgebliche Quelle; hier stehen nur Titel und Symbol als Rueckfall, falls
 * die Durchreichung einmal ausbleibt.
 */

(function () {
	var blocks = window.wp && window.wp.blocks;
	var element = window.wp && window.wp.element;
	var blockEditor = window.wp && window.wp.blockEditor;
	var components = window.wp && window.wp.components;
	var data = window.wp && window.wp.data;

	if (!blocks || !element || !blockEditor || !components) {
		return;
	}

	var el = element.createElement;
	var Fragment = element.Fragment;
	var InspectorControls = blockEditor.InspectorControls;
	var useBlockProps = blockEditor.useBlockProps;
	var PanelBody = components.PanelBody;
	var RangeControl = components.RangeControl;
	var SelectControl = components.SelectControl;
	var ToggleControl = components.ToggleControl;
	var Spinner = components.Spinner;
	var ServerSideRender = window.wp.serverSideRender;

	blocks.registerBlockType('fos/inhaltsverzeichnis', {
		title: 'Inhaltsverzeichnis',
		icon: 'list-view',

		edit: function (props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			// Seitenliste fuer die Auswahl der Startseite. Nur die Felder
			// holen, die gebraucht werden - die Website hat mehrere hundert
			// Seiten, der vollstaendige Datensatz waere unnoetig gross.
			var seiten = data
				? data.useSelect(function (select) {
						return select('core').getEntityRecords('postType', 'page', {
							per_page: -1,
							orderby: 'menu_order',
							order: 'asc',
							status: 'publish',
							_fields: 'id,title,parent',
						});
				  }, [])
				: null;

			var seitenOptionen = [{ label: 'Alle Seiten (oberste Ebene)', value: 0 }];
			if (seiten) {
				seiten.forEach(function (seite) {
					var titel =
						seite.title && seite.title.rendered
							? seite.title.rendered.replace(/<[^>]*>/g, '')
							: '(ohne Titel)';
					// Unterseiten leicht einruecken, damit die Hierarchie
					// erkennbar bleibt.
					seitenOptionen.push({
						label: (seite.parent ? '— ' : '') + titel,
						value: seite.id,
					});
				});
			}

			var steuerung = el(
				InspectorControls,
				{},
				el(
					PanelBody,
					{ title: 'Einstellungen', initialOpen: true },

					seiten === null
						? el(
								'p',
								{ style: { display: 'flex', alignItems: 'center', gap: '8px' } },
								el(Spinner, {}),
								'Seiten werden geladen …'
						  )
						: el(SelectControl, {
								label: 'Startseite',
								help: 'Zeigt die Unterseiten dieser Seite. „Alle Seiten“ zeigt die oberste Ebene der Website.',
								value: attributes.rootPage,
								options: seitenOptionen,
								onChange: function (wert) {
									setAttributes({ rootPage: parseInt(wert, 10) || 0 });
								},
						  }),

					el(RangeControl, {
						label: 'Maximale Tiefe',
						help: '1 zeigt nur die Kapitel, 2 zusätzlich deren Unterseiten.',
						value: attributes.maxDepth,
						min: 1,
						max: 5,
						onChange: function (wert) {
							setAttributes({ maxDepth: wert });
						},
					}),

					el(SelectControl, {
						label: 'Darstellung',
						value: attributes.layout,
						options: [
							{ label: 'Kapitelkarten', value: 'cards' },
							{ label: 'Einfache Liste', value: 'list' },
							{ label: 'Mehrspaltig', value: 'columns' },
						],
						onChange: function (wert) {
							setAttributes({ layout: wert });
						},
					}),

					// Spalten wirken NUR bei "Mehrspaltig". Kapitelkarten stehen
					// bewusst immer untereinander, die einfache Liste ohnehin.
					// Die Einstellung deshalb dort ausblenden, statt einen
					// Regler zu zeigen, der nichts bewirkt.
					attributes.layout === 'columns'
						? el(RangeControl, {
								label: 'Spalten',
								value: attributes.columns,
								min: 1,
								max: 4,
								onChange: function (wert) {
									setAttributes({ columns: wert });
								},
						  })
						: null,

					el(ToggleControl, {
						label: 'Unterseiten zum Aufklappen',
						help: 'Unterseiten werden erst auf Klick sichtbar.',
						checked: attributes.collapsible,
						onChange: function (wert) {
							setAttributes({ collapsible: wert });
						},
					}),

					attributes.collapsible
						? el(ToggleControl, {
								label: 'Beim Laden aufgeklappt',
								checked: attributes.openByDefault,
								onChange: function (wert) {
									setAttributes({ openByDefault: wert });
								},
						  })
						: null,

					el(ToggleControl, {
						label: 'Suchfeld anzeigen',
						help: 'Filtert die Liste im Browser, ohne die Seite neu zu laden.',
						checked: attributes.showSearch,
						onChange: function (wert) {
							setAttributes({ showSearch: wert });
						},
					}),

					el(ToggleControl, {
						label: 'Anzahl der Unterseiten anzeigen',
						checked: attributes.showCounts,
						onChange: function (wert) {
							setAttributes({ showCounts: wert });
						},
					})
				)
			);

			// Vorschau ueber dieselbe PHP-Renderfunktion wie im Frontend -
			// dadurch kann die Darstellung im Editor nicht von der auf der
			// Website abweichen.
			var vorschau = ServerSideRender
				? el(ServerSideRender, {
						block: 'fos/inhaltsverzeichnis',
						attributes: attributes,
				  })
				: el('p', {}, 'Vorschau nicht verfügbar.');

			var blockProps = useBlockProps ? useBlockProps() : {};

			return el(Fragment, {}, steuerung, el('div', blockProps, vorschau));
		},
	});
})();
