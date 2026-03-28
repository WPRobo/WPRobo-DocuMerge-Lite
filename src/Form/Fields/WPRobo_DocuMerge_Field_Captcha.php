<?php
/**
 * Captcha field type for WPRobo DocuMerge form builder.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.0.0
 */

namespace WPRobo\DocuMerge\Form\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Field_Captcha
 *
 * Handles captcha spam-protection fields within the DocuMerge form builder.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Field_Captcha {

	/**
	 * Returns the field type slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_type() {
		return 'captcha';
	}

	/**
	 * Returns the translated human-readable label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_label() {
		return __( 'Spam Protection', 'wprobo-documerge' );
	}

	/**
	 * Returns the dashicon class name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_icon() {
		return 'dashicons-shield';
	}

	/**
	 * Returns the default field configuration array.
	 *
	 * @since 1.0.0
	 *
	 * @return array
	 */
	public function wprobo_documerge_get_default_config() {
		$config = array(
			'id'         => '',
			'type'       => 'captcha',
			'label'      => 'Spam Protection',
			'name'       => 'captcha',
			'help_text'  => '',
			'required'   => true,
			'width'      => 'full',
			'step'       => 1,
			'conditions' => array(),
		);

		/** This filter is documented in src/Form/Fields/WPRobo_DocuMerge_Field_Text.php */
		return apply_filters( 'wprobo_documerge_field_default_config', $config, $this->wprobo_documerge_get_type() );
	}

	/**
	 * Renders the admin settings panel HTML for this field.
	 *
	 * Captcha configuration is managed in Settings, so this panel only
	 * displays an informational note.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field_data The field configuration data.
	 * @return string
	 */
	public function wprobo_documerge_render_admin_settings( $field_data ) {
		$html = '';

		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<p class="description">';
		$html .= esc_html__( 'Captcha type and keys are configured in Settings &rarr; reCAPTCHA tab.', 'wprobo-documerge' );
		$html .= '</p>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Renders the frontend form HTML for this field.
	 *
	 * Output depends on the active captcha type configured in plugin settings.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $field_data The field configuration data.
	 * @param string $value      The current field value.
	 * @return string
	 */
	public function wprobo_documerge_render_frontend( $field_data, $value = '' ) {
		$captcha_type = get_option( 'wprobo_documerge_captcha_type', 'recaptcha_v2' );

		switch ( $captcha_type ) {
			case 'recaptcha_v2':
				$site_key = get_option( 'wprobo_documerge_recaptcha_v2_site_key', '' );

				if ( empty( $site_key ) ) {
					return '<div class="wdm-field-group"><p class="wdm-notice wdm-notice-warning">' . esc_html__( 'reCAPTCHA not configured.', 'wprobo-documerge' ) . '</p></div>';
				}

				$html  = '<div class="wdm-field-group" data-captcha-required="1">';
				$html .= '<div class="g-recaptcha" data-sitekey="' . esc_attr( $site_key ) . '" data-callback="wdmCaptchaVerified" data-expired-callback="wdmCaptchaExpired"></div>';
				$html .= '<input type="hidden" name="wdm_captcha_type" value="recaptcha_v2">';
				$html .= '</div>';

				return $html;

			case 'recaptcha_v3':
				$site_key = get_option( 'wprobo_documerge_recaptcha_v3_site_key', '' );

				$html  = '<div class="wdm-field-group">';
				$html .= '<input type="hidden" name="wdm_recaptcha_token" id="wdm-recaptcha-v3-token" value="">';
				$html .= '<input type="hidden" name="wdm_captcha_type" value="recaptcha_v3">';
				$html .= '</div>';

				return $html;

			case 'hcaptcha':
				$site_key = get_option( 'wprobo_documerge_hcaptcha_site_key', '' );

				$html  = '<div class="wdm-field-group" data-captcha-required="1">';
				$html .= '<div class="h-captcha" data-sitekey="' . esc_attr( $site_key ) . '" data-callback="wdmCaptchaVerified" data-expired-callback="wdmCaptchaExpired"></div>';
				$html .= '<input type="hidden" name="wdm_captcha_type" value="hcaptcha">';
				$html .= '</div>';

				return $html;

			default:
				return '';
		}
	}

	/**
	 * Sanitizes the submitted captcha token value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value      The raw submitted value.
	 * @param array $field_data The field configuration data.
	 * @return string
	 */
	public function wprobo_documerge_sanitize( $value, $field_data ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Validates the submitted captcha value.
	 *
	 * Always returns true because actual verification is handled
	 * server-side by WPRobo_DocuMerge_Captcha_Verifier.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value      The sanitized value.
	 * @param array $field_data The field configuration data.
	 * @return true
	 */
	public function wprobo_documerge_validate( $value, $field_data ) {
		return true;
	}
}
