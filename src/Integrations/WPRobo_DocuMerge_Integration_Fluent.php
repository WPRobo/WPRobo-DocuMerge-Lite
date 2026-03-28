<?php
/**
 * Fluent Forms integration handler.
 *
 * Bridges Fluent Forms submissions with the DocuMerge document-generation pipeline.
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
 * Class WPRobo_DocuMerge_Integration_Fluent
 *
 * Listens to Fluent Forms submission hooks (both v4 legacy and v5+ namespaced),
 * normalises the submitted data by stripping internal fields, and forwards it
 * to the base integration for document generation.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Integration_Fluent extends WPRobo_DocuMerge_Integration_Base {

	/**
	 * Integration slug.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $wprobo_documerge_plugin_slug = 'fluent';

	/**
	 * Human-readable integration name.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $wprobo_documerge_plugin_name = 'Fluent Forms';

	/**
	 * Check whether Fluent Forms is active.
	 *
	 * @since  1.0.0
	 * @return bool True if Fluent Forms is active, false otherwise.
	 */
	public function wprobo_documerge_is_active() {
		return defined( 'FLUENTFORM' );
	}

	/**
	 * Register WordPress hooks for the Fluent Forms integration.
	 *
	 * Registers both the v5+ namespaced hook (`fluentform/submission_inserted`)
	 * and the legacy v4 hook (`fluentform_submission_inserted`) for backward
	 * compatibility. Bails early if Fluent Forms is not available.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function wprobo_documerge_register_hooks() {
		if ( ! defined( 'FLUENTFORM' ) ) {
			return;
		}

		// New hook name (Fluent Forms v5+).
		add_action( 'fluentform/submission_inserted', array( $this, 'wprobo_documerge_on_submission' ), 10, 3 );

		// Legacy hook name (Fluent Forms v4 and earlier).
		add_action( 'fluentform_submission_inserted', array( $this, 'wprobo_documerge_on_submission' ), 10, 3 );
	}

	/**
	 * Handle a Fluent Forms submission.
	 *
	 * Fired by either `fluentform/submission_inserted` or
	 * `fluentform_submission_inserted`. Normalises the form data, searches
	 * for an email field in the normalised output, and passes everything to
	 * the parent processing method.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $insert_id The submission insert ID.
	 * @param array  $form_data The raw POST data from Fluent Forms.
	 * @param object $form      The Fluent Forms form object.
	 * @return void
	 */
	public function wprobo_documerge_on_submission( $insert_id, $form_data, $form ) {
		if ( ! defined( 'FLUENTFORM' ) ) {
			return;
		}

		$external_form_id = absint( $form->id );
		$normalised       = $this->wprobo_documerge_normalise_submission( array( $insert_id, $form_data, $form ) );

		// Find email.
		$email = '';
		foreach ( $normalised as $key => $value ) {
			if ( strpos( $key, 'email' ) !== false && is_email( $value ) ) {
				$email = $value;
				break;
			}
		}

		$this->wprobo_documerge_process_submission( $external_form_id, $normalised, $email );
	}

	/**
	 * Normalise Fluent Forms submission data into a flat key-value array.
	 *
	 * Strips internal Fluent Forms fields (identified by prefix or key name)
	 * and sanitises all remaining values. Array values are collapsed into
	 * comma-separated strings.
	 *
	 * @since  1.0.0
	 *
	 * @param array $hook_args Array containing the insert ID at index 0,
	 *                         raw form data at index 1, and form object at index 2.
	 * @return array Flat associative array of sanitised field data.
	 */
	protected function wprobo_documerge_normalise_submission( $hook_args ) {
		$form_data = $hook_args[1]; // Raw POST data from Fluent Forms.
		$data      = array();

		if ( ! is_array( $form_data ) ) {
			return $data;
		}

		// Fields to skip.
		$skip_prefixes = array( '__', '_fluentform', '_token', '_wp_http', 'g-recaptcha', 'h-captcha', 'cf-turnstile' );
		$skip_keys     = array( '_token', '__fluent_form_embded_post_id', '_fluentform_15_fluentformnonce', 'submit' );

		foreach ( $form_data as $key => $value ) {
			// Skip internal fields.
			$skip = false;
			foreach ( $skip_prefixes as $prefix ) {
				if ( strpos( $key, $prefix ) === 0 ) {
					$skip = true;
					break;
				}
			}

			if ( $skip || in_array( $key, $skip_keys, true ) ) {
				continue;
			}

			$clean_key = sanitize_key( $key );

			if ( empty( $clean_key ) ) {
				continue;
			}

			if ( is_array( $value ) ) {
				$value = implode( ', ', array_map( 'sanitize_text_field', $value ) );
			} else {
				$value = sanitize_text_field( $value );
			}

			$data[ $clean_key ] = $value;
		}

		return $data;
	}

	/**
	 * Retrieve all available Fluent Forms forms.
	 *
	 * Queries the Fluent Forms database table directly for published forms.
	 * Returns an empty array if Fluent Forms is not available or the table
	 * does not exist.
	 *
	 * @since  1.0.0
	 * @return array Array of associative arrays with 'id' and 'title' keys.
	 */
	public function wprobo_documerge_get_available_forms() {
		if ( ! defined( 'FLUENTFORM' ) ) {
			return array();
		}

		global $wpdb;
		$table = $wpdb->prefix . 'fluentform_forms';

		// Check table exists.
		if ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table ) ) !== $table ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			return array();
		}

		$forms = $wpdb->get_results( "SELECT id, title FROM {$table} WHERE status = 'published' ORDER BY title ASC" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		if ( ! $forms ) {
			return array();
		}

		$result = array();
		foreach ( $forms as $form ) {
			$result[] = array(
				'id'    => absint( $form->id ),
				'title' => sanitize_text_field( $form->title ),
			);
		}

		return $result;
	}

	/**
	 * Retrieve the fields configured for a specific Fluent Forms form.
	 *
	 * Parses the JSON-encoded form fields from the database and skips
	 * non-input element types such as section breaks, custom HTML, reCAPTCHA,
	 * and similar. Returns an empty array if Fluent Forms is not available
	 * or the form cannot be found.
	 *
	 * @since  1.0.0
	 *
	 * @param int $external_form_id Fluent Forms form ID.
	 * @return array Array of associative arrays with 'key', 'label', and 'type' keys.
	 */
	public function wprobo_documerge_get_form_fields( $external_form_id ) {
		if ( ! defined( 'FLUENTFORM' ) ) {
			return array();
		}

		global $wpdb;
		$table = $wpdb->prefix . 'fluentform_forms';
		$form  = $wpdb->get_row( $wpdb->prepare( "SELECT form_fields FROM {$table} WHERE id = %d", absint( $external_form_id ) ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

		if ( ! $form || empty( $form->form_fields ) ) {
			return array();
		}

		$fields_data = json_decode( $form->form_fields, true );

		if ( ! is_array( $fields_data ) || empty( $fields_data['fields'] ) ) {
			return array();
		}

		$result     = array();
		$skip_types = array( 'section_break', 'custom_html', 'form_step', 'recaptcha', 'hcaptcha', 'turnstile', 'action_hook', 'shortcode' );

		foreach ( $fields_data['fields'] as $field ) {
			$element = isset( $field['element'] ) ? $field['element'] : '';

			if ( in_array( $element, $skip_types, true ) ) {
				continue;
			}

			$name  = isset( $field['attributes']['name'] ) ? $field['attributes']['name'] : '';
			$label = isset( $field['settings']['label'] ) ? $field['settings']['label'] : $name;

			if ( empty( $name ) ) {
				continue;
			}

			$result[] = array(
				'key'   => sanitize_key( $name ),
				'label' => sanitize_text_field( $label ),
				'type'  => sanitize_key( $element ),
			);
		}

		return $result;
	}
}
