/**
 * WPRobo DocuMerge — Form Builder
 *
 * Handles the two-panel form builder UI: field type selection,
 * drag-and-drop canvas ordering, field settings editing,
 * merge tag generation, and form save/delete via AJAX.
 *
 * @package WPRobo_DocuMerge
 * @since   1.0.0
 */

(function($) {
    'use strict';

    /**
     * WPRobo DocuMerge Form Builder module.
     *
     * @since 1.0.0
     * @type  {Object}
     */
    var WPRoboDocuMerge_FormBuilder = {

        /**
         * Counter for generating unique field IDs.
         *
         * @since 1.0.0
         * @type  {number}
         */
        fieldCounter: 0,

        /**
         * Counter for generating unique step IDs.
         *
         * @since 1.2.0
         * @type  {number}
         */
        stepCounter: 0,

        /**
         * Array of field configuration objects.
         *
         * @since 1.0.0
         * @type  {Array}
         */
        fields: [],

        /**
         * Cycling colours for step container left borders and badges.
         *
         * @since 1.2.0
         * @type  {Array}
         */
        stepColors: ['#042157', '#166441', '#7c3aed', '#d97706', '#dc2626'],

        /**
         * Map of field types to their human-readable labels.
         *
         * @since 1.0.0
         * @type  {Object}
         */
        typeLabels: {
            text:        'Text',
            textarea:    'Textarea',
            email:       'Email',
            phone:       'Phone',
            number:      'Number',
            date:        'Date',
            dropdown:    'Dropdown',
            radio:       'Radio',
            checkbox:    'Checkbox',
            file_upload: 'File Upload',
            address:     'Address',
            name:        'Name',
            hidden:      'Hidden',
            html:             'HTML Block',
            section_divider:  'Section Divider',
            password:         'Password',
            url:              'Website',
            rating:           'Rating',
            repeater:         'Repeater',
            ip_address:       'IP Address',
            tracking:         'Tracking'
        },

        /**
         * Initialize the form builder module.
         *
         * Binds events, initializes sortable, and loads existing
         * fields when editing an existing form.
         *
         * @since 1.0.0
         */
        init: function() {
            this.bindEvents();
            this.initSortable();

            if ( typeof wprobo_documerge_form_fields !== 'undefined' && wprobo_documerge_form_fields ) {
                this.loadExistingFields();
            }

            // Restore active tab ONLY after a save redirect (has &saved=1).
            var urlParams  = new URLSearchParams(window.location.search);
            var wasSaved   = urlParams.get('saved') === '1';

            if ( wasSaved ) {
                var tabParam = urlParams.get('tab');
                if ( tabParam && tabParam !== 'fields' ) {
                    var $targetTab = $('.wdm-builder-main-tab[data-tab="' + tabParam + '"]');
                    if ( $targetTab.length ) {
                        $targetTab.trigger('click');
                    }
                }

                // Restore active settings sub-tab.
                if ( subtabParam ) {
                    var $targetSubtab = $('.wdm-settings-subtab[data-subtab="' + subtabParam + '"]');
                    if ( $targetSubtab.length ) {
                        $targetSubtab.trigger('click');
                    }
                }

                // Clean URL so tab params don't persist on manual navigation.
                if ( window.history && window.history.replaceState ) {
                    var cleanUrl = window.location.pathname + '?page=wprobo-documerge-forms&action=edit&id=' + $('#wdm-form-id').val();
                    window.history.replaceState( null, '', cleanUrl );
                }
            }
        },

        /**
         * Bind all UI event handlers.
         *
         * @since 1.0.0
         */
        bindEvents: function() {
            var self = this;

            // Add field from sidebar.
            $(document).on('click', '.wdm-field-type-btn', function(e) {
                e.preventDefault();
                self.addField.call(self, e, $(this));
            });

            // Toggle field settings panel.
            $(document).on('click', '.wdm-field-edit-btn', function(e) {
                e.preventDefault();
                self.toggleFieldSettings($(this));
            });

            // Delete field from canvas.
            $(document).on('click', '.wdm-field-delete-btn', function(e) {
                e.preventDefault();
                self.deleteField($(this));
            });

            // Save form.
            $(document).on('click', '#wdm-save-form', function(e) {
                e.preventDefault();
                self.saveForm();
            });

            // Preview document.
            $(document).on('click', '#wdm-preview-doc', function(e) {
                e.preventDefault();
                self.wprobo_documerge_preview_document();
            });

            // Copy shortcode.
            $(document).on('click', '.wdm-copy-shortcode', function(e) {
                e.preventDefault();
                self.copyShortcode($(this));
            });

            // Delete form.
            $(document).on('click', '.wdm-form-delete', function(e) {
                e.preventDefault();
                self.deleteForm($(this));
            });

            // Update field data on setting change.
            $(document).on('change', '.wdm-builder-setting-input', function(e) {
                self.updateFieldData($(this));
            });

            // Update merge tag on label input.
            $(document).on('input', '.wdm-builder-setting-input[data-setting="label"]', function(e) {
                self.updateMergeTag($(this));
            });

            // Options manager events.
            $(document).on('click', '.wdm-add-option', function(e) {
                e.preventDefault();
                self.addOption(e);
            });

            $(document).on('click', '.wdm-option-remove', function(e) {
                e.preventDefault();
                self.removeOption(e);
            });

            $(document).on('input', '.wdm-option-label', function(e) {
                self.updateOptionValue(e);
            });

            $(document).on('change', '.wdm-option-label, .wdm-option-value', function(e) {
                self.updateFieldOptions(e);
            });

            // Conditional logic events.
            $(document).on('change', '.wdm-enable-conditions', function(e) {
                self.toggleConditions(e);
            });

            $(document).on('click', '.wdm-add-condition', function(e) {
                e.preventDefault();
                self.addCondition(e);
            });

            $(document).on('click', '.wdm-condition-remove', function(e) {
                e.preventDefault();
                self.removeCondition(e);
            });

            $(document).on('change', '.wdm-condition-field, .wdm-condition-operator, .wdm-condition-value', function(e) {
                self.updateFieldConditions(e);
            });

            // Hide/show value input based on operator (is_empty / is_not_empty don't need a value).
            $(document).on('change', '.wdm-condition-operator', function() {
                var op = $(this).val();
                var $value = $(this).closest('.wdm-condition-row').find('.wdm-condition-value');
                if (op === 'is_empty' || op === 'is_not_empty') {
                    $value.css('visibility', 'hidden').val('');
                } else {
                    $value.css('visibility', 'visible');
                }
            });

            // Multi-step toggle.
            $(document).on('change', '#wdm-multistep', function(e) {
                self.toggleMultistep(e);
            });

            // Payment enabled toggle.
            $(document).on('change', '#wdm-payment-enabled', function() {
                if ( $(this).is(':checked') ) {
                    $('.wdm-payment-fields-wrap').slideDown(200);
                } else {
                    $('.wdm-payment-fields-wrap').slideUp(200);
                }
            });

            // Step container events.
            $(document).on('click', '.wdm-step-collapse', function(e) {
                e.preventDefault();
                self.toggleStepCollapse($(this));
            });

            $(document).on('click', '.wdm-step-delete', function(e) {
                e.preventDefault();
                self.removeStepContainer($(this));
            });

            $(document).on('click', '#wdm-add-step-btn', function(e) {
                e.preventDefault();
                self.addStepContainer();
            });

            // Builder main tabs (Fields vs Settings).
            $(document).on('click', '.wdm-builder-main-tab', function(e) {
                e.preventDefault();
                var $tab = $(this);
                var tabName = $tab.data('tab');

                $('.wdm-builder-main-tab').removeClass('wdm-builder-main-tab-active');
                $tab.addClass('wdm-builder-main-tab-active');

                $('.wdm-builder-main-content').removeClass('wdm-builder-main-content-active');
                $('.wdm-builder-main-content[data-tab="' + tabName + '"]').addClass('wdm-builder-main-content-active');
            });

            // Create Page with shortcode.
            $(document).on('click', '#wdm-create-page', function(e) {
                e.preventDefault();
                var formId = $('#wdm-form-id').val();
                if ( ! formId || formId === '0' ) {
                    self.showNotice('error', 'Please save the form first.');
                    return;
                }

                var formTitle = $.trim($('#wdm-form-title').val()) || 'DocuMerge Form';
                var $btn = $(this);
                $btn.prop('disabled', true).addClass('wdm-loading');

                $.ajax({
                    url:  wprobo_documerge_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action:    'wprobo_documerge_create_form_page',
                        nonce:     wprobo_documerge_vars.nonce,
                        form_id:   formId,
                        form_title: formTitle
                    },
                    success: function(response) {
                        $btn.prop('disabled', false).removeClass('wdm-loading');
                        if ( response.success && response.data.edit_url ) {
                            self.showNotice('success', 'Page created! Opening in new tab...');
                            window.open(response.data.edit_url, '_blank');
                        } else {
                            self.showNotice('error', response.data ? response.data.message : 'Failed to create page.');
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).removeClass('wdm-loading');
                        self.showNotice('error', 'Network error.');
                    }
                });
            });

            // Settings sub-tab switching.
            $(document).on('click', '.wdm-settings-subtab', function(e) {
                e.preventDefault();
                var $stab = $(this);
                var subtab = $stab.data('subtab');

                $('.wdm-settings-subtab').removeClass('wdm-settings-subtab-active');
                $stab.addClass('wdm-settings-subtab-active');

                $('.wdm-settings-subtab-content').removeClass('wdm-settings-subtab-active');
                $('.wdm-settings-subtab-content[data-subtab="' + subtab + '"]').addClass('wdm-settings-subtab-active');
            });

            // Load external form fields via AJAX when the WPForms dropdown changes.
            $(document).on('change', '#wdm-external-form-id', function() {
                var extFormId = $(this).val();
                var $mapWrap  = $('#wdm-field-map-wrap');

                if ( ! extFormId ) {
                    $mapWrap.html('<p class="wdm-text-muted">Select an external form to see field mapping.</p>');
                    return;
                }

                $mapWrap.html('<p class="wdm-text-muted">Loading fields...</p>');

                $.ajax({
                    url:  wprobo_documerge_vars.ajax_url,
                    type: 'POST',
                    data: {
                        action:           'wprobo_documerge_get_external_fields',
                        nonce:            wprobo_documerge_vars.nonce,
                        external_form_id: extFormId,
                        integration:      $('#wdm-form-integration').val() || '',
                        template_id:      $('#wdm-form-template').val() || ''
                    },
                    success: function(response) {
                        if ( response.success && response.data.html ) {
                            $mapWrap.html(response.data.html);
                        } else {
                            $mapWrap.html('<p class="wdm-text-muted">No fields found.</p>');
                        }
                    },
                    error: function() {
                        $mapWrap.html('<p class="wdm-text-muted">Failed to load fields.</p>');
                    }
                });
            });

            // Alignment radio buttons — toggle active class for browsers without :has().
            $(document).on('change', '.wdm-align-option input[type="radio"]', function() {
                var $group = $(this).closest('.wdm-btn-align-selector');
                $group.find('.wdm-align-option').removeClass('wdm-align-active');
                $(this).closest('.wdm-align-option').addClass('wdm-align-active');
            });

            // Set initial active state on page load.
            $('.wdm-align-option input[type="radio"]:checked').closest('.wdm-align-option').addClass('wdm-align-active');

            // Repeater column manager — add column.
            $(document).on('click', '.wdm-repeater-col-add', function(e) {
                e.preventDefault();
                var $list = $(this).siblings('.wdm-repeater-columns-list');
                var idx = $list.find('.wdm-repeater-col-row').length;
                var rowHtml = '<div class="wdm-repeater-col-row" data-index="' + idx + '">' +
                    '<input type="text" data-setting="col_label" class="wdm-input" placeholder="Column Label" value="">' +
                    '<input type="text" data-setting="col_name" class="wdm-input" placeholder="Column Name" value="">' +
                    '<button type="button" class="wdm-repeater-col-remove"><span class="dashicons dashicons-no-alt"></span></button>' +
                '</div>';
                $list.append(rowHtml);
            });

            // Repeater column manager — remove column.
            $(document).on('click', '.wdm-repeater-col-remove', function(e) {
                e.preventDefault();
                $(this).closest('.wdm-repeater-col-row').remove();
            });

            // Repeater column manager — update columns data on input change.
            $(document).on('change input', '[data-setting="col_label"], [data-setting="col_name"]', function() {
                var $card = $(this).closest('.wdm-field-card');
                var fieldId = $card.data('field-id');
                var cols = [];
                $card.find('.wdm-repeater-col-row').each(function() {
                    cols.push({
                        label: $(this).find('[data-setting="col_label"]').val() || '',
                        name: $(this).find('[data-setting="col_name"]').val() || ''
                    });
                });
                $.each(self.fields, function(i, f) {
                    if (f.id === fieldId) {
                        f.columns = cols;
                        return false;
                    }
                });
            });

            // Click field label to open settings (same as edit button).
            $(document).on('click', '.wdm-field-label-preview', function(e) {
                e.preventDefault();
                $(this).closest('.wdm-field-card').find('.wdm-field-card-settings').slideToggle(200);
            });

            // Field settings tab switching.
            $(document).on('click', '.wdm-field-tab', function(e) {
                e.preventDefault();
                var $tab = $(this);
                var $card = $tab.closest('.wdm-field-card');
                var tabName = $tab.data('tab');

                // Switch tab buttons.
                $card.find('.wdm-field-tab').removeClass('wdm-field-tab-active');
                $tab.addClass('wdm-field-tab-active');

                // Switch content panels.
                $card.find('.wdm-field-tab-content').removeClass('wdm-field-tab-active');
                $card.find('.wdm-field-tab-content[data-tab="' + tabName + '"]').addClass('wdm-field-tab-active');
            });
        },

        /**
         * Initialize jQuery UI Sortable on the builder canvas.
         *
         * @since 1.0.0
         */
        initSortable: function() {
            var self = this;

            // Make canvas sortable for reordering AND receiving drops.
            $('#wdm-builder-canvas').sortable({
                handle:      '.wdm-field-drag-handle',
                placeholder: 'wdm-field-placeholder',
                items:       '.wdm-field-card',
                distance:    3,
                tolerance:   'pointer',
                cursor:      'grabbing',
                revert:      false,
                scroll:      true,
                scrollSensitivity: 60,
                helper: function(e, el) {
                    var $clone = el.clone().css({ width: el.outerWidth(), opacity: 0.9 });
                    return $clone;
                },
                start: function(e, ui) {
                    ui.item.css('visibility', 'hidden');
                },
                stop: function(e, ui) {
                    ui.item.css('visibility', '');
                },
                update: function() {
                    self.updateFieldOrder();
                }
            });

            // Track drop insertion point when dragging from sidebar.
            self._dropIndex = -1;

            // Make sidebar field type buttons draggable.
            $('.wdm-field-type-btn').not('[disabled]').draggable({
                helper: function() {
                    var type  = $(this).data('type') || 'text';
                    var label = $.trim($(this).text());
                    var badge = (self.typeLabels[type] || type).toUpperCase();
                    return $('<div class="wdm-dragging-field-card">' +
                        '<span class="wdm-field-drag-handle dashicons dashicons-menu"></span>' +
                        '<span class="wdm-field-card-type">' + badge + '</span> ' +
                        '<span class="wdm-field-card-label">' + $('<span>').text(label).html() + '</span>' +
                    '</div>');
                },
                appendTo:       'body',
                zIndex:         100010,
                revert:         'invalid',
                revertDuration: 150,
                cursor:         'grabbing',
                cursorAt:       { left: 40, top: 18 },
                cancel:         '',
                distance:       5,
                start: function() {
                    $('#wdm-builder-canvas').addClass('wdm-canvas-drop-active');
                    self._dropIndex = -1;
                },
                stop: function() {
                    $('#wdm-builder-canvas').removeClass('wdm-canvas-drop-active');
                    $('.wdm-drop-indicator').remove();
                },
                drag: function(e) {
                    var mouseY = e.pageY;
                    var mouseX = e.pageX;
                    $('.wdm-drop-indicator').remove();

                    // Find the closest step body or flat canvas the cursor is over.
                    var $target = null;
                    $('.wdm-step-container-body').each(function() {
                        var $body  = $(this);
                        var offset = $body.offset();
                        var w = $body.outerWidth();
                        var h = $body.outerHeight();
                        if (mouseX >= offset.left && mouseX <= offset.left + w &&
                            mouseY >= offset.top  && mouseY <= offset.top + h) {
                            $target = $body;
                            return false;
                        }
                    });

                    // Fallback to flat canvas if no step containers.
                    if (!$target) {
                        var $canvas = $('#wdm-builder-canvas');
                        var co = $canvas.offset();
                        if (mouseX >= co.left && mouseX <= co.left + $canvas.outerWidth() &&
                            mouseY >= co.top  && mouseY <= co.top + $canvas.outerHeight()) {
                            $target = $canvas;
                        }
                    }

                    if (!$target) { self._dropTarget = null; return; }

                    self._dropTarget = $target;
                    var $cards = $target.find('> .wdm-field-card');
                    var placed = false;

                    $cards.each(function(i) {
                        var $card  = $(this);
                        var top    = $card.offset().top;
                        var middle = top + ($card.outerHeight() / 2);

                        if (mouseY < middle) {
                            self._dropIndex = i;
                            $('<div class="wdm-drop-indicator"></div>').insertBefore($card);
                            placed = true;
                            return false;
                        }
                    });

                    if (!placed) {
                        self._dropIndex = $cards.length;
                        $target.append('<div class="wdm-drop-indicator"></div>');
                    }
                }
            });

            // Make canvas accept drops from sidebar.
            $('#wdm-builder-canvas').droppable({
                accept:     '.wdm-field-type-btn',
                hoverClass: 'wdm-canvas-drop-hover',
                tolerance:  'pointer',
                drop: function(e, ui) {
                    var $btn = ui.draggable;
                    $('.wdm-drop-indicator').remove();
                    self.addFieldAtIndex(e, $btn, self._dropIndex);
                    $(this).removeClass('wdm-canvas-drop-active wdm-canvas-drop-hover');
                }
            });
        },

        /**
         * Add a new field at a specific index in the canvas.
         *
         * @since 1.1.0
         * @param {Event}  e      The drop event.
         * @param {jQuery} $btn   The dragged field type button.
         * @param {number} index  Position to insert at (-1 or >= length = end).
         */
        addFieldAtIndex: function(e, $btn, index) {
            var type = $btn.data('type');
            if ($btn.prop('disabled')) { return; }

            // Singleton fields — only one allowed per form.
            if ( this.isSingletonField(type) && this.hasFieldType(type) ) {
                this.showNotice('error', this.typeLabels[type] + ' field can only be added once per form.');
                return;
            }

            this.fieldCounter++;
            var fieldId  = 'field_' + this.fieldCounter;
            var label    = this.typeLabels[type] || type;
            var mergeTag = label.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');

            var field = {
                id: fieldId, type: type, label: label, name: mergeTag,
                placeholder: '', help_text: '', required: false, width: 'full',
                options: [], conditions: [], step: 1, error_message: '',
                min_length: '', max_length: '', min_value: '', max_value: '',
                step_value: '', date_format: 'Y-m-d',
                disable_past: false, max_future_months: '', searchable: false,
                css_class: '', css_id: '',
                label_position: 'top',
                show_country_code: true, default_country: 'GB',
                allowed_types: 'jpg,jpeg,png,gif,pdf,doc,docx',
                max_file_size: 5, multiple: false,
                show_line2: true, show_country: true,
                name_format: 'first_last',
                default_value: '', dynamic_value: 'none', custom_value: '',
                html_content: '<p>Add your content here.</p>',
                divider_style: 'line',
                show_title: true,
                title_alignment: 'left',
                confirm_password: false, show_strength: true,
                max_stars: 5,
                columns: [{ label: 'Item', name: 'item' }, { label: 'Quantity', name: 'qty' }],
                min_rows: 1,
                max_rows: 10,
                track_utms: true,
                track_referrer: true,
                track_landing_page: true
            };

            if (type === 'dropdown' || type === 'radio' || type === 'checkbox') {
                field.options = [
                    {label: 'Option 1', value: 'option_1'},
                    {label: 'Option 2', value: 'option_2'},
                    {label: 'Option 3', value: 'option_3'}
                ];
            }

            var cardHtml = this.generateFieldCard(field);

            // In multi-step mode, sidebar drag-drop targets step bodies directly.
            // This fallback handles the flat canvas mode only.
            var $stepContainers = $('#wdm-builder-canvas .wdm-step-container');
            if ( $stepContainers.length ) {
                // Append to last step body as fallback for flat canvas drop.
                var $lastBody = $stepContainers.last().find('.wdm-step-container-body');
                var lastStep  = parseInt( $stepContainers.last().data('step'), 10 ) || 1;
                field.step = lastStep;

                // Use _dropIndex for position within the target step body.
                var $stepTarget = this._dropTarget || $lastBody;
                var $stepCards  = $stepTarget.find('> .wdm-field-card');
                var sIdx        = this._dropIndex;

                if ( typeof sIdx === 'number' && sIdx >= 0 && sIdx < $stepCards.length ) {
                    $(cardHtml).insertBefore($stepCards.eq(sIdx));
                    // Update step number from the actual container.
                    field.step = parseInt( $stepTarget.closest('.wdm-step-container').data('step'), 10 ) || lastStep;
                } else {
                    $lastBody.append(cardHtml);
                }

                this.fields.push(field);
                this.syncFieldStepsFromDOM();
                this.updateFieldOrder();
                this.updateStepEmptyStates();
            } else {
                var $cards = $('#wdm-builder-canvas').find('.wdm-field-card');

                // Insert at the correct position.
                if (index >= 0 && index < $cards.length) {
                    $(cardHtml).insertBefore($cards.eq(index));
                } else {
                    $('#wdm-builder-canvas').append(cardHtml);
                }

                // Insert into fields array at the correct index.
                if (index >= 0 && index < this.fields.length) {
                    this.fields.splice(index, 0, field);
                } else {
                    this.fields.push(field);
                }
            }

            // Hide placeholder.
            $('#wdm-canvas-placeholder').hide();

            // Refresh sortable and singleton button states.
            if ( ! $stepContainers.length && $('#wdm-builder-canvas').hasClass('ui-sortable') ) {
                $('#wdm-builder-canvas').sortable('refresh');
            }
            this.updateSingletonButtons();
        },

        // ─────────────────────────────────────────────────────────
        //  Field management
        // ─────────────────────────────────────────────────────────

        /**
         * Add a new field to the canvas.
         *
         * Reads the field type from the clicked button, creates a
         * default configuration object, generates the card HTML,
         * and appends it to the canvas.
         *
         * @since 1.0.0
         * @param {Event}  e    The click event.
         * @param {jQuery} $btn The clicked field type button.
         */
        addField: function(e, $btn) {
            var type = $btn.data('type');

            // Bail if the field type is disabled.
            if ( $btn.prop('disabled') ) {
                return;
            }

            // Singleton fields — only one allowed per form.
            if ( this.isSingletonField(type) && this.hasFieldType(type) ) {
                this.showNotice('error', this.typeLabels[type] + ' field can only be added once per form.');
                return;
            }

            this.fieldCounter++;

            var fieldId    = 'field_' + this.fieldCounter;
            var label      = this.typeLabels[type] || type;
            var mergeTag   = label.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');

            // Payment and captcha are always required.
            var alwaysRequired = ( type === 'payment' || type === 'captcha' );

            var field = {
                id:            fieldId,
                type:          type,
                label:         label,
                name:          mergeTag,
                placeholder:   '',
                help_text:     '',
                required:      alwaysRequired,
                width:         'full',
                options:       [],
                conditions:    [],
                step:          1,
                error_message: '',
                min_length:    '',
                max_length:    '',
                min_value:     '',
                max_value:     '',
                step_value:    '',
                date_format:   'Y-m-d',
                css_class:     '',
                css_id:        '',
                label_position: 'top',
                show_country_code: true,
                default_country: 'GB',
                allowed_types: 'jpg,jpeg,png,gif,pdf,doc,docx',
                max_file_size: 5,
                multiple:      false,
                show_line2:    true,
                show_country:  true,
                name_format:   'first_last',
                default_value: '',
                dynamic_value: 'none',
                custom_value:  '',
                html_content:  '<p>Add your content here.</p>',
                divider_style: 'line',
                show_title:    true,
                title_alignment: 'left',
                confirm_password: false, show_strength: true,
                max_stars:     5,
                columns:       [{ label: 'Item', name: 'item' }, { label: 'Quantity', name: 'qty' }],
                min_rows:      1,
                max_rows:      10,
                track_utms:    true,
                track_referrer: true,
                track_landing_page: true
            };

            // Add default options for choice fields.
            if ( type === 'dropdown' || type === 'radio' || type === 'checkbox' ) {
                field.options = [
                    { label: 'Option 1', value: 'option_1' },
                    { label: 'Option 2', value: 'option_2' },
                    { label: 'Option 3', value: 'option_3' }
                ];
            }

            var cardHtml = this.generateFieldCard(field);

            $('#wdm-canvas-placeholder').hide();

            // If multi-step is active, append to the last step container.
            var $stepContainers = $('#wdm-builder-canvas .wdm-step-container');
            if ( $stepContainers.length ) {
                var $lastBody = $stepContainers.last().find('.wdm-step-container-body');
                var lastStep  = parseInt( $stepContainers.last().data('step'), 10 ) || 1;
                field.step = lastStep;
                $lastBody.append(cardHtml);
                this.updateStepEmptyStates();
            } else {
                $('#wdm-builder-canvas').append(cardHtml);
            }

            this.fields.push(field);
            this.applyPendingConditions(field.id);
            this.updateSingletonButtons();
        },

        /**
         * Generate the HTML markup for a field card.
         *
         * @since 1.0.0
         * @param  {Object} field The field configuration object.
         * @return {string}       The field card HTML string.
         */
        generateFieldCard: function(field) {
            var typeLabel   = this.typeLabels[field.type] || field.type;
            var mergeTag    = '{' + field.name + '}';
            var checkedAttr = field.required ? ' checked' : '';

            var html = '' +
                '<div class="wdm-field-card" data-field-id="' + field.id + '" data-field-type="' + field.type + '">' +
                    '<div class="wdm-field-card-header">' +
                        '<span class="wdm-field-drag-handle dashicons dashicons-menu"></span>' +
                        '<span class="wdm-field-type-badge">' + $('<span>').text(typeLabel).html() + '</span>' +
                        '<span class="wdm-field-label-preview">' + $('<span>').text(field.label).html() + '</span>' +
                        '<div class="wdm-field-card-actions">' +
                            '<button type="button" class="wdm-field-edit-btn"><span class="dashicons dashicons-edit"></span></button>' +
                            '<button type="button" class="wdm-field-delete-btn"><span class="dashicons dashicons-trash"></span></button>' +
                        '</div>' +
                    '</div>' +
                    '<div class="wdm-field-card-settings" style="display:none;">' +

                        // Tab buttons.
                        '<div class="wdm-field-tabs">' +
                            '<button type="button" class="wdm-field-tab wdm-field-tab-active" data-tab="general">General</button>' +
                            '<button type="button" class="wdm-field-tab" data-tab="validation">Validation</button>' +
                            '<button type="button" class="wdm-field-tab" data-tab="logic">Logic</button>' +
                            '<button type="button" class="wdm-field-tab" data-tab="appearance">Appearance</button>' +
                        '</div>' +

                        // ── Tab 1: General ──────────────────────────
                        '<div class="wdm-field-tab-content wdm-field-tab-active" data-tab="general">';

            // IP Address fields: show Label + Merge Tag only.
            if ( field.type === 'ip_address' ) {
                html += '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Label (admin reference)').html() + '</label>' +
                                '<input type="text" data-setting="label" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.label).html() + '">' +
                                '<span class="wdm-description">For admin reference only. IP is captured automatically.</span>' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Merge Tag').html() + '</label>' +
                                '<input type="text" class="wdm-input wdm-merge-tag-display" value="' + $('<span>').text(mergeTag).html() + '" readonly>' +
                            '</div>';

            // Tracking fields: show Label, Merge Tag, and tracking option checkboxes.
            } else if ( field.type === 'tracking' ) {
                html += '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Label (admin reference)').html() + '</label>' +
                                '<input type="text" data-setting="label" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.label).html() + '">' +
                                '<span class="wdm-description">For admin reference only. Not shown on the frontend.</span>' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Merge Tag').html() + '</label>' +
                                '<input type="text" class="wdm-input wdm-merge-tag-display" value="' + $('<span>').text(mergeTag).html() + '" readonly>' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label><input type="checkbox" data-setting="track_utms" class="wdm-builder-setting-input"' + (field.track_utms !== false ? ' checked' : '') + '> Track UTM Parameters</label>' +
                                '<span class="wdm-description">Captures utm_source, utm_medium, utm_campaign, utm_content, utm_term</span>' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label><input type="checkbox" data-setting="track_referrer" class="wdm-builder-setting-input"' + (field.track_referrer !== false ? ' checked' : '') + '> Track Referrer URL</label>' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label><input type="checkbox" data-setting="track_landing_page" class="wdm-builder-setting-input"' + (field.track_landing_page !== false ? ' checked' : '') + '> Track Landing Page URL</label>' +
                            '</div>';

            // Hidden fields: show Label + Merge Tag only (no placeholder, help text, required, width).
            } else if ( field.type === 'hidden' ) {
                html += '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Label (admin reference)').html() + '</label>' +
                                '<input type="text" data-setting="label" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.label).html() + '">' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Merge Tag').html() + '</label>' +
                                '<input type="text" class="wdm-input wdm-merge-tag-display" value="' + $('<span>').text(mergeTag).html() + '" readonly>' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>Default Value</label>' +
                                '<input type="text" data-setting="default_value" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.default_value || '').html() + '">' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>Dynamic Value</label>' +
                                '<select data-setting="dynamic_value" class="wdm-builder-setting-input wdm-select">' +
                                    '<option value="none"' + (field.dynamic_value === 'none' ? ' selected' : '') + '>None (use default)</option>' +
                                    '<option value="user_id"' + (field.dynamic_value === 'user_id' ? ' selected' : '') + '>Current User ID</option>' +
                                    '<option value="user_email"' + (field.dynamic_value === 'user_email' ? ' selected' : '') + '>Current User Email</option>' +
                                    '<option value="user_name"' + (field.dynamic_value === 'user_name' ? ' selected' : '') + '>Current User Name</option>' +
                                    '<option value="page_url"' + (field.dynamic_value === 'page_url' ? ' selected' : '') + '>Current Page URL</option>' +
                                    '<option value="page_title"' + (field.dynamic_value === 'page_title' ? ' selected' : '') + '>Page Title</option>' +
                                    '<option value="referrer"' + (field.dynamic_value === 'referrer' ? ' selected' : '') + '>Referrer URL</option>' +
                                    '<option value="custom"' + (field.dynamic_value === 'custom' ? ' selected' : '') + '>Custom Value</option>' +
                                '</select>' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>Custom Value</label>' +
                                '<input type="text" data-setting="custom_value" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.custom_value || '').html() + '">' +
                            '</div>';

            // HTML block: show Label (admin reference) + HTML content only.
            } else if ( field.type === 'html' ) {
                html += '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Label (admin reference)').html() + '</label>' +
                                '<input type="text" data-setting="label" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.label).html() + '">' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>HTML Content</label>' +
                                '<textarea data-setting="html_content" class="wdm-builder-setting-input wdm-textarea" rows="6">' + $('<span>').text(field.html_content || '<p>Add your content here.</p>').html() + '</textarea>' +
                                '<span class="wdm-description">Supports HTML: headings, paragraphs, lists, links, images.</span>' +
                            '</div>';

            // Section Divider: custom General tab — title, alignment, style.
            } else if ( field.type === 'section_divider' ) {
                var sel = function(current, val) { return current === val ? ' selected' : ''; };
                html += '<div class="wdm-builder-field-setting">' +
                                '<label>Title Text</label>' +
                                '<input type="text" data-setting="label" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.label).html() + '">' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label><input type="checkbox" data-setting="show_title" class="wdm-builder-setting-input"' + (field.show_title !== false ? ' checked' : '') + '> Show Title</label>' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>Title Alignment</label>' +
                                '<select data-setting="title_alignment" class="wdm-builder-setting-input wdm-select">' +
                                    '<option value="left"' + sel(field.title_alignment, 'left') + '>Left</option>' +
                                    '<option value="center"' + sel(field.title_alignment, 'center') + '>Center</option>' +
                                    '<option value="right"' + sel(field.title_alignment, 'right') + '>Right</option>' +
                                '</select>' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>Divider Style</label>' +
                                '<select data-setting="divider_style" class="wdm-builder-setting-input wdm-select">' +
                                    '<option value="line"' + sel(field.divider_style, 'line') + '>Solid Line</option>' +
                                    '<option value="dashed"' + sel(field.divider_style, 'dashed') + '>Dashed</option>' +
                                    '<option value="dotted"' + sel(field.divider_style, 'dotted') + '>Dotted</option>' +
                                    '<option value="double"' + sel(field.divider_style, 'double') + '>Double</option>' +
                                    '<option value="none"' + sel(field.divider_style, 'none') + '>No Line</option>' +
                                '</select>' +
                            '</div>';

            // Repeater: custom General tab — label, help text, columns manager, min/max rows.
            } else if ( field.type === 'repeater' ) {
                html += '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Label').html() + '</label>' +
                                '<input type="text" data-setting="label" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.label).html() + '">' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Help Text').html() + '</label>' +
                                '<input type="text" data-setting="help_text" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.help_text).html() + '">' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label><input type="checkbox" data-setting="required" class="wdm-builder-setting-input"' + checkedAttr + '> ' + $('<span>').text('Required').html() + '</label>' +
                            '</div>';
                var cols = field.columns || [{label:'Item',name:'item'},{label:'Quantity',name:'qty'}];
                html += '<div class="wdm-builder-field-setting">' +
                            '<label>Columns</label>' +
                            '<div class="wdm-repeater-columns-list">';
                for (var ci = 0; ci < cols.length; ci++) {
                    html += '<div class="wdm-repeater-col-row" data-index="' + ci + '">' +
                        '<input type="text" data-setting="col_label" class="wdm-input" placeholder="Column Label" value="' + $('<span>').text(cols[ci].label).html() + '">' +
                        '<input type="text" data-setting="col_name" class="wdm-input" placeholder="Column Name" value="' + $('<span>').text(cols[ci].name).html() + '">' +
                        '<button type="button" class="wdm-repeater-col-remove"><span class="dashicons dashicons-no-alt"></span></button>' +
                    '</div>';
                }
                html += '</div>' +
                    '<button type="button" class="wdm-repeater-col-add">+ Add Column</button>' +
                '</div>' +
                '<div class="wdm-builder-field-setting">' +
                    '<label>Min Rows</label>' +
                    '<input type="number" data-setting="min_rows" class="wdm-builder-setting-input wdm-input wdm-input-small" value="' + (field.min_rows || 1) + '" min="1" max="50">' +
                '</div>' +
                '<div class="wdm-builder-field-setting">' +
                    '<label>Max Rows</label>' +
                    '<input type="number" data-setting="max_rows" class="wdm-builder-setting-input wdm-input wdm-input-small" value="' + (field.max_rows || 10) + '" min="1" max="50">' +
                '</div>';

            // All other field types: standard General tab fields.
            } else {
                html += '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Label').html() + '</label>' +
                                '<input type="text" data-setting="label" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.label).html() + '">' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>Label Position</label>' +
                                '<select data-setting="label_position" class="wdm-builder-setting-input wdm-select">' +
                                    '<option value="top"' + (field.label_position === 'top' ? ' selected' : '') + '>Above field</option>' +
                                    '<option value="bottom"' + (field.label_position === 'bottom' ? ' selected' : '') + '>Below field</option>' +
                                    '<option value="hidden"' + (field.label_position === 'hidden' ? ' selected' : '') + '>Hidden (screen reader only)</option>' +
                                '</select>' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Placeholder').html() + '</label>' +
                                '<input type="text" data-setting="placeholder" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.placeholder).html() + '">' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Merge Tag').html() + '</label>' +
                                '<input type="text" class="wdm-input wdm-merge-tag-display" value="' + $('<span>').text(mergeTag).html() + '" readonly>' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Help Text').html() + '</label>' +
                                '<input type="text" data-setting="help_text" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.help_text).html() + '">' +
                            '</div>' +
                            '<div class="wdm-builder-field-setting">';

                var isAlwaysRequired = ( field.type === 'payment' || field.type === 'captcha' );
                if ( isAlwaysRequired ) {
                    html += '<label><input type="checkbox" data-setting="required" class="wdm-builder-setting-input" checked disabled> ' + $('<span>').text('Required (always)').html() + '</label>';
                } else {
                    html += '<label><input type="checkbox" data-setting="required" class="wdm-builder-setting-input"' + checkedAttr + '> ' + $('<span>').text('Required').html() + '</label>';
                }

                html += '</div>' +
                            '<div class="wdm-builder-field-setting">' +
                                '<label>' + $('<span>').text('Width').html() + '</label>' +
                                '<div class="wdm-width-selector">' +
                                    '<label><input type="radio" name="width_' + field.id + '" data-setting="width" value="full" class="wdm-builder-setting-input"' + (field.width === 'full' ? ' checked' : '') + '> ' + $('<span>').text('Full').html() + '</label>' +
                                    '<label><input type="radio" name="width_' + field.id + '" data-setting="width" value="half" class="wdm-builder-setting-input"' + (field.width === 'half' ? ' checked' : '') + '> ' + $('<span>').text('Half').html() + '</label>' +
                                    '<label><input type="radio" name="width_' + field.id + '" data-setting="width" value="third" class="wdm-builder-setting-input"' + (field.width === 'third' ? ' checked' : '') + '> ' + $('<span>').text('Third').html() + '</label>' +
                                '</div>' +
                            '</div>';
            }

            // Date format selector (inside General tab, date fields only).
            if ( field.type === 'date' ) {
                var dateFormat = field.date_format || 'Y-m-d';
                var dateFormats = [
                    { value: 'Y-m-d',   preview: '2026-03-25' },
                    { value: 'd/m/Y',   preview: '25/03/2026' },
                    { value: 'm/d/Y',   preview: '03/25/2026' },
                    { value: 'd-m-Y',   preview: '25-03-2026' },
                    { value: 'd.m.Y',   preview: '25.03.2026' },
                    { value: 'F j, Y',  preview: 'March 25, 2026' },
                    { value: 'M j, Y',  preview: 'Mar 25, 2026' },
                    { value: 'j F Y',   preview: '25 March 2026' }
                ];

                html += '<div class="wdm-builder-field-setting">' +
                            '<label>Date Format</label>' +
                            '<select data-setting="date_format" class="wdm-builder-setting-input wdm-select">';

                $.each(dateFormats, function(i, fmt) {
                    var sel = (fmt.value === dateFormat) ? ' selected' : '';
                    html += '<option value="' + fmt.value + '"' + sel + '>' +
                                $('<span>').text(fmt.value + '  →  ' + fmt.preview).html() +
                            '</option>';
                });

                html += '</select>' +
                        '<span class="wdm-description">How the date appears in the form and document.</span>' +
                    '</div>';
            }

            // Options manager for choice fields (inside General tab).
            if ( field.type === 'dropdown' || field.type === 'radio' || field.type === 'checkbox' ) {
                var options = field.options || [];
                html += '<div class="wdm-builder-field-setting wdm-options-manager">' +
                            '<label>Options</label>' +
                            '<div class="wdm-options-list">';

                $.each(options, function(i, option) {
                    html += '<div class="wdm-option-row">' +
                                '<span class="wdm-option-drag dashicons dashicons-menu"></span>' +
                                '<input type="text" class="wdm-option-label wdm-input" placeholder="Label" value="' + $('<span>').text(option.label).html() + '">' +
                                '<input type="text" class="wdm-option-value wdm-input" placeholder="Value (auto)" value="' + $('<span>').text(option.value).html() + '">' +
                                '<button type="button" class="wdm-option-remove"><span class="dashicons dashicons-no-alt"></span></button>' +
                            '</div>';
                });

                html += '</div>' +
                        '<button type="button" class="wdm-add-option wdm-btn wdm-btn-sm">+ Add Option</button>' +
                    '</div>';

                // Searchable toggle — dropdown only.
                if ( field.type === 'dropdown' ) {
                    var searchChecked = field.searchable ? ' checked' : '';
                    html += '<div class="wdm-builder-field-setting">' +
                                '<label><input type="checkbox" data-setting="searchable" class="wdm-builder-setting-input"' + searchChecked + '> ' +
                                $('<span>').text('Enable search (users can type to filter options)').html() +
                                '</label>' +
                                '<span class="wdm-description">Recommended for dropdowns with many options.</span>' +
                            '</div>';
                }
            }

            // File Upload field settings (inside General tab).
            if ( field.type === 'file_upload' ) {
                html += '<div class="wdm-builder-field-setting">' +
                            '<label>Allowed File Types</label>' +
                            '<input type="text" data-setting="allowed_types" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.allowed_types || 'jpg,jpeg,png,gif,pdf,doc,docx').html() + '" placeholder="jpg,png,pdf,docx">' +
                            '<span class="wdm-description">Comma-separated file extensions</span>' +
                        '</div>' +
                        '<div class="wdm-builder-field-setting">' +
                            '<label>Max File Size (MB)</label>' +
                            '<input type="number" data-setting="max_file_size" class="wdm-builder-setting-input wdm-input wdm-input-small" value="' + (field.max_file_size || 5) + '" min="1" max="50">' +
                        '</div>' +
                        '<div class="wdm-builder-field-setting">' +
                            '<label><input type="checkbox" data-setting="multiple" class="wdm-builder-setting-input"' + (field.multiple ? ' checked' : '') + '> Allow multiple files</label>' +
                        '</div>';
            }

            // Address field settings (inside General tab).
            if ( field.type === 'address' ) {
                html += '<div class="wdm-builder-field-setting">' +
                            '<label><input type="checkbox" data-setting="show_line2" class="wdm-builder-setting-input"' + (field.show_line2 !== false ? ' checked' : '') + '> Show Address Line 2</label>' +
                        '</div>' +
                        '<div class="wdm-builder-field-setting">' +
                            '<label><input type="checkbox" data-setting="show_country" class="wdm-builder-setting-input"' + (field.show_country !== false ? ' checked' : '') + '> Show Country field</label>' +
                        '</div>' +
                        '<div class="wdm-builder-field-setting">' +
                            '<label>Default Country</label>' +
                            '<input type="text" data-setting="default_country" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.default_country || '').html() + '" placeholder="e.g. United Kingdom">' +
                        '</div>';
            }

            // Name field settings (inside General tab).
            if ( field.type === 'name' ) {
                html += '<div class="wdm-builder-field-setting">' +
                            '<label>Name Format</label>' +
                            '<select data-setting="name_format" class="wdm-builder-setting-input wdm-select">' +
                                '<option value="first_last"' + (field.name_format === 'first_last' ? ' selected' : '') + '>First + Last</option>' +
                                '<option value="first_middle_last"' + (field.name_format === 'first_middle_last' ? ' selected' : '') + '>First + Middle + Last</option>' +
                                '<option value="title_first_last"' + (field.name_format === 'title_first_last' ? ' selected' : '') + '>Title + First + Last</option>' +
                            '</select>' +
                        '</div>';
            }

            // Password field settings (inside General tab).
            if ( field.type === 'password' ) {
                html += '<div class="wdm-builder-field-setting">' +
                            '<label><input type="checkbox" data-setting="confirm_password" class="wdm-builder-setting-input"' + (field.confirm_password ? ' checked' : '') + '> Require password confirmation</label>' +
                            '<span class="wdm-description">Shows a second &ldquo;Confirm Password&rdquo; field</span>' +
                        '</div>' +
                        '<div class="wdm-builder-field-setting">' +
                            '<label><input type="checkbox" data-setting="show_strength" class="wdm-builder-setting-input"' + (field.show_strength !== false ? ' checked' : '') + '> Show password strength indicator</label>' +
                        '</div>';
            }

            // Rating field settings (inside General tab).
            if ( field.type === 'rating' ) {
                html += '<div class="wdm-builder-field-setting">' +
                            '<label>Max Stars</label>' +
                            '<select data-setting="max_stars" class="wdm-builder-setting-input wdm-select">' +
                                '<option value="3"' + (parseInt(field.max_stars, 10) === 3 ? ' selected' : '') + '>3 Stars</option>' +
                                '<option value="5"' + (parseInt(field.max_stars, 10) === 5 ? ' selected' : '') + '>5 Stars</option>' +
                                '<option value="10"' + (parseInt(field.max_stars, 10) === 10 ? ' selected' : '') + '>10 Stars</option>' +
                            '</select>' +
                        '</div>';
            }

            // Close General tab content.
            html += '</div>';

            // ── Tab 2: Validation ───────────────────────────
            var errorMessage = field.error_message || '';
            var minValue     = field.min_value || '';
            var maxValue     = field.max_value || '';
            var stepVal      = field.step_value || '';
            var minLength    = field.min_length || '';
            var maxLength    = field.max_length || '';

            html += '<div class="wdm-field-tab-content" data-tab="validation">' +
                        '<div class="wdm-builder-field-setting">' +
                            '<label>' + $('<span>').text('Custom Error Message').html() + '</label>' +
                            '<input type="text" data-setting="error_message" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(errorMessage).html() + '" placeholder="Leave blank for default">' +
                        '</div>';

            // Number-specific validation fields.
            if ( field.type === 'number' ) {
                html += '<div class="wdm-builder-field-setting">' +
                            '<label>' + $('<span>').text('Min Value').html() + '</label>' +
                            '<input type="number" data-setting="min_value" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(minValue).html() + '">' +
                        '</div>' +
                        '<div class="wdm-builder-field-setting">' +
                            '<label>' + $('<span>').text('Max Value').html() + '</label>' +
                            '<input type="number" data-setting="max_value" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(maxValue).html() + '">' +
                        '</div>' +
                        '<div class="wdm-builder-field-setting">' +
                            '<label>' + $('<span>').text('Step').html() + '</label>' +
                            '<input type="number" data-setting="step_value" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(stepVal).html() + '" placeholder="e.g. 1, 0.01">' +
                        '</div>';
            }

            // Text-specific validation fields.
            if ( field.type === 'text' || field.type === 'textarea' || field.type === 'email' || field.type === 'phone' || field.type === 'password' || field.type === 'url' ) {
                html += '<div class="wdm-builder-field-setting">' +
                            '<label>' + $('<span>').text('Min Length').html() + '</label>' +
                            '<input type="number" data-setting="min_length" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(minLength).html() + '">' +
                        '</div>' +
                        '<div class="wdm-builder-field-setting">' +
                            '<label>' + $('<span>').text('Max Length').html() + '</label>' +
                            '<input type="number" data-setting="max_length" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(maxLength).html() + '">' +
                        '</div>';
            }

            // Close Validation tab content.
            html += '</div>';

            // ── Tab 3: Logic ────────────────────────────────
            html += '<div class="wdm-field-tab-content" data-tab="logic">';

            // Conditional logic section.
            var conditions = field.conditions || [];
            var hasConditions = conditions.length > 0;
            html += '<div class="wdm-builder-field-setting wdm-conditions-section">' +
                        '<label>' +
                            '<input type="checkbox" class="wdm-enable-conditions" data-setting="has_conditions"' + ( hasConditions ? ' checked' : '' ) + '> ' +
                            'Show this field only when...' +
                        '</label>' +
                        '<div class="wdm-conditions-wrap" style="display:' + ( hasConditions ? 'block' : 'none' ) + ';">' +
                            '<div class="wdm-conditions-list">';

            var self = this;
            $.each(conditions, function(i, condition) {
                var otherFieldOptions = self.getOtherFieldOptions(field.id);
                var hideValue = (condition.operator === 'is_empty' || condition.operator === 'is_not_empty');
                html += '<div class="wdm-condition-row">' +
                            '<select class="wdm-condition-field wdm-select">' + otherFieldOptions + '</select>' +
                            '<select class="wdm-condition-operator wdm-select">' +
                                '<option value="equals"' + ( condition.operator === 'equals' ? ' selected' : '' ) + '>Equals</option>' +
                                '<option value="not_equals"' + ( condition.operator === 'not_equals' ? ' selected' : '' ) + '>Not equals</option>' +
                                '<option value="contains"' + ( condition.operator === 'contains' ? ' selected' : '' ) + '>Contains</option>' +
                                '<option value="not_contains"' + ( condition.operator === 'not_contains' ? ' selected' : '' ) + '>Not contains</option>' +
                                '<option value="is_empty"' + ( condition.operator === 'is_empty' ? ' selected' : '' ) + '>Is empty</option>' +
                                '<option value="is_not_empty"' + ( condition.operator === 'is_not_empty' ? ' selected' : '' ) + '>Is not empty</option>' +
                            '</select>' +
                            '<input type="text" class="wdm-condition-value wdm-input" placeholder="Enter value..." value="' + $('<span>').text(condition.value || '').html() + '"' + ( hideValue ? ' style="visibility:hidden;"' : '' ) + '>' +
                            '<button type="button" class="wdm-condition-remove" title="Remove condition"><span class="dashicons dashicons-no-alt"></span></button>' +
                        '</div>';
            });

            html += '</div>' +
                        '<button type="button" class="wdm-add-condition wdm-btn wdm-btn-sm">+ Add Condition</button>' +
                    '</div>' +
                '</div>';

            // Multi-step assignment is now handled by visual step containers.
            // The field's step is determined by which container it resides in.

            // Close Logic tab content.
            html += '</div>';

            // ── Tab 4: Appearance ──────────────────────────
            html += '<div class="wdm-field-tab-content" data-tab="appearance">' +
                        '<div class="wdm-builder-field-setting">' +
                            '<label>CSS Class</label>' +
                            '<input type="text" data-setting="css_class" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.css_class || '').html() + '" placeholder="e.g. my-custom-field">' +
                            '<span class="wdm-description">Add custom CSS class(es). Separate multiple with spaces.</span>' +
                        '</div>' +
                        '<div class="wdm-builder-field-setting">' +
                            '<label>CSS ID</label>' +
                            '<input type="text" data-setting="css_id" class="wdm-builder-setting-input wdm-input" value="' + $('<span>').text(field.css_id || '').html() + '" placeholder="e.g. my-field-id">' +
                            '<span class="wdm-description">Optional unique HTML ID for this field.</span>' +
                        '</div>' +
                    '</div>';

            // Close settings panel and card.
            html += '</div>' +
                '</div>';

            // After appending, we need to set selected condition fields.
            // Store conditions data on the card for post-render selection.
            this._pendingConditions = this._pendingConditions || {};
            if ( hasConditions ) {
                this._pendingConditions[field.id] = conditions;
            }

            return html;
        },

        /**
         * Toggle the settings panel for a field card.
         *
         * @since 1.0.0
         * @param {jQuery} $btn The clicked edit button.
         */
        toggleFieldSettings: function($btn) {
            var $card     = $btn.closest('.wdm-field-card');
            var $settings = $card.find('.wdm-field-card-settings');

            $settings.slideToggle(200);
        },

        /**
         * Delete a field from the canvas and fields array.
         *
         * Prompts for confirmation before removing.
         *
         * @since 1.0.0
         * @param {jQuery} $btn The clicked delete button.
         */
        deleteField: function($btn) {
            if ( ! window.confirm('Are you sure you want to remove this field?') ) {
                return;
            }

            var $card   = $btn.closest('.wdm-field-card');
            var fieldId = $card.data('field-id');
            var self    = this;

            // Remove from fields array.
            this.fields = $.grep(this.fields, function(f) {
                return f.id !== fieldId;
            });

            var self = this;
            $card.fadeOut(200, function() {
                $(this).remove();

                // Show placeholder if canvas is empty (flat mode).
                if ( ! $('#wdm-builder-canvas').find('.wdm-field-card').length &&
                     ! $('#wdm-builder-canvas').find('.wdm-step-container').length ) {
                    $('#wdm-canvas-placeholder').show();
                }

                // Update step empty states if in multi-step mode.
                self.updateStepEmptyStates();

                // Re-enable singleton buttons after deletion.
                self.updateSingletonButtons();
            });
        },

        /**
         * Update a field's data when a setting input changes.
         *
         * @since 1.0.0
         * @param {jQuery} $input The changed input element.
         */
        updateFieldData: function($input) {
            var $card   = $input.closest('.wdm-field-card');
            var fieldId = $card.data('field-id');
            var setting = $input.data('setting');
            var value;

            if ( ! setting ) {
                return;
            }

            // Repeater column inputs are handled by a dedicated handler.
            if ( setting === 'col_label' || setting === 'col_name' ) {
                return;
            }

            // Determine value based on input type.
            if ( $input.is(':checkbox') ) {
                value = $input.is(':checked');
            } else if ( $input.is(':radio') ) {
                value = $input.val();
            } else {
                value = $input.val();
            }

            // Find and update the field in the array.
            $.each(this.fields, function(i, field) {
                if ( field.id === fieldId ) {
                    field[setting] = value;
                    return false;
                }
            });

            // Update label preview if label changed.
            if ( setting === 'label' ) {
                $card.find('.wdm-field-label-preview').text(value);
            }
        },

        /**
         * Update the merge tag display when the label changes.
         *
         * Converts the label to a lowercase, underscore-separated,
         * alphanumeric-only string for use as a merge tag.
         *
         * @since 1.0.0
         * @param {jQuery} $input The label input element.
         */
        updateMergeTag: function($input) {
            var $card    = $input.closest('.wdm-field-card');
            var fieldId  = $card.data('field-id');
            var label    = $input.val();
            var mergeTag = label.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');

            // Update the readonly merge tag display.
            $card.find('.wdm-merge-tag-display').val('{' + mergeTag + '}');

            // Update the field name in the array.
            $.each(this.fields, function(i, field) {
                if ( field.id === fieldId ) {
                    field.name = mergeTag;
                    return false;
                }
            });
        },

        /**
         * Rebuild the fields array to match the current DOM order.
         *
         * Called after jQuery UI Sortable reorders the field cards.
         *
         * @since 1.0.0
         */
        updateFieldOrder: function() {
            var self      = this;
            var reordered = [];

            // Collect all field cards in DOM order (works for both flat and step modes).
            $('#wdm-builder-canvas').find('.wdm-field-card').each(function() {
                var fieldId = $(this).data('field-id');

                $.each(self.fields, function(i, field) {
                    if ( field.id === fieldId ) {
                        reordered.push(field);
                        return false;
                    }
                });
            });

            this.fields = reordered;
        },

        /**
         * Check if a field type is a singleton (only one allowed per form).
         *
         * @param  {string}  type Field type slug.
         * @return {boolean}
         */
        isSingletonField: function(type) {
            return type === 'captcha' || type === 'payment';
        },

        /**
         * Check if the form already contains a field of the given type.
         *
         * @param  {string}  type Field type slug.
         * @return {boolean}
         */
        hasFieldType: function(type) {
            var found = false;
            $.each(this.fields, function(i, field) {
                if (field.type === type) {
                    found = true;
                    return false;
                }
            });
            return found;
        },

        /**
         * Update sidebar button states — disable buttons for singleton
         * fields that are already on the canvas.
         */
        updateSingletonButtons: function() {
            var self = this;
            var singletons = ['captcha', 'payment'];

            $.each(singletons, function(i, type) {
                var $btn = $('.wdm-field-type-btn[data-type="' + type + '"]');
                if (self.hasFieldType(type)) {
                    $btn.prop('disabled', true).addClass('wdm-singleton-used');
                } else {
                    $btn.prop('disabled', false).removeClass('wdm-singleton-used');
                }
            });
        },

        // ─────────────────────────────────────────────────────────
        //  Save & load
        // ─────────────────────────────────────────────────────────

        /**
         * Save the form via AJAX.
         *
         * Collects all form data including fields, settings, and
         * metadata, validates the title, and sends to the server.
         *
         * @since 1.0.0
         */
        saveForm: function() {
            var self           = this;
            var formId         = $('#wdm-form-id').val();
            var title          = $.trim($('#wdm-form-title').val());
            var templateId     = $('#wdm-form-template').val();
            var outputFormat   = $('#wdm-form-output').val();
            var submitLabel    = $.trim($('#wdm-submit-label').val());
            var successMessage = $.trim($('#wdm-success-message').val());
            var multistep      = $('#wdm-multistep').is(':checked') ? 1 : 0;

            // Sync field step assignments from DOM if multi-step is active.
            if ( multistep ) {
                this.syncFieldStepsFromDOM();
            }

            // Collect multi-step labels.
            var multistepLabels = [];
            if ( multistep ) {
                $('.wdm-step-container').each(function() {
                    multistepLabels.push( $.trim( $(this).find('.wdm-step-label-input').val() ) || 'Step ' + $(this).data('step') );
                });
            }

            // Button appearance settings.
            var settingsObj = {
                btn_width:      $('#wdm-btn-width').val() || 'auto',
                btn_align:      $('input[name="wdm_btn_align"]:checked').val() || 'right',
                btn_style:      $('#wdm-btn-style').val() || 'filled',
                btn_size:       $('#wdm-btn-size').val() || 'medium',
                btn_radius:     $('#wdm-btn-radius').val() || '6',
                btn_bg_color:   $('#wdm-btn-bg-color').val() || '#042157',
                btn_text_color: $('#wdm-btn-text-color').val() || '#ffffff',
                btn_hover_bg:   $('#wdm-btn-hover-bg').val() || '#0a3d8f',
                btn_hover_text: $('#wdm-btn-hover-text').val() || '#ffffff',
                entry_limit:       $('#wdm-entry-limit').val() || '',
                limit_per_email:   $('#wdm-limit-per-email').val() || '0',
                limit_email_field: $('#wdm-limit-email-field').val() || '',
                limit_per_ip:      $('#wdm-limit-per-ip').val() || '0',
                limit_per_user:    $('#wdm-limit-per-user').val() || '0',
                closed_message:    $.trim($('#wdm-closed-message').val()) || '',
                webhook_url:    $.trim($('#wdm-webhook-url-field').val()) || ''
            };

            // Integration settings (external_form_id + field_map).
            if ( $('#wdm-external-form-id').length && $('#wdm-external-form-id').val() ) {
                settingsObj.external_form_id = $('#wdm-external-form-id').val();
            }
            var fieldMap = {};
            $('.wdm-field-map-select').each(function() {
                var tag = $(this).data('merge-tag');
                var val = $(this).val();
                if ( tag && val ) {
                    fieldMap[tag] = val;
                }
            });
            if ( Object.keys(fieldMap).length > 0 ) {
                settingsObj.field_map = fieldMap;
            }

            if ( multistep && multistepLabels.length ) {
                settingsObj.multistep_labels = multistepLabels;
            }

            var settings = JSON.stringify(settingsObj);

            // Validate title.
            if ( ! title || title === 'Untitled Form' ) {
                self.showNotice('error', 'Please enter a form title.');
                return;
            }

            var $btn = $('#wdm-save-form');

            $.ajax({
                url:      wprobo_documerge_vars.ajax_url,
                type:     'POST',
                dataType: 'json',
                data: {
                    action:          'wprobo_documerge_save_form',
                    nonce:           wprobo_documerge_vars.nonce,
                    id:              formId,
                    title:           title,
                    template_id:     templateId,
                    fields:          JSON.stringify(self.fields),
                    output_format:   outputFormat,
                    submit_label:    submitLabel,
                    success_message: successMessage,
                    multistep:       multistep,
                    settings:        settings,
                    mode:            $('#wdm-form-mode').val() || 'standalone',
                    integration:     $('#wdm-form-integration').val() || '',
                    delivery_methods: JSON.stringify(
                        $('.wdm-delivery-method:checked').map(function() { return $(this).val(); }).get()
                    ),
                    enable_payment:  $('#wdm-payment-enabled').is(':checked') ? '1' : '0',
                    payment_amount:  $('#wdm-payment-amount').val() || '0',
                    payment_currency: $('#wdm-payment-currency').val() || 'USD'
                },
                beforeSend: function() {
                    $btn.prop('disabled', true).addClass('wdm-loading');
                },
                success: function(response) {
                    if ( response.success ) {
                        var formId = response.data && response.data.id ? response.data.id : $('#wdm-form-id').val();

                        // Detect which tab is currently active.
                        var activeTab = 'fields';
                        var $activeMainTab = $('.wdm-builder-main-tab-active');
                        if ($activeMainTab.length) {
                            activeTab = $activeMainTab.data('tab') || 'fields';
                        }

                        // Detect which settings sub-tab is active.
                        var activeSubtab = '';
                        var $activeSubTab = $('.wdm-settings-subtab-active');
                        if ($activeSubTab.length) {
                            activeSubtab = $activeSubTab.data('subtab') || '';
                        }

                        // Redirect to the edit page — reloads integration fields, step containers, etc.
                        window.location.href = window.location.pathname +
                            '?page=wprobo-documerge-forms&action=edit&id=' + formId + '&saved=1&tab=' + activeTab +
                            (activeSubtab ? '&subtab=' + activeSubtab : '');
                        return;
                    } else {
                        self.showNotice('error',
                            response.data && response.data.message
                                ? response.data.message
                                : 'An error occurred while saving the form.'
                        );
                    }
                },
                error: function() {
                    self.showNotice('error', 'Network error. Please try again.');
                },
                complete: function() {
                    $btn.prop('disabled', false).removeClass('wdm-loading');
                }
            });
        },

        /**
         * Generate a preview document with sample data.
         *
         * Sends the current form ID to the server, which fills the
         * template with sample data and returns a temporary PDF URL.
         * Opens the preview in a new browser tab.
         *
         * @since 1.4.0
         */
        wprobo_documerge_preview_document: function() {
            var self   = this;
            var formId = $('#wdm-form-id').val();

            if ( ! formId || formId === '0' ) {
                self.showNotice('error', 'Please save the form first.');
                return;
            }

            var $btn = $('#wdm-preview-doc');
            $btn.prop('disabled', true).addClass('wdm-loading');

            $.ajax({
                url:      wprobo_documerge_vars.ajax_url,
                type:     'POST',
                dataType: 'json',
                data: {
                    action:  'wprobo_documerge_preview_document',
                    nonce:   wprobo_documerge_vars.nonce,
                    form_id: formId
                },
                success: function(response) {
                    $btn.prop('disabled', false).removeClass('wdm-loading');
                    if ( response.success && response.data.url ) {
                        window.open(response.data.url, '_blank');
                    } else {
                        self.showNotice('error', response.data ? response.data.message : 'Preview failed.');
                    }
                },
                error: function() {
                    $btn.prop('disabled', false).removeClass('wdm-loading');
                    self.showNotice('error', 'Network error. Please try again.');
                }
            });
        },

        /**
         * Load existing fields into the canvas when editing a form.
         *
         * Reads the global wprobo_documerge_form_fields array, renders
         * each field card, and sets the field counter to avoid ID collisions.
         *
         * @since 1.0.0
         */
        loadExistingFields: function() {
            var self     = this;
            var existing = wprobo_documerge_form_fields;
            var maxNum   = 0;

            if ( ! $.isArray(existing) || ! existing.length ) {
                return;
            }

            $.each(existing, function(i, field) {
                self.addFieldFromData(field);

                // Track the highest field number for the counter.
                var match = field.id ? field.id.match(/field_(\d+)/) : null;
                if ( match ) {
                    var num = parseInt(match[1], 10);
                    if ( num > maxNum ) {
                        maxNum = num;
                    }
                }
            });

            this.fieldCounter = maxNum;
            this.updateSingletonButtons();

            // Build step containers if multi-step is enabled.
            if ( $('#wdm-multistep').is(':checked') ) {
                this.buildStepContainers();
            }
        },

        /**
         * Add a single field to the canvas from existing field data.
         *
         * Used by loadExistingFields to render pre-saved fields.
         *
         * @since 1.0.0
         * @param {Object} field The field data object.
         */
        addFieldFromData: function(field) {
            // Ensure defaults for any missing properties.
            // Preserve ALL saved properties — don't whitelist.
            var defaults = $.extend({
                id:            'field_0',
                type:          'text',
                label:         'Untitled',
                name:          '',
                placeholder:   '',
                help_text:     '',
                required:      false,
                width:         'full',
                options:       [],
                conditions:    [],
                step:          1,
                error_message: '',
                min_length:    '',
                max_length:    '',
                min_value:     '',
                max_value:     '',
                step_value:    '',
                date_format:   'Y-m-d',
                css_class:     '',
                css_id:        ''
            }, field);

            var cardHtml = this.generateFieldCard(defaults);

            $('#wdm-canvas-placeholder').hide();
            $('#wdm-builder-canvas').append(cardHtml);

            this.fields.push(defaults);
            this.applyPendingConditions(defaults.id);
        },

        // ─────────────────────────────────────────────────────────
        //  Form list actions
        // ─────────────────────────────────────────────────────────

        /**
         * Copy a shortcode string to the clipboard.
         *
         * @since 1.0.0
         * @param {jQuery} $btn The clicked copy button.
         */
        copyShortcode: function($btn) {
            var shortcode = $btn.data('shortcode');

            if ( ! shortcode ) {
                return;
            }

            var $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(shortcode).select();
            document.execCommand('copy');
            $temp.remove();

            this.showToast('Copied!', 'success');
        },

        /**
         * Delete a form after user confirmation.
         *
         * @since 1.0.0
         * @param {jQuery} $btn The clicked delete button.
         */
        deleteForm: function($btn) {
            var self   = this;
            var formId = $btn.data('id');

            if ( ! window.confirm('Are you sure you want to delete this form? This cannot be undone.') ) {
                return;
            }

            var $row = $btn.closest('tr, .wdm-form-card');

            $.ajax({
                url:      wprobo_documerge_vars.ajax_url,
                type:     'POST',
                dataType: 'json',
                data: {
                    action:  'wprobo_documerge_delete_form',
                    nonce:   wprobo_documerge_vars.nonce,
                    form_id: formId
                },
                beforeSend: function() {
                    $btn.prop('disabled', true).addClass('wdm-loading');
                },
                success: function(response) {
                    if ( response.success ) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                        });
                        self.showNotice('success',
                            response.data && response.data.message
                                ? response.data.message
                                : 'Form deleted.'
                        );
                    } else {
                        self.showNotice('error',
                            response.data && response.data.message
                                ? response.data.message
                                : 'An error occurred.'
                        );
                    }
                },
                error: function() {
                    self.showNotice('error', 'Network error. Please try again.');
                },
                complete: function() {
                    $btn.prop('disabled', false).removeClass('wdm-loading');
                }
            });
        },

        // ─────────────────────────────────────────────────────────
        //  Notices & toasts
        // ─────────────────────────────────────────────────────────

        /**
         * Display an admin notice in the notices container.
         *
         * @since 1.0.0
         * @param {string} type    Notice type: 'success' or 'error'.
         * @param {string} message The notice message text.
         */
        showNotice: function(type, message) {
            var $container = $('#wdm-notices');
            var iconClass  = type === 'success' ? 'dashicons-yes-alt' : 'dashicons-warning';

            var $notice = $(
                '<div class="wdm-notice wdm-notice-' + type + '">' +
                    '<span class="dashicons ' + iconClass + '"></span> ' +
                    '<span class="wdm-notice-message">' + $('<span>').text(message).html() + '</span>' +
                    '<button type="button" class="wdm-notice-dismiss">&times;</button>' +
                '</div>'
            );

            $container.append($notice);

            $('html, body').animate({
                scrollTop: $container.offset().top - 50
            }, 300);

            // Auto-dismiss after 5 seconds.
            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);

            // Manual dismiss.
            $notice.on('click', '.wdm-notice-dismiss', function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            });
        },

        /**
         * Show a temporary toast notification.
         *
         * @since 1.0.0
         * @param {string} message The toast message text.
         * @param {string} type    Toast type: 'success' or 'error'.
         */
        showToast: function(message, type) {
            var typeClass = type ? 'wdm-toast-' + type : 'wdm-toast-success';

            var $toast = $(
                '<div class="wdm-toast ' + typeClass + '">' +
                    '<span class="wdm-toast-message">' + $('<span>').text(message).html() + '</span>' +
                '</div>'
            );

            $('body').append($toast);

            // Trigger reflow to enable CSS transition.
            $toast[0].offsetHeight;
            $toast.addClass('wdm-toast-visible');

            // Remove after 3 seconds.
            setTimeout(function() {
                $toast.removeClass('wdm-toast-visible');
                setTimeout(function() {
                    $toast.remove();
                }, 300);
            }, 3000);
        },

        // ─────────────────────────────────────────────────────────
        //  Options manager
        // ─────────────────────────────────────────────────────────

        /**
         * Add a new option row to the options list.
         *
         * @since 1.1.0
         * @param {Event} e The click event.
         */
        addOption: function(e) {
            var $btn  = $(e.target).closest('.wdm-add-option');
            var $card = $btn.closest('.wdm-field-card');
            var $list = $card.find('.wdm-options-list');

            var rowHtml = '<div class="wdm-option-row">' +
                '<span class="wdm-option-drag dashicons dashicons-menu"></span>' +
                '<input type="text" class="wdm-option-label wdm-input" placeholder="Label" value="">' +
                '<input type="text" class="wdm-option-value wdm-input" placeholder="Value (auto)" value="">' +
                '<button type="button" class="wdm-option-remove"><span class="dashicons dashicons-no-alt"></span></button>' +
            '</div>';

            $list.append(rowHtml);
        },

        /**
         * Remove an option row and update field data.
         *
         * @since 1.1.0
         * @param {Event} e The click event.
         */
        removeOption: function(e) {
            var $btn  = $(e.target).closest('.wdm-option-remove');
            var $card = $btn.closest('.wdm-field-card');

            $btn.closest('.wdm-option-row').remove();

            // Rebuild field options.
            this.rebuildFieldOptions($card);
        },

        /**
         * Auto-generate value from label if the value field is empty.
         *
         * @since 1.1.0
         * @param {Event} e The input event.
         */
        updateOptionValue: function(e) {
            var $label = $(e.target);
            var $row   = $label.closest('.wdm-option-row');
            var $value = $row.find('.wdm-option-value');

            // Only auto-generate if value field is empty or was auto-generated.
            if ( ! $value.val() || $value.data('auto-generated') ) {
                var generated = $label.val().toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');
                $value.val(generated);
                $value.data('auto-generated', true);
            }
        },

        /**
         * Rebuild field.options[] from all option row inputs.
         *
         * @since 1.1.0
         * @param {Event} e The change event.
         */
        updateFieldOptions: function(e) {
            var $card = $(e.target).closest('.wdm-field-card');
            this.rebuildFieldOptions($card);
        },

        /**
         * Rebuild options array from DOM rows for a given card.
         *
         * @since 1.1.0
         * @param {jQuery} $card The field card element.
         */
        rebuildFieldOptions: function($card) {
            var fieldId = $card.data('field-id');
            var options = [];

            $card.find('.wdm-option-row').each(function() {
                var label = $(this).find('.wdm-option-label').val();
                var value = $(this).find('.wdm-option-value').val();
                options.push({ label: label, value: value });
            });

            $.each(this.fields, function(i, field) {
                if ( field.id === fieldId ) {
                    field.options = options;
                    return false;
                }
            });
        },

        // ─────────────────────────────────────────────────────────
        //  Conditional logic
        // ─────────────────────────────────────────────────────────

        /**
         * Toggle the conditions wrap visibility based on checkbox.
         *
         * @since 1.1.0
         * @param {Event} e The change event.
         */
        toggleConditions: function(e) {
            var $checkbox = $(e.target);
            var $card     = $checkbox.closest('.wdm-field-card');
            var $wrap     = $card.find('.wdm-conditions-wrap');

            if ( $checkbox.is(':checked') ) {
                $wrap.slideDown(200);
            } else {
                $wrap.slideUp(200);
                // Clear conditions when unchecked.
                var fieldId = $card.data('field-id');
                $.each(this.fields, function(i, field) {
                    if ( field.id === fieldId ) {
                        field.conditions = [];
                        return false;
                    }
                });
            }
        },

        /**
         * Add a new condition row.
         *
         * @since 1.1.0
         * @param {Event} e The click event.
         */
        addCondition: function(e) {
            var $btn     = $(e.target).closest('.wdm-add-condition');
            var $card    = $btn.closest('.wdm-field-card');
            var fieldId  = $card.data('field-id');
            var $list    = $card.find('.wdm-conditions-list');
            var options  = this.getOtherFieldOptions(fieldId);

            var rowHtml = '<div class="wdm-condition-row">' +
                '<select class="wdm-condition-field wdm-select">' + options + '</select>' +
                '<select class="wdm-condition-operator wdm-select">' +
                    '<option value="equals">Equals</option>' +
                    '<option value="not_equals">Not equals</option>' +
                    '<option value="contains">Contains</option>' +
                    '<option value="not_contains">Not contains</option>' +
                    '<option value="is_empty">Is empty</option>' +
                    '<option value="is_not_empty">Is not empty</option>' +
                '</select>' +
                '<input type="text" class="wdm-condition-value wdm-input" placeholder="Enter value..." value="">' +
                '<button type="button" class="wdm-condition-remove" title="Remove condition"><span class="dashicons dashicons-no-alt"></span></button>' +
            '</div>';

            $list.append(rowHtml);
        },

        /**
         * Remove a condition row and update field data.
         *
         * @since 1.1.0
         * @param {Event} e The click event.
         */
        removeCondition: function(e) {
            var $btn  = $(e.target).closest('.wdm-condition-remove');
            var $card = $btn.closest('.wdm-field-card');

            $btn.closest('.wdm-condition-row').remove();

            // Rebuild conditions.
            this.rebuildFieldConditions($card);
        },

        /**
         * Rebuild field.conditions[] from all condition rows.
         *
         * @since 1.1.0
         * @param {Event} e The change event.
         */
        updateFieldConditions: function(e) {
            var $card = $(e.target).closest('.wdm-field-card');
            this.rebuildFieldConditions($card);
        },

        /**
         * Rebuild conditions array from DOM rows for a given card.
         *
         * @since 1.1.0
         * @param {jQuery} $card The field card element.
         */
        rebuildFieldConditions: function($card) {
            var fieldId    = $card.data('field-id');
            var conditions = [];

            $card.find('.wdm-condition-row').each(function() {
                conditions.push({
                    field:    $(this).find('.wdm-condition-field').val(),
                    operator: $(this).find('.wdm-condition-operator').val(),
                    value:    $(this).find('.wdm-condition-value').val(),
                    action:   'show'
                });
            });

            $.each(this.fields, function(i, field) {
                if ( field.id === fieldId ) {
                    field.conditions = conditions;
                    return false;
                }
            });
        },

        /**
         * Get <option> HTML for all fields except the specified one.
         *
         * @since 1.1.0
         * @param  {string} currentFieldId The field ID to exclude.
         * @return {string}                The option elements HTML.
         */
        getOtherFieldOptions: function(currentFieldId) {
            var html = '<option value="">-- Select field --</option>';

            $.each(this.fields, function(i, field) {
                if ( field.id !== currentFieldId ) {
                    html += '<option value="' + $('<span>').text(field.name).html() + '">' + $('<span>').text(field.label).html() + '</option>';
                }
            });

            return html;
        },

        /**
         * Apply pending condition field selections after card is in DOM.
         *
         * @since 1.1.0
         * @param {string} fieldId The field ID to apply conditions for.
         */
        applyPendingConditions: function(fieldId) {
            if ( ! this._pendingConditions || ! this._pendingConditions[fieldId] ) {
                return;
            }

            var conditions = this._pendingConditions[fieldId];
            var $card = $('.wdm-field-card[data-field-id="' + fieldId + '"]');
            var $rows = $card.find('.wdm-condition-row');

            $rows.each(function(i) {
                if ( conditions[i] && conditions[i].field ) {
                    $(this).find('.wdm-condition-field').val(conditions[i].field);
                }
            });

            delete this._pendingConditions[fieldId];
        },

        // ─────────────────────────────────────────────────────────
        //  Multi-step support
        // ─────────────────────────────────────────────────────────

        /**
         * Toggle multi-step mode on or off.
         *
         * When enabled, builds visual step containers on the canvas.
         * When disabled, flattens all containers back to a flat canvas.
         *
         * @since 1.2.0
         * @param {Event} e The change event.
         */
        toggleMultistep: function(e) {
            var $checkbox = $(e.target);
            var isChecked = $checkbox.is(':checked');

            if ( isChecked ) {
                this.buildStepContainers();
            } else {
                this.flattenStepContainers();
            }
        },

        // ─────────────────────────────────────────────────────────
        //  Multi-step visual containers
        // ─────────────────────────────────────────────────────────

        /**
         * Get the colour for a step number (cycles through stepColors).
         *
         * @since  1.2.0
         * @param  {number} stepNum 1-based step number.
         * @return {string}         Hex colour string.
         */
        getStepColor: function(stepNum) {
            return this.stepColors[ (stepNum - 1) % this.stepColors.length ];
        },

        /**
         * Build step container HTML for a given step number and label.
         *
         * @since  1.2.0
         * @param  {number} stepNum Step number (1-based).
         * @param  {string} label   Step label text.
         * @return {string}         HTML string.
         */
        buildStepContainerHTML: function(stepNum, label) {
            var color = this.getStepColor(stepNum);
            var canDelete = stepNum > 1 ? '' : ' style="display:none;"';

            return '<div class="wdm-step-container" data-step="' + stepNum + '" style="border-left-color: ' + color + ';">' +
                '<div class="wdm-step-container-header">' +
                    '<span class="wdm-step-number" style="background: ' + color + ';">' + stepNum + '</span>' +
                    '<input type="text" class="wdm-step-label-input" value="' + $('<span>').text(label).html() + '" placeholder="Step name...">' +
                    '<div class="wdm-step-container-actions">' +
                        '<button type="button" class="wdm-step-collapse" title="Collapse"><span class="dashicons dashicons-arrow-up-alt2"></span></button>' +
                        '<button type="button" class="wdm-step-delete" title="Remove step"' + canDelete + '><span class="dashicons dashicons-no-alt"></span></button>' +
                    '</div>' +
                '</div>' +
                '<div class="wdm-step-container-body">' +
                    '<div class="wdm-step-container-empty">Drag fields here</div>' +
                '</div>' +
            '</div>';
        },

        /**
         * Build visual step containers and distribute existing field cards.
         *
         * Groups fields by their step property and creates accordion-style
         * step panels. Makes each step body sortable and connected.
         *
         * @since 1.2.0
         */
        buildStepContainers: function() {
            var self    = this;
            var $canvas = $('#wdm-builder-canvas');

            // Determine existing steps from field data.
            var maxStep = 1;
            $.each(this.fields, function(i, field) {
                var s = parseInt(field.step, 10) || 1;
                if ( s > maxStep ) { maxStep = s; }
            });

            // Ensure at least 2 steps when enabling multi-step.
            if ( maxStep < 2 ) { maxStep = 2; }

            // Read saved step labels from form settings if available.
            var savedLabels = [];
            try {
                var settingsJson = $('#wdm-form-settings-json').val();
                if ( settingsJson ) {
                    var parsed = JSON.parse(settingsJson);
                    if ( parsed.multistep_labels ) { savedLabels = parsed.multistep_labels; }
                }
            } catch(ex) {}

            // Also try to read from existing PHP-rendered settings data.
            if ( ! savedLabels.length && typeof wprobo_documerge_form_settings !== 'undefined' && wprobo_documerge_form_settings && wprobo_documerge_form_settings.multistep_labels ) {
                savedLabels = wprobo_documerge_form_settings.multistep_labels;
            }

            // Detach all existing field cards from the canvas.
            var $fieldCards = $canvas.find('.wdm-field-card').detach();

            // Remove any existing step containers and add-step button.
            $canvas.find('.wdm-step-container, #wdm-add-step-btn').remove();

            // Hide flat-canvas placeholder.
            $('#wdm-canvas-placeholder').hide();

            // Create step containers.
            this.stepCounter = 0;
            for ( var s = 1; s <= maxStep; s++ ) {
                var label = savedLabels[s - 1] || 'Step ' + s;
                $canvas.append( this.buildStepContainerHTML(s, label) );
                this.stepCounter = s;
            }

            // Add the "Add Step" button.
            $canvas.append('<button type="button" class="wdm-add-step-btn" id="wdm-add-step-btn"><span class="dashicons dashicons-plus-alt2"></span> Add Step</button>');

            // Distribute field cards into their step containers.
            $fieldCards.each(function() {
                var fieldId = $(this).data('field-id');
                var stepNum = 1;

                $.each(self.fields, function(i, field) {
                    if ( field.id === fieldId ) {
                        stepNum = parseInt(field.step, 10) || 1;
                        return false;
                    }
                });

                // Clamp to available steps.
                if ( stepNum > self.stepCounter ) { stepNum = self.stepCounter; }

                var $container = $canvas.find('.wdm-step-container[data-step="' + stepNum + '"]');
                $container.find('.wdm-step-container-body').append($(this));
            });

            // Update empty state visibility for each container.
            this.updateStepEmptyStates();

            // Initialize connected sortables on step bodies.
            this.initStepSortables();
        },

        /**
         * Initialize jQuery UI Sortable on all step container bodies.
         *
         * Each body is sortable and connected to all other step bodies,
         * allowing drag between steps.
         *
         * @since 1.2.0
         */
        initStepSortables: function() {
            var self = this;

            // Destroy existing sortable and droppable on flat canvas if active.
            if ( $('#wdm-builder-canvas').hasClass('ui-sortable') ) {
                $('#wdm-builder-canvas').sortable('destroy');
            }
            if ( $('#wdm-builder-canvas').hasClass('ui-droppable') ) {
                $('#wdm-builder-canvas').droppable('destroy');
            }

            var $bodies = $('.wdm-step-container-body');

            $bodies.sortable({
                handle:      '.wdm-field-drag-handle',
                placeholder: 'wdm-field-placeholder',
                items:       '.wdm-field-card',
                connectWith: '.wdm-step-container-body',
                distance:    3,
                tolerance:   'pointer',
                cursor:      'grabbing',
                revert:      false,
                scroll:      true,
                scrollSensitivity: 60,
                helper: function(e, el) {
                    return el.clone().css({ width: el.outerWidth(), opacity: 0.9 });
                },
                start: function(e, ui) {
                    ui.item.css('visibility', 'hidden');
                },
                stop: function(e, ui) {
                    ui.item.css('visibility', '');
                },
                update: function(event, ui) {
                    if ( this === ui.item.parent()[0] ) {
                        self.syncFieldStepsFromDOM();
                        self.updateFieldOrder();
                        self.updateStepEmptyStates();
                    }
                },
                receive: function() {
                    self.updateStepEmptyStates();
                },
                remove: function() {
                    self.updateStepEmptyStates();
                }
            });

            // Make canvas also droppable for sidebar drag-drops.
            $bodies.droppable({
                accept:     '.wdm-field-type-btn',
                hoverClass: 'wdm-step-drop-hover',
                tolerance:  'pointer',
                drop: function(e, ui) {
                    var $btn  = ui.draggable;
                    var $body = $(this);
                    var stepNum = parseInt( $body.closest('.wdm-step-container').data('step'), 10 );

                    $('.wdm-drop-indicator').remove();
                    self.addFieldToStep(e, $btn, $body, stepNum);
                    $body.removeClass('wdm-step-drop-hover');
                }
            });
        },

        /**
         * Add a new field to a specific step container body.
         *
         * @since  1.2.0
         * @param  {Event}  e       The drop event.
         * @param  {jQuery} $btn    The dragged field type button.
         * @param  {jQuery} $body   The step container body element.
         * @param  {number} stepNum The step number.
         */
        addFieldToStep: function(e, $btn, $body, stepNum) {
            var type = $btn.data('type');
            if ( $btn.prop('disabled') ) { return; }

            if ( this.isSingletonField(type) && this.hasFieldType(type) ) {
                this.showNotice('error', this.typeLabels[type] + ' field can only be added once per form.');
                return;
            }

            this.fieldCounter++;
            var fieldId  = 'field_' + this.fieldCounter;
            var label    = this.typeLabels[type] || type;
            var mergeTag = label.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/g, '');

            var field = {
                id: fieldId, type: type, label: label, name: mergeTag,
                placeholder: '', help_text: '', required: false, width: 'full',
                options: [], conditions: [], step: stepNum, error_message: '',
                min_length: '', max_length: '', min_value: '', max_value: '',
                step_value: '', date_format: 'Y-m-d',
                disable_past: false, max_future_months: '', searchable: false,
                css_class: '', css_id: '',
                label_position: 'top',
                show_country_code: true, default_country: 'GB',
                allowed_types: 'jpg,jpeg,png,gif,pdf,doc,docx',
                max_file_size: 5, multiple: false,
                show_line2: true, show_country: true,
                name_format: 'first_last',
                default_value: '', dynamic_value: 'none', custom_value: '',
                html_content: '<p>Add your content here.</p>',
                divider_style: 'line',
                show_title: true,
                title_alignment: 'left',
                confirm_password: false, show_strength: true,
                max_stars: 5,
                columns: [{ label: 'Item', name: 'item' }, { label: 'Quantity', name: 'qty' }],
                min_rows: 1,
                max_rows: 10,
                track_utms: true,
                track_referrer: true,
                track_landing_page: true
            };

            if ( type === 'dropdown' || type === 'radio' || type === 'checkbox' ) {
                field.options = [
                    { label: 'Option 1', value: 'option_1' },
                    { label: 'Option 2', value: 'option_2' },
                    { label: 'Option 3', value: 'option_3' }
                ];
            }

            var cardHtml = this.generateFieldCard(field);
            var $cards   = $body.find('> .wdm-field-card');
            var dropIdx  = this._dropIndex;

            // Insert at the correct position using _dropIndex.
            if ( typeof dropIdx === 'number' && dropIdx >= 0 && dropIdx < $cards.length ) {
                $(cardHtml).insertBefore($cards.eq(dropIdx));
            } else {
                $body.append(cardHtml);
            }

            this.fields.push(field);
            this.applyPendingConditions(field.id);
            this.syncFieldStepsFromDOM();
            this.updateFieldOrder();
            this.updateSingletonButtons();
            this.updateStepEmptyStates();

            // Refresh sortable.
            $body.sortable('refresh');
        },

        /**
         * Flatten step containers back to a flat canvas.
         *
         * Called when multi-step is disabled. All field cards move
         * back into the flat canvas. All fields reset to step 1.
         *
         * @since 1.2.0
         */
        flattenStepContainers: function() {
            var $canvas = $('#wdm-builder-canvas');

            // Destroy sortable on step bodies.
            $('.wdm-step-container-body').each(function() {
                if ( $(this).hasClass('ui-sortable') ) {
                    $(this).sortable('destroy');
                }
                if ( $(this).hasClass('ui-droppable') ) {
                    $(this).droppable('destroy');
                }
            });

            // Detach all field cards in DOM order.
            var $allCards = $canvas.find('.wdm-field-card').detach();

            // Remove step containers and add-step button.
            $canvas.find('.wdm-step-container, #wdm-add-step-btn').remove();

            // Re-append field cards to flat canvas.
            if ( $allCards.length ) {
                $canvas.append($allCards);
                $('#wdm-canvas-placeholder').hide();
            } else {
                $('#wdm-canvas-placeholder').show();
            }

            // Reset all fields to step 1.
            $.each(this.fields, function(i, field) {
                field.step = 1;
            });

            // Re-initialize flat canvas sortable.
            this.initSortable();
        },

        /**
         * Add a new step container at the end.
         *
         * @since 1.2.0
         */
        addStepContainer: function() {
            this.stepCounter++;
            var stepNum = this.stepCounter;
            var label   = 'Step ' + stepNum;
            var html    = this.buildStepContainerHTML(stepNum, label);

            $(html).insertBefore('#wdm-add-step-btn');

            // Initialize sortable on the new step body.
            var self    = this;
            var $newBody = $('.wdm-step-container[data-step="' + stepNum + '"] .wdm-step-container-body');

            $newBody.sortable({
                handle:      '.wdm-field-drag-handle',
                placeholder: 'wdm-field-placeholder',
                items:       '.wdm-field-card',
                connectWith: '.wdm-step-container-body',
                distance:    3,
                tolerance:   'pointer',
                cursor:      'grabbing',
                revert:      false,
                scroll:      true,
                scrollSensitivity: 60,
                helper: function(e, el) {
                    return el.clone().css({ width: el.outerWidth(), opacity: 0.9 });
                },
                start: function(e, ui) {
                    ui.item.css('visibility', 'hidden');
                },
                stop: function(e, ui) {
                    ui.item.css('visibility', '');
                },
                update: function(event, ui) {
                    if ( this === ui.item.parent()[0] ) {
                        self.syncFieldStepsFromDOM();
                        self.updateFieldOrder();
                        self.updateStepEmptyStates();
                    }
                },
                receive: function() { self.updateStepEmptyStates(); },
                remove: function() { self.updateStepEmptyStates(); }
            });

            $newBody.droppable({
                accept:     '.wdm-field-type-btn',
                hoverClass: 'wdm-step-drop-hover',
                tolerance:  'pointer',
                drop: function(e, ui) {
                    var $btn = ui.draggable;
                    $('.wdm-drop-indicator').remove();
                    self.addFieldToStep(e, $btn, $(this), stepNum);
                }
            });

            // Refresh existing sortables to include new connectWith target.
            $('.wdm-step-container-body').sortable('option', 'connectWith', '.wdm-step-container-body');

            // Scroll to the new step.
            $('html, body').animate({
                scrollTop: $newBody.closest('.wdm-step-container').offset().top - 80
            }, 300);
        },

        /**
         * Remove a step container and move its fields to the previous step.
         *
         * @since  1.2.0
         * @param  {jQuery} $btn The clicked delete button.
         */
        removeStepContainer: function($btn) {
            var $container = $btn.closest('.wdm-step-container');
            var stepNum    = parseInt( $container.data('step'), 10 );
            var $containers = $('.wdm-step-container');

            // Cannot remove the only step.
            if ( $containers.length <= 1 ) {
                this.showNotice('error', 'You must have at least one step.');
                return;
            }

            if ( ! window.confirm('Remove this step? Its fields will move to the previous step.') ) {
                return;
            }

            // Find the previous step container (or next if this is step 1).
            var $target;
            var $prev = $container.prev('.wdm-step-container');
            if ( $prev.length ) {
                $target = $prev;
            } else {
                $target = $container.next('.wdm-step-container');
            }

            // Move field cards to target container.
            var $cards = $container.find('.wdm-field-card');
            if ( $cards.length ) {
                $target.find('.wdm-step-container-body').append($cards);
            }

            // Destroy sortable/droppable on the removed container body.
            var $body = $container.find('.wdm-step-container-body');
            if ( $body.hasClass('ui-sortable') ) { $body.sortable('destroy'); }
            if ( $body.hasClass('ui-droppable') ) { $body.droppable('destroy'); }

            $container.fadeOut(200, function() {
                $(this).remove();
            });

            // Re-number remaining steps after a short delay for the fadeOut.
            var self = this;
            setTimeout(function() {
                self.renumberStepContainers();
                self.syncFieldStepsFromDOM();
                self.updateStepEmptyStates();
            }, 250);
        },

        /**
         * Toggle collapse/expand of a step container body.
         *
         * @since  1.2.0
         * @param  {jQuery} $btn The collapse button.
         */
        toggleStepCollapse: function($btn) {
            var $container = $btn.closest('.wdm-step-container');
            var $body      = $container.find('.wdm-step-container-body');
            var $icon      = $btn.find('.dashicons');

            $body.slideToggle(200);
            $container.toggleClass('wdm-step-collapsed');

            if ( $container.hasClass('wdm-step-collapsed') ) {
                $icon.removeClass('dashicons-arrow-up-alt2').addClass('dashicons-arrow-down-alt2');
            } else {
                $icon.removeClass('dashicons-arrow-down-alt2').addClass('dashicons-arrow-up-alt2');
            }
        },

        /**
         * Re-number all step containers after one is removed.
         *
         * Updates data-step, badge numbers, colours, labels, and
         * the delete button visibility (step 1 cannot be deleted).
         *
         * @since 1.2.0
         */
        renumberStepContainers: function() {
            var self = this;
            this.stepCounter = 0;

            $('.wdm-step-container').each(function(i) {
                var newNum = i + 1;
                var color  = self.getStepColor(newNum);

                $(this).attr('data-step', newNum).css('border-left-color', color);
                $(this).find('.wdm-step-number').text(newNum).css('background', color);

                // Update default label if it matches the old pattern "Step N".
                var $label = $(this).find('.wdm-step-label-input');
                var currentLabel = $.trim( $label.val() );
                if ( /^Step \d+$/.test(currentLabel) ) {
                    $label.val('Step ' + newNum);
                }

                // Show/hide delete button (cannot delete step 1 if only 1 step).
                var $delBtn = $(this).find('.wdm-step-delete');
                if ( newNum === 1 && $('.wdm-step-container').length <= 1 ) {
                    $delBtn.hide();
                } else {
                    $delBtn.show();
                }

                self.stepCounter = newNum;
            });
        },

        /**
         * Sync field step assignments from DOM position.
         *
         * Reads which step container each field card currently resides in
         * and updates the corresponding field object's step property.
         *
         * @since 1.2.0
         */
        syncFieldStepsFromDOM: function() {
            var self = this;

            $('.wdm-step-container').each(function() {
                var stepNum = parseInt( $(this).data('step'), 10 ) || 1;

                $(this).find('.wdm-field-card').each(function() {
                    var fieldId = $(this).data('field-id');

                    $.each(self.fields, function(i, field) {
                        if ( field.id === fieldId ) {
                            field.step = stepNum;
                            return false;
                        }
                    });
                });
            });
        },

        /**
         * Show or hide the empty-state placeholder in each step container.
         *
         * @since 1.2.0
         */
        updateStepEmptyStates: function() {
            $('.wdm-step-container-body').each(function() {
                var $body  = $(this);
                var $empty = $body.find('.wdm-step-container-empty');

                if ( $body.find('.wdm-field-card').length ) {
                    $empty.hide();
                } else {
                    $empty.show();
                }
            });
        }
    };

    $(document).ready(function() {
        WPRoboDocuMerge_FormBuilder.init();
    });

})(jQuery);
