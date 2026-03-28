/**
 * WPRobo DocuMerge — Password Field Handler
 *
 * Handles show/hide password toggle and password strength indicator.
 *
 * @package WPRobo_DocuMerge
 * @since   1.3.0
 */

(function($) {
    'use strict';

    // Toggle password visibility.
    $(document).on('click', '.wdm-password-toggle', function(e) {
        e.preventDefault();
        var $wrap  = $(this).closest('.wdm-password-wrap');
        var $input = $wrap.find('.wdm-password-input');
        var $icon  = $(this).find('.dashicons');

        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('dashicons-visibility').addClass('dashicons-hidden');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('dashicons-hidden').addClass('dashicons-visibility');
        }
    });

    // Password strength indicator.
    $(document).on('input', '.wdm-password-input', function() {
        var $input    = $(this);
        var $strength = $input.closest('.wdm-field-group').find('.wdm-password-strength');

        // Only process the main password field (not confirm).
        if ($input.hasClass('wdm-password-confirm') || !$strength.length) {
            return;
        }

        var val    = $input.val();
        var score  = 0;
        var labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];

        if (val.length >= 6)  { score++; }
        if (val.length >= 10) { score++; }
        if (/[a-z]/.test(val) && /[A-Z]/.test(val)) { score++; }
        if (/\d/.test(val))   { score++; }
        if (/[^a-zA-Z0-9]/.test(val)) { score++; }

        // Cap at 4.
        if (score > 4) { score = 4; }

        // Empty = hide.
        if (!val) { score = 0; }

        // Update classes and label.
        $strength.attr('class', 'wdm-password-strength wdm-strength-' + score);
        $strength.find('.wdm-strength-label').text(labels[score] || '');
    });

})(jQuery);
