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

                // Handle receiving items from another list (hierarchy change)
                receive: function(event, ui) {
                    const $targetList = $(this);
                    const $item = ui.item;
                    const pageId = $item.data('page-id');
                    const newParentId = $targetList.data('parent');

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

                    // Save the new hierarchy
                    self.updateParent(pageId, newParentId);
                }
            });
        },

        /**
         * Update page parent via AJAX
         *
         * @param {number} pageId Page ID
         * @param {number} newParentId New parent ID
         */
        updateParent: function(pageId, newParentId) {
            const self = this;

            // Show saving indicator
            self.showStatus('saving');

            // Send AJAX request
            $.ajax({
                url: pageManagerData.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'page_manager_update_parent',
                    nonce: pageManagerData.nonce,
                    page_id: pageId,
                    new_parent: newParentId
                },
                success: function(response) {
                    if (response.success) {
                        self.showStatus('saved', response.data.message);

                        // Update data attribute
                        $('.page-item[data-page-id="' + pageId + '"]').attr('data-parent-id', newParentId);
                    } else {
                        self.showStatus('error', response.data.message);
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
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        PageManager.init();
    });

})(jQuery);
