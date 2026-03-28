<?php
/**
 * Asset management class.
 *
 * Handles enqueuing of all admin and frontend CSS/JS assets.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Core
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.0.0
 */

namespace WPRobo\DocuMerge\Core;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPRobo_DocuMerge_Assets
 *
 * Registers and enqueues all plugin scripts and styles
 * for both admin and frontend contexts.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Assets {

    /**
     * Singleton instance.
     *
     * @since 1.0.0
     * @var   WPRobo_DocuMerge_Assets|null
     */
    private static $wprobo_documerge_instance = null;

    /**
     * Get singleton instance.
     *
     * @since  1.0.0
     * @return WPRobo_DocuMerge_Assets
     */
    public static function get_instance() {
        if ( null === self::$wprobo_documerge_instance ) {
            self::$wprobo_documerge_instance = new self();
        }
        return self::$wprobo_documerge_instance;
    }

    /**
     * Constructor — private for singleton.
     *
     * @since 1.0.0
     */
    private function __construct() {
        // Intentionally empty — hooks registered via wprobo_documerge_init_hooks().
    }

    /**
     * Register WordPress hooks for asset enqueuing.
     *
     * @since 1.0.0
     * @return void
     */
    public function wprobo_documerge_init_hooks() {
        add_action( 'admin_enqueue_scripts', array( $this, 'wprobo_documerge_enqueue_admin_assets' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'wprobo_documerge_enqueue_frontend_assets' ) );
    }

    /**
     * Enqueue admin scripts and styles.
     *
     * Only loads assets on plugin admin pages to avoid
     * unnecessary overhead on other WordPress screens.
     *
     * @since 1.0.0
     * @param string $hook The current admin page hook suffix.
     * @return void
     */
    public function wprobo_documerge_enqueue_admin_assets( $hook ) {

        // Only load on plugin pages.
        if ( strpos( $hook, 'wprobo-documerge' ) === false ) {
            return;
        }

        // CSS.
        wp_enqueue_style(
            'wprobo-documerge-admin',
            WPROBO_DOCUMERGE_URL . 'assets/css/admin/main.min.css',
            array(),
            WPROBO_DOCUMERGE_VERSION
        );

        // JS — always in footer.
        wp_enqueue_script(
            'wprobo-documerge-admin',
            WPROBO_DOCUMERGE_URL . 'assets/js/admin/main.min.js',
            array( 'jquery' ),
            WPROBO_DOCUMERGE_VERSION,
            true
        );

        // Localize immediately after enqueue.
        wp_localize_script(
            'wprobo-documerge-admin',
            'wprobo_documerge_vars',
            array(
                'ajax_url'       => admin_url( 'admin-ajax.php' ),
                'nonce'          => wp_create_nonce( 'wprobo_documerge_admin' ),
                'settings_nonce' => wp_create_nonce( 'wprobo_documerge_settings' ),
                'plugin_url'     => WPROBO_DOCUMERGE_URL,
                'admin_url'      => admin_url( 'admin.php?page=wprobo-documerge-submissions' ),
                'i18n'           => array(
                    'error'          => __( 'An error occurred. Please try again.', 'wprobo-documerge' ),
                    'network_error'  => __( 'Network error. Please check your connection.', 'wprobo-documerge' ),
                    'confirm_delete' => __( 'Are you sure you want to delete this?', 'wprobo-documerge' ),
                    'saving'         => __( 'Saving...', 'wprobo-documerge' ),
                    'saved'          => __( 'Saved!', 'wprobo-documerge' ),
                    'copied'         => __( 'Copied to clipboard!', 'wprobo-documerge' ),
                    'no_submissions' => __( 'No submissions found.', 'wprobo-documerge' ),
                    'confirm_bulk'   => __( 'Delete selected submissions? This cannot be undone.', 'wprobo-documerge' ),
                    'confirm_rerun_wizard' => __( 'Re-run the setup wizard? You will be redirected to the wizard screen.', 'wprobo-documerge' ),
                    'export_none'          => __( 'Please select at least one data type to export.', 'wprobo-documerge' ),
                    'export_success'       => __( 'Export downloaded successfully.', 'wprobo-documerge' ),
                    'import_invalid_type'  => __( 'Please select a valid JSON file.', 'wprobo-documerge' ),
                    'import_too_large'     => __( 'File is too large. Maximum size is 50 MB.', 'wprobo-documerge' ),
                    'import_invalid_json'  => __( 'Could not parse JSON file.', 'wprobo-documerge' ),
                    'import_wrong_plugin'  => __( 'This file is not a valid DocuMerge export.', 'wprobo-documerge' ),
                    'import_no_data'       => __( 'Export file contains no data.', 'wprobo-documerge' ),
                    'import_none'          => __( 'Please select at least one data type to import.', 'wprobo-documerge' ),
                    'import_replace_confirm' => __( 'Replace mode will DELETE all existing data for the selected types before importing. Continue?', 'wprobo-documerge' ),
                    'import_version'       => __( 'Version', 'wprobo-documerge' ),
                    'import_date'          => __( 'Exported', 'wprobo-documerge' ),
                    'import_site'          => __( 'Site', 'wprobo-documerge' ),
                    'templates'            => __( 'Templates', 'wprobo-documerge' ),
                    'forms'                => __( 'Forms', 'wprobo-documerge' ),
                    'submissions'          => __( 'Submissions', 'wprobo-documerge' ),
                    'settings_label'       => __( 'Settings', 'wprobo-documerge' ),
                    'analytics'            => __( 'Analytics', 'wprobo-documerge' ),
                    'imported'             => __( 'imported', 'wprobo-documerge' ),
                    'settings_saved'       => __( 'Settings saved.', 'wprobo-documerge' ),
                ),
            )
        );

        // Chart.js — only on dashboard page.
        if ( 'toplevel_page_wprobo-documerge' === $hook ) {
            wp_enqueue_script(
                'wprobo-documerge-chartjs',
                'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
                array(),
                '4.4.1',
                true
            );
        }

        // Settings JS + Select2 + CodeMirror — only on settings page.
        if ( strpos( $hook, 'wprobo-documerge-settings' ) !== false ) {
            wp_enqueue_script(
                'wprobo-documerge-settings',
                WPROBO_DOCUMERGE_URL . 'assets/js/admin/settings.min.js',
                array( 'jquery', 'wprobo-documerge-admin' ),
                WPROBO_DOCUMERGE_VERSION,
                true
            );
            // Select2 for searchable font dropdown in Styles tab.
            wp_enqueue_style(
                'wprobo-documerge-select2',
                WPROBO_DOCUMERGE_URL . 'assets/vendor/select2/select2.min.css',
                array(),
                '4.1.0'
            );
            wp_enqueue_script(
                'wprobo-documerge-select2',
                WPROBO_DOCUMERGE_URL . 'assets/vendor/select2/select2.min.js',
                array( 'jquery' ),
                '4.1.0',
                true
            );
            wp_enqueue_style(
                'wprobo-documerge-codemirror',
                'https://cdn.jsdelivr.net/npm/codemirror@5.65.16/lib/codemirror.min.css',
                array(),
                '5.65.16'
            );
            wp_enqueue_style(
                'wprobo-documerge-codemirror-theme',
                'https://cdn.jsdelivr.net/npm/codemirror@5.65.16/theme/material-darker.min.css',
                array( 'wprobo-documerge-codemirror' ),
                '5.65.16'
            );
            wp_enqueue_script(
                'wprobo-documerge-codemirror',
                'https://cdn.jsdelivr.net/npm/codemirror@5.65.16/lib/codemirror.min.js',
                array(),
                '5.65.16',
                true
            );
            wp_enqueue_script(
                'wprobo-documerge-codemirror-css',
                'https://cdn.jsdelivr.net/npm/codemirror@5.65.16/mode/css/css.min.js',
                array( 'wprobo-documerge-codemirror' ),
                '5.65.16',
                true
            );
            wp_enqueue_script(
                'wprobo-documerge-codemirror-hint',
                'https://cdn.jsdelivr.net/npm/codemirror@5.65.16/addon/hint/show-hint.min.js',
                array( 'wprobo-documerge-codemirror' ),
                '5.65.16',
                true
            );
            wp_enqueue_style(
                'wprobo-documerge-codemirror-hint-css',
                'https://cdn.jsdelivr.net/npm/codemirror@5.65.16/addon/hint/show-hint.min.css',
                array( 'wprobo-documerge-codemirror' ),
                '5.65.16'
            );
            wp_enqueue_script(
                'wprobo-documerge-codemirror-csshint',
                'https://cdn.jsdelivr.net/npm/codemirror@5.65.16/addon/hint/css-hint.min.js',
                array( 'wprobo-documerge-codemirror', 'wprobo-documerge-codemirror-hint' ),
                '5.65.16',
                true
            );
        }

        // Template Manager JS — only on templates page.
        if ( strpos( $hook, 'wprobo-documerge-templates' ) !== false ) {
            wp_enqueue_script(
                'wprobo-documerge-template-manager',
                WPROBO_DOCUMERGE_URL . 'assets/js/admin/template-manager.min.js',
                array( 'jquery', 'wprobo-documerge-admin' ),
                WPROBO_DOCUMERGE_VERSION,
                true
            );
        }

        // Form Builder JS — only on forms page.
        if ( strpos( $hook, 'wprobo-documerge-forms' ) !== false ) {
            wp_enqueue_script( 'jquery-ui-sortable' );
            wp_enqueue_script( 'jquery-ui-draggable' );
            wp_enqueue_script( 'jquery-ui-droppable' );
            wp_enqueue_script(
                'wprobo-documerge-form-builder',
                WPROBO_DOCUMERGE_URL . 'assets/js/admin/form-builder.min.js',
                array( 'jquery', 'jquery-ui-sortable', 'jquery-ui-draggable', 'jquery-ui-droppable', 'wprobo-documerge-admin' ),
                WPROBO_DOCUMERGE_VERSION,
                true
            );
        }

        // Submissions JS — only on submissions page.
        if ( strpos( $hook, 'wprobo-documerge-submissions' ) !== false ) {
            wp_enqueue_script(
                'wprobo-documerge-submissions',
                WPROBO_DOCUMERGE_URL . 'assets/js/admin/submissions.min.js',
                array( 'jquery', 'wprobo-documerge-admin' ),
                WPROBO_DOCUMERGE_VERSION,
                true
            );
        }
    }

    /**
     * Enqueue frontend scripts and styles.
     *
     * Only loads assets on pages that contain the DocuMerge
     * shortcode or block to avoid unnecessary overhead.
     *
     * @since 1.0.0
     * @return void
     */
    public function wprobo_documerge_enqueue_frontend_assets() {

        global $post;

        // Bail if no post object available.
        if ( ! $post instanceof \WP_Post ) {
            return;
        }

        // Check for shortcode or block presence.
        $wprobo_documerge_has_shortcode = has_shortcode( $post->post_content, 'documerge_form' );
        $wprobo_documerge_has_block     = has_block( 'wprobo-documerge/form-embed', $post );

        if ( ! $wprobo_documerge_has_shortcode && ! $wprobo_documerge_has_block ) {
            return;
        }

        // Dashicons for frontend icons.
        wp_enqueue_style( 'dashicons' );

        // Frontend CSS.
        wp_enqueue_style(
            'wprobo-documerge-frontend',
            WPROBO_DOCUMERGE_URL . 'assets/css/frontend/form.min.css',
            array( 'dashicons' ),
            WPROBO_DOCUMERGE_VERSION
        );

        // Frontend JS — form renderer + validator.
        wp_enqueue_script(
            'wprobo-documerge-frontend',
            WPROBO_DOCUMERGE_URL . 'assets/js/frontend/form-renderer.min.js',
            array( 'jquery' ),
            WPROBO_DOCUMERGE_VERSION,
            true
        );

        // Auto-save draft handler.
        wp_enqueue_script(
            'wprobo-documerge-autosave',
            WPROBO_DOCUMERGE_URL . 'assets/js/frontend/autosave-handler.min.js',
            array( 'jquery', 'wprobo-documerge-frontend' ),
            WPROBO_DOCUMERGE_VERSION,
            true
        );

        // Analytics tracker — fires on every page with a form.
        wp_enqueue_script(
            'wprobo-documerge-analytics',
            WPROBO_DOCUMERGE_URL . 'assets/js/frontend/analytics-handler.min.js',
            array( 'jquery' ),
            WPROBO_DOCUMERGE_VERSION,
            true
        );

        // Detect field types by loading actual form data from the DB.
        // The post content only has [documerge_form id="X"] — field info is in wprdm_forms.
        $wprobo_documerge_has_signature  = false;
        $wprobo_documerge_has_captcha    = false;
        $wprobo_documerge_has_payment    = false;
        $wprobo_documerge_has_phone      = false;
        $wprobo_documerge_has_date       = false;
        $wprobo_documerge_has_searchable = false;
        $wprobo_documerge_has_rating     = false;
        $wprobo_documerge_has_repeater   = false;
        $wprobo_documerge_has_password   = false;
        $wprobo_documerge_has_tracking  = false;
        $wprobo_documerge_captcha_type  = get_option( 'wprobo_documerge_captcha_type', 'none' );

        // Extract form IDs from shortcodes and blocks in the post content.
        $wprobo_documerge_form_ids = array();
        if ( preg_match_all( '/\[documerge_form\s+id=["\']?(\d+)["\']?\s*\]/', $post->post_content, $matches ) ) {
            $wprobo_documerge_form_ids = array_map( 'absint', $matches[1] );
        }
        // Also check for Gutenberg block formId attribute.
        if ( preg_match_all( '/"formId"\s*:\s*(\d+)/', $post->post_content, $matches ) ) {
            $wprobo_documerge_form_ids = array_merge( $wprobo_documerge_form_ids, array_map( 'absint', $matches[1] ) );
        }
        $wprobo_documerge_form_ids = array_unique( array_filter( $wprobo_documerge_form_ids ) );

        // Check each form's fields for special field types.
        if ( ! empty( $wprobo_documerge_form_ids ) ) {
            $form_builder = new \WPRobo\DocuMerge\Form\WPRobo_DocuMerge_Form_Builder();
            foreach ( $wprobo_documerge_form_ids as $fid ) {
                $form_obj = $form_builder->wprobo_documerge_get_form( $fid );
                if ( ! $form_obj ) {
                    continue;
                }
                $fields_json = isset( $form_obj->fields ) ? $form_obj->fields : '';
                if ( strpos( $fields_json, '"signature"' ) !== false ) {
                    $wprobo_documerge_has_signature = true;
                }
                if ( strpos( $fields_json, '"captcha"' ) !== false ) {
                    $wprobo_documerge_has_captcha = true;
                }
                if ( strpos( $fields_json, '"payment"' ) !== false || ! empty( $form_obj->payment_enabled ) ) {
                    $wprobo_documerge_has_payment = true;
                }
                if ( strpos( $fields_json, '"phone"' ) !== false ) {
                    $wprobo_documerge_has_phone = true;
                }
                if ( strpos( $fields_json, '"date"' ) !== false ) {
                    $wprobo_documerge_has_date = true;
                }
                if ( strpos( $fields_json, '"searchable":true' ) !== false || strpos( $fields_json, '"searchable":"1"' ) !== false ) {
                    $wprobo_documerge_has_searchable = true;
                }
                if ( strpos( $fields_json, '"rating"' ) !== false ) {
                    $wprobo_documerge_has_rating = true;
                }
                if ( strpos( $fields_json, '"repeater"' ) !== false ) {
                    $wprobo_documerge_has_repeater = true;
                }
                if ( strpos( $fields_json, '"password"' ) !== false ) {
                    $wprobo_documerge_has_password = true;
                }
                if ( strpos( $fields_json, '"tracking"' ) !== false ) {
                    $wprobo_documerge_has_tracking = true;
                }
            }
        }

        // intl-tel-input — only on pages with phone field.
        if ( $wprobo_documerge_has_phone ) {
            wp_enqueue_style(
                'wprobo-documerge-intl-tel',
                WPROBO_DOCUMERGE_URL . 'assets/vendor/intl-tel-input/intlTelInput.min.css',
                array(),
                '24.6.0'
            );
            wp_enqueue_script(
                'wprobo-documerge-intl-tel',
                WPROBO_DOCUMERGE_URL . 'assets/vendor/intl-tel-input/intlTelInput.min.js',
                array(),
                '24.6.0',
                true
            );
            wp_enqueue_script(
                'wprobo-documerge-phone-handler',
                WPROBO_DOCUMERGE_URL . 'assets/js/frontend/phone-handler.min.js',
                array( 'jquery', 'wprobo-documerge-intl-tel' ),
                WPROBO_DOCUMERGE_VERSION,
                true
            );
        }

        // Flatpickr datepicker — only on pages with date field.
        if ( $wprobo_documerge_has_date ) {
            wp_enqueue_style(
                'wprobo-documerge-flatpickr',
                WPROBO_DOCUMERGE_URL . 'assets/vendor/flatpickr/flatpickr.min.css',
                array(),
                '4.6.13'
            );
            wp_enqueue_script(
                'wprobo-documerge-flatpickr',
                WPROBO_DOCUMERGE_URL . 'assets/vendor/flatpickr/flatpickr.min.js',
                array(),
                '4.6.13',
                true
            );
            wp_enqueue_script(
                'wprobo-documerge-datepicker-handler',
                WPROBO_DOCUMERGE_URL . 'assets/js/frontend/datepicker-handler.min.js',
                array( 'jquery', 'wprobo-documerge-flatpickr' ),
                WPROBO_DOCUMERGE_VERSION,
                true
            );
        }

        // Select2 — only on pages with searchable dropdown field.
        if ( $wprobo_documerge_has_searchable ) {
            wp_enqueue_style(
                'wprobo-documerge-select2',
                WPROBO_DOCUMERGE_URL . 'assets/vendor/select2/select2.min.css',
                array(),
                '4.1.0'
            );
            wp_enqueue_script(
                'wprobo-documerge-select2',
                WPROBO_DOCUMERGE_URL . 'assets/vendor/select2/select2.min.js',
                array( 'jquery' ),
                '4.1.0',
                true
            );
            // Init Select2 on searchable dropdowns.
            wp_add_inline_script(
                'wprobo-documerge-select2',
                'jQuery(function($){ $(".wdm-select2").select2({ width: "100%", placeholder: $(this).data("placeholder") || "", allowClear: true }); });'
            );
        }

        // Signature Pad — only on pages with signature field.
        if ( $wprobo_documerge_has_signature ) {
            wp_enqueue_script(
                'wprobo-documerge-signature-pad',
                WPROBO_DOCUMERGE_URL . 'assets/vendor/signature-pad/signature-pad.min.js',
                array(),
                '4.1.7',
                true
            );
            wp_enqueue_script(
                'wprobo-documerge-signature-handler',
                WPROBO_DOCUMERGE_URL . 'assets/js/frontend/signature-handler.min.js',
                array( 'jquery', 'wprobo-documerge-signature-pad' ),
                WPROBO_DOCUMERGE_VERSION,
                true
            );
        }

        // Rating stars — only on pages with rating field.
        if ( $wprobo_documerge_has_rating ) {
            wp_enqueue_script(
                'wprobo-documerge-rating-handler',
                WPROBO_DOCUMERGE_URL . 'assets/js/frontend/rating-handler.min.js',
                array( 'jquery' ),
                WPROBO_DOCUMERGE_VERSION,
                true
            );
        }

        // Repeater — only on pages with repeater field.
        if ( $wprobo_documerge_has_repeater ) {
            wp_enqueue_script(
                'wprobo-documerge-repeater-handler',
                WPROBO_DOCUMERGE_URL . 'assets/js/frontend/repeater-handler.min.js',
                array( 'jquery' ),
                WPROBO_DOCUMERGE_VERSION,
                true
            );
        }

        // Password toggle — only on pages with password field.
        if ( $wprobo_documerge_has_password ) {
            wp_enqueue_script(
                'wprobo-documerge-password-handler',
                WPROBO_DOCUMERGE_URL . 'assets/js/frontend/password-handler.min.js',
                array( 'jquery' ),
                WPROBO_DOCUMERGE_VERSION,
                true
            );
        }

        // Tracking handler — only on pages with tracking field.
        if ( $wprobo_documerge_has_tracking ) {
            wp_enqueue_script(
                'wprobo-documerge-tracking',
                WPROBO_DOCUMERGE_URL . 'assets/js/frontend/tracking-handler.min.js',
                array( 'jquery' ),
                WPROBO_DOCUMERGE_VERSION,
                true
            );
        }

        // Captcha scripts — only on pages with captcha field.
        if ( $wprobo_documerge_has_captcha && 'none' !== $wprobo_documerge_captcha_type ) {
            if ( 'recaptcha_v2' === $wprobo_documerge_captcha_type ) {
                wp_enqueue_script( 'wprobo-documerge-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
            } elseif ( 'recaptcha_v3' === $wprobo_documerge_captcha_type ) {
                $wprobo_documerge_v3_key = get_option( 'wprobo_documerge_recaptcha_v3_site_key', '' );
                if ( ! empty( $wprobo_documerge_v3_key ) ) {
                    wp_enqueue_script( 'wprobo-documerge-recaptcha', 'https://www.google.com/recaptcha/api.js?render=' . esc_attr( $wprobo_documerge_v3_key ), array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
                }
            } elseif ( 'hcaptcha' === $wprobo_documerge_captcha_type ) {
                wp_enqueue_script( 'wprobo-documerge-hcaptcha', 'https://js.hcaptcha.com/1/api.js', array(), null, true ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
            }

            wp_enqueue_script(
                'wprobo-documerge-captcha-handler',
                WPROBO_DOCUMERGE_URL . 'assets/js/frontend/captcha-handler.min.js',
                array( 'jquery' ),
                WPROBO_DOCUMERGE_VERSION,
                true
            );
        }

        // Stripe.js — only on pages with payment-enabled forms.
        $wprobo_documerge_stripe_pub = '';

        if ( $wprobo_documerge_has_payment ) {
            $stripe_handler = \WPRobo\DocuMerge\Payment\WPRobo_DocuMerge_Stripe_Handler::get_instance();
            if ( $stripe_handler->wprobo_documerge_is_configured() ) {
                $pub_key = $stripe_handler->wprobo_documerge_get_stripe_key( 'publishable' );
                if ( ! is_wp_error( $pub_key ) ) {
                    $wprobo_documerge_stripe_pub = $pub_key;

                    wp_enqueue_script(
                        'wprobo-documerge-stripe-js',
                        'https://js.stripe.com/v3/',
                        array(),
                        null, // phpcs:ignore WordPress.WP.EnqueuedResourceParameters
                        true
                    );
                    wp_enqueue_script(
                        'wprobo-documerge-stripe-payment',
                        WPROBO_DOCUMERGE_URL . 'assets/js/frontend/stripe-payment.min.js',
                        array( 'jquery', 'wprobo-documerge-stripe-js' ),
                        WPROBO_DOCUMERGE_VERSION,
                        true
                    );
                }
            }
        }

        // Localize frontend script.
        wp_localize_script(
            'wprobo-documerge-frontend',
            'wprobo_documerge_frontend_vars',
            array(
                'ajax_url'               => admin_url( 'admin-ajax.php' ),
                'nonce'                  => wp_create_nonce( 'wprobo_documerge_frontend' ),
                'captcha_type'           => $wprobo_documerge_captcha_type,
                'recaptcha_v3_site_key'  => get_option( 'wprobo_documerge_recaptcha_v3_site_key', '' ),
                'stripe_publishable_key' => $wprobo_documerge_stripe_pub,
                'stripe_mode'            => get_option( 'wprobo_documerge_stripe_mode', 'test' ),
                'stripe_card_layout'     => get_option( 'wprobo_documerge_stripe_card_layout', 'single' ),
                'stripe_hide_postal'     => get_option( 'wprobo_documerge_stripe_hide_postal', '0' ),
                'intl_tel_utils_url'     => WPROBO_DOCUMERGE_URL . 'assets/vendor/intl-tel-input/utils.js',
            )
        );

        // Output custom CSS if set.
        $custom_css = get_option( 'wprobo_documerge_custom_css', '' );
        if ( ! empty( $custom_css ) ) {
            wp_add_inline_style( 'wprobo-documerge-frontend', wp_strip_all_tags( $custom_css ) );
        }
    }
}
