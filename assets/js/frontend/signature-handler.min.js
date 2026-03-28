/**
 * WPRobo DocuMerge — Signature Handler
 *
 * Wraps the Signature Pad library for signature canvas fields.
 *
 * @package WPRobo_DocuMerge
 * @since   1.0.0
 */

(function($) {
    'use strict';

    var WPRoboDocuMerge_Signature = {

        pads: {},

        /**
         * Initialize all signature canvases on the page.
         *
         * @since 1.0.0
         */
        init: function() {
            var self = this;
            $('.wdm-signature-canvas').each(function() {
                self.wprobo_documerge_init_canvas($(this));
            });
            this.bindEvents();
        },

        /**
         * Bind clear and resize events.
         *
         * @since 1.0.0
         */
        bindEvents: function() {
            var self = this;

            $(document).on('click', '.wdm-signature-clear', function(e) {
                e.preventDefault();
                var targetId = $(this).data('target');
                self.wprobo_documerge_clear_canvas($('#' + targetId));
            });

            var resizeTimer;
            $(window).on('resize', function() {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(function() {
                    self.wprobo_documerge_handle_resize();
                }, 250);
            });
        },

        /**
         * Initialize a single signature canvas.
         *
         * @since 1.0.0
         * @param {jQuery} $canvas jQuery canvas element.
         */
        wprobo_documerge_init_canvas: function($canvas) {
            var self     = this;
            var canvasEl = $canvas[0];
            var penColor = $canvas.data('pen-color') || '#042157';
            var bgColor  = $canvas.data('bg-color') || '#ffffff';

            // Wait for layout to complete so dimensions are accurate.
            setTimeout(function() {
                // Set canvas pixel dimensions to match its CSS display size.
                // SignaturePad v4 handles devicePixelRatio internally —
                // do NOT multiply by ratio or call ctx.scale().
                var displayWidth  = $canvas.width() || $canvas.parent().width() || 600;
                var displayHeight = $canvas.height() || 200;

                canvasEl.width  = displayWidth;
                canvasEl.height = displayHeight;

                // Fill background colour.
                var ctx = canvasEl.getContext('2d');
                ctx.fillStyle = bgColor;
                ctx.fillRect(0, 0, displayWidth, displayHeight);

                // Create SignaturePad — it handles HiDPI scaling itself.
                var pad = new SignaturePad(canvasEl, {
                    penColor:        penColor,
                    backgroundColor: bgColor,
                    minWidth:        1,
                    maxWidth:        2.5,
                });

                // Export to hidden input after each stroke.
                pad.addEventListener('endStroke', function() {
                    var $input = $('#' + canvasEl.id.replace('wdm-sig-', 'wdm-sig-input-'));
                    $input.val(pad.toDataURL('image/png'));
                });

                self.pads[canvasEl.id] = pad;
            }, 150);
        },

        /**
         * Clear a signature canvas and its hidden input.
         *
         * @since 1.0.0
         * @param {jQuery} $canvas Canvas element to clear.
         */
        wprobo_documerge_clear_canvas: function($canvas) {
            if (!$canvas.length) {
                return;
            }
            var canvasEl = $canvas[0];
            var pad      = this.pads[canvasEl.id];

            if (pad) {
                pad.clear();
                var bgColor = $canvas.data('bg-color') || '#ffffff';
                var ctx     = canvasEl.getContext('2d');
                ctx.fillStyle = bgColor;
                ctx.fillRect(0, 0, canvasEl.width, canvasEl.height);
            }

            var $input = $('#' + canvasEl.id.replace('wdm-sig-', 'wdm-sig-input-'));
            $input.val('');
        },

        /**
         * Check whether a signature canvas is empty.
         *
         * @since  1.0.0
         * @param  {jQuery} $canvas Canvas element to check.
         * @return {boolean} True if empty.
         */
        wprobo_documerge_is_empty: function($canvas) {
            if (!$canvas.length) {
                return true;
            }
            var pad = this.pads[$canvas[0].id];
            return pad ? pad.isEmpty() : true;
        },

        /**
         * Handle window resize — recalculate canvas and clear.
         *
         * @since 1.0.0
         */
        wprobo_documerge_handle_resize: function() {
            var self = this;
            $.each(this.pads, function(id, pad) {
                var $canvas  = $('#' + id);
                var canvasEl = $canvas[0];

                if (!canvasEl) {
                    return;
                }

                var displayWidth  = $canvas.width() || 600;
                var displayHeight = $canvas.height() || 200;

                canvasEl.width  = displayWidth;
                canvasEl.height = displayHeight;

                pad.clear();

                var bgColor = $canvas.data('bg-color') || '#ffffff';
                var ctx     = canvasEl.getContext('2d');
                ctx.fillStyle = bgColor;
                ctx.fillRect(0, 0, displayWidth, displayHeight);

                var $input = $('#' + id.replace('wdm-sig-', 'wdm-sig-input-'));
                $input.val('');
            });
        },
    };

    $(document).ready(function() {
        WPRoboDocuMerge_Signature.init();
    });

    window.WPRoboDocuMerge_Signature = WPRoboDocuMerge_Signature;

})(jQuery);
