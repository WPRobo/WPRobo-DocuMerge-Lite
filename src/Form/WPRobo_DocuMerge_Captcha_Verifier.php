<?php
/**
 * Server-side captcha verification for WPRobo DocuMerge.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.0.0
 */

namespace WPRobo\DocuMerge\Form;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Captcha_Verifier
 *
 * Provides static methods for server-side captcha token verification
 * against reCAPTCHA v2, reCAPTCHA v3, and hCaptcha services.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Captcha_Verifier {

	/**
	 * Verifies a captcha token based on the configured captcha type.
	 *
	 * @since 1.0.0
	 *
	 * @param string $token     The captcha response token.
	 * @param string $remote_ip Optional. The remote IP address. Defaults to $_SERVER['REMOTE_ADDR'].
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public static function wprobo_documerge_verify( $token, $remote_ip = '' ) {
		$captcha_type = get_option( 'wprobo_documerge_captcha_type', '' );

		if ( empty( $captcha_type ) || 'none' === $captcha_type ) {
			return true;
		}

		if ( empty( $token ) ) {
			return new \WP_Error(
				'captcha_missing',
				__( 'Please complete the captcha.', 'wprobo-documerge' )
			);
		}

		if ( empty( $remote_ip ) ) {
			$remote_ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';

			if ( ! empty( $remote_ip ) && false === filter_var( $remote_ip, FILTER_VALIDATE_IP ) ) {
				$remote_ip = '';
			}
		}

		switch ( $captcha_type ) {
			case 'recaptcha_v2':
				$verified = self::wprobo_documerge_verify_recaptcha_v2( $token, $remote_ip );
				break;

			case 'recaptcha_v3':
				$verified = self::wprobo_documerge_verify_recaptcha_v3( $token, $remote_ip );
				break;

			case 'hcaptcha':
				$verified = self::wprobo_documerge_verify_hcaptcha( $token, $remote_ip );
				break;

			default:
				$verified = true;
				break;
		}

		/**
		 * Filters the captcha verification result.
		 *
		 * Allows third-party code to override or augment captcha
		 * verification. The value may be true on success or a WP_Error
		 * on failure.
		 *
		 * @since 1.2.0
		 *
		 * @param true|\WP_Error $verified     The verification result.
		 * @param string         $captcha_type The captcha type (recaptcha_v2, recaptcha_v3, hcaptcha).
		 * @param string         $token        The captcha response token.
		 */
		$verified = apply_filters( 'wprobo_documerge_captcha_verified', $verified, $captcha_type, $token );

		return $verified;
	}

	/**
	 * Verifies a reCAPTCHA v2 token.
	 *
	 * @since 1.0.0
	 *
	 * @param string $token The reCAPTCHA response token.
	 * @param string $ip    The remote IP address.
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public static function wprobo_documerge_verify_recaptcha_v2( $token, $ip ) {
		$secret = get_option( 'wprobo_documerge_recaptcha_v2_secret_key', '' );

		if ( empty( $secret ) ) {
			return new \WP_Error(
				'captcha_not_configured',
				__( 'reCAPTCHA secret key is not configured.', 'wprobo-documerge' )
			);
		}

		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'timeout' => 10,
				'body'    => array(
					'secret'   => $secret,
					'response' => $token,
					'remoteip' => $ip,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new \WP_Error(
				'captcha_request_failed',
				__( 'Captcha verification request failed.', 'wprobo-documerge' )
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body ) || true !== ( $body['success'] ?? false ) ) {
			return new \WP_Error(
				'captcha_failed',
				__( 'Captcha verification failed.', 'wprobo-documerge' )
			);
		}

		return true;
	}

	/**
	 * Verifies a reCAPTCHA v3 token and checks the score threshold.
	 *
	 * @since 1.0.0
	 *
	 * @param string $token The reCAPTCHA response token.
	 * @param string $ip    The remote IP address.
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public static function wprobo_documerge_verify_recaptcha_v3( $token, $ip ) {
		$secret = get_option( 'wprobo_documerge_recaptcha_v3_secret_key', '' );

		if ( empty( $secret ) ) {
			return new \WP_Error(
				'captcha_not_configured',
				__( 'reCAPTCHA secret key is not configured.', 'wprobo-documerge' )
			);
		}

		$response = wp_remote_post(
			'https://www.google.com/recaptcha/api/siteverify',
			array(
				'timeout' => 10,
				'body'    => array(
					'secret'   => $secret,
					'response' => $token,
					'remoteip' => $ip,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new \WP_Error(
				'captcha_request_failed',
				__( 'Captcha verification request failed.', 'wprobo-documerge' )
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body ) || true !== ( $body['success'] ?? false ) ) {
			return new \WP_Error(
				'captcha_failed',
				__( 'Captcha verification failed.', 'wprobo-documerge' )
			);
		}

		$threshold = (float) get_option( 'wprobo_documerge_recaptcha_v3_threshold', 0.5 );
		$score     = isset( $body['score'] ) ? (float) $body['score'] : 0.0;

		if ( $score < $threshold ) {
			return new \WP_Error(
				'captcha_score_low',
				__( 'Automated submission detected.', 'wprobo-documerge' )
			);
		}

		return true;
	}

	/**
	 * Verifies an hCaptcha token.
	 *
	 * @since 1.0.0
	 *
	 * @param string $token The hCaptcha response token.
	 * @param string $ip    The remote IP address.
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public static function wprobo_documerge_verify_hcaptcha( $token, $ip ) {
		$secret = get_option( 'wprobo_documerge_hcaptcha_secret_key', '' );

		if ( empty( $secret ) ) {
			return new \WP_Error(
				'captcha_not_configured',
				__( 'hCaptcha secret key is not configured.', 'wprobo-documerge' )
			);
		}

		$response = wp_remote_post(
			'https://hcaptcha.com/siteverify',
			array(
				'timeout' => 10,
				'body'    => array(
					'secret'   => $secret,
					'response' => $token,
					'remoteip' => $ip,
				),
			)
		);

		if ( is_wp_error( $response ) ) {
			return new \WP_Error(
				'captcha_request_failed',
				__( 'Captcha verification request failed.', 'wprobo-documerge' )
			);
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( empty( $body ) || true !== ( $body['success'] ?? false ) ) {
			return new \WP_Error(
				'captcha_failed',
				__( 'Captcha verification failed.', 'wprobo-documerge' )
			);
		}

		return true;
	}
}
