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

    const PageManager = {

        /**
         * Initialize the page manager
         */
        init: function() {
            this.bindEvents();
            this.initSortables();
        },

        /**
         * Bind UI events
         */
        bindEvents: function() {
            const self = this;

            // Create new page - open modal
            $('#create-new-page').on('click', function() {
                $('#new-page-modal').fadeIn(200);
                $('#new-page-title').focus();
            });

            // Create new page - close modal
            $('#create-page-cancel').on('click', function() {
                $('#new-page-modal').fadeOut(200);
                $('#new-page-title').val('');
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
                    $('#create-page-cancel').click();
                }
            });

            // Delete page
            $(document).on('click', '.delete-page', function() {
                const pageId = $(this).data('page-id');
                const pageTitle = $(this).data('page-title');

                if (confirm('Möchten Sie die Seite "' + pageTitle + '" wirklich löschen?')) {
                    self.deletePage(pageId);
                }
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
            });

            // Expand all
            $('#expand-all').on('click', function() {
                $('.page-tree-children').addClass('visible').slideDown(200);
                $('.toggle-children')
                    .attr('aria-expanded', 'true')
                    .find('.dashicons')
                    .removeClass('dashicons-arrow-right-alt2')
                    .addClass('dashicons-arrow-down-alt2');
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
            });
        },

        /**
         * Initialize jQuery UI Sortable on all lists
         */
        initSortables: function() {
            const self = this;

            // Make all sortable lists sortable
            $('.sortable-list').sortable({
                items: '> .page-item',
                handle: '.drag-handle',
                placeholder: 'page-item-placeholder',
                connectWith: '.sortable-list',
                tolerance: 'pointer',
                cursor: 'grabbing',
                opacity: 0.8,
                revert: 100,

                // Visual feedback on start
                start: function(event, ui) {
                    ui.placeholder.height(ui.item.height());
                    ui.item.addClass('dragging');

                    // Expand empty drop zones
                    $('.empty-children').addClass('accepting-drop');
                },

                // Clean up on stop
                stop: function(event, ui) {
                    ui.item.removeClass('dragging');
                    $('.empty-children').removeClass('accepting-drop');
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

            // Collect order from all lists
            $('.sortable-list').each(function() {
                const parentId = $(this).data('parent');

                $(this).children('.page-item').each(function(index) {
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
         * Create a new page
         *
         * @param {string} title - Page title
         */
        createPage: function(title) {
            const self = this;

            // Show loading
            self.showStatus('saving', 'Erstelle Seite...');

            // Send AJAX request
            $.ajax({
                url: pageManagerData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'page_manager_create_page',
                    nonce: pageManagerData.nonce,
                    title: title
                },
                success: function(response) {
                    if (response.success) {
                        self.showStatus('saved', response.data.message);

                        // Close modal
                        $('#new-page-modal').fadeOut(200);
                        $('#new-page-title').val('');

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
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        PageManager.init();
    });

})(jQuery);
