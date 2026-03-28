(function($) {
    'use strict';

    var WPRoboDocuMerge_Stripe = {

        stripe: null,
        elements: null,
        cardElement: null,
        cardNumberElement: null,
        cardExpiryElement: null,
        cardCvcElement: null,
        submissionId: null,
        statusToken: null,
        layout: 'single',
        cardComplete: false,
        cardEmpty: true,

        /**
         * Initialize Stripe with publishable key and mount card element(s).
         * @param {string} publishableKey Stripe publishable key
         */
        init: function(publishableKey) {
            if (!publishableKey || typeof Stripe === 'undefined') return;

            this.stripe = Stripe(publishableKey);
            this.elements = this.stripe.elements();

            var vars = typeof wprobo_documerge_frontend_vars !== 'undefined' ? wprobo_documerge_frontend_vars : {};
            this.layout = vars.stripe_card_layout || 'single';
            var hidePostal = vars.stripe_hide_postal === '1';

            var baseStyle = {
                fontSize: '16px',
                color: '#1a1a1a',
                fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif',
                '::placeholder': { color: '#6b7280' }
            };
            var invalidStyle = { color: '#dc2626' };

            var $mount = $('.wdm-stripe-card-element');
            if (!$mount.length) return;

            if ('multi' === this.layout) {
                // Replace single mount point with multi-line fields.
                var postalHtml = '';
                if (!hidePostal) {
                    postalHtml =
                        '<div class="wdm-stripe-field-row">' +
                            '<label>' + (vars.i18n_postal_code || 'Postal / ZIP Code') + '</label>' +
                            '<input type="text" id="wdm-card-postal" class="wdm-stripe-postal-input" placeholder="e.g. SW1A 1AA" autocomplete="postal-code">' +
                        '</div>';
                }

                var multiHtml =
                    '<div class="wdm-stripe-multi-fields">' +
                        '<div class="wdm-stripe-field-row">' +
                            '<label>' + (vars.i18n_card_number || 'Card Number') + '</label>' +
                            '<div class="wdm-stripe-field" id="wdm-card-number"></div>' +
                        '</div>' +
                        '<div class="wdm-stripe-field-row wdm-stripe-row-half">' +
                            '<div class="wdm-stripe-field-col">' +
                                '<label>' + (vars.i18n_card_expiry || 'Expiry') + '</label>' +
                                '<div class="wdm-stripe-field" id="wdm-card-expiry"></div>' +
                            '</div>' +
                            '<div class="wdm-stripe-field-col">' +
                                '<label>' + (vars.i18n_card_cvc || 'CVC') + '</label>' +
                                '<div class="wdm-stripe-field" id="wdm-card-cvc"></div>' +
                            '</div>' +
                        '</div>' +
                        postalHtml +
                    '</div>';
                $mount.replaceWith(multiHtml);

                this.cardNumberElement = this.elements.create('cardNumber', {
                    style: { base: baseStyle, invalid: invalidStyle },
                    showIcon: true
                });
                this.cardExpiryElement = this.elements.create('cardExpiry', {
                    style: { base: baseStyle, invalid: invalidStyle }
                });
                this.cardCvcElement = this.elements.create('cardCvc', {
                    style: { base: baseStyle, invalid: invalidStyle }
                });

                this.cardNumberElement.mount('#wdm-card-number');
                this.cardExpiryElement.mount('#wdm-card-expiry');
                this.cardCvcElement.mount('#wdm-card-cvc');

                // Error display + track completion from any element.
                var self = this;
                var multiState = { number: false, expiry: false, cvc: false, numberEmpty: true };
                var showError = function(event) {
                    var $error = $('.wdm-stripe-card-error');
                    $error.text(event.error ? event.error.message : '');
                };
                this.cardNumberElement.on('change', function(e) {
                    showError(e);
                    multiState.number = e.complete;
                    multiState.numberEmpty = e.empty;
                    self.cardComplete = multiState.number && multiState.expiry && multiState.cvc;
                    self.cardEmpty = multiState.numberEmpty;
                });
                this.cardExpiryElement.on('change', function(e) {
                    showError(e);
                    multiState.expiry = e.complete;
                    self.cardComplete = multiState.number && multiState.expiry && multiState.cvc;
                });
                this.cardCvcElement.on('change', function(e) {
                    showError(e);
                    multiState.cvc = e.complete;
                    self.cardComplete = multiState.number && multiState.expiry && multiState.cvc;
                });

                // For confirmCardPayment, we use the cardNumber element.
                this.cardElement = this.cardNumberElement;
            } else {
                // Single line card element.
                this.cardElement = this.elements.create('card', {
                    style: { base: baseStyle, invalid: invalidStyle },
                    hidePostalCode: hidePostal
                });
                this.cardElement.mount($mount[0]);

                var self = this;
                this.cardElement.on('change', function(event) {
                    var $error = $('.wdm-stripe-card-error');
                    $error.text(event.error ? event.error.message : '');
                    self.cardComplete = event.complete;
                    self.cardEmpty = event.empty;
                });
            }
        },

        /**
         * Confirm card payment with the given client secret.
         * @param {string} clientSecret PaymentIntent client_secret
         * @return {object} jQuery Deferred promise
         */
        wprobo_documerge_confirm_payment: function(clientSecret) {
            var deferred = $.Deferred();

            var paymentData = {
                payment_method: { card: this.cardElement }
            };

            // Include postal code in billing details if available.
            var $postal = $('#wdm-card-postal');
            if ($postal.length && $postal.val().trim()) {
                paymentData.payment_method.billing_details = {
                    address: { postal_code: $postal.val().trim() }
                };
            }

            this.stripe.confirmCardPayment(clientSecret, paymentData).then(function(result) {
                if (result.error) {
                    deferred.reject(result.error);
                } else if (result.paymentIntent && result.paymentIntent.status === 'succeeded') {
                    deferred.resolve(result.paymentIntent);
                } else {
                    deferred.reject({ message: 'Payment could not be completed.' });
                }
            });

            return deferred.promise();
        },

        /**
         * Check if Stripe is initialized and card element is mounted.
         * @return {boolean}
         */
        wprobo_documerge_is_ready: function() {
            return this.stripe !== null && this.cardElement !== null;
        },

        /**
         * Check if the card has been filled (not empty).
         * @return {boolean}
         */
        wprobo_documerge_is_card_filled: function() {
            return !this.cardEmpty;
        },

        /**
         * Poll for submission status after payment confirmation.
         * @param {number} submissionId
         * @param {string} statusToken
         * @param {Function} onComplete callback(data)
         * @param {Function} onError callback(message)
         */
        wprobo_documerge_poll_status: function(submissionId, statusToken, onComplete, onError) {
            var attempts = 0;
            var maxAttempts = 15;
            var ajaxUrl = (typeof wprobo_documerge_frontend_vars !== 'undefined') ? wprobo_documerge_frontend_vars.ajax_url : ajaxurl;
            var nonce = (typeof wprobo_documerge_frontend_vars !== 'undefined') ? wprobo_documerge_frontend_vars.nonce : '';

            var poll = function() {
                attempts++;
                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'wprobo_documerge_check_status',
                        nonce: nonce,
                        submission_id: submissionId,
                        status_token: statusToken,
                    },
                    success: function(response) {
                        if (!response.success) {
                            onError(response.data ? response.data.message : 'Status check failed.');
                            return;
                        }
                        var status = response.data.status;
                        if (status === 'completed') {
                            onComplete(response.data);
                        } else if (status === 'error') {
                            onError('Document generation failed. Please contact support.');
                        } else if (attempts < maxAttempts) {
                            setTimeout(poll, 2000);
                        } else {
                            onComplete({
                                status: 'timeout',
                                message: 'Taking longer than expected. Check your email for the document.'
                            });
                        }
                    },
                    error: function() {
                        if (attempts < maxAttempts) {
                            setTimeout(poll, 2000);
                        } else {
                            onError('Network error while checking status.');
                        }
                    }
                });
            };

            poll();
        },

        /**
         * Reset the card element(s) after an error.
         */
        wprobo_documerge_reset: function() {
            if ('multi' === this.layout) {
                if (this.cardNumberElement) this.cardNumberElement.clear();
                if (this.cardExpiryElement) this.cardExpiryElement.clear();
                if (this.cardCvcElement) this.cardCvcElement.clear();
            } else if (this.cardElement) {
                this.cardElement.clear();
            }
            $('.wdm-stripe-card-error').text('');
        },
    };

    // Initialize when DOM ready if publishable key is available
    $(document).ready(function() {
        if (typeof wprobo_documerge_frontend_vars !== 'undefined' && wprobo_documerge_frontend_vars.stripe_publishable_key) {
            WPRoboDocuMerge_Stripe.init(wprobo_documerge_frontend_vars.stripe_publishable_key);
        }
    });

    window.WPRoboDocuMerge_Stripe = WPRoboDocuMerge_Stripe;

})(jQuery);
