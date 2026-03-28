<?php
/**
 * Stripe webhook handler for WPRobo DocuMerge.
 *
 * Processes incoming Stripe webhook events including payment successes,
 * failures, and refunds. Verifies webhook signatures using HMAC-SHA256
 * without requiring the Stripe PHP SDK.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Payment
 * @since      1.0.0
 */

namespace WPRobo\DocuMerge\Payment;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPRobo\DocuMerge\Document\WPRobo_DocuMerge_Document_Generator;
use WPRobo\DocuMerge\Document\WPRobo_DocuMerge_Delivery_Engine;
use WPRobo\DocuMerge\Helpers\WPRobo_DocuMerge_Logger;
use WPRobo\DocuMerge\Helpers\WPRobo_DocuMerge_Encryptor;

/**
 * Class WPRobo_DocuMerge_Stripe_Webhook
 *
 * Handles all Stripe webhook event processing. Verifies incoming webhook
 * signatures, routes events to the appropriate handler, and orchestrates
 * document generation and delivery after successful payments. Also provides
 * an admin AJAX endpoint for retrying failed submissions.
 *
 * This class is NOT a singleton. Instantiate it directly where needed.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Stripe_Webhook {

	/**
	 * Handle an incoming Stripe webhook request.
	 *
	 * Reads the raw request body, verifies the Stripe signature header
	 * against the configured webhook secret, parses the event payload,
	 * and routes to the appropriate event handler based on event type.
	 *
	 * @since 1.0.0
	 *
	 * @return void Outputs HTTP status and exits.
	 */
	public function wprobo_documerge_handle_request() {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$payload    = file_get_contents( 'php://input' );
		$sig_header = isset( $_SERVER['HTTP_STRIPE_SIGNATURE'] )
			? sanitize_text_field( wp_unslash( $_SERVER['HTTP_STRIPE_SIGNATURE'] ) )
			: '';

		// Retrieve the webhook secret.
		$handler = WPRobo_DocuMerge_Stripe_Handler::get_instance();
		$secret  = $handler->wprobo_documerge_get_stripe_key( 'webhook_secret' );

		// If the key method returns WP_Error (not configured via mode-based keys),
		// try decrypting the dedicated webhook secret option, then fall back to raw.
		if ( is_wp_error( $secret ) || empty( $secret ) ) {
			$encrypted = get_option( 'wprobo_documerge_stripe_webhook_secret', '' );
			$secret    = WPRobo_DocuMerge_Encryptor::wprobo_documerge_decrypt( $encrypted );

			if ( empty( $secret ) ) {
				// Fall back to raw (unencrypted) option value.
				$secret = get_option( 'wprobo_documerge_stripe_webhook_secret', '' );
			}
		}

		if ( is_wp_error( $secret ) || empty( $secret ) ) {
			status_header( 500 );
			exit( 'Webhook secret not configured' );
		}

		if ( ! $this->wprobo_documerge_verify_signature( $payload, $sig_header, $secret ) ) {
			status_header( 400 );
			exit( 'Invalid signature' );
		}

		$event = json_decode( $payload, true );

		if ( ! $event || ! isset( $event['type'] ) ) {
			status_header( 400 );
			exit( 'Invalid payload' );
		}

		WPRobo_DocuMerge_Logger::wprobo_documerge_log(
			sprintf( 'Stripe webhook received: %s', sanitize_text_field( $event['type'] ) ),
			'info',
			array( 'event_type' => $event['type'] )
		);

		switch ( $event['type'] ) {
			case 'payment_intent.succeeded':
				$this->wprobo_documerge_handle_payment_succeeded( $event['data']['object'] );
				break;

			case 'payment_intent.payment_failed':
				$this->wprobo_documerge_handle_payment_failed( $event['data']['object'] );
				break;

			case 'charge.refunded':
				$this->wprobo_documerge_handle_refund( $event['data']['object'] );
				break;

			default:
				WPRobo_DocuMerge_Logger::wprobo_documerge_log(
					sprintf( 'Unhandled Stripe event type: %s', sanitize_text_field( $event['type'] ) ),
					'info',
					array( 'event_type' => $event['type'] )
				);
				break;
		}

		status_header( 200 );
		exit( 'OK' );
	}

	/**
	 * Verify a Stripe webhook signature using HMAC-SHA256.
	 *
	 * Parses the Stripe-Signature header to extract the timestamp and
	 * v1 signature values, applies replay protection (5-minute tolerance),
	 * computes the expected HMAC, and compares against all provided
	 * v1 signatures using timing-safe comparison.
	 *
	 * @since 1.0.0
	 *
	 * @param string $payload    The raw request body.
	 * @param string $sig_header The Stripe-Signature header value.
	 * @param string $secret     The webhook signing secret.
	 * @return bool True if the signature is valid, false otherwise.
	 */
	public function wprobo_documerge_verify_signature( $payload, $sig_header, $secret ) {
		if ( empty( $payload ) || empty( $sig_header ) || empty( $secret ) ) {
			return false;
		}

		$timestamp  = '';
		$signatures = array();

		// Parse the signature header: t=timestamp,v1=sig1,v1=sig2,...
		$parts = explode( ',', $sig_header );

		foreach ( $parts as $part ) {
			$pair = explode( '=', $part, 2 );

			if ( 2 !== count( $pair ) ) {
				continue;
			}

			$key   = trim( $pair[0] );
			$value = trim( $pair[1] );

			if ( 't' === $key ) {
				$timestamp = $value;
			} elseif ( 'v1' === $key ) {
				$signatures[] = $value;
			}
		}

		if ( empty( $timestamp ) || empty( $signatures ) ) {
			return false;
		}

		// Replay protection: reject events older than 5 minutes.
		if ( ( time() - absint( $timestamp ) ) > 300 ) {
			return false;
		}

		$signed_payload = $timestamp . '.' . $payload;
		$expected       = hash_hmac( 'sha256', $signed_payload, $secret );

		foreach ( $signatures as $sig ) {
			if ( hash_equals( $expected, $sig ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Handle a successful payment intent event.
	 *
	 * Performs an idempotency check against the submission status,
	 * then schedules a WP-Cron event with a 5-second delay to
	 * process document generation asynchronously.
	 *
	 * @since 1.0.0
	 *
	 * @param array $intent The payment_intent object from the Stripe event.
	 * @return void
	 */
	public function wprobo_documerge_handle_payment_succeeded( $intent ) {
		$intent_id  = sanitize_text_field( $intent['id'] );
		$submission = $this->wprobo_documerge_find_submission_by_intent( $intent_id );

		if ( ! $submission ) {
			WPRobo_DocuMerge_Logger::wprobo_documerge_log(
				sprintf( 'Payment succeeded but no submission found for intent %s', $intent_id ),
				'warning',
				array( 'intent_id' => $intent_id )
			);
			return;
		}

		// Idempotency: skip if already processed.
		if ( in_array( $submission->status, array( 'completed', 'processing' ), true ) ) {
			return;
		}

		wp_schedule_single_event(
			time() + 5,
			'wprobo_documerge_process_payment_success',
			array( $intent_id )
		);

		WPRobo_DocuMerge_Logger::wprobo_documerge_log(
			sprintf( 'Payment succeeded for intent %s, cron scheduled', $intent_id ),
			'info',
			array(
				'intent_id'     => $intent_id,
				'submission_id' => $submission->id,
			)
		);
	}

	/**
	 * Handle a failed payment intent event.
	 *
	 * Locates the submission by Stripe intent ID and updates its
	 * status to 'payment_failed' with the error message from Stripe.
	 *
	 * @since 1.0.0
	 *
	 * @param array $intent The payment_intent object from the Stripe event.
	 * @return void
	 */
	public function wprobo_documerge_handle_payment_failed( $intent ) {
		global $wpdb;

		$intent_id  = sanitize_text_field( $intent['id'] );
		$submission = $this->wprobo_documerge_find_submission_by_intent( $intent_id );

		if ( ! $submission ) {
			WPRobo_DocuMerge_Logger::wprobo_documerge_log(
				sprintf( 'Payment failed but no submission found for intent %s', $intent_id ),
				'warning',
				array( 'intent_id' => $intent_id )
			);
			return;
		}

		$error_msg = isset( $intent['last_payment_error']['message'] )
			? sanitize_text_field( $intent['last_payment_error']['message'] )
			: __( 'Payment failed', 'wprobo-documerge' );

		$submissions_table = $wpdb->prefix . 'wprdm_submissions';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$submissions_table,
			array(
				'status'         => 'payment_failed',
				'payment_status' => 'failed',
				'error_log'      => $error_msg,
				'updated_at'     => current_time( 'mysql' ),
			),
			array( 'id' => absint( $submission->id ) ),
			array( '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);

		/**
		 * Fires after a payment failure has been recorded.
		 *
		 * @since 1.1.0
		 *
		 * @param int    $submission_id The submission ID.
		 * @param string $error_msg     The error message from Stripe.
		 * @param string $intent_id     The Stripe PaymentIntent ID.
		 */
		do_action( 'wprobo_documerge_payment_failed_hook', $submission->id, $error_msg, $intent_id );

		WPRobo_DocuMerge_Logger::wprobo_documerge_log(
			sprintf( 'Payment failed for intent %s: %s', $intent_id, $error_msg ),
			'error',
			array(
				'intent_id'     => $intent_id,
				'submission_id' => $submission->id,
				'error'         => $error_msg,
			)
		);
	}

	/**
	 * Handle a charge refund event.
	 *
	 * Locates the submission by the charge's payment_intent ID and
	 * updates the payment status to 'refunded'. Does not delete any
	 * generated documents.
	 *
	 * @since 1.0.0
	 *
	 * @param array $charge The charge object from the Stripe event.
	 * @return void
	 */
	public function wprobo_documerge_handle_refund( $charge ) {
		global $wpdb;

		if ( empty( $charge['payment_intent'] ) ) {
			WPRobo_DocuMerge_Logger::wprobo_documerge_log(
				'Refund received but charge has no payment_intent',
				'warning',
				array( 'charge_id' => isset( $charge['id'] ) ? $charge['id'] : '' )
			);
			return;
		}

		$intent_id  = sanitize_text_field( $charge['payment_intent'] );
		$submission = $this->wprobo_documerge_find_submission_by_intent( $intent_id );

		if ( ! $submission ) {
			WPRobo_DocuMerge_Logger::wprobo_documerge_log(
				sprintf( 'Refund received but no submission found for intent %s', $intent_id ),
				'warning',
				array( 'intent_id' => $intent_id )
			);
			return;
		}

		$submissions_table = $wpdb->prefix . 'wprdm_submissions';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$submissions_table,
			array(
				'payment_status' => 'refunded',
				'updated_at'     => current_time( 'mysql' ),
			),
			array( 'id' => absint( $submission->id ) ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		/**
		 * Fires after a payment refund has been processed.
		 *
		 * @since 1.1.0
		 *
		 * @param int    $submission_id  The submission ID.
		 * @param float  $payment_amount The original payment amount.
		 * @param string $intent_id      The Stripe PaymentIntent ID.
		 */
		do_action( 'wprobo_documerge_payment_refunded', $submission->id, floatval( $submission->payment_amount ), $intent_id );

		WPRobo_DocuMerge_Logger::wprobo_documerge_log(
			sprintf( 'Refund processed for intent %s, submission #%d', $intent_id, $submission->id ),
			'info',
			array(
				'intent_id'     => $intent_id,
				'submission_id' => $submission->id,
			)
		);
	}

	/**
	 * Process a successful payment via WP-Cron.
	 *
	 * This is the cron callback scheduled by wprobo_documerge_handle_payment_succeeded().
	 * Performs idempotency checks, updates the submission status, generates
	 * the document, delivers it, and fires a completion action hook.
	 *
	 * @since 1.0.0
	 *
	 * @param string $intent_id The Stripe Payment Intent ID.
	 * @return void
	 */
	public static function wprobo_documerge_process_payment_cron( $intent_id ) {
		global $wpdb;

		$intent_id         = sanitize_text_field( $intent_id );
		$submissions_table = $wpdb->prefix . 'wprdm_submissions';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$submission = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$submissions_table} WHERE stripe_intent_id = %s",
				$intent_id
			)
		);

		if ( ! $submission ) {
			WPRobo_DocuMerge_Logger::wprobo_documerge_log(
				sprintf( 'Cron: no submission found for intent %s', $intent_id ),
				'error',
				array( 'intent_id' => $intent_id )
			);
			return;
		}

		// Idempotency: only process submissions in pending_payment status.
		if ( 'pending_payment' !== $submission->status ) {
			WPRobo_DocuMerge_Logger::wprobo_documerge_log(
				sprintf( 'Cron: submission #%d already processed (status: %s)', $submission->id, $submission->status ),
				'info',
				array(
					'submission_id' => $submission->id,
					'status'        => $submission->status,
				)
			);
			return;
		}

		// Update status to processing and mark payment as paid.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$submissions_table,
			array(
				'payment_status' => 'paid',
				'status'         => 'processing',
				'updated_at'     => current_time( 'mysql' ),
			),
			array( 'id' => absint( $submission->id ) ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		/**
		 * Fires after a payment has been confirmed and the submission updated.
		 *
		 * @since 1.1.0
		 *
		 * @param int    $submission_id   The submission ID.
		 * @param float  $payment_amount  The payment amount.
		 * @param string $payment_currency The payment currency code.
		 * @param string $intent_id       The Stripe PaymentIntent ID.
		 */
		do_action(
			'wprobo_documerge_payment_received',
			$submission->id,
			floatval( $submission->payment_amount ),
			$submission->payment_currency,
			$intent_id
		);

		/**
		 * Fires after a Stripe payment has been confirmed successful.
		 *
		 * This hook runs during the WP-Cron callback after the payment
		 * intent succeeds, just before document generation begins.
		 *
		 * @since 1.2.0
		 *
		 * @param int    $submission_id  The submission ID.
		 * @param float  $payment_amount The payment amount.
		 * @param string $currency       The ISO 4217 currency code.
		 * @param string $intent_id      The Stripe PaymentIntent ID.
		 */
		do_action( 'wprobo_documerge_payment_succeeded', $submission->id, floatval( $submission->payment_amount ), $submission->payment_currency, $intent_id );

		// Generate document.
		$generator = new WPRobo_DocuMerge_Document_Generator();
		$result    = $generator->wprobo_documerge_generate( $submission->id );

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$submissions_table,
				array(
					'status'     => 'error',
					'error_log'  => sanitize_textarea_field( $result->get_error_message() ),
					'updated_at' => current_time( 'mysql' ),
				),
				array( 'id' => absint( $submission->id ) ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);

			WPRobo_DocuMerge_Logger::wprobo_documerge_log(
				sprintf( 'Cron: document generation failed for submission #%d: %s', $submission->id, $result->get_error_message() ),
				'error',
				array(
					'submission_id' => $submission->id,
					'error'         => $result->get_error_message(),
				)
			);

			// Notify admin of the error.
			$generator->wprobo_documerge_notify_admin_error( $submission->id, $result->get_error_message() );

			return;
		}

		// Deliver document.
		$delivery = new WPRobo_DocuMerge_Delivery_Engine();
		$delivery->wprobo_documerge_deliver( $submission->id );

		// Send webhook if configured.
		$forms_table = $wpdb->prefix . 'wprdm_forms';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form_row = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT settings FROM {$forms_table} WHERE id = %d",
				absint( $submission->form_id )
			)
		);

		if ( $form_row && ! empty( $form_row->settings ) ) {
			$form_settings = json_decode( $form_row->settings, true );
			$webhook_url   = isset( $form_settings['webhook_url'] ) ? esc_url_raw( $form_settings['webhook_url'] ) : '';

			if ( ! empty( $webhook_url ) ) {
				$webhook_sender = new \WPRobo\DocuMerge\Document\WPRobo_DocuMerge_Webhook_Sender();
				$webhook_sender->wprobo_documerge_send( $submission->id, $submission->form_id, $webhook_url );
			}
		}

		// Mark as completed.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$submissions_table,
			array(
				'status'     => 'completed',
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => absint( $submission->id ) ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		/**
		 * Fires after a paid document has been generated and delivered.
		 *
		 * @since 1.0.0
		 *
		 * @param int $submission_id The submission ID.
		 */
		do_action( 'wprobo_documerge_payment_document_delivered', $submission->id );

		WPRobo_DocuMerge_Logger::wprobo_documerge_log(
			sprintf( 'Cron: document generated and delivered for submission #%d', $submission->id ),
			'info',
			array( 'submission_id' => $submission->id )
		);
	}

	/**
	 * Find a submission by its Stripe Payment Intent ID.
	 *
	 * Queries the submissions table for a row matching the given
	 * stripe_intent_id value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $intent_id The Stripe Payment Intent ID.
	 * @return object|null The submission row object, or null if not found.
	 */
	public function wprobo_documerge_find_submission_by_intent( $intent_id ) {
		global $wpdb;

		$submissions_table = $wpdb->prefix . 'wprdm_submissions';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$submissions_table} WHERE stripe_intent_id = %s",
				sanitize_text_field( $intent_id )
			)
		);
	}

	/**
	 * AJAX handler: Retry a failed submission.
	 *
	 * Admin-only endpoint that allows retrying document generation and
	 * delivery for submissions that encountered an error. Only submissions
	 * with an 'error' status can be retried; payment failures require
	 * the customer to resubmit.
	 *
	 * @since 1.0.0
	 *
	 * @return void Outputs JSON response and terminates.
	 */
	public static function wprobo_documerge_ajax_retry_submission() {
		check_ajax_referer( 'wprobo_documerge_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to perform this action.', 'wprobo-documerge' ),
				)
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$submission_id = isset( $_POST['submission_id'] ) ? absint( $_POST['submission_id'] ) : 0;

		if ( 0 === $submission_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid submission ID.', 'wprobo-documerge' ),
				)
			);
		}

		global $wpdb;

		$submissions_table = $wpdb->prefix . 'wprdm_submissions';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$submission = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$submissions_table} WHERE id = %d",
				$submission_id
			)
		);

		if ( ! $submission ) {
			wp_send_json_error(
				array(
					'message' => __( 'Submission not found.', 'wprobo-documerge' ),
				)
			);
		}

		if ( 'payment_failed' === $submission->status ) {
			wp_send_json_error(
				array(
					'message' => __( 'Cannot retry — payment was not received. The customer must submit again.', 'wprobo-documerge' ),
				)
			);
		}

		if ( 'error' !== $submission->status ) {
			wp_send_json_error(
				array(
					'message' => __( 'This submission cannot be retried.', 'wprobo-documerge' ),
				)
			);
		}

		// Increment retry count.
		$retry_count = absint( $submission->retry_count ) + 1;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$submissions_table,
			array(
				'retry_count' => $retry_count,
				'status'      => 'processing',
				'updated_at'  => current_time( 'mysql' ),
			),
			array( 'id' => $submission_id ),
			array( '%d', '%s', '%s' ),
			array( '%d' )
		);

		// Generate document.
		$generator = new WPRobo_DocuMerge_Document_Generator();
		$result    = $generator->wprobo_documerge_generate( $submission_id );

		if ( is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$submissions_table,
				array(
					'status'     => 'error',
					'error_log'  => sanitize_textarea_field( $result->get_error_message() ),
					'updated_at' => current_time( 'mysql' ),
				),
				array( 'id' => $submission_id ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);

			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				)
			);
		}

		// Deliver document.
		$delivery = new WPRobo_DocuMerge_Delivery_Engine();
		$delivery->wprobo_documerge_deliver( $submission_id );

		// Mark as completed.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$submissions_table,
			array(
				'status'     => 'completed',
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $submission_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		wp_send_json_success(
			array(
				'message' => __( 'Document regenerated and delivered successfully.', 'wprobo-documerge' ),
			)
		);
	}

	/**
	 * Register WordPress hooks for webhook processing.
	 *
	 * Registers the WP-Cron callback for processing successful payments
	 * and the admin AJAX handler for retrying failed submissions.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wprobo_documerge_init_hooks() {
		add_action( 'wprobo_documerge_process_payment_success', array( __CLASS__, 'wprobo_documerge_process_payment_cron' ) );
		add_action( 'wp_ajax_wprobo_documerge_retry_submission', array( __CLASS__, 'wprobo_documerge_ajax_retry_submission' ) );
	}
}
