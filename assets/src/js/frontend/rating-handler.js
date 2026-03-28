/**
 * WPRobo DocuMerge — Rating Field Handler
 *
 * Handles star rating hover effects and click-to-set interaction
 * on the frontend form rating field.
 *
 * @package WPRobo_DocuMerge
 * @since   1.0.0
 */

(function($) {
    'use strict';

    /**
     * WPRobo DocuMerge Rating module.
     *
     * @since 1.0.0
     * @type  {Object}
     */
    var WPRoboDocuMerge_Rating = {

        /**
         * Initialize the rating handler.
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
            // Hover effect — highlight stars up to hovered value.
            $(document).on('mouseenter', '.wdm-star', function() {
                var val = parseInt($(this).data('value'), 10);
                var $wrap = $(this).closest('.wdm-rating-wrap');
                $wrap.find('.wdm-star').each(function() {
                    var sv = parseInt($(this).data('value'), 10);
                    $(this).toggleClass('wdm-star-hover', sv <= val);
                });
            });

            // Remove hover effect when mouse leaves the rating wrapper.
            $(document).on('mouseleave', '.wdm-rating-wrap', function() {
                $(this).find('.wdm-star').removeClass('wdm-star-hover');
            });

            // Click to set rating value.
            $(document).on('click', '.wdm-star', function() {
                var val = parseInt($(this).data('value'), 10);
                var $wrap = $(this).closest('.wdm-rating-wrap');
                $wrap.find('.wdm-rating-value').val(val).trigger('change');
                $wrap.find('.wdm-star').each(function() {
                    var sv = parseInt($(this).data('value'), 10);
                    var $icon = $(this).find('.dashicons');
                    $(this).toggleClass('wdm-star-filled', sv <= val);
                    $icon.toggleClass('dashicons-star-filled', sv <= val)
                         .toggleClass('dashicons-star-empty', sv > val);
                });
            });
        }
    };

    $(document).ready(function() {
        WPRoboDocuMerge_Rating.init();
    });

    window.WPRoboDocuMerge_Rating = WPRoboDocuMerge_Rating;

})(jQuery);
