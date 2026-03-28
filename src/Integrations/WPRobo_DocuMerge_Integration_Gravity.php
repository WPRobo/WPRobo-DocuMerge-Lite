<?php
/**
 * Gravity Forms integration handler.
 *
 * Bridges Gravity Forms submissions with the DocuMerge document-generation pipeline.
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
 * Class WPRobo_DocuMerge_Integration_Gravity
 *
 * Listens to Gravity Forms submission hooks, normalises the submitted data
 * (including composite fields such as name, address, and checkbox), and
 * forwards it to the base integration for document generation.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Integration_Gravity extends WPRobo_DocuMerge_Integration_Base {

	/**
	 * Integration slug.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $wprobo_documerge_plugin_slug = 'gravity';

	/**
	 * Human-readable integration name.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $wprobo_documerge_plugin_name = 'Gravity Forms';

	/**
	 * Check whether Gravity Forms is active.
	 *
	 * @since  1.0.0
	 * @return bool True if Gravity Forms is active, false otherwise.
	 */
	public function wprobo_documerge_is_active() {
		return class_exists( 'GFForms' );
	}

	/**
	 * Register WordPress hooks for the Gravity Forms integration.
	 *
	 * Hooks into `gform_after_submission` to capture completed form entries.
	 * Bails early if Gravity Forms is not available.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function wprobo_documerge_register_hooks() {
		if ( ! class_exists( 'GFForms' ) ) {
			return;
		}

		add_action( 'gform_after_submission', array( $this, 'wprobo_documerge_on_submission' ), 10, 2 );
	}

	/**
	 * Handle a Gravity Forms submission.
	 *
	 * Fired by the `gform_after_submission` action. Normalises the entry data,
	 * extracts the submitter email from the first email-type field, and passes
	 * everything to the parent processing method.
	 *
	 * @since 1.0.0
	 *
	 * @param array $entry The Gravity Forms entry object.
	 * @param array $form  The Gravity Forms form object.
	 * @return void
	 */
	public function wprobo_documerge_on_submission( $entry, $form ) {
		if ( ! class_exists( 'GFForms' ) ) {
			return;
		}

		$external_form_id = absint( $form['id'] );
		$normalised       = $this->wprobo_documerge_normalise_submission( array( $entry, $form ) );

		// Find email: look for field type 'email' in form fields.
		$email = '';
		foreach ( $form['fields'] as $field ) {
			if ( 'email' === $field->type && ! empty( $entry[ $field->id ] ) ) {
				$email = sanitize_email( $entry[ $field->id ] );
				break;
			}
		}

		$this->wprobo_documerge_process_submission( $external_form_id, $normalised, $email );
	}

	/**
	 * Normalise Gravity Forms entry data into a flat key-value array.
	 *
	 * Handles composite field types (name, address, checkbox) by combining
	 * their sub-field values into single strings. Skips non-input field types
	 * such as HTML, section, page, captcha, and file upload.
	 *
	 * @since  1.0.0
	 *
	 * @param array $hook_args Array containing the entry at index 0 and form at index 1.
	 * @return array Flat associative array of sanitised field data.
	 */
	protected function wprobo_documerge_normalise_submission( $hook_args ) {
		$entry = $hook_args[0];
		$form  = $hook_args[1];
		$data  = array();

		foreach ( $form['fields'] as $field ) {
			// Skip non-input types.
			$skip_types = array( 'html', 'section', 'page', 'captcha', 'fileupload' );
			if ( in_array( $field->type, $skip_types, true ) ) {
				continue;
			}

			$field_id = $field->id;
			$label    = sanitize_key( $field->label ? $field->label : 'field_' . $field_id );

			// Handle composite fields.
			if ( 'name' === $field->type ) {
				// Name fields have subfields: .3 (first), .6 (last).
				$first = isset( $entry[ $field_id . '.3' ] ) ? sanitize_text_field( $entry[ $field_id . '.3' ] ) : '';
				$last  = isset( $entry[ $field_id . '.6' ] ) ? sanitize_text_field( $entry[ $field_id . '.6' ] ) : '';
				$data[ $label ] = trim( $first . ' ' . $last );
				continue;
			}

			if ( 'address' === $field->type ) {
				// Address has subfields: .1 (street), .2 (line2), .3 (city), .4 (state), .5 (zip), .6 (country).
				$parts = array();
				foreach ( array( '.1', '.2', '.3', '.4', '.5', '.6' ) as $sub ) {
					if ( ! empty( $entry[ $field_id . $sub ] ) ) {
						$parts[] = sanitize_text_field( $entry[ $field_id . $sub ] );
					}
				}
				$data[ $label ] = implode( ', ', $parts );
				continue;
			}

			if ( 'checkbox' === $field->type ) {
				// Checkboxes store each choice in separate entry keys.
				$checked = array();
				if ( ! empty( $field->inputs ) && is_array( $field->inputs ) ) {
					foreach ( $field->inputs as $input ) {
						if ( ! empty( $entry[ strval( $input['id'] ) ] ) ) {
							$checked[] = sanitize_text_field( $entry[ strval( $input['id'] ) ] );
						}
					}
				}
				$data[ $label ] = implode( ', ', $checked );
				continue;
			}

			// Standard fields.
			$value          = isset( $entry[ $field_id ] ) ? $entry[ $field_id ] : '';
			$data[ $label ] = sanitize_text_field( $value );
		}

		return $data;
	}

	/**
	 * Retrieve all available Gravity Forms forms.
	 *
	 * Uses the GFAPI class to fetch all forms. Returns an empty array if
	 * the Gravity Forms API is not available.
	 *
	 * @since  1.0.0
	 * @return array Array of associative arrays with 'id' and 'title' keys.
	 */
	public function wprobo_documerge_get_available_forms() {
		if ( ! class_exists( 'GFAPI' ) ) {
			return array();
		}

		$forms = \GFAPI::get_forms();

		if ( ! is_array( $forms ) ) {
			return array();
		}

		$result = array();
		foreach ( $forms as $form ) {
			$result[] = array(
				'id'    => absint( $form['id'] ),
				'title' => sanitize_text_field( $form['title'] ),
			);
		}

		return $result;
	}

	/**
	 * Retrieve the fields configured for a specific Gravity Forms form.
	 *
	 * Skips non-input field types such as HTML, section, page, captcha,
	 * and file upload. Returns an empty array if the Gravity Forms API
	 * is not available or the form cannot be found.
	 *
	 * @since  1.0.0
	 *
	 * @param int $external_form_id Gravity Forms form ID.
	 * @return array Array of associative arrays with 'key', 'label', and 'type' keys.
	 */
	public function wprobo_documerge_get_form_fields( $external_form_id ) {
		if ( ! class_exists( 'GFAPI' ) ) {
			return array();
		}

		$form = \GFAPI::get_form( absint( $external_form_id ) );

		if ( ! $form || empty( $form['fields'] ) ) {
			return array();
		}

		$result = array();
		$skip   = array( 'html', 'section', 'page', 'captcha', 'fileupload' );

		foreach ( $form['fields'] as $field ) {
			if ( in_array( $field->type, $skip, true ) ) {
				continue;
			}

			$result[] = array(
				'key'   => sanitize_key( $field->label ? $field->label : 'field_' . $field->id ),
				'label' => sanitize_text_field( $field->label ),
				'type'  => sanitize_key( $field->type ),
			);
		}

		return $result;
	}
}
