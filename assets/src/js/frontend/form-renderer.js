(function($) {
    'use strict';

    // ── Validator ────────────────────────────────────────────
    var WPRoboDocuMerge_Validator = {
        init: function() {
            this.bindBlurValidation();
            this.bindFocusClear();
        },

        bindBlurValidation: function() {
            $(document).on('blur', '.wdm-form input, .wdm-form select, .wdm-form textarea', function() {
                WPRoboDocuMerge_Validator.validateField($(this));
            });
        },

        bindFocusClear: function() {
            $(document).on('focus', '.wdm-form input, .wdm-form select, .wdm-form textarea', function() {
                WPRoboDocuMerge_Validator.clearFieldError($(this));
            });
        },

        validateField: function($field) {
            // Skip honeypot
            if ($field.attr('name') === 'wdm_trap') {
                return true;
            }

            // Skip fields hidden by conditional logic — uses the class we add
            // instead of :hidden (which fails during slideUp animation)
            if ($field.closest('.wdm-conditionally-hidden').length) {
                return true;
            }

            // Skip fields that are display:none (hidden steps, etc.)
            if ($field.closest('.wdm-field-group').is(':hidden')) {
                return true;
            }

            var $outerWrap   = $field.closest('[data-field-id]');
            var value        = $field.val() || '';
            var type         = $field.closest('.wdm-field-group').data('field-type') || $field.attr('type') || 'text';
            var required     = $field.prop('required') || $field.data('required');
            var customError  = $outerWrap.length ? $outerWrap.data('error-message') : '';
            var error        = '';

            // Required check
            if (required && !value.trim()) {
                error = customError || 'This field is required.';
            }

            // Format checks (only if has value)
            if (!error && value.trim()) {
                switch (type) {
                    case 'email':
                        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                            error = customError || 'Please enter a valid email address.';
                        }
                        break;
                    case 'phone':
                        // Use intl-tel-input validation if available.
                        if (typeof WPRoboDocuMerge_Phone !== 'undefined' && $field.hasClass('wdm-intl-phone')) {
                            if (!WPRoboDocuMerge_Phone.isValid($field[0])) {
                                error = customError || WPRoboDocuMerge_Phone.getError($field[0]);
                            }
                        } else {
                            // Fallback: basic regex.
                            if (!/^[+\d\s\-().]{4,}$/.test(value)) {
                                error = customError || 'Please enter a valid phone number.';
                            }
                        }
                        break;
                    case 'number':
                        if (isNaN(parseFloat(value)) || !isFinite(value)) {
                            error = customError || 'Please enter a valid number.';
                        }
                        break;
                    case 'url':
                        // Must start with http:// or https://
                        if (!/^https?:\/\/.+\..+/.test(value)) {
                            error = customError || 'Please enter a valid URL (e.g. https://example.com).';
                        }
                        break;
                    case 'password':
                        // Check confirm password match if confirm field exists.
                        var $confirmField = $field.closest('.wdm-field-group').find('.wdm-password-confirm');
                        if ($confirmField.length && $confirmField.val() && value !== $confirmField.val()) {
                            error = customError || 'Passwords do not match.';
                        }
                        break;
                    case 'date':
                        // Use format-aware parsing — Date.parse fails for d/m/Y etc.
                        var dateFmt = $outerWrap.data('date-format') || 'Y-m-d';
                        var parsed  = WPRoboDocuMerge_Validator.wprdmParseDate(value, dateFmt);
                        if (value && !parsed) {
                            error = customError || 'Please enter a valid date.';
                        }
                        // Min date check
                        var minDt = $outerWrap.data('min-date') || '';
                        if (!error && value && parsed && minDt) {
                            var minObj = new Date(minDt + 'T00:00:00');
                            if (parsed < minObj) {
                                error = customError || 'Date must be on or after ' + minDt + '.';
                            }
                        }
                        // Max date check
                        var maxDt = $outerWrap.data('max-date') || '';
                        if (!error && value && parsed && maxDt) {
                            var maxObj = new Date(maxDt + 'T00:00:00');
                            if (parsed > maxObj) {
                                error = customError || 'Date must be on or before ' + maxDt + '.';
                            }
                        }
                        break;
                    case 'checkbox':
                        var $checkboxes = $field.closest('.wdm-field-group').find('input[type="checkbox"]:checked');
                        var checkedCount = $checkboxes.length;
                        var minSel = parseInt($outerWrap.data('min-selections'), 10);
                        var maxSel = parseInt($outerWrap.data('max-selections'), 10);
                        if (required && checkedCount === 0) {
                            error = customError || 'Please select at least one option.';
                        }
                        if (!error && minSel && checkedCount < minSel) {
                            error = customError || 'Please select at least ' + minSel + ' option(s).';
                        }
                        if (!error && maxSel && checkedCount > maxSel) {
                            error = customError || 'Please select at most ' + maxSel + ' option(s).';
                        }
                        break;
                }
            }

            // Min/Max length checks (text, textarea)
            if (!error && value.trim() && $outerWrap.length) {
                var minLen = parseInt($outerWrap.data('min-length'), 10);
                var maxLen = parseInt($outerWrap.data('max-length'), 10);

                if (minLen && value.trim().length < minLen) {
                    error = customError || 'Minimum ' + minLen + ' characters required.';
                }
                if (!error && maxLen && value.trim().length > maxLen) {
                    error = customError || 'Maximum ' + maxLen + ' characters allowed.';
                }
            }

            // Min/Max value checks (number)
            if (!error && value.trim() && type === 'number' && $outerWrap.length) {
                var numVal  = parseFloat(value);
                var minVal  = $outerWrap.data('min-value');
                var maxVal  = $outerWrap.data('max-value');

                if (minVal !== undefined && minVal !== '' && numVal < parseFloat(minVal)) {
                    error = customError || 'Value must be at least ' + minVal + '.';
                }
                if (!error && maxVal !== undefined && maxVal !== '' && numVal > parseFloat(maxVal)) {
                    error = customError || 'Value must be at most ' + maxVal + '.';
                }
            }

            // Signature check
            if (type === 'signature' && required) {
                if (typeof WPRoboDocuMerge_Signature !== 'undefined') {
                    var $canvas = $field.closest('.wdm-field-group').find('.wdm-signature-canvas');
                    if ($canvas.length && WPRoboDocuMerge_Signature.wprobo_documerge_is_empty($canvas)) {
                        error = customError || 'Please provide your signature.';
                    }
                }
            }

            if (error) {
                this.showFieldError($field, error);
                return false;
            }
            this.clearFieldError($field);
            return true;
        },

        validateStep: function($step) {
            var valid = true;
            var self  = this;
            $step.find('.wdm-field-group:visible').each(function() {
                var $input = $(this).find('input, select, textarea').not('[type="hidden"]').first();
                if ($input.length && !self.validateField($input)) {
                    valid = false;
                }
            });
            // Check signature fields in step
            $step.find('.wdm-signature-canvas').each(function() {
                var $canvas = $(this);
                var $group  = $canvas.closest('.wdm-field-group');
                if ($group.is(':visible') && $group.find('input[type="hidden"]').data('required')) {
                    if (typeof WPRoboDocuMerge_Signature !== 'undefined' && WPRoboDocuMerge_Signature.wprobo_documerge_is_empty($canvas)) {
                        self.showFieldError($canvas, 'Please provide your signature.');
                        valid = false;
                    }
                }
            });
            return valid;
        },

        validateAll: function($form) {
            var valid = true;
            var self  = this;
            $form.find('.wdm-field-group:visible').not('.wdm-conditionally-hidden').each(function() {
                // Also skip if inside a conditionally hidden parent
                if ($(this).closest('.wdm-conditionally-hidden').length) {
                    return;
                }
                var $input = $(this).find('input, select, textarea').not('[type="hidden"]').first();
                if ($input.length && !self.validateField($input)) {
                    valid = false;
                }
            });
            return valid;
        },

        showFieldError: function($field, message) {
            var $group = $field.closest('.wdm-field-group');
            $group.addClass('wdm-field-has-error');
            if (!$group.find('.wdm-field-error-msg').length) {
                $group.append('<span class="wdm-field-error-msg" role="alert">' + $('<span>').text(message).html() + '</span>');
            }
        },

        /**
         * Parse a date string using a PHP-style format.
         * Returns a Date object or null if invalid.
         *
         * @param {string} value  The date string entered by user.
         * @param {string} format The PHP date format (Y-m-d, d/m/Y, etc.).
         * @return {Date|null}
         */
        wprdmParseDate: function(value, format) {
            if (!value) { return null; }
            var y, m, d, parts;

            switch (format) {
                case 'Y-m-d':
                    parts = value.split('-');
                    if (parts.length === 3) { y = parts[0]; m = parts[1]; d = parts[2]; }
                    break;
                case 'd/m/Y':
                    parts = value.split('/');
                    if (parts.length === 3) { d = parts[0]; m = parts[1]; y = parts[2]; }
                    break;
                case 'm/d/Y':
                    parts = value.split('/');
                    if (parts.length === 3) { m = parts[0]; d = parts[1]; y = parts[2]; }
                    break;
                case 'd-m-Y':
                    parts = value.split('-');
                    if (parts.length === 3) { d = parts[0]; m = parts[1]; y = parts[2]; }
                    break;
                case 'd.m.Y':
                    parts = value.split('.');
                    if (parts.length === 3) { d = parts[0]; m = parts[1]; y = parts[2]; }
                    break;
                default:
                    // F j, Y / M j, Y / j F Y — try native parsing as fallback.
                    var fallback = new Date(value);
                    return isNaN(fallback.getTime()) ? null : fallback;
            }

            if (y && m && d) {
                var yi = parseInt(y, 10);
                var mi = parseInt(m, 10);
                var di = parseInt(d, 10);
                if (mi < 1 || mi > 12 || di < 1 || di > 31 || yi < 1900) { return null; }
                var dateObj = new Date(yi, mi - 1, di);
                // Verify the date is valid (e.g. Feb 30 would roll over to Mar).
                if (dateObj.getFullYear() !== yi || dateObj.getMonth() !== mi - 1 || dateObj.getDate() !== di) {
                    return null;
                }
                return dateObj;
            }
            return null;
        },

        clearFieldError: function($field) {
            var $group = $field.closest('.wdm-field-group');
            $group.removeClass('wdm-field-has-error');
            $group.find('.wdm-field-error-msg').remove();
        }
    };

    // ── Form Controller ──────────────────────────────────────
    var WPRoboDocuMerge_Form = {
        currentStep: 1,
        totalSteps: 1,

        init: function() {
            WPRoboDocuMerge_Validator.init();
            this.bindEvents();
            this.initMultiStep();
            this.initConditions();
            this.initCharacterCounters();
            this.initTooltips();
            this.initCaptchaLock();
        },

        bindEvents: function() {
            var self = this;
            $(document).on('submit', '.wdm-form', function(e) {
                e.preventDefault();
                self.submitForm($(this));
            });
            $(document).on('click', '.wdm-step-next', function(e) {
                e.preventDefault();
                self.nextStep();
            });
            $(document).on('click', '.wdm-step-back', function(e) {
                e.preventDefault();
                self.prevStep();
            });
            $(document).on('click', '.wdm-try-again', function(e) {
                e.preventDefault();
                self.resetForm($(this));
            });
            // Clickable step labels — navigate to any step (validates all steps before it).
            $(document).on('click', '.wdm-step-label', function(e) {
                e.preventDefault();
                var targetStep = parseInt($(this).data('step'), 10);
                if (targetStep === self.currentStep) { return; }
                self.goToStep(targetStep);
            });
            // Condition evaluation on field change
            $(document).on('change input', '.wdm-form input, .wdm-form select, .wdm-form textarea', function() {
                self.evaluateConditions($(this).closest('.wdm-form'));
            });
        },

        initMultiStep: function() {
            var $steps = $('.wdm-step');
            if ($steps.length > 1) {
                this.totalSteps = $steps.length;
                this.currentStep = 1;
                this.showStep();
                // Hide submit, show next on init.
                $('.wdm-form-submit').hide();
                $('.wdm-step-next').show();
                this.updateProgress();
            }
        },

        initConditions: function() {
            $('.wdm-form').each(function() {
                WPRoboDocuMerge_Form.evaluateConditions($(this));
            });
        },

        nextStep: function() {
            var $currentStep = $('.wdm-step[data-step="' + this.currentStep + '"]');
            if (!WPRoboDocuMerge_Validator.validateStep($currentStep)) {
                // Scroll to first error
                var $firstError = $currentStep.find('.wdm-field-has-error').first();
                if ($firstError.length) {
                    $('html, body').animate({scrollTop: $firstError.offset().top - 80}, 300);
                }
                return;
            }
            this.currentStep++;
            this.showStep();
            this.updateNavButtons();
        },

        prevStep: function() {
            if (this.currentStep <= 1) {
                return;
            }
            this.currentStep--;
            this.showStep();
            this.updateNavButtons();
        },

        /**
         * Navigate to a specific step. Going forward validates all steps
         * in between. Going backward is always allowed.
         *
         * @param {number} targetStep The step number to navigate to.
         */
        goToStep: function(targetStep) {
            if (targetStep < 1 || targetStep > this.totalSteps || targetStep === this.currentStep) {
                return;
            }

            // Going backward — always allowed.
            if (targetStep < this.currentStep) {
                this.currentStep = targetStep;
                this.showStep();
                this.updateNavButtons();
                return;
            }

            // Going forward — validate each step in between.
            for (var s = this.currentStep; s < targetStep; s++) {
                var $stepDiv = $('.wdm-step[data-step="' + s + '"]');
                if (!WPRoboDocuMerge_Validator.validateStep($stepDiv)) {
                    // Stop at the first invalid step and show it.
                    this.currentStep = s;
                    this.showStep();
                    this.updateNavButtons();
                    var $firstError = $stepDiv.find('.wdm-field-has-error').first();
                    if ($firstError.length) {
                        $('html, body').animate({scrollTop: $firstError.offset().top - 80}, 300);
                    }
                    return;
                }
            }

            // All intermediate steps valid — go to target.
            this.currentStep = targetStep;
            this.showStep();
            this.updateNavButtons();
        },

        /**
         * Update Next/Back/Submit button visibility based on current step.
         */
        updateNavButtons: function() {
            if (this.currentStep >= this.totalSteps) {
                $('.wdm-step-next').hide();
                $('.wdm-form-submit').show();
            } else {
                $('.wdm-step-next').show();
                $('.wdm-form-submit').hide();
            }
        },

        showStep: function() {
            $('.wdm-step').removeClass('wdm-step-active');
            $('.wdm-step[data-step="' + this.currentStep + '"]').addClass('wdm-step-active');
            $('.wdm-step-back').toggle(this.currentStep > 1);
            this.updateProgress();
            // Update step labels — active and completed states.
            var currentStep = this.currentStep;
            $('.wdm-step-label').each(function() {
                var stepNum = parseInt($(this).data('step'), 10);
                $(this).removeClass('wdm-step-active wdm-step-completed');
                if (stepNum === currentStep) {
                    $(this).addClass('wdm-step-active');
                } else if (stepNum < currentStep) {
                    $(this).addClass('wdm-step-completed');
                }
            });
            // Scroll to top of form on step change.
            var $form = $('.wdm-form-wrap');
            if ($form.length) {
                $('html, body').animate({scrollTop: $form.offset().top - 40}, 200);
            }
        },

        updateProgress: function() {
            var pct = ((this.currentStep - 1) / (this.totalSteps - 1)) * 100;
            $('#wdm-step-progress').css('width', Math.min(pct, 100) + '%');
        },

        /**
         * Initialize character counters on fields with maxlength.
         *
         * @since 1.3.0
         */
        initCharacterCounters: function() {
            // Fields WITH maxlength — show "23/100" with warning colors.
            $('.wdm-form').find('input[maxlength], textarea[maxlength]').each(function() {
                var $field = $(this);
                var max = parseInt($field.attr('maxlength'), 10);
                if (!max || max <= 0) { return; }

                var $counter = $('<span class="wdm-char-count"><span class="wdm-char-current">0</span>/' + max + '</span>');
                $field.after($counter);

                $field.on('input', function() {
                    var len = $(this).val().length;
                    $counter.find('.wdm-char-current').text(len);
                    $counter.toggleClass('wdm-char-limit', len >= max);
                    $counter.toggleClass('wdm-char-warning', len >= max * 0.9 && len < max);
                });
                $field.trigger('input');
            });

            // Textareas WITHOUT maxlength — show simple character count.
            // Exclude textareas inside captcha widgets (reCAPTCHA injects hidden textareas).
            $('.wdm-form').find('textarea:not([maxlength])').filter(function() {
                return !$(this).closest('.g-recaptcha, .h-captcha, [data-captcha-required], .wdm-hp').length;
            }).each(function() {
                var $field = $(this);
                var $counter = $('<span class="wdm-char-count"><span class="wdm-char-current">0</span> chars</span>');
                $field.after($counter);

                $field.on('input', function() {
                    $counter.find('.wdm-char-current').text($(this).val().length);
                });
                $field.trigger('input');
            });
        },

        /**
         * Convert help text paragraphs into tooltip icons next to labels.
         *
         * @since 1.3.0
         */
        initTooltips: function() {
            $('.wdm-form .wdm-help-text').each(function() {
                var $helpText = $(this);
                var text = $helpText.text().trim();
                if (!text) {
                    return;
                }

                var $label = $helpText.closest('.wdm-field-group').find('> label').first();
                if (!$label.length) {
                    // Try the inner wdm-field-group label.
                    $label = $helpText.closest('[data-field-id]').find('label').first();
                }
                if (!$label.length) {
                    return;
                }

                // Create tooltip icon and append to label.
                var $tooltip = $('<span class="wdm-tooltip-wrap">' +
                    '<span class="wdm-tooltip-icon dashicons dashicons-editor-help"></span>' +
                    '<span class="wdm-tooltip-text">' + $('<span>').text(text).html() + '</span>' +
                '</span>');

                $label.append($tooltip);

                // Remove the original help text paragraph.
                $helpText.remove();
            });
        },

        initCaptchaLock: function() {
            // For reCAPTCHA v2 and hCaptcha, disable submit until verified.
            // reCAPTCHA v3 is invisible/automatic — no lock needed.
            $('.wdm-form-wrap').each(function() {
                var $wrap = $(this);
                if ($wrap.find('[data-captcha-required]').length) {
                    $wrap.find('.wdm-submit-btn').prop('disabled', true).addClass('wdm-btn-disabled');
                }
            });
        },

        evaluateConditions: function($form) {
            $form.find('.wdm-field-group[data-conditions]').each(function() {
                var $group     = $(this);
                var conditions = $group.data('conditions');

                // jQuery .data() auto-parses JSON strings; handle both cases.
                if (typeof conditions === 'string') {
                    try {
                        conditions = JSON.parse(conditions);
                    } catch (e) {
                        return;
                    }
                }

                if (!conditions || !conditions.length) {
                    return;
                }

                var show = true;
                $.each(conditions, function(i, cond) {
                    // Look up the source field by name attribute.
                    // Try exact name first, then try by data-field-name for
                    // fields whose HTML name may include a prefix.
                    var $sourceField = $form.find('[name="' + cond.field + '"]');
                    if (!$sourceField.length) {
                        $sourceField = $form.find('.wdm-field-group[data-field-name="' + cond.field + '"]')
                            .find('input, select, textarea').not('[type="hidden"]').first();
                    }

                    var fieldVal = $sourceField.val() || '';

                    // For radio buttons, get the checked value.
                    if ($sourceField.length && $sourceField.attr('type') === 'radio') {
                        fieldVal = $form.find('[name="' + cond.field + '"]:checked').val() || '';
                    }

                    // For checkboxes, collect checked values.
                    if ($form.find('[name="' + cond.field + '[]"]').length) {
                        var checked = [];
                        $form.find('[name="' + cond.field + '[]"]:checked').each(function() {
                            checked.push($(this).val());
                        });
                        fieldVal = checked.join(',');
                    }

                    var match = false;
                    switch (cond.operator) {
                        case 'equals':
                            match = (fieldVal === cond.value);
                            break;
                        case 'not_equals':
                            match = (fieldVal !== cond.value);
                            break;
                        case 'contains':
                            match = (fieldVal.indexOf(cond.value) !== -1);
                            break;
                        case 'not_contains':
                            match = (fieldVal.indexOf(cond.value) === -1);
                            break;
                        case 'is_empty':
                            match = (!fieldVal);
                            break;
                        case 'is_not_empty':
                            match = (!!fieldVal);
                            break;
                    }

                    // Combine multiple conditions with AND logic.
                    if (cond.action === 'show') {
                        if (!match) {
                            show = false;
                        }
                    }
                    if (cond.action === 'hide') {
                        if (match) {
                            show = false;
                        }
                    }
                });

                if (show) {
                    $group.slideDown(200).removeClass('wdm-conditionally-hidden');
                } else {
                    $group.slideUp(200).addClass('wdm-conditionally-hidden');
                }
            });
        },

        submitForm: function($form) {
            var self = this;

            // Validate all visible fields
            if (!WPRoboDocuMerge_Validator.validateAll($form)) {
                var $firstError = $form.find('.wdm-field-has-error').first();
                if ($firstError.length) {
                    $('html, body').animate({scrollTop: $firstError.offset().top - 80}, 300);
                }
                return;
            }

            // Check payment — block if payment field exists but card not filled.
            var $paymentField = $form.find('.wdm-payment-field[data-payment-enabled="1"]');
            if ($paymentField.length) {
                var stripeReady = typeof WPRoboDocuMerge_Stripe !== 'undefined' && WPRoboDocuMerge_Stripe.wprobo_documerge_is_ready();
                var cardFilled  = stripeReady && typeof WPRoboDocuMerge_Stripe.wprobo_documerge_is_card_filled === 'function' && WPRoboDocuMerge_Stripe.wprobo_documerge_is_card_filled();

                if (!stripeReady || !cardFilled) {
                    $paymentField.addClass('wdm-payment-error');
                    var $payErr = $paymentField.find('.wdm-stripe-card-error');
                    var payMsg = !stripeReady
                        ? 'Payment system is not loaded. Please refresh the page.'
                        : 'Please enter your card details to proceed with payment.';
                    $payErr.text(payMsg);

                    // Highlight the card fields.
                    $paymentField.find('.wdm-stripe-card-element, .wdm-stripe-field').addClass('wdm-stripe-field-error');

                    $('html, body').animate({scrollTop: $paymentField.offset().top - 80}, 300);

                    // Remove error highlight when user starts typing in card.
                    if (stripeReady) {
                        var clearPayError = function() {
                            $paymentField.removeClass('wdm-payment-error');
                            $paymentField.find('.wdm-stripe-card-element, .wdm-stripe-field').removeClass('wdm-stripe-field-error');
                            $payErr.text('');
                        };
                        if (WPRoboDocuMerge_Stripe.cardElement) {
                            WPRoboDocuMerge_Stripe.cardElement.once('change', clearPayError);
                        }
                    }
                    return;
                }
            }

            // Check captcha — show visible error if not completed.
            if (typeof WPRoboDocuMerge_Captcha !== 'undefined' && !WPRoboDocuMerge_Captcha.wprobo_documerge_validate()) {
                var $captchaWrap = $form.find('.wdm-captcha-wrap, .wdm-field-group[data-field-type="captcha"]');
                if ($captchaWrap.length) {
                    $captchaWrap.addClass('wdm-field-has-error');
                    if (!$captchaWrap.find('.wdm-field-error-msg').length) {
                        var captchaMsg = (typeof wprobo_documerge_frontend_vars !== 'undefined' && wprobo_documerge_frontend_vars.i18n)
                            ? wprobo_documerge_frontend_vars.i18n.captcha_required
                            : 'Please complete the CAPTCHA verification.';
                        $captchaWrap.append('<span class="wdm-field-error-msg wdm-captcha-error" role="alert">' + captchaMsg + '</span>');
                    }
                    $('html, body').animate({scrollTop: $captchaWrap.offset().top - 80}, 300);
                }
                return;
            }

            // Show loading state + hide draft notice + lock submit button.
            var $wrap = $form.closest('.wdm-form-wrap');
            $wrap.find('.wdm-draft-notice').slideUp(200);

            var $btn = $form.find('.wdm-submit-btn');
            $btn.prop('disabled', true).addClass('wdm-btn-submitting');
            $btn.find('.wdm-submit-text').hide();
            $btn.find('.wdm-submit-spinner').show();

            // Get captcha token (may be async for v3)
            var tokenPromise = $.Deferred().resolve('');
            if (typeof WPRoboDocuMerge_Captcha !== 'undefined') {
                tokenPromise = WPRoboDocuMerge_Captcha.wprobo_documerge_get_token();
            }

            $.when(tokenPromise).then(function(captchaToken) {
                var formData = new FormData($form[0]);
                if (captchaToken) {
                    formData.append('wdm_recaptcha_token', captchaToken);
                }
                formData.append('action', 'wprobo_documerge_submit_form');
                formData.append('page_url', window.location.href);
                formData.append('referrer', document.referrer || '');

                $.ajax({
                    url: (typeof wprobo_documerge_frontend_vars !== 'undefined') ? wprobo_documerge_frontend_vars.ajax_url : ajaxurl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // If payment is required, trigger Stripe payment flow.
                            if (response.data.payment_required && typeof WPRoboDocuMerge_Stripe !== 'undefined' && WPRoboDocuMerge_Stripe.wprobo_documerge_is_ready()) {
                                self.handlePaymentFlow($form, response.data);
                                return;
                            }
                            self.showSuccess($form, response.data);
                        } else {
                            if (response.data && response.data.field_errors) {
                                self.showFieldErrors($form, response.data.field_errors);
                            } else {
                                self.showError($form, response.data ? response.data.message : '');
                            }
                        }
                    },
                    error: function() {
                        self.showError($form, '');
                    },
                    complete: function(jqXHR, textStatus) {
                        // Only reset button on failure — on success the form is hidden anyway.
                        var response = jqXHR.responseJSON;
                        var isSuccess = response && response.success && !( response.data && response.data.field_errors );
                        if ( !isSuccess ) {
                            $btn.prop('disabled', false).removeClass('wdm-btn-submitting');
                            $btn.find('.wdm-submit-text').show();
                            $btn.find('.wdm-submit-spinner').hide();
                            if (typeof WPRoboDocuMerge_Captcha !== 'undefined') {
                                WPRoboDocuMerge_Captcha.wprobo_documerge_reset();
                            }
                        }
                    }
                });
            });
        },

        showSuccess: function($form, data) {
            var $wrap = $form.closest('.wdm-form-wrap');
            var formId = $wrap.data('form-id');

            // Trigger custom event so autosave can clear the draft.
            if (formId) {
                $(document).trigger('wdm_form_submitted', [formId]);
            }

            $form.hide();
            $wrap.find('.wdm-form-nav').hide();
            var $success = $wrap.find('.wdm-form-success');

            // Always display the success message from the server
            if (data.message) {
                if (data.submitter_name) {
                    $success.find('#wdm-success-msg').text('Thank you, ' + data.submitter_name + '. ' + data.message);
                } else {
                    $success.find('#wdm-success-msg').text(data.message);
                }
            } else if (data.submitter_name) {
                $success.find('#wdm-success-msg').text('Thank you, ' + data.submitter_name + '. Your personalised document has been generated.');
            } else {
                $success.find('#wdm-success-msg').text('Your document has been generated successfully.');
            }

            if (data.download_url) {
                $success.find('#wdm-download-link').attr('href', data.download_url).show();
            } else {
                $success.find('#wdm-download-link').hide();
            }
            if (data.email_sent && data.submitter_email) {
                $success.find('#wdm-success-email').text('A copy has been sent to ' + data.submitter_email).show();
            }
            $success.fadeIn(300);
            $('html, body').animate({scrollTop: $wrap.offset().top - 60}, 300);
        },

        /**
         * Handle the Stripe payment flow after server confirms payment is required.
         * Creates a Payment Intent via AJAX, then confirms with Stripe.js.
         */
        handlePaymentFlow: function($form, data) {
            var self = this;
            var submissionId = data.submission_id;
            var ajaxUrl = (typeof wprobo_documerge_frontend_vars !== 'undefined') ? wprobo_documerge_frontend_vars.ajax_url : ajaxurl;
            var nonce   = (typeof wprobo_documerge_frontend_vars !== 'undefined') ? wprobo_documerge_frontend_vars.nonce : '';

            // Step 1: Create Payment Intent on the server.
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'wprobo_documerge_create_payment_intent',
                    nonce: nonce,
                    submission_id: submissionId
                },
                success: function(response) {
                    if (!response.success || !response.data.client_secret) {
                        self.resetSubmitButton($form);
                        var $payErr = $form.find('.wdm-stripe-card-error');
                        $payErr.text(response.data ? response.data.message : 'Payment setup failed.');
                        return;
                    }

                    // Step 2: Confirm payment with Stripe.js.
                    WPRoboDocuMerge_Stripe.wprobo_documerge_confirm_payment(response.data.client_secret)
                        .done(function(paymentIntent) {
                            // Step 3: Payment succeeded — poll for document generation.
                            var $wrap = $form.closest('.wdm-form-wrap');
                            $form.find('.wdm-submit-text').text(
                                (typeof wprobo_documerge_frontend_vars !== 'undefined' && wprobo_documerge_frontend_vars.i18n)
                                    ? (wprobo_documerge_frontend_vars.i18n.generating || 'Generating document...')
                                    : 'Generating document...'
                            ).show();
                            $form.find('.wdm-submit-spinner').hide();

                            if (typeof WPRoboDocuMerge_Stripe.wprobo_documerge_poll_status === 'function' && data.status_token) {
                                WPRoboDocuMerge_Stripe.wprobo_documerge_poll_status(
                                    submissionId,
                                    data.status_token,
                                    function(statusData) { self.showSuccess($form, statusData); },
                                    function(errMsg) { self.showError($form, errMsg); }
                                );
                            } else {
                                // Fallback: show success with message to check email.
                                self.showSuccess($form, {
                                    message: (typeof wprobo_documerge_frontend_vars !== 'undefined' && wprobo_documerge_frontend_vars.i18n)
                                        ? (wprobo_documerge_frontend_vars.i18n.payment_success || 'Payment successful! Your document will be emailed to you shortly.')
                                        : 'Payment successful! Your document will be emailed to you shortly.'
                                });
                            }
                        })
                        .fail(function(error) {
                            self.resetSubmitButton($form);
                            var $payErr = $form.find('.wdm-stripe-card-error');
                            $payErr.text(error.message || 'Payment failed. Please try again.');
                            WPRoboDocuMerge_Stripe.wprobo_documerge_reset();
                        });
                },
                error: function() {
                    self.resetSubmitButton($form);
                    var $payErr = $form.find('.wdm-stripe-card-error');
                    $payErr.text('Network error. Please try again.');
                }
            });
        },

        resetSubmitButton: function($form) {
            var $btn = $form.find('.wdm-submit-btn');
            $btn.prop('disabled', false);
            $btn.find('.wdm-submit-text').show();
            $btn.find('.wdm-submit-spinner').hide();
        },

        showError: function($form, message) {
            var $wrap = $form.closest('.wdm-form-wrap');
            $form.hide();
            $wrap.find('.wdm-form-nav').hide();
            if (message) {
                $wrap.find('.wdm-error-message').text(message);
            }
            $wrap.find('.wdm-form-error').show();
            $('html, body').animate({scrollTop: $wrap.offset().top - 60}, 300);
        },

        showFieldErrors: function($form, errors) {
            $.each(errors, function(fieldName, errorMsg) {
                var $field = $form.find('[name="' + fieldName + '"]');
                if ($field.length) {
                    WPRoboDocuMerge_Validator.showFieldError($field, errorMsg);
                }
            });
            var $firstError = $form.find('.wdm-field-has-error').first();
            if ($firstError.length) {
                $('html, body').animate({scrollTop: $firstError.offset().top - 80}, 300);
            }
        },

        resetForm: function($btn) {
            var $wrap = $btn.closest('.wdm-form-wrap');
            $wrap.find('.wdm-form-error').hide();
            $wrap.find('.wdm-form').show();
            $wrap.find('.wdm-form-nav').show();
        }
    };

    $(document).ready(function() {
        WPRoboDocuMerge_Form.init();
    });

    // Global captcha callbacks — called by reCAPTCHA v2 / hCaptcha widgets.
    window.wdmCaptchaVerified = function() {
        $('.wdm-form-wrap').each(function() {
            var $wrap = $(this);
            if ($wrap.find('[data-captcha-required]').length) {
                $wrap.find('.wdm-submit-btn').prop('disabled', false).removeClass('wdm-btn-disabled');
                $wrap.find('.wdm-captcha-error').remove();
                $wrap.find('[data-captcha-required]').removeClass('wdm-field-has-error');
            }
        });
    };

    window.wdmCaptchaExpired = function() {
        $('.wdm-form-wrap').each(function() {
            var $wrap = $(this);
            if ($wrap.find('[data-captcha-required]').length) {
                $wrap.find('.wdm-submit-btn').prop('disabled', true).addClass('wdm-btn-disabled');
            }
        });
    };

    window.WPRoboDocuMerge_Form      = WPRoboDocuMerge_Form;
    window.WPRoboDocuMerge_Validator  = WPRoboDocuMerge_Validator;

})(jQuery);
