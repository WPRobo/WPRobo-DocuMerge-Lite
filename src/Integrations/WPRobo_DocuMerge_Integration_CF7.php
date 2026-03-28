<?php
/**
 * Contact Form 7 integration handler.
 *
 * Bridges Contact Form 7 submissions with the DocuMerge document-generation pipeline.
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
 * Class WPRobo_DocuMerge_Integration_CF7
 *
 * Listens to Contact Form 7 mail-sent hooks, normalises the submitted data,
 * and forwards it to the base integration for document generation.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Integration_CF7 extends WPRobo_DocuMerge_Integration_Base {

    /**
     * Integration slug.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $wprobo_documerge_plugin_slug = 'cf7';

    /**
     * Human-readable integration name.
     *
     * @since 1.0.0
     * @var   string
     */
    protected $wprobo_documerge_plugin_name = 'Contact Form 7';

    /**
     * Check whether Contact Form 7 is active.
     *
     * @since  1.0.0
     * @return bool
     */
    public function wprobo_documerge_is_active() {
        return class_exists( 'WPCF7' );
    }

    /**
     * Register WordPress hooks for the Contact Form 7 integration.
     *
     * @since 1.0.0
     * @return void
     */
    public function wprobo_documerge_register_hooks() {
        add_action( 'wpcf7_mail_sent', array( $this, 'wprobo_documerge_on_submission' ), 10, 1 );
    }

    /**
     * Handle a Contact Form 7 submission.
     *
     * Fired by the `wpcf7_mail_sent` action. Retrieves posted data from the
     * current submission instance, normalises it, and passes it to the parent
     * processing method.
     *
     * @since 1.0.0
     *
     * @param \WPCF7_ContactForm $contact_form The contact form instance.
     * @return void
     */
    public function wprobo_documerge_on_submission( $contact_form ) {
        $external_form_id = absint( $contact_form->id() );
        $submission        = \WPCF7_Submission::get_instance();

        if ( ! $submission ) {
            return;
        }

        $posted_data = $submission->get_posted_data();
        $normalised  = $this->wprobo_documerge_normalise_submission( $posted_data );

        $email = '';
        if ( ! empty( $normalised['your-email'] ) ) {
            $email = $normalised['your-email'];
        } elseif ( ! empty( $normalised['email'] ) ) {
            $email = $normalised['email'];
        }

        $this->wprobo_documerge_process_submission( $external_form_id, $normalised, $email );
    }

    /**
     * Normalise Contact Form 7 posted data into a flat key-value array.
     *
     * Strips internal CF7 fields (prefixed with `_wpcf7` or `_wpnonce`)
     * and sanitises all remaining values.
     *
     * @since  1.0.0
     *
     * @param array $posted_data Flat associative array from CF7 submission.
     * @return array Flat associative array of sanitised field data.
     */
    public function wprobo_documerge_normalise_submission( $posted_data ) {
        $normalised = array();

        foreach ( $posted_data as $key => $value ) {
            if ( 0 === strpos( $key, '_wpcf7' ) || 0 === strpos( $key, '_wpnonce' ) ) {
                continue;
            }

            $clean_key   = sanitize_key( $key );
            $clean_value = is_array( $value )
                ? implode( ', ', array_map( 'sanitize_text_field', $value ) )
                : sanitize_text_field( $value );

            $normalised[ $clean_key ] = $clean_value;
        }

        return $normalised;
    }

    /**
     * Retrieve all available Contact Form 7 forms.
     *
     * @since  1.0.0
     * @return array Array of associative arrays with 'id' and 'title' keys.
     */
    public function wprobo_documerge_get_available_forms() {
        if ( ! class_exists( 'WPCF7' ) ) {
            return array();
        }

        $forms = get_posts( array(
            'post_type'   => 'wpcf7_contact_form',
            'numberposts' => -1,
            'orderby'     => 'title',
            'order'       => 'ASC',
        ) );

        return array_map( function ( $form ) {
            return array(
                'id'    => $form->ID,
                'title' => $form->post_title,
            );
        }, $forms );
    }

    /**
     * Retrieve the fields configured for a specific Contact Form 7 form.
     *
     * Skips non-input tag types such as submit, hidden, acceptance, quiz,
     * and file. Entries with an empty key are filtered out.
     *
     * @since  1.0.0
     *
     * @param int $external_form_id CF7 form post ID.
     * @return array Array of associative arrays with 'key', 'label', and 'type' keys.
     */
    public function wprobo_documerge_get_form_fields( $external_form_id ) {
        if ( ! class_exists( 'WPCF7' ) ) {
            return array();
        }

        $form = \WPCF7_ContactForm::get_instance( absint( $external_form_id ) );

        if ( ! $form ) {
            return array();
        }

        $tags       = $form->scan_form_tags();
        $skip_types = array( 'submit', 'hidden', 'acceptance', 'quiz', 'file' );
        $result     = array();

        foreach ( $tags as $tag ) {
            if ( in_array( $tag->basetype, $skip_types, true ) ) {
                continue;
            }

            $key = sanitize_key( $tag->name );

            if ( empty( $key ) ) {
                continue;
            }

            $result[] = array(
                'key'   => $key,
                'label' => sanitize_text_field( $tag->name ), // CF7 tags don't have separate labels.
                'type'  => sanitize_key( $tag->basetype ),
            );
        }

        return $result;
    }
}
