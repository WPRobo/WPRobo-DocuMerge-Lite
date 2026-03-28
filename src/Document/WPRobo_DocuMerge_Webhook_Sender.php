<?php
/**
 * Webhook sender for external integrations.
 *
 * Sends submission data and document status to a configured
 * webhook URL (Zapier, Make, n8n, etc.) after a form submission
 * is successfully processed.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Document
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.4.0
 */

namespace WPRobo\DocuMerge\Document;

use WPRobo\DocuMerge\Helpers\WPRobo_DocuMerge_Logger;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Webhook_Sender
 *
 * Sends a JSON payload containing submission fields, document status,
 * and metadata to an external webhook endpoint via wp_remote_post().
 * Provides a filter for customising the payload before dispatch.
 *
 * @since 1.4.0
 */
class WPRobo_DocuMerge_Webhook_Sender {

	/**
	 * Send submission data to the configured webhook URL.
	 *
	 * Loads the submission record with a JOIN to the forms table,
	 * builds a JSON payload, applies the wprobo_documerge_webhook_payload
	 * filter, and dispatches a POST request. Returns true on a 2xx
	 * response or WP_Error on failure.
	 *
	 * @since  1.4.0
	 * @param  int    $submission_id The submission ID.
	 * @param  int    $form_id       The form ID.
	 * @param  string $webhook_url   The target webhook URL.
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public function wprobo_documerge_send( $submission_id, $form_id, $webhook_url ) {
		if ( empty( $webhook_url ) || ! filter_var( $webhook_url, FILTER_VALIDATE_URL ) ) {
			return new \WP_Error( 'invalid_url', __( 'Invalid webhook URL.', 'wprobo-documerge' ) );
		}

		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$submission = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT s.*, f.title AS form_title
				 FROM {$wpdb->prefix}wprdm_submissions s
				 LEFT JOIN {$wpdb->prefix}wprdm_forms f ON s.form_id = f.id
				 WHERE s.id = %d",
				absint( $submission_id )
			)
		);

		if ( ! $submission ) {
			return new \WP_Error( 'not_found', __( 'Submission not found.', 'wprobo-documerge' ) );
		}

		// Build payload.
		$form_data = json_decode( $submission->form_data, true );
		$fields    = isset( $form_data['fields'] ) ? $form_data['fields'] : $form_data;

		// Build document availability info.
		$doc_urls = array();
		if ( ! empty( $submission->doc_path_pdf ) ) {
			$doc_urls['pdf'] = 'Generated';
		}
		if ( ! empty( $submission->doc_path_docx ) ) {
			$doc_urls['docx'] = 'Generated';
		}

		$payload = array(
			'event'           => 'submission_completed',
			'submission_id'   => absint( $submission_id ),
			'form_id'         => absint( $form_id ),
			'form_name'       => isset( $submission->form_title ) ? $submission->form_title : '',
			'submitter_email' => $submission->submitter_email,
			'submitted_at'    => $submission->created_at,
			'status'          => $submission->status,
			'payment_status'  => isset( $submission->payment_status ) ? $submission->payment_status : 'none',
			'payment_amount'  => isset( $submission->payment_amount ) ? floatval( $submission->payment_amount ) : 0,
			'fields'          => $fields,
			'documents'       => $doc_urls,
			'site_url'        => get_site_url(),
			'site_name'       => get_bloginfo( 'name' ),
		);

		/**
		 * Filters the webhook payload before sending.
		 *
		 * Allows developers to add, remove, or modify the data
		 * included in the outgoing webhook request.
		 *
		 * @since 1.4.0
		 *
		 * @param array $payload       The webhook payload.
		 * @param int   $submission_id The submission ID.
		 * @param int   $form_id       The form ID.
		 */
		$payload = apply_filters( 'wprobo_documerge_webhook_payload', $payload, $submission_id, $form_id );

		$response = wp_remote_post(
			$webhook_url,
			array(
				'body'    => wp_json_encode( $payload ),
				'headers' => array(
					'Content-Type' => 'application/json',
					'User-Agent'   => 'WPRobo-DocuMerge/' . WPROBO_DOCUMERGE_VERSION,
				),
				'timeout' => 15,
			)
		);

		if ( is_wp_error( $response ) ) {
			WPRobo_DocuMerge_Logger::wprobo_documerge_log(
				sprintf( 'Webhook delivery failed for submission #%d: %s', $submission_id, $response->get_error_message() ),
				'error',
				array(
					'submission_id' => $submission_id,
					'webhook_url'   => $webhook_url,
					'error'         => $response->get_error_message(),
				)
			);
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( $code < 200 || $code >= 300 ) {
			WPRobo_DocuMerge_Logger::wprobo_documerge_log(
				sprintf( 'Webhook returned HTTP %d for submission #%d', $code, $submission_id ),
				'error',
				array(
					'submission_id' => $submission_id,
					'webhook_url'   => $webhook_url,
					'http_code'     => $code,
				)
			);
			return new \WP_Error(
				'webhook_failed',
				/* translators: %d: HTTP response code. */
				sprintf( __( 'Webhook returned HTTP %d', 'wprobo-documerge' ), $code )
			);
		}

		/**
		 * Fires after a webhook is sent successfully.
		 *
		 * @since 1.4.0
		 *
		 * @param int   $submission_id The submission ID.
		 * @param array $payload       The sent payload.
		 */
		do_action( 'wprobo_documerge_webhook_sent', $submission_id, $payload );

		WPRobo_DocuMerge_Logger::wprobo_documerge_log(
			sprintf( 'Webhook sent successfully for submission #%d', $submission_id ),
			'info',
			array(
				'submission_id' => $submission_id,
				'webhook_url'   => $webhook_url,
			)
		);

		return true;
	}
}
