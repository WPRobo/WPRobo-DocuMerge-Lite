(function($) {
    'use strict';

    var WPRoboDocuMerge_Captcha = {

        type: '', // Set from localized vars

        /**
         * Initialize captcha handler by reading type from localized variables.
         *
         * @return {void}
         */
        init: function() {
            if (typeof wprobo_documerge_frontend_vars !== 'undefined') {
                this.type = wprobo_documerge_frontend_vars.captcha_type || 'none';
            }
        },

        /**
         * Get captcha token.
         * For v3: returns a jQuery Deferred/Promise. For others: resolves immediately.
         *
         * @return {jQuery.Promise} A promise that resolves with the captcha token string.
         */
        wprobo_documerge_get_token: function() {
            var deferred = $.Deferred();

            switch (this.type) {
                case 'recaptcha_v2':
                    if (typeof grecaptcha !== 'undefined') {
                        deferred.resolve(grecaptcha.getResponse());
                    } else {
                        deferred.resolve('');
                    }
                    break;

                case 'recaptcha_v3':
                    if (typeof grecaptcha !== 'undefined') {
                        var siteKey = wprobo_documerge_frontend_vars.recaptcha_v3_site_key || '';
                        grecaptcha.ready(function() {
                            grecaptcha.execute(siteKey, {action: 'submit'}).then(function(token) {
                                $('#wdm-recaptcha-v3-token').val(token);
                                deferred.resolve(token);
                            });
                        });
                    } else {
                        deferred.resolve('');
                    }
                    break;

                case 'hcaptcha':
                    if (typeof hcaptcha !== 'undefined') {
                        deferred.resolve(hcaptcha.getResponse());
                    } else {
                        deferred.resolve('');
                    }
                    break;

                default:
                    deferred.resolve('');
                    break;
            }

            return deferred.promise();
        },

        /**
         * Validate that a captcha response has been provided.
         *
         * @return {boolean} True if validation passes or captcha is not required.
         */
        wprobo_documerge_validate: function() {
            switch (this.type) {
                case 'recaptcha_v2':
                    return typeof grecaptcha !== 'undefined' && grecaptcha.getResponse().length > 0;
                case 'recaptcha_v3':
                    return true; // v3 always gets token on submit
                case 'hcaptcha':
                    return typeof hcaptcha !== 'undefined' && hcaptcha.getResponse().length > 0;
                default:
                    return true;
            }
        },

        /**
         * Reset the captcha widget so it can be solved again.
         *
         * @return {void}
         */
        wprobo_documerge_reset: function() {
            switch (this.type) {
                case 'recaptcha_v2':
                    if (typeof grecaptcha !== 'undefined') { grecaptcha.reset(); }
                    break;
                case 'hcaptcha':
                    if (typeof hcaptcha !== 'undefined') { hcaptcha.reset(); }
                    break;
                case 'recaptcha_v3':
                    // v3 re-executes automatically on next submit
                    break;
            }
        },
    };

    $(document).ready(function() {
        WPRoboDocuMerge_Captcha.init();
    });

    window.WPRoboDocuMerge_Captcha = WPRoboDocuMerge_Captcha;

})(jQuery);
