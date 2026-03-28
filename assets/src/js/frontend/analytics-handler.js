/**
 * WPRobo DocuMerge — Frontend Analytics Tracker
 *
 * Tracks form view and start events on the frontend.
 * Completions are tracked server-side in PHP to ensure reliability.
 *
 * @package WPRobo_DocuMerge
 * @since   1.3.0
 */

(function($) {
    'use strict';

    /**
     * WPRobo DocuMerge Analytics
     *
     * @since 1.3.0
     */
    var WPRoboDocuMerge_Analytics = {

        /**
         * Tracks which events have already been sent in this page view.
         *
         * @since 1.3.0
         * @type  {Object}
         */
        tracked: {},

        /**
         * Initialize analytics tracking for all forms on the page.
         *
         * @since 1.3.0
         */
        init: function() {
            var self = this;

            $('.wdm-form-wrap').each(function() {
                var formId = $(this).data('form-id');
                if (!formId) {
                    return;
                }

                // Track view immediately.
                self.wproboDocuMerge_track(formId, 'view');

                // Track start on first interaction with any input.
                $(this).one('focus', 'input, select, textarea', function() {
                    self.wproboDocuMerge_track(formId, 'start');
                });
            });
        },

        /**
         * Send an analytics event to the server.
         *
         * Prevents duplicate tracking for the same form/event
         * combination within a single page view.
         *
         * @since 1.3.0
         * @param {number} formId    The form ID.
         * @param {string} eventType The event type (view or start).
         */
        wproboDocuMerge_track: function(formId, eventType) {
            var key = formId + '_' + eventType;

            // Prevent duplicate tracking in the same page view.
            if (this.tracked[key]) {
                return;
            }
            this.tracked[key] = true;

            var ajaxUrl = (typeof wprobo_documerge_frontend_vars !== 'undefined')
                ? wprobo_documerge_frontend_vars.ajax_url
                : '';
            var nonce = (typeof wprobo_documerge_frontend_vars !== 'undefined')
                ? wprobo_documerge_frontend_vars.nonce
                : '';

            if (!ajaxUrl) {
                return;
            }

            $.ajax({
                url:  ajaxUrl,
                type: 'POST',
                data: {
                    action:     'wprobo_documerge_track_event',
                    nonce:      nonce,
                    form_id:    formId,
                    event_type: eventType
                }
            });
        }
    };

    $(document).ready(function() {
        WPRoboDocuMerge_Analytics.init();
    });

    window.WPRoboDocuMerge_Analytics = WPRoboDocuMerge_Analytics;

})(jQuery);
