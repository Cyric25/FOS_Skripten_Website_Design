/**
 * Seitenmanager - Page Manager JavaScript
 *
 * Handles drag & drop for changing page hierarchy (parent-child relationships only).
 * Does NOT handle menu_order - only post_parent.
 *
 * @package FOS_Online_Schulbuch
 * @since 1.4.7
 */

(function($) {
    'use strict';

    // Check if we're on the page manager
    if (typeof pageManagerData === 'undefined') {
        return;
    }

    const STORAGE_KEY = 'pageManagerExpandedState';

    const PageManager = {

        /**
         * Initialize the page manager
         */
        init: function() {
            this.bindEvents();
            this.initSortables();
            this.restoreExpandedState();
            this.aktualisiereAuswahl();
        },

        /**
         * Bind UI events
         */
        bindEvents: function() {
            const self = this;

            // Create new page - open modal (top level)
            $('#create-new-page').on('click', function() {
                self.openCreateModal(0, '');
            });

            // Create child page - open modal
            $(document).on('click', '.create-child-page', function() {
                const parentId = $(this).data('page-id');
                const parentTitle = $(this).data('page-title');
                self.openCreateModal(parentId, parentTitle);
            });

            // Create new page - close modal
            $('#create-page-cancel').on('click', function() {
                self.closeCreateModal();
            });

            // Create new page - submit
            $('#create-page-submit').on('click', function() {
                const title = $('#new-page-title').val().trim();

                if (!title) {
                    alert('Bitte geben Sie einen Titel ein.');
                    return;
                }

                self.createPage(title);
            });

            // Create new page - Enter key
            $('#new-page-title').on('keypress', function(e) {
                if (e.which === 13) { // Enter key
                    $('#create-page-submit').click();
                }
            });

            // Close modal on ESC key
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#new-page-modal').is(':visible')) {
                    self.closeCreateModal();
                }
            });

            // Delete page
            $(document).on('click', '.delete-page', function() {
                const pageId = $(this).data('page-id');
                const pageTitle = $(this).data('page-title');

                if (confirm('Möchten Sie die Seite "' + pageTitle + '" in den Papierkorb verschieben?')) {
                    self.deletePage(pageId);
                }
            });

            // Toggle status
            $(document).on('click', '.toggle-status', function() {
                const pageId = $(this).data('page-id');
                const $button = $(this);
                self.toggleStatus(pageId, $button);
            });

            // Toggle children visibility
            $(document).on('click', '.toggle-children', function(e) {
                e.preventDefault();
                const $pageItem = $(this).closest('.page-item');
                const $children = $pageItem.children('.page-tree-children');
                const isExpanded = $(this).attr('aria-expanded') === 'true';

                if (isExpanded) {
                    // Collapse
                    $children.slideUp(200, function() {
                        $(this).removeClass('visible');
                    });
                } else {
                    // Expand
                    $children.addClass('visible').slideDown(200);
                }

                $(this).attr('aria-expanded', !isExpanded);
                $(this).find('.dashicons')
                    .toggleClass('dashicons-arrow-down-alt2', !isExpanded)
                    .toggleClass('dashicons-arrow-right-alt2', isExpanded);

                // Save state
                self.saveExpandedState();
            });

            // Expand all
            $('#expand-all').on('click', function() {
                $('.page-tree-children').addClass('visible').slideDown(200);
                $('.toggle-children')
                    .attr('aria-expanded', 'true')
                    .find('.dashicons')
                    .removeClass('dashicons-arrow-right-alt2')
                    .addClass('dashicons-arrow-down-alt2');
                self.saveExpandedState();
            });

            // --- Sammelaktionen ---------------------------------------
            // Ereignisse an document binden, damit sie auch nach einem
            // Neuladen des Baums greifen.

            // Auswahl einer einzelnen Zeile
            $(document).on('change', '.page-select', function() {
                self.aktualisiereAuswahl();
            });

            // Bereichsauswahl mit gedrückter Umschalttaste
            $(document).on('click', '.page-select', function(e) {
                const $alle = $('.page-select:visible');
                const index = $alle.index(this);

                if (e.shiftKey && self.letzteAuswahl !== null && self.letzteAuswahl !== index) {
                    const von = Math.min(self.letzteAuswahl, index);
                    const bis = Math.max(self.letzteAuswahl, index);
                    const zustand = this.checked;
                    $alle.slice(von, bis + 1).prop('checked', zustand);
                    self.aktualisiereAuswahl();
                }
                self.letzteAuswahl = index;
            });

            // Alle auswählen
            //
            // Anhaken betrifft nur die SICHTBAREN Zeilen — zugeklappte
            // Unterbäume sollen nicht unbemerkt mitkommen. Das Abhaken räumt
            // dagegen ALLE Häkchen weg, auch die in zugeklappten Zweigen;
            // sonst gäbe es keinen Weg, eine versteckte Vorauswahl wieder
            // loszuwerden (siehe Hinweis in aktualisiereAuswahl()).
            $('#page-select-all').on('change', function() {
                if (this.checked) {
                    $('.page-select:visible').prop('checked', true);
                } else {
                    $('.page-select').prop('checked', false);
                }
                self.letzteAuswahl = null;
                self.aktualisiereAuswahl();
            });

            // Aktionswahl – die Elternauswahl nur bei set_parent zeigen
            $('#page-bulk-action').on('change', function() {
                $('#page-bulk-parent').prop('hidden', this.value !== 'set_parent');
                self.aktualisiereAuswahl();
            });

            // Ausführen
            $('#page-bulk-apply').on('click', function() {
                self.fuehreBulkAus();
            });

            // Collapse all
            $('#collapse-all').on('click', function() {
                $('.page-tree-children').slideUp(200, function() {
                    $(this).removeClass('visible');
                });
                $('.toggle-children')
                    .attr('aria-expanded', 'false')
                    .find('.dashicons')
                    .removeClass('dashicons-arrow-down-alt2')
                    .addClass('dashicons-arrow-right-alt2');
                self.saveExpandedState();
            });
        },

        /**
         * Index der zuletzt angeklickten Auswahl-Checkbox (für Shift-Bereiche)
         */
        letzteAuswahl: null,

        /**
         * Auswahlzähler, Kopf-Checkbox und Ausführen-Knopf nachziehen
         *
         * Gezählt wird über ALLE Häkchen, nicht nur die sichtbaren — genau
         * die Menge also, die fuehreBulkAus() später verschickt. Vorher zählte
         * diese Stelle `.page-select:visible`: wer Unterseiten auswählte und
         * den Elternknoten danach zuklappte, sah „0 ausgewählt" und einen
         * ausgegrauten Knopf, während in Wahrheit noch fünf Häkchen gesetzt
         * waren — und beim nächsten Ausführen sechs statt einer Seite
         * geändert wurden.
         *
         * Zugeklappte Häkchen bleiben absichtlich erhalten (eine Auswahl
         * verschwindet nicht, nur weil man einen Zweig zuklappt), werden im
         * Zähler aber getrennt ausgewiesen.
         */
        aktualisiereAuswahl: function() {
            const $alle = $('.page-select');
            const anzahl = $alle.filter(':checked').length;

            const $sichtbar = $('.page-select:visible');
            const sichtbarGewaehlt = $sichtbar.filter(':checked').length;
            const versteckt = anzahl - sichtbarGewaehlt;

            let text = anzahl + ' ausgewählt';
            if (versteckt > 0) {
                text += ' (' + versteckt + ' in zugeklappten Zweigen)';
            }
            $('#page-bulk-count').text(text);

            const aktion = $('#page-bulk-action').val();
            $('#page-bulk-apply').prop('disabled', anzahl === 0 || !aktion);

            const $alleBox = $('#page-select-all');
            if ($alleBox.length) {
                $alleBox.prop('checked', sichtbarGewaehlt > 0 && sichtbarGewaehlt === $sichtbar.length);
                $alleBox.prop('indeterminate', sichtbarGewaehlt > 0 && sichtbarGewaehlt < $sichtbar.length);
            }
        },

        /**
         * Wie viele Seiten gehen je Anfrage an den Server?
         *
         * Die drei Aktionen unten schreiben über wp_update_post() bzw.
         * wp_trash_post() und lösen damit save_post aus — Glossar-Scan,
         * Revision, Cache-Verwurf. Das kostet je Seite mit echtem Inhalt rund
         * 1,5 s im CLI und 2,5 bis 4,5 s über HTTP — je größer die Seite,
         * desto teurer. Drei Seiten je Anfrage halten eine Anfrage damit bei
         * rund 13 s und die Fortschrittsanzeige in Bewegung.
         *
         * Alle übrigen Aktionen schreiben nur post_parent oder ein Meta und
         * brauchen für zehn Seiten rund 0,3 s — die dürfen in großen Paketen
         * laufen, sonst zahlt man für 500 Seiten unnötig viele Roundtrips.
         *
         * Diese Größen sind bewusst nur eine Schätzung: Was tatsächlich in
         * eine Anfrage passt, entscheidet der Server anhand seines
         * Zeitbudgets und stellt den Rest über das Antwortfeld `offen`
         * zurück (siehe ajax_bulk_action() in page-manager.php). Die Zahlen
         * hier sparen nur Roundtrips, sie sind nicht die Absicherung.
         */
        paketGroesse: function(aktion) {
            const teuer = ['status_publish', 'status_draft', 'trash'];
            return teuer.indexOf(aktion) !== -1 ? 3 : 100;
        },

        /**
         * Sammelaktion ausführen
         *
         * Verschickt die Auswahl in Paketen (siehe paketGroesse) und zählt die
         * Rückmeldungen zusammen. Bricht ein Paket ab, hält der Lauf an und
         * meldet, wie weit er gekommen ist — angefangene Sammelaktionen bleiben
         * dadurch nachvollziehbar statt stumm halb erledigt.
         */
        fuehreBulkAus: function() {
            const self = this;
            const ids = $('.page-select:checked').map(function() {
                return parseInt(this.value, 10);
            }).get();
            const aktion = $('#page-bulk-action').val();

            if (ids.length === 0 || !aktion) {
                return;
            }

            // Rückfragen bei allem, was nach außen sichtbar oder schwer
            // rückgängig zu machen ist.
            if (aktion === 'trash') {
                if (!confirm('Sollen ' + ids.length + ' Seite(n) wirklich in den Papierkorb verschoben werden?')) {
                    return;
                }
            } else if (aktion === 'status_publish') {
                if (!confirm(ids.length + ' Seite(n) veröffentlichen?')) {
                    return;
                }
            }

            const parentId = (aktion === 'set_parent')
                ? (parseInt($('#page-bulk-parent').val(), 10) || 0)
                : null;

            const groesse = self.paketGroesse(aktion);

            // Warteschlange statt fester Pakete: Der Server darf Seiten
            // zurückstellen, wenn sein Zeitbudget aufgebraucht ist
            // (ajax_bulk_action(), Antwortfeld `offen`). Die kommen dann vorn
            // wieder rein und gehen mit der nächsten Anfrage raus.
            const warteschlange = ids.slice();

            const bilanz = {
                gesamt: ids.length,
                erledigt: 0,
                geaendert: 0,
                uebersprungen: 0,
                errors: [],
                reload: false,
                // Serverseitige Kennzahlen je Paket (Zeitlimit, Budget, Dauer).
                // Landen bei einem Abbruch in der Browser-Konsole — ohne sie
                // ist auf einem fremden Server nicht zu unterscheiden, ob das
                // Budget zu großzügig war oder ob etwas vor PHP die Verbindung
                // gekappt hat.
                diagnose: []
            };

            $('#page-bulk-apply').prop('disabled', true);

            const sendePaket = function() {
                if (warteschlange.length === 0) {
                    self.bulkAbschluss(bilanz, null);
                    return;
                }

                const paket = warteschlange.splice(0, groesse);

                if (bilanz.gesamt > paket.length) {
                    self.showStatus('saving', 'Aktion wird ausgeführt... '
                        + bilanz.erledigt + ' von ' + bilanz.gesamt);
                } else {
                    self.showStatus('saving', 'Aktion wird ausgeführt...');
                }

                const daten = {
                    action: 'page_manager_bulk_action',
                    nonce: pageManagerData.nonce,
                    bulk_action: aktion,
                    page_ids: paket
                };
                if (parentId !== null) {
                    daten.parent_id = parentId;
                }

                $.ajax({
                    url: pageManagerData.ajaxUrl,
                    type: 'POST',
                    data: daten,
                    success: function(response) {
                        if (!response || !response.success) {
                            const grund = (response && response.data && response.data.message)
                                ? response.data.message
                                : 'Unerwartete Antwort vom Server';
                            bilanz.errors.push(grund);
                            self.bulkAbschluss(bilanz, grund);
                            return;
                        }

                        const offen = (response.data.offen && response.data.offen.length)
                            ? response.data.offen
                            : [];
                        const verarbeitet = paket.length - offen.length;

                        bilanz.erledigt += verarbeitet;
                        bilanz.geaendert += response.data.geaendert || 0;
                        bilanz.uebersprungen += response.data.uebersprungen || 0;
                        if (response.data.errors && response.data.errors.length > 0) {
                            bilanz.errors = bilanz.errors.concat(response.data.errors);
                        }
                        if (response.data.reload) {
                            bilanz.reload = true;
                        }
                        if (response.data.diagnose) {
                            bilanz.diagnose.push(response.data.diagnose);
                        }

                        if (offen.length > 0) {
                            // Kam nichts voran, würde erneutes Senden endlos
                            // pendeln — dann lieber sauber abbrechen. Der Server
                            // bearbeitet immer mindestens eine Seite, dieser Fall
                            // sollte also nie eintreten.
                            if (verarbeitet <= 0) {
                                self.bulkAbschluss(bilanz, 'Server kam im Zeitbudget nicht voran');
                                return;
                            }
                            Array.prototype.unshift.apply(warteschlange, offen);
                        }

                        sendePaket();
                    },
                    error: function(xhr) {
                        // Sollte durch das Zeitbudget des Servers nicht mehr
                        // vorkommen. Falls doch (Gateway-Timeout, Neustart):
                        // nicht stillschweigend weitermachen, sondern melden,
                        // wie weit der Lauf gekommen ist — ein Teil des Pakets
                        // kann bereits geschrieben sein.
                        const grund = 'Anfrage fehlgeschlagen (HTTP ' + (xhr ? xhr.status : '?') + ')';
                        bilanz.errors.push(grund);
                        self.bulkAbschluss(bilanz, grund);
                    }
                });
            };

            sendePaket();
        },

        /**
         * Sammelaktion abschließen: Bilanz melden und ggf. neu laden
         *
         * @param {Object} bilanz Aufsummierte Rückmeldungen aller Pakete
         * @param {?string} abbruchgrund Gesetzt, wenn ein Paket gescheitert ist
         */
        bulkAbschluss: function(bilanz, abbruchgrund) {
            const self = this;

            let meldung = bilanz.geaendert + ' Seite(n) geändert.';
            if (bilanz.uebersprungen > 0) {
                meldung += ' ' + bilanz.uebersprungen + ' ohne Änderung.';
            }

            if (abbruchgrund) {
                meldung = 'Abgebrochen nach ' + bilanz.erledigt + ' von '
                    + bilanz.gesamt + ' Seiten: ' + abbruchgrund + ' — ' + meldung;
            }

            if (abbruchgrund) {
                console.warn('Sammelaktion abgebrochen. Server-Kennzahlen je Paket:',
                    bilanz.diagnose);
            }

            if (bilanz.errors.length > 0) {
                console.warn('Sammelaktion – Meldungen:', bilanz.errors);
                if (!abbruchgrund) {
                    meldung += ' (' + bilanz.errors.length
                        + ' übersprungen — Details in der Konsole)';
                }
            }

            self.showStatus(abbruchgrund ? 'error' : 'saved', meldung);

            if (bilanz.reload && bilanz.geaendert > 0) {
                // Aufklapp-Zustand sichern, damit er das Neuladen
                // übersteht (dasselbe Muster wie in createPage()).
                self.saveExpandedState();
                setTimeout(function() {
                    location.reload();
                }, abbruchgrund ? 2500 : 600);
            } else {
                self.aktualisiereAuswahl();
            }
        },

        /**
         * Initialize jQuery UI Sortable on all lists
         */
        initSortables: function() {
            const self = this;
            let invalidDropDetected = false;  // Flag for invalid drop

            // Make all sortable lists sortable
            $('.sortable-list').sortable({
                items: '> .page-item',
                handle: '.drag-handle',
                placeholder: 'page-item-placeholder',
                connectWith: '.sortable-list',
                tolerance: 'intersect',  // Better drop detection - element must overlap drop zone
                cursor: 'grabbing',
                opacity: 0.8,
                revert: 200,  // Always show revert animation for invalid drops
                forceHelperSize: true,
                scroll: true,  // Enable auto-scrolling when dragging near edges
                scrollSensitivity: 40,
                scrollSpeed: 40,
                // No containment - allow free dragging to prevent "flying away" effect

                // Visual feedback on start
                start: function(event, ui) {
                    invalidDropDetected = false;  // Reset flag

                    ui.placeholder.height(ui.item.height());
                    ui.item.addClass('dragging');

                    // Store original parent list for safety
                    ui.item.data('original-parent', ui.item.parent());
                    ui.item.data('original-index', ui.item.index());

                    // Show all empty-children as potential drop zones
                    $('.empty-children').addClass('accepting-drop').css('display', 'block');

                    // Highlight root list as drop zone
                    $('.page-manager-container').addClass('drag-active');
                },

                // When hovering over a page item row
                over: function(event, ui) {
                    const $list = $(this);

                    // If hovering over an empty-children list, show it
                    if ($list.hasClass('empty-children')) {
                        $list.addClass('accepting-drop').css('display', 'block');
                    }
                },

                // Before stop - CRITICAL: Detect invalid drops
                beforeStop: function(event, ui) {
                    const $currentParent = ui.item.parent();

                    // Check 1: Not in a sortable list at all
                    if (!$currentParent.hasClass('sortable-list')) {
                        invalidDropDetected = true;
                    }

                    // Check 2: In a collapsed/hidden children list
                    if ($currentParent.hasClass('page-tree-children') &&
                        !$currentParent.hasClass('visible') &&
                        !$currentParent.hasClass('accepting-drop')) {
                        invalidDropDetected = true;
                    }

                    // Check 3: Negative position indicates invalid drop
                    const itemPos = ui.item.position();
                    if (itemPos && itemPos.top < -2) {
                        invalidDropDetected = true;
                    }
                },

                // Clean up on stop
                stop: function(event, ui) {
                    ui.item.removeClass('dragging');

                    // CRITICAL: If invalid drop detected, move back manually
                    if (invalidDropDetected) {
                        const $originalParent = ui.item.data('original-parent');
                        const originalIndex = ui.item.data('original-index');

                        if ($originalParent && $originalParent.length) {
                            const $siblings = $originalParent.children('.page-item');
                            if (originalIndex >= $siblings.length) {
                                $originalParent.append(ui.item);
                            } else {
                                ui.item.insertBefore($siblings.eq(originalIndex));
                            }
                        }

                        // Reset flag
                        invalidDropDetected = false;
                    }

                    // Remove drag-active class from container
                    $('.page-manager-container').removeClass('drag-active');

                    // Hide empty-children that are still empty
                    $('.empty-children').each(function() {
                        const $list = $(this);
                        if ($list.children('.page-item').length === 0) {
                            $list.removeClass('accepting-drop').css('display', '');
                        } else {
                            // Has children now, keep it visible and remove empty-children class
                            $list.removeClass('empty-children accepting-drop').addClass('visible');
                        }
                    });
                },

                // Handle changes (both within list and between lists)
                update: function(event, ui) {
                    // Only fire on the list that received the change
                    if (this === ui.item.parent()[0]) {
                        self.saveOrder();
                    }
                },

                // Handle receiving items from another list (hierarchy change)
                receive: function(event, ui) {
                    const $targetList = $(this);
                    const $item = ui.item;

                    // Update UI
                    $targetList.removeClass('empty-children');

                    // Update the parent's has-children class
                    const $parentItem = $targetList.closest('.page-item');
                    if ($parentItem.length) {
                        $parentItem.addClass('has-children');

                        // Add toggle button if not present
                        if (!$parentItem.find('> .page-item-row > .toggle-children').length) {
                            const $placeholder = $parentItem.find('> .page-item-row > .toggle-placeholder');
                            $placeholder.replaceWith(
                                '<button class="toggle-children" aria-expanded="false">' +
                                '<span class="dashicons dashicons-arrow-right-alt2"></span>' +
                                '</button>'
                            );
                        }
                    }
                }
            });
        },

        /**
         * Collect current order and save via AJAX
         */
        saveOrder: function() {
            const self = this;
            const orderData = [];

            // Collect from root list
            $('#page-tree-root').children('.page-item').each(function(index) {
                orderData.push({
                    id: $(this).data('page-id'),
                    parent: 0,
                    order: index
                });
            });

            // Collect from all children lists that have actual pages
            // (visible OR newly populated empty-children that now have items)
            $('.page-tree-children').each(function() {
                const $list = $(this);
                const $items = $list.children('.page-item');

                // Skip if this list has no items
                if ($items.length === 0) {
                    return;
                }

                const parentId = $list.data('parent');

                $items.each(function(index) {
                    orderData.push({
                        id: $(this).data('page-id'),
                        parent: parentId,
                        order: index
                    });
                });
            });

            // Show saving indicator
            self.showStatus('saving');

            // Send AJAX request
            $.ajax({
                url: pageManagerData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'page_manager_update_order',
                    nonce: pageManagerData.nonce,
                    order: orderData
                },
                success: function(response) {
                    if (response.success) {
                        self.showStatus('saved', response.data.message);

                        // Update data attributes
                        orderData.forEach(function(item) {
                            $('.page-item[data-page-id="' + item.id + '"]').attr('data-parent-id', item.parent);
                        });
                    } else {
                        self.showStatus('error', response.data.message);
                        if (response.data.errors && response.data.errors.length > 0) {
                            console.error('Fehler:', response.data.errors);
                        }
                    }
                },
                error: function() {
                    self.showStatus('error', pageManagerData.strings.error);
                }
            });
        },

        /**
         * Show save status
         *
         * @param {string} status - 'saving', 'saved', or 'error'
         * @param {string} message - Optional message
         */
        showStatus: function(status, message) {
            const $spinner = $('#save-spinner');
            const $status = $('#save-status');

            $status.removeClass('status-saved status-error');

            switch (status) {
                case 'saving':
                    $spinner.addClass('is-active');
                    $status.text(pageManagerData.strings.loading);
                    break;

                case 'saved':
                    $spinner.removeClass('is-active');
                    $status.addClass('status-saved').text(message || pageManagerData.strings.saved);
                    // Auto-hide after 3 seconds
                    setTimeout(function() {
                        $status.fadeOut(200, function() {
                            $(this).text('').show();
                        });
                    }, 3000);
                    break;

                case 'error':
                    $spinner.removeClass('is-active');
                    $status.addClass('status-error').text(message || pageManagerData.strings.error);
                    break;
            }
        },

        /**
         * Open the create page modal
         *
         * @param {int} parentId - Parent page ID (0 for top level)
         * @param {string} parentTitle - Parent page title
         */
        openCreateModal: function(parentId, parentTitle) {
            $('#new-page-parent-id').val(parentId);

            if (parentId > 0) {
                $('#new-page-modal-title').text('Unterseite erstellen');
                $('#new-page-parent-name').text(parentTitle);
                $('#new-page-modal-parent-info').show();
            } else {
                $('#new-page-modal-title').text('Neue Seite erstellen');
                $('#new-page-modal-parent-info').hide();
            }

            $('#new-page-modal').fadeIn(200);
            $('#new-page-title').focus();
        },

        /**
         * Close the create page modal
         */
        closeCreateModal: function() {
            $('#new-page-modal').fadeOut(200);
            $('#new-page-title').val('');
            $('#new-page-parent-id').val(0);
            $('#new-page-modal-parent-info').hide();
        },

        /**
         * Create a new page
         *
         * @param {string} title - Page title
         */
        createPage: function(title) {
            const self = this;
            const parentId = parseInt($('#new-page-parent-id').val(), 10) || 0;

            // Show loading
            self.showStatus('saving', 'Erstelle Seite...');

            // Send AJAX request
            $.ajax({
                url: pageManagerData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'page_manager_create_page',
                    nonce: pageManagerData.nonce,
                    title: title,
                    parent_id: parentId
                },
                success: function(response) {
                    if (response.success) {
                        self.showStatus('saved', response.data.message);
                        self.closeCreateModal();

                        // Save current expanded state before reload
                        self.saveExpandedState();

                        // If child page created, ensure parent is expanded
                        if (parentId > 0) {
                            const state = self.getExpandedState();
                            state[parentId] = true;
                            localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
                        }

                        // Reload page to show new page
                        setTimeout(function() {
                            location.reload();
                        }, 500);
                    } else {
                        self.showStatus('error', response.data.message);
                    }
                },
                error: function() {
                    self.showStatus('error', 'Fehler beim Erstellen der Seite.');
                }
            });
        },

        /**
         * Delete a page
         *
         * @param {int} pageId - Page ID to delete
         */
        deletePage: function(pageId) {
            const self = this;

            // Show loading
            self.showStatus('saving', 'Lösche Seite...');

            // Send AJAX request
            $.ajax({
                url: pageManagerData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'page_manager_delete_page',
                    nonce: pageManagerData.nonce,
                    page_id: pageId
                },
                success: function(response) {
                    if (response.success) {
                        self.showStatus('saved', response.data.message);

                        // Remove item from DOM
                        $('.page-item[data-page-id="' + pageId + '"]').fadeOut(300, function() {
                            $(this).remove();
                        });
                    } else {
                        self.showStatus('error', response.data.message);
                    }
                },
                error: function() {
                    self.showStatus('error', 'Fehler beim Löschen der Seite.');
                }
            });
        },

        /**
         * Toggle page status (publish <-> draft)
         *
         * @param {int} pageId - Page ID
         * @param {jQuery} $button - Button element
         */
        /**
         * Get expanded state from localStorage
         *
         * @returns {Object} Map of pageId -> boolean
         */
        getExpandedState: function() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
            } catch (e) {
                return {};
            }
        },

        /**
         * Save current expanded state to localStorage
         */
        saveExpandedState: function() {
            const state = {};
            // Use a small delay so the DOM has updated aria-expanded
            setTimeout(function() {
                $('.toggle-children').each(function() {
                    const pageId = $(this).closest('.page-item').data('page-id');
                    const isExpanded = $(this).attr('aria-expanded') === 'true';
                    if (isExpanded) {
                        state[pageId] = true;
                    }
                });
                localStorage.setItem(STORAGE_KEY, JSON.stringify(state));
            }, 50);
        },

        /**
         * Restore expanded state from localStorage
         */
        restoreExpandedState: function() {
            const state = this.getExpandedState();
            const expandedIds = Object.keys(state).filter(function(id) { return state[id]; });

            if (expandedIds.length === 0) {
                return;
            }

            expandedIds.forEach(function(pageId) {
                const $pageItem = $('.page-item[data-page-id="' + pageId + '"]');
                const $toggle = $pageItem.find('> .page-item-row > .toggle-children');
                const $children = $pageItem.children('.page-tree-children');

                if ($toggle.length && $children.length) {
                    $children.addClass('visible').show();
                    $toggle.attr('aria-expanded', 'true');
                    $toggle.find('.dashicons')
                        .removeClass('dashicons-arrow-right-alt2')
                        .addClass('dashicons-arrow-down-alt2');
                }
            });
        },

        toggleStatus: function(pageId, $button) {
            const self = this;

            // Show loading
            self.showStatus('saving', 'Status wird geändert...');

            // Send AJAX request
            $.ajax({
                url: pageManagerData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'page_manager_toggle_status',
                    nonce: pageManagerData.nonce,
                    page_id: pageId
                },
                success: function(response) {
                    if (response.success) {
                        self.showStatus('saved', response.data.message);

                        const newStatus = response.data.new_status;
                        const newIcon = response.data.icon;

                        // Update button
                        $button.data('current-status', newStatus);
                        $button.find('.dashicons')
                            .removeClass('dashicons-visibility dashicons-hidden')
                            .addClass(newIcon);

                        // Update page item class
                        const $pageItem = $button.closest('.page-item');
                        $pageItem.removeClass('status-publish status-draft status-pending status-private');
                        $pageItem.addClass('status-' + newStatus);

                        // Update status badge
                        const $badge = $pageItem.find('.page-status-badge');
                        if (newStatus === 'draft') {
                            if ($badge.length === 0) {
                                $pageItem.find('.page-title').after('<span class="page-status-badge badge-draft">Entwurf</span>');
                            } else {
                                $badge.removeClass().addClass('page-status-badge badge-draft').text('Entwurf');
                            }
                        } else if (newStatus === 'publish') {
                            $badge.remove();
                        }
                    } else {
                        self.showStatus('error', response.data.message);
                    }
                },
                error: function() {
                    self.showStatus('error', 'Fehler beim Ändern des Status.');
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        PageManager.init();
    });

})(jQuery);
