<?php
/**
 * Payment field type for WPRobo DocuMerge form builder.
 *
 * Renders a Stripe card element for collecting payment information
 * on the frontend. Payment processing is handled entirely by Stripe.js.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.0.0
 */

namespace WPRobo\DocuMerge\Form\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use WPRobo\DocuMerge\Payment\WPRobo_DocuMerge_Stripe_Handler;

/**
 * Class WPRobo_DocuMerge_Field_Payment
 *
 * Handles the payment field within the DocuMerge form builder. Renders
 * a Stripe card element on the frontend for secure payment collection.
 * Card validation and tokenization are handled client-side by Stripe.js.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Field_Payment {

	/**
	 * Returns the field type slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_type() {
		return 'payment';
	}

	/**
	 * Returns the translated human-readable label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_label() {
		return __( 'Payment', 'wprobo-documerge' );
	}

	/**
	 * Returns the dashicon class name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_icon() {
		return 'dashicons-money-alt';
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
			'type'       => 'payment',
			'label'      => 'Payment',
			'name'       => 'payment',
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
	 * Payment amount and currency are configured at the form level,
	 * so this panel displays an informational note only.
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
		$html .= esc_html__( 'Payment amount and currency are configured in Form Settings.', 'wprobo-documerge' );
		$html .= '</p>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Renders the frontend form HTML for the payment field.
	 *
	 * Outputs the Stripe card element container, payment amount display,
	 * error message placeholder, and a security badge. The actual card
	 * input is mounted by Stripe.js into the card element container.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $field_data The field configuration data.
	 * @param string $value      The current field value (unused for payment fields).
	 * @return string
	 */
	public function wprobo_documerge_render_frontend( $field_data, $value = '' ) {
		$field_data = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );

		// Retrieve payment settings from the form record.
		$payment_amount   = 0;
		$payment_currency = 'usd';

		if ( isset( $field_data['form_payment_amount'] ) ) {
			$payment_amount = floatval( $field_data['form_payment_amount'] );
		}

		if ( isset( $field_data['form_payment_currency'] ) ) {
			$payment_currency = sanitize_key( $field_data['form_payment_currency'] );
		}

		$stripe_handler  = WPRobo_DocuMerge_Stripe_Handler::get_instance();
		$currency_symbol = $stripe_handler->wprobo_documerge_get_currency_symbol( $payment_currency );
		$formatted_amount = number_format( $payment_amount, 2 );

		$html  = '<div class="wdm-field-group wdm-field-width-full">';
		$html .= '<div class="wdm-payment-field" data-payment-enabled="1">';

		// Test mode banner — only visible when Stripe is in test mode.
		$stripe_mode = get_option( 'wprobo_documerge_stripe_mode', 'test' );
		if ( 'test' === $stripe_mode && current_user_can( 'manage_options' ) ) {
			$html .= '<div class="wdm-test-mode-banner">';
			$html .= '<span class="dashicons dashicons-info" style="font-size:14px;width:14px;height:14px;vertical-align:middle;margin-right:4px;margin-bottom:2px;"></span>';
			$html .= esc_html__( 'Stripe is in TEST MODE — no real charges will be made. Use card 4242 4242 4242 4242.', 'wprobo-documerge' );
			$html .= '</div>';
		}

		// Payment header with amount.
		$html .= '<div class="wdm-payment-header">';
		$html .= '<span class="wdm-payment-amount">' . esc_html( $currency_symbol . $formatted_amount ) . '</span>';
		$html .= '<span class="wdm-payment-label">' . esc_html__( 'Payment required', 'wprobo-documerge' ) . '</span>';
		$html .= '</div>';

		// Stripe card element mount point.
		$html .= '<div class="wdm-stripe-card-element" id="wdm-card-element"></div>';

		// Card error message container.
		$html .= '<div class="wdm-stripe-card-error" id="wdm-card-error" role="alert"></div>';

		// Payment footer with security badge.
		$html .= '<div class="wdm-payment-footer">';
		$html .= '<span class="wdm-stripe-badge">' . esc_html__( 'Secured by Stripe', 'wprobo-documerge' ) . '</span>';
		$html .= '</div>';

		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Sanitizes the submitted payment field value.
	 *
	 * Payment data is handled entirely by Stripe.js and never submitted
	 * through the form POST data, so this always returns an empty string.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value      The raw submitted value.
	 * @param array $field_data The field configuration data.
	 * @return string Always returns an empty string.
	 */
	public function wprobo_documerge_sanitize( $value, $field_data ) {
		return '';
	}

	/**
	 * Validates the submitted payment field value.
	 *
	 * Card validation is performed client-side by Stripe.js, so this
	 * method always returns true. Actual payment verification occurs
	 * through the Stripe Payment Intent confirmation flow.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value      The sanitized value.
	 * @param array $field_data The field configuration data.
	 * @return true Always returns true.
	 */
	public function wprobo_documerge_validate( $value, $field_data ) {
		return true;
	}
}
