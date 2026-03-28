<?php
/**
 * WPForms integration handler.
 *
 * Bridges WPForms submissions with the DocuMerge document-generation pipeline.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Integrations
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.0.0
 */

namespace WPRobo\DocuMerge\Integrations;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPRobo_DocuMerge_Integration_WPForms
 *
 * Listens to WPForms submission hooks, normalises the submitted data,
 * and forwards it to the base integration for document generation.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Integration_WPForms extends WPRobo_DocuMerge_Integration_Base {

    /**
     * Integration slug.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $wprobo_documerge_plugin_slug = 'wpforms';

    /**
     * Human-readable integration name.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $wprobo_documerge_plugin_name = 'WPForms';

    /**
     * Check whether WPForms is active.
     *
     * @since  1.0.0
     * @return bool
     */
    public function wprobo_documerge_is_active() {
        return function_exists( 'wpforms' );
    }

    /**
     * Register WordPress hooks for the WPForms integration.
     *
     * @since 1.0.0
     * @return void
     */
    public function wprobo_documerge_register_hooks() {
        add_action( 'wpforms_process_complete', array( $this, 'wprobo_documerge_on_submission' ), 10, 4 );
    }

    /**
     * Handle a WPForms submission.
     *
     * Fired by the `wpforms_process_complete` action. Normalises the field
     * data and passes it to the parent processing method.
     *
     * @since 1.0.0
     *
     * @param array $fields    Sanitised entry field values/properties.
     * @param array $entry     Original $_POST global.
     * @param array $form_data Form settings/data.
     * @param int   $entry_id  Entry ID.
     * @return void
     */
    public function wprobo_documerge_on_submission( $fields, $entry, $form_data, $entry_id ) {
        $external_form_id = absint( $form_data['id'] );
        $normalised       = $this->wprobo_documerge_normalise_submission( $fields );

        $email = '';
        foreach ( $fields as $field ) {
            if ( 'email' === $field['type'] ) {
                $email = $field['value'];
                break;
            }
        }

        $this->wprobo_documerge_process_submission( $external_form_id, $normalised, $email );
    }

    /**
     * Normalise WPForms field data into a flat key-value array.
     *
     * Skips file-upload fields and builds a sanitised associative array
     * suitable for merge-tag replacement.
     *
     * @since  1.0.0
     *
     * @param array $fields Array of field arrays, each containing id, type, name, and value.
     * @return array Flat associative array of sanitised field data.
     */
    public function wprobo_documerge_normalise_submission( $fields ) {
        $normalised = array();

        foreach ( $fields as $field ) {
            if ( 'file-upload' === $field['type'] ) {
                continue;
            }

            $key   = sanitize_key( $field['id'] );
            $value = sanitize_text_field( $field['value'] );

            $normalised[ $key ] = $value;
        }

        return $normalised;
    }

    /**
     * Retrieve all available WPForms forms.
     *
     * @since  1.0.0
     * @return array Array of associative arrays with 'id' and 'title' keys.
     */
    public function wprobo_documerge_get_available_forms() {
        if ( ! function_exists( 'wpforms' ) ) {
            return array();
        }

        $forms = wpforms()->form->get( '', array(
            'orderby' => 'title',
            'order'   => 'ASC',
        ) );

        if ( empty( $forms ) || ! is_array( $forms ) ) {
            return array();
        }

        return array_map( function ( $form ) {
            return array(
                'id'    => $form->ID,
                'title' => $form->post_title,
            );
        }, $forms );
    }

    /**
     * Retrieve the fields configured for a specific WPForms form.
     *
     * Skips layout-only and non-input field types such as divider,
     * html, pagebreak, and captcha.
     *
     * @since  1.0.0
     *
     * @param int $external_form_id WPForms form post ID.
     * @return array Array of associative arrays with 'key', 'label', and 'type' keys.
     */
    public function wprobo_documerge_get_form_fields( $external_form_id ) {
        if ( ! function_exists( 'wpforms' ) ) {
            return array();
        }

        $form = wpforms()->form->get( absint( $external_form_id ) );

        if ( ! $form ) {
            return array();
        }

        $content = wpforms_decode( $form->post_content );

        if ( empty( $content['fields'] ) ) {
            return array();
        }

        $skip_types = array( 'divider', 'html', 'pagebreak', 'captcha' );
        $result     = array();

        foreach ( $content['fields'] as $field ) {
            if ( in_array( $field['type'], $skip_types, true ) ) {
                continue;
            }

            $result[] = array(
                'key'   => sanitize_key( $field['id'] ),
                'label' => sanitize_text_field( $field['label'] ?? 'Field ' . $field['id'] ),
                'type'  => sanitize_key( $field['type'] ),
            );
        }

        return $result;
    }
}
