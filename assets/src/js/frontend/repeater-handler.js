/**
 * WPRobo DocuMerge — Repeater Field Handler
 *
 * Handles add/remove row interactions and name attribute re-indexing
 * for the frontend form repeater field.
 *
 * @package WPRobo_DocuMerge
 * @since   1.0.0
 */

(function($) {
    'use strict';

    /**
     * WPRobo DocuMerge Repeater module.
     *
     * @since 1.0.0
     * @type  {Object}
     */
    var WPRoboDocuMerge_Repeater = {

        /**
         * Initialize the repeater handler.
         *
         * @since 1.0.0
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind all event handlers.
         *
         * @since 1.0.0
         */
        bindEvents: function() {
            // Add a new row to the repeater.
            $(document).on('click', '.wdm-repeater-add', function(e) {
                e.preventDefault();
                var $wrap = $(this).closest('.wdm-repeater-wrap');
                var $tbody = $wrap.find('tbody');
                var maxRows = parseInt($wrap.data('max'), 10) || 10;
                var currentRows = $tbody.find('.wdm-repeater-row').length;

                if (currentRows >= maxRows) { return; }

                // Clone the first row and clear values.
                var $firstRow = $tbody.find('.wdm-repeater-row').first();
                var $newRow = $firstRow.clone();
                var rowIndex = currentRows;

                $newRow.find('input').each(function() {
                    $(this).val('');
                    // Update name attribute index.
                    var name = $(this).attr('name') || '';
                    name = name.replace(/\[\d+\]/, '[' + rowIndex + ']');
                    $(this).attr('name', name);
                });

                $tbody.append($newRow);

                // Hide add button if at max.
                if (currentRows + 1 >= maxRows) {
                    $(this).hide();
                }
            });

            // Remove a row from the repeater.
            $(document).on('click', '.wdm-repeater-remove', function(e) {
                e.preventDefault();
                var $wrap = $(this).closest('.wdm-repeater-wrap');
                var $tbody = $wrap.find('tbody');
                var minRows = parseInt($wrap.data('min'), 10) || 1;
                var currentRows = $tbody.find('.wdm-repeater-row').length;

                if (currentRows <= minRows) { return; }

                $(this).closest('.wdm-repeater-row').remove();

                // Re-show add button.
                $wrap.find('.wdm-repeater-add').show();

                // Re-index names.
                $tbody.find('.wdm-repeater-row').each(function(ri) {
                    $(this).find('input').each(function() {
                        var name = $(this).attr('name') || '';
                        name = name.replace(/\[\d+\]/, '[' + ri + ']');
                        $(this).attr('name', name);
                    });
                });
            });
        }
    };

    $(document).ready(function() {
        WPRoboDocuMerge_Repeater.init();
    });

    window.WPRoboDocuMerge_Repeater = WPRoboDocuMerge_Repeater;

})(jQuery);
