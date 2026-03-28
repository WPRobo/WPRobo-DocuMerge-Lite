<?php
/**
 * Feature Gate — controls Lite vs Pro feature availability.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Core
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.0.0
 */

namespace WPRobo\DocuMerge\Core;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPRobo_DocuMerge_Feature_Gate
 *
 * Determines which features are available based on Lite or Pro edition.
 * Pro edition defines WPROBO_DOCUMERGE_PRO = true; Lite does not.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Feature_Gate {

    /**
     * Singleton instance.
     *
     * @since 1.0.0
     * @var   WPRobo_DocuMerge_Feature_Gate
     */
    private static $wprobo_documerge_instance = null;

    /**
     * Features available in the Lite (free) edition.
     *
     * @since 1.0.0
     * @var   array
     */
    private $wprobo_documerge_lite_features = array(
        // Fields.
        'field_text',
        'field_textarea',
        'field_email',
        'field_phone',
        'field_number',
        'field_date',
        'field_dropdown',
        'field_radio',
        'field_checkbox',
        // Core.
        'templates',
        'forms',
        'submissions',
        'shortcode',
        'gutenberg_block',
        'setup_wizard',
        'download_delivery',
        'docx_generation',
        'pdf_generation',
        'import_export',
        'admin_bar',
        'submission_notes',
    );

    /**
     * Features available only in the Pro edition.
     *
     * @since 1.0.0
     * @var   array
     */
    private $wprobo_documerge_pro_features = array(
        // Fields.
        'field_signature',
        'field_payment',
        'field_file_upload',
        'field_repeater',
        'field_rating',
        'field_address',
        'field_name',
        'field_password',
        'field_hidden',
        'field_html',
        'field_section_divider',
        // Features.
        'stripe_payments',
        'recaptcha',
        'hcaptcha',
        'multistep_forms',
        'conditional_logic',
        'email_delivery',
        'media_delivery',
        'wpforms_integration',
        'cf7_integration',
        'gravity_integration',
        'fluent_integration',
        'elementor_widget',
        'analytics_dashboard',
        'form_styles',
        'custom_css',
        'webhooks',
        'entry_limits',
        'email_template_editor',
        'document_preview',
        'merge_conditionals',
        'merge_tables',
        'merge_images',
        'merge_qr',
    );

    /**
     * Get singleton instance.
     *
     * @since  1.0.0
     * @return WPRobo_DocuMerge_Feature_Gate
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
    private function __construct() {}

    /**
     * Check if the current installation is the Pro edition.
     *
     * @since  1.0.0
     * @return bool True if Pro, false if Lite.
     */
    public function wprobo_documerge_is_pro() {
        return defined( 'WPROBO_DOCUMERGE_PRO' ) && WPROBO_DOCUMERGE_PRO;
    }

    /**
     * Check if a specific feature is available.
     *
     * @since  1.0.0
     * @param  string $feature Feature slug to check.
     * @return bool   True if the feature is available.
     */
    public function wprobo_documerge_can( $feature ) {
        if ( $this->wprobo_documerge_is_pro() ) {
            return true;
        }
        return in_array( $feature, $this->wprobo_documerge_lite_features, true );
    }

    /**
     * Get all Pro-only features for upsell display.
     *
     * @since  1.0.0
     * @return array List of Pro-only feature slugs.
     */
    public function wprobo_documerge_get_pro_features() {
        return $this->wprobo_documerge_pro_features;
    }

    /**
     * Get the upgrade URL with UTM tracking.
     *
     * @since  1.0.0
     * @return string Upgrade URL.
     */
    public function wprobo_documerge_get_upgrade_url() {
        return 'https://wprobo.com/plugins/wprobo-documerge/?utm_source=lite&utm_medium=plugin&utm_campaign=upgrade';
    }

    /**
     * Get field types that are Pro-only.
     *
     * @since  1.0.0
     * @return array List of Pro-only field type slugs.
     */
    public function wprobo_documerge_get_pro_field_types() {
        return array(
            'signature',
            'payment',
            'file_upload',
            'repeater',
            'rating',
            'address',
            'name',
            'password',
            'hidden',
            'html',
            'section_divider',
        );
    }

    /**
     * Check if a field type is Pro-only.
     *
     * @since  1.0.0
     * @param  string $type Field type slug.
     * @return bool   True if the field type requires Pro.
     */
    public function wprobo_documerge_is_pro_field( $type ) {
        return in_array( $type, $this->wprobo_documerge_get_pro_field_types(), true );
    }
}
