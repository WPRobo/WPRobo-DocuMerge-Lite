<?php
/**
 * Stripe payment handler for WPRobo DocuMerge.
 *
 * Manages Stripe Payment Intents, API key retrieval, connection
 * testing, and AJAX endpoints for frontend payment processing.
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

use WPRobo\DocuMerge\Helpers\WPRobo_DocuMerge_Encryptor;
use WPRobo\DocuMerge\Document\WPRobo_DocuMerge_Delivery_Engine;

/**
 * Class WPRobo_DocuMerge_Stripe_Handler
 *
 * Handles Stripe payment integration including creating Payment Intents,
 * managing API keys, verifying configuration, and processing AJAX
 * requests for payment flows.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Stripe_Handler {

	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 * @var WPRobo_DocuMerge_Stripe_Handler|null
	 */
	private static $instance = null;

	/**
	 * Returns the singleton instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @return WPRobo_DocuMerge_Stripe_Handler
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor to enforce singleton pattern.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {}

	/**
	 * Create a Stripe Payment Intent for a submission.
	 *
	 * Sends a POST request to the Stripe API to create a new Payment Intent
	 * with the specified amount, currency, and submission metadata. Uses an
	 * idempotency key derived from the submission ID to prevent duplicate charges.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $amount_cents  The payment amount in the smallest currency unit (e.g. cents).
	 * @param string $currency      The three-letter ISO currency code (e.g. 'usd').
	 * @param int    $submission_id The submission ID to associate with this payment.
	 * @return array|\WP_Error The parsed Payment Intent response array on success, or WP_Error on failure.
	 */
	public function wprobo_documerge_create_payment_intent( $amount_cents, $currency, $submission_id ) {
		$secret_key = $this->wprobo_documerge_get_stripe_key( 'secret' );

		if ( is_wp_error( $secret_key ) ) {
			return $secret_key;
		}

		global $wpdb;

		$submissions_table = $wpdb->prefix . 'wprdm_submissions';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$submission = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT form_id FROM {$submissions_table} WHERE id = %d",
				$submission_id
			)
		);

		$form_id = $submission ? absint( $submission->form_id ) : 0;

		/**
		 * Fires before a Stripe PaymentIntent is created.
		 *
		 * @since 1.1.0
		 *
		 * @param int    $submission_id The submission ID.
		 * @param int    $amount_cents  The payment amount in cents.
		 * @param string $currency      The three-letter ISO currency code.
		 */
		do_action( 'wprobo_documerge_before_payment_intent', $submission_id, $amount_cents, $currency );

		$body = array(
			'amount'                  => absint( $amount_cents ),
			'currency'                => sanitize_key( $currency ),
			'metadata[form_id]'       => $form_id,
			'metadata[submission_id]' => absint( $submission_id ),
			'metadata[plugin]'        => 'wprobo-documerge',
			'metadata[site_url]'      => home_url(),
		);

		/**
		 * Filters the Stripe PaymentIntent API request body.
		 *
		 * Sensitive keys (secret_key, api_key) are stripped after filtering.
		 *
		 * @since 1.1.0
		 *
		 * @param array $body          The request body parameters.
		 * @param int   $submission_id The submission ID.
		 */
		$body = apply_filters( 'wprobo_documerge_payment_intent_args', $body, $submission_id );
		unset( $body['secret_key'], $body['api_key'] );

		$response = wp_remote_post(
			'https://api.stripe.com/v1/payment_intents',
			array(
				'timeout' => 30,
				'headers' => array(
					'Authorization'   => 'Bearer ' . $secret_key,
					'Content-Type'    => 'application/x-www-form-urlencoded',
					'Idempotency-Key' => 'wdm-' . absint( $submission_id ),
				),
				'body'    => $body,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! is_array( $body ) ) {
			return new \WP_Error(
				'stripe_error',
				__( 'Invalid response from Stripe.', 'wprobo-documerge' )
			);
		}

		if ( isset( $body['error'] ) ) {
			return new \WP_Error(
				'stripe_error',
				isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'An unknown Stripe error occurred.', 'wprobo-documerge' )
			);
		}

		return $body;
	}

	/**
	 * Retrieve and decrypt a Stripe API key.
	 *
	 * Fetches the encrypted key from the WordPress options table based on
	 * the current mode (test or live) and key type, then decrypts it using
	 * the Encryptor helper.
	 *
	 * @since 1.0.0
	 *
	 * @param string $type The key type: 'publishable' or 'secret'.
	 * @return string|\WP_Error The decrypted key string on success, or WP_Error if not configured.
	 */
	public function wprobo_documerge_get_stripe_key( $type ) {
		$mode        = $this->wprobo_documerge_get_mode();
		$option_name = 'wprobo_documerge_stripe_' . $mode . '_' . $type . '_key';
		$encrypted   = get_option( $option_name, '' );
		$decrypted   = WPRobo_DocuMerge_Encryptor::wprobo_documerge_decrypt( $encrypted );

		if ( empty( $decrypted ) ) {
			return new \WP_Error(
				'stripe_not_configured',
				/* translators: %s: key type (publishable or secret) */
				sprintf( __( 'Stripe %s key is not configured.', 'wprobo-documerge' ), $type )
			);
		}

		return $decrypted;
	}

	/**
	 * Check whether Stripe is fully configured for the current mode.
	 *
	 * Verifies that both the publishable key and the secret key are
	 * present and can be decrypted for the active Stripe mode.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if both keys are configured, false otherwise.
	 */
	public function wprobo_documerge_is_configured() {
		$publishable = $this->wprobo_documerge_get_stripe_key( 'publishable' );
		$secret      = $this->wprobo_documerge_get_stripe_key( 'secret' );

		if ( is_wp_error( $publishable ) || is_wp_error( $secret ) ) {
			return false;
		}

		if ( empty( $publishable ) || empty( $secret ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the current Stripe operating mode.
	 *
	 * Returns either 'test' or 'live' based on the plugin setting.
	 * Defaults to 'test' if not configured.
	 *
	 * @since 1.0.0
	 *
	 * @return string The Stripe mode: 'test' or 'live'.
	 */
	public function wprobo_documerge_get_mode() {
		return get_option( 'wprobo_documerge_stripe_mode', 'test' );
	}

	/**
	 * Test the Stripe API connection using the current secret key.
	 *
	 * Makes a lightweight GET request to the Stripe Payment Methods
	 * endpoint to verify that the configured secret key is valid and
	 * the API is reachable.
	 *
	 * @since 1.0.0
	 *
	 * @return true|\WP_Error True on success, or WP_Error on failure.
	 */
	public function wprobo_documerge_test_connection() {
		$secret_key = $this->wprobo_documerge_get_stripe_key( 'secret' );

		if ( is_wp_error( $secret_key ) ) {
			return $secret_key;
		}

		$response = wp_remote_get(
			'https://api.stripe.com/v1/payment_methods?limit=1&type=card',
			array(
				'timeout' => 10,
				'headers' => array(
					'Authorization' => 'Bearer ' . $secret_key,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( isset( $body['error'] ) ) {
			return new \WP_Error(
				'stripe_error',
				isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Stripe connection test failed.', 'wprobo-documerge' )
			);
		}

		return true;
	}

	/**
	 * Get the currency symbol for a given currency code.
	 *
	 * Maps common three-letter ISO currency codes to their display symbols.
	 * Returns the uppercase currency code if no symbol mapping exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $currency The three-letter ISO currency code.
	 * @return string The currency symbol or uppercase currency code.
	 */
	public function wprobo_documerge_get_currency_symbol( $currency ) {
		$symbols = array(
			'usd' => '$',
			'gbp' => '£',
			'eur' => '€',
			'cad' => 'C$',
			'aud' => 'A$',
		);

		$currency_lower = strtolower( $currency );

		if ( isset( $symbols[ $currency_lower ] ) ) {
			return $symbols[ $currency_lower ];
		}

		return strtoupper( $currency );
	}

	/**
	 * Register AJAX hooks for Stripe payment handling.
	 *
	 * Registers WordPress AJAX action handlers for creating payment intents,
	 * testing the Stripe connection, and checking submission payment status.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wprobo_documerge_init_hooks() {
		add_action( 'wp_ajax_wprobo_documerge_create_payment_intent', array( $this, 'wprobo_documerge_ajax_create_payment_intent' ) );
		add_action( 'wp_ajax_nopriv_wprobo_documerge_create_payment_intent', array( $this, 'wprobo_documerge_ajax_create_payment_intent' ) );
		add_action( 'wp_ajax_wprobo_documerge_test_stripe', array( $this, 'wprobo_documerge_ajax_test_stripe' ) );
		add_action( 'wp_ajax_wprobo_documerge_check_status', array( $this, 'wprobo_documerge_ajax_check_status' ) );
		add_action( 'wp_ajax_nopriv_wprobo_documerge_check_status', array( $this, 'wprobo_documerge_ajax_check_status' ) );
	}

	/**
	 * AJAX handler: Create a Stripe Payment Intent for a submission.
	 *
	 * Validates the request, retrieves the submission and form data,
	 * converts the payment amount to cents, creates the Payment Intent,
	 * and returns the client secret for frontend Stripe.js confirmation.
	 *
	 * @since 1.0.0
	 *
	 * @return void Outputs JSON response and terminates.
	 */
	public function wprobo_documerge_ajax_create_payment_intent() {
		check_ajax_referer( 'wprobo_documerge_frontend', 'nonce' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$submission_id = isset( $_POST['submission_id'] ) ? absint( $_POST['submission_id'] ) : 0;

		if ( 0 === $submission_id ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid submission.', 'wprobo-documerge' ),
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

		if ( null === $submission ) {
			wp_send_json_error(
				array(
					'message' => __( 'Submission not found.', 'wprobo-documerge' ),
				)
			);
		}

		if ( 'pending_payment' !== $submission->status ) {
			wp_send_json_error(
				array(
					'message' => __( 'This submission is not awaiting payment.', 'wprobo-documerge' ),
				)
			);
		}

		// Load the form to get payment settings.
		$forms_table = $wpdb->prefix . 'wprdm_forms';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form = $wpdb->get_row(
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				"SELECT * FROM {$forms_table} WHERE id = %d",
				absint( $submission->form_id )
			)
		);

		if ( null === $form ) {
			wp_send_json_error(
				array(
					'message' => __( 'Form not found.', 'wprobo-documerge' ),
				)
			);
		}

		$payment_amount   = isset( $form->payment_amount ) ? $form->payment_amount : 0;
		$payment_currency = isset( $form->payment_currency ) ? $form->payment_currency : 'usd';
		$amount_cents     = (int) round( floatval( $payment_amount ) * 100 );

		if ( $amount_cents <= 0 ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid payment amount.', 'wprobo-documerge' ),
				)
			);
		}

		$result = $this->wprobo_documerge_create_payment_intent( $amount_cents, $payment_currency, $submission_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				)
			);
		}

		// Store the Stripe intent ID on the submission.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$submissions_table,
			array(
				'stripe_intent_id' => sanitize_text_field( $result['id'] ),
				'updated_at'       => current_time( 'mysql' ),
			),
			array( 'id' => $submission_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		wp_send_json_success(
			array(
				'client_secret' => $result['client_secret'],
			)
		);
	}

	/**
	 * AJAX handler: Test the Stripe API connection.
	 *
	 * Admin-only endpoint that verifies the configured Stripe keys can
	 * successfully authenticate with the Stripe API.
	 *
	 * @since 1.0.0
	 *
	 * @return void Outputs JSON response and terminates.
	 */
	public function wprobo_documerge_ajax_test_stripe() {
		check_ajax_referer( 'wprobo_documerge_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to perform this action.', 'wprobo-documerge' ),
				)
			);
		}

		$result = $this->wprobo_documerge_test_connection();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				)
			);
		}

		wp_send_json_success(
			array(
				'message' => __( 'Stripe connection successful.', 'wprobo-documerge' ),
			)
		);
	}

	/**
	 * AJAX handler: Check the status of a submission.
	 *
	 * Frontend endpoint that allows checking the current status of a
	 * submission after payment. Requires a valid status token stored
	 * as a transient to prevent unauthorized access.
	 *
	 * @since 1.0.0
	 *
	 * @return void Outputs JSON response and terminates.
	 */
	public function wprobo_documerge_ajax_check_status() {
		check_ajax_referer( 'wprobo_documerge_frontend', 'nonce' );

		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$submission_id = isset( $_POST['submission_id'] ) ? absint( $_POST['submission_id'] ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
		$token         = isset( $_POST['status_token'] ) ? sanitize_text_field( wp_unslash( $_POST['status_token'] ) ) : '';

		if ( 0 === $submission_id || empty( $token ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid request.', 'wprobo-documerge' ),
				)
			);
		}

		// Verify the status token.
		$stored_token = get_transient( 'wprobo_documerge_status_' . $submission_id );

		if ( empty( $stored_token ) || ! hash_equals( $stored_token, $token ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid or expired status token.', 'wprobo-documerge' ),
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

		if ( null === $submission ) {
			wp_send_json_error(
				array(
					'message' => __( 'Submission not found.', 'wprobo-documerge' ),
				)
			);
		}

		$data = array(
			'status' => $submission->status,
		);

		if ( 'completed' === $submission->status ) {
			$delivery     = new WPRobo_DocuMerge_Delivery_Engine();
			$download     = $delivery->wprobo_documerge_prepare_download( $submission_id );

			if ( ! is_wp_error( $download ) && is_array( $download ) && isset( $download['download_url'] ) ) {
				$data['download_url'] = esc_url( $download['download_url'] );
			}
		}

		wp_send_json_success( $data );
	}
}
