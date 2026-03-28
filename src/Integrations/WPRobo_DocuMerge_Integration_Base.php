<?php
/**
 * Abstract base class for form plugin integrations.
 *
 * Provides the common submission-processing pipeline that every
 * integration (WPForms, CF7, etc.) shares. Concrete integrations
 * extend this class and implement the abstract methods to normalise
 * their plugin-specific data into a unified format.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Integrations
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.0.0
 */

namespace WPRobo\DocuMerge\Integrations;

use WPRobo\DocuMerge\Document\WPRobo_DocuMerge_Document_Generator;
use WPRobo\DocuMerge\Document\WPRobo_DocuMerge_Delivery_Engine;
use WPRobo\DocuMerge\Helpers\WPRobo_DocuMerge_Logger;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Integration_Base
 *
 * Abstract base that all form-plugin integrations must extend.
 * Subclasses register their own hooks, normalise submissions into
 * a standard key-value array, and then delegate to the shared
 * pipeline provided by wprobo_documerge_process_submission().
 *
 * @since 1.0.0
 */
abstract class WPRobo_DocuMerge_Integration_Base {

	/**
	 * Unique slug identifying this integration (e.g. 'wpforms', 'cf7').
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $wprobo_documerge_plugin_slug = '';

	/**
	 * Human-readable name of the integrated plugin.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $wprobo_documerge_plugin_name = '';

	/**
	 * Register WordPress hooks that listen for the external plugin's
	 * form submission events.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	abstract public function wprobo_documerge_register_hooks();

	/**
	 * Retrieve the fields configured for a specific external form.
	 *
	 * @since  1.0.0
	 * @param  int $external_form_id The external plugin's form ID.
	 * @return array Array of field definitions.
	 */
	abstract public function wprobo_documerge_get_form_fields( $external_form_id );

	/**
	 * Retrieve all forms available in the external plugin.
	 *
	 * @since  1.0.0
	 * @return array Array of available forms.
	 */
	abstract public function wprobo_documerge_get_available_forms();

	/**
	 * Normalise the external plugin's submission data into a standard
	 * key-value array of field values.
	 *
	 * @since  1.0.0
	 * @param  mixed $hook_args Raw arguments from the external plugin's hook.
	 * @return array Associative array of normalised field data.
	 */
	abstract protected function wprobo_documerge_normalise_submission( $hook_args );

	/**
	 * Check whether the integrated plugin is currently active.
	 *
	 * Subclasses should override this to perform plugin-specific
	 * detection (e.g. function_exists or class_exists checks).
	 *
	 * @since  1.0.0
	 * @return bool True if the integrated plugin is active, false otherwise.
	 */
	public function wprobo_documerge_is_active() {
		return false;
	}

	/**
	 * Get the integration slug.
	 *
	 * @since  1.0.0
	 * @return string The integration slug.
	 */
	public function wprobo_documerge_get_slug() {
		return $this->wprobo_documerge_plugin_slug;
	}

	/**
	 * Get the human-readable integration name.
	 *
	 * @since  1.0.0
	 * @return string The integration name.
	 */
	public function wprobo_documerge_get_name() {
		return $this->wprobo_documerge_plugin_name;
	}

	/**
	 * Common submission-processing pipeline.
	 *
	 * Called by concrete integrations after they normalise the incoming
	 * submission. Finds all DocuMerge forms mapped to the given external
	 * form, builds merge data, inserts a submission record, generates
	 * the document (or defers to Stripe), and triggers delivery.
	 *
	 * @since  1.0.0
	 * @param  int    $external_form_id The external plugin's form ID.
	 * @param  array  $field_data       Normalised key-value field data.
	 * @param  string $user_email       Optional submitter email address.
	 * @return void
	 */
	final protected function wprobo_documerge_process_submission( $external_form_id, $field_data, $user_email = '' ) {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$forms = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}wprdm_forms WHERE mode = 'integrated' AND integration = %s",
				$this->wprobo_documerge_plugin_slug
			)
		);

		if ( empty( $forms ) ) {
			return;
		}

		foreach ( $forms as $form ) {
			$settings = json_decode( $form->settings, true );

			if ( empty( $settings['external_form_id'] ) || absint( $settings['external_form_id'] ) !== absint( $external_form_id ) ) {
				continue;
			}

			$field_map = isset( $settings['field_map'] ) ? $settings['field_map'] : array();

			// Build merge data from the field map.
			$merge_data = array();
			foreach ( $field_map as $merge_tag => $external_field_key ) {
				$merge_data[ $merge_tag ] = isset( $field_data[ $external_field_key ] ) ? $field_data[ $external_field_key ] : '';
			}

			$now        = gmdate( 'Y-m-d\TH:i:s\Z' );
			$ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
			$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
			$page_url   = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
			$referrer   = $page_url;

			// Build form_data JSON (Section 41 format).
			$form_data_array = array(
				'submission_id' => 0,
				'form_id'       => absint( $form->id ),
				'template_id'   => absint( $form->template_id ),
				'submitted_at'  => $now,
				'fields'        => $merge_data,
				'meta'          => array(
					'ip'         => $ip_address,
					'user_agent' => $user_agent,
					'page_url'   => $page_url,
					'referrer'   => $referrer,
				),
			);

			$form_data_json = wp_json_encode( $form_data_array );

			// Insert submission record.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$wpdb->insert(
				$wpdb->prefix . 'wprdm_submissions',
				array(
					'form_id'         => absint( $form->id ),
					'template_id'     => absint( $form->template_id ),
					'submitter_email' => sanitize_email( $user_email ),
					'form_data'       => $form_data_json,
					'status'          => 'processing',
					'ip_address'      => $ip_address,
					'created_at'      => $now,
					'updated_at'      => $now,
				),
				array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
			);

			$submission_id = $wpdb->insert_id;

			if ( ! $submission_id ) {
				WPRobo_DocuMerge_Logger::wprobo_documerge_log(
					__( 'Failed to insert integration submission record.', 'wprobo-documerge' ),
					'error',
					array(
						'integration'      => $this->wprobo_documerge_plugin_slug,
						'external_form_id' => $external_form_id,
					)
				);
				continue;
			}

			// Update submission_id inside form_data JSON.
			$form_data_array['submission_id'] = $submission_id;
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$wpdb->prefix . 'wprdm_submissions',
				array( 'form_data' => wp_json_encode( $form_data_array ) ),
				array( 'id' => $submission_id ),
				array( '%s' ),
				array( '%d' )
			);

			// If payment is enabled, defer generation to Stripe webhook.
			if ( ! empty( $form->payment_enabled ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->update(
					$wpdb->prefix . 'wprdm_submissions',
					array(
						'status'     => 'pending_payment',
						'updated_at' => gmdate( 'Y-m-d\TH:i:s\Z' ),
					),
					array( 'id' => $submission_id ),
					array( '%s', '%s' ),
					array( '%d' )
				);
				continue;
			}

			// Generate the document.
			$generator = new WPRobo_DocuMerge_Document_Generator();
			$result    = $generator->wprobo_documerge_generate( $submission_id );

			if ( is_wp_error( $result ) ) {
				// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->update(
					$wpdb->prefix . 'wprdm_submissions',
					array(
						'status'     => 'error',
						'updated_at' => gmdate( 'Y-m-d\TH:i:s\Z' ),
					),
					array( 'id' => $submission_id ),
					array( '%s', '%s' ),
					array( '%d' )
				);

				WPRobo_DocuMerge_Logger::wprobo_documerge_log(
					$result->get_error_message(),
					'error',
					array(
						'submission_id' => $submission_id,
						'integration'   => $this->wprobo_documerge_plugin_slug,
					)
				);

				// Notify site admin of the failure.
				wp_mail(
					get_option( 'admin_email' ),
					/* translators: %d: submission ID */
					sprintf( __( 'DocuMerge: Document generation failed for submission #%d', 'wprobo-documerge' ), $submission_id ),
					/* translators: %s: error message */
					sprintf( __( 'Error: %s', 'wprobo-documerge' ), $result->get_error_message() )
				);

				continue;
			}

			// Deliver the generated document.
			$delivery = new WPRobo_DocuMerge_Delivery_Engine();
			$delivery->wprobo_documerge_deliver( $submission_id );

			/**
			 * Fires after an integration submission has been fully processed.
			 *
			 * @since 1.0.0
			 * @param int    $submission_id              The submission record ID.
			 * @param string $wprobo_documerge_plugin_slug The integration slug.
			 */
			do_action( 'wprobo_documerge_integration_submission_processed', $submission_id, $this->wprobo_documerge_plugin_slug );
		}
	}
}
