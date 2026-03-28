<?php
/**
 * Password field type for WPRobo DocuMerge form builder.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.3.0
 */

namespace WPRobo\DocuMerge\Form\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Field_Password
 *
 * Handles password input fields with optional visibility toggle and
 * confirmation field within the DocuMerge form builder.
 *
 * @since 1.3.0
 */
class WPRobo_DocuMerge_Field_Password {

	/**
	 * Returns the field type slug.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_type() {
		return 'password';
	}

	/**
	 * Returns the translated human-readable label.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_label() {
		return __( 'Password', 'wprobo-documerge' );
	}

	/**
	 * Returns the dashicon class name.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_icon() {
		return 'dashicons-lock';
	}

	/**
	 * Returns the default field configuration array.
	 *
	 * @since 1.3.0
	 *
	 * @return array
	 */
	public function wprobo_documerge_get_default_config() {
		$config = array(
			'id'               => '',
			'type'             => 'password',
			'label'            => 'Password',
			'name'             => '',
			'placeholder'      => '',
			'help_text'        => '',
			'required'         => false,
			'width'            => 'full',
			'min_length'       => '',
			'max_length'       => '',
			'confirm_password' => false,
			'show_strength'    => true,
			'error_message'    => '',
			'conditions'       => array(),
		);

		/** This filter is documented in src/Form/Fields/WPRobo_DocuMerge_Field_Text.php */
		return apply_filters( 'wprobo_documerge_field_default_config', $config, $this->wprobo_documerge_get_type() );
	}

	/**
	 * Renders the admin settings panel HTML for this field.
	 *
	 * @since 1.3.0
	 *
	 * @param array $field_data The field configuration data.
	 * @return string
	 */
	public function wprobo_documerge_render_admin_settings( $field_data ) {
		$field_data = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );

		$id               = esc_attr( $field_data['id'] );
		$label            = esc_attr( $field_data['label'] );
		$placeholder      = esc_attr( $field_data['placeholder'] );
		$help_text        = esc_attr( $field_data['help_text'] );
		$required         = ! empty( $field_data['required'] ) ? 'checked' : '';
		$width            = esc_attr( $field_data['width'] );
		$min_length       = esc_attr( $field_data['min_length'] );
		$max_length       = esc_attr( $field_data['max_length'] );
		$confirm_password = ! empty( $field_data['confirm_password'] ) ? 'checked' : '';
		$error_message    = esc_attr( $field_data['error_message'] );

		$html = '';

		// Label.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Label', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="text" data-setting="label" class="wdm-builder-setting-input" value="' . $label . '">';
		$html .= '</div>';

		// Placeholder.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Placeholder', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="text" data-setting="placeholder" class="wdm-builder-setting-input" value="' . $placeholder . '">';
		$html .= '</div>';

		// Help Text.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Help Text', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="text" data-setting="help_text" class="wdm-builder-setting-input" value="' . $help_text . '">';
		$html .= '</div>';

		// Required.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Required', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="checkbox" data-setting="required" class="wdm-builder-setting-input" ' . $required . '>';
		$html .= '</div>';

		// Min Length.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Min Length', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="number" data-setting="min_length" class="wdm-builder-setting-input" value="' . $min_length . '">';
		$html .= '</div>';

		// Max Length.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Max Length', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="number" data-setting="max_length" class="wdm-builder-setting-input" value="' . $max_length . '">';
		$html .= '</div>';

		// Confirm Password.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label><input type="checkbox" data-setting="confirm_password" class="wdm-builder-setting-input" ' . $confirm_password . '> ';
		$html .= esc_html__( 'Require password confirmation', 'wprobo-documerge' ) . '</label>';
		$html .= '<span class="wdm-description">' . esc_html__( 'Shows a second "Confirm Password" field.', 'wprobo-documerge' ) . '</span>';
		$html .= '</div>';

		// Show Strength Bar.
		$show_strength = ! empty( $field_data['show_strength'] ) ? 'checked' : '';
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label><input type="checkbox" data-setting="show_strength" class="wdm-builder-setting-input" ' . $show_strength . '> ';
		$html .= esc_html__( 'Show password strength indicator', 'wprobo-documerge' ) . '</label>';
		$html .= '</div>';

		// Custom Error Message.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Custom Error Message', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="text" data-setting="error_message" class="wdm-builder-setting-input wdm-input" value="' . $error_message . '" placeholder="' . esc_attr__( 'Leave blank for default', 'wprobo-documerge' ) . '">';
		$html .= '<span class="wdm-description">' . esc_html__( 'Optional. Shown when validation fails.', 'wprobo-documerge' ) . '</span>';
		$html .= '</div>';

		// Width.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Width', 'wprobo-documerge' ) . '</label>';
		$html .= '<div class="wdm-width-selector">';
		$html .= '<label><input type="radio" name="width_' . $id . '" data-setting="width" value="full"' . checked( $width, 'full', false ) . '> ' . esc_html__( 'Full', 'wprobo-documerge' ) . '</label>';
		$html .= '<label><input type="radio" name="width_' . $id . '" data-setting="width" value="half"' . checked( $width, 'half', false ) . '> ' . esc_html__( 'Half', 'wprobo-documerge' ) . '</label>';
		$html .= '<label><input type="radio" name="width_' . $id . '" data-setting="width" value="third"' . checked( $width, 'third', false ) . '> ' . esc_html__( 'Third', 'wprobo-documerge' ) . '</label>';
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Renders the frontend form HTML for this field.
	 *
	 * Outputs a password input with a visibility toggle button. When
	 * confirm_password is enabled, a second confirmation input is also
	 * rendered.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $field_data The field configuration data.
	 * @param string $value      The current field value.
	 * @return string
	 */
	public function wprobo_documerge_render_frontend( $field_data, $value = '' ) {
		$field_data = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );

		$id               = esc_attr( $field_data['id'] );
		$name             = esc_attr( $field_data['name'] );
		$label            = esc_html( $field_data['label'] );
		$placeholder      = esc_attr( $field_data['placeholder'] );
		$help_text        = esc_html( $field_data['help_text'] );
		$required         = ! empty( $field_data['required'] ) ? 'required' : '';
		$min_length       = ! empty( $field_data['min_length'] ) ? 'minlength="' . esc_attr( $field_data['min_length'] ) . '"' : '';
		$max_length       = ! empty( $field_data['max_length'] ) ? 'maxlength="' . esc_attr( $field_data['max_length'] ) . '"' : '';
		$confirm_password = ! empty( $field_data['confirm_password'] );

		$html = '<div class="wdm-field-group" data-field-type="password">';
		$html .= '<label for="wdm-field-' . $id . '">' . $label;
		if ( ! empty( $field_data['required'] ) ) {
			$html .= ' <span class="wdm-required">*</span>';
		}
		$html .= '</label>';

		// Password input with toggle.
		$html .= '<div class="wdm-password-wrap">';
		$html .= '<input type="password" id="wdm-field-' . $id . '" name="' . $name . '" class="wdm-input wdm-password-input" placeholder="' . $placeholder . '" ' . $min_length . ' ' . $max_length . ' ' . $required . '>';
		$html .= '<button type="button" class="wdm-password-toggle" aria-label="' . esc_attr__( 'Toggle password visibility', 'wprobo-documerge' ) . '">';
		$html .= '<span class="dashicons dashicons-visibility"></span>';
		$html .= '</button>';
		$html .= '</div>';

		// Password strength bar.
		$show_strength = isset( $field_data['show_strength'] ) ? $field_data['show_strength'] : true;
		if ( $show_strength ) {
			$html .= '<div class="wdm-password-strength wdm-strength-0" id="wdm-strength-' . $id . '">';
			$html .= '<div class="wdm-strength-bar"><div class="wdm-strength-fill"></div></div>';
			$html .= '<span class="wdm-strength-label"></span>';
			$html .= '</div>';
		}

		// Confirm password field.
		if ( $confirm_password ) {
			$html .= '<div class="wdm-password-confirm-wrap">';
			$html .= '<label for="wdm-field-' . $id . '-confirm">' . esc_html__( 'Confirm Password', 'wprobo-documerge' );
			if ( ! empty( $field_data['required'] ) ) {
				$html .= ' <span class="wdm-required">*</span>';
			}
			$html .= '</label>';
			$html .= '<div class="wdm-password-wrap">';
			$html .= '<input type="password" id="wdm-field-' . $id . '-confirm" name="' . $name . '_confirm" class="wdm-input wdm-password-input wdm-password-confirm" placeholder="' . esc_attr__( 'Re-enter your password', 'wprobo-documerge' ) . '" ' . $required . '>';
			$html .= '<button type="button" class="wdm-password-toggle" aria-label="' . esc_attr__( 'Toggle password visibility', 'wprobo-documerge' ) . '">';
			$html .= '<span class="dashicons dashicons-visibility"></span>';
			$html .= '</button>';
			$html .= '</div>';
			$html .= '<span class="wdm-field-error-msg" role="alert"></span>';
			$html .= '</div>';
		}

		if ( ! empty( $field_data['help_text'] ) ) {
			$html .= '<p class="wdm-help-text">' . $help_text . '</p>';
		}
		$html .= '<span class="wdm-field-error-msg" role="alert"></span>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Sanitizes the submitted value.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $value      The raw submitted value.
	 * @param array $field_data The field configuration data.
	 * @return string
	 */
	public function wprobo_documerge_sanitize( $value, $field_data ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Validates the submitted value.
	 *
	 * Checks required, min/max length, and password confirmation match
	 * when confirm_password is enabled.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $value      The sanitized value.
	 * @param array $field_data The field configuration data.
	 * @return true|\WP_Error
	 */
	public function wprobo_documerge_validate( $value, $field_data ) {
		$field_data   = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );
		$custom_error = ! empty( $field_data['error_message'] ) ? $field_data['error_message'] : '';

		// Required check.
		if ( ! empty( $field_data['required'] ) && '' === trim( $value ) ) {
			return new \WP_Error(
				'wprobo_documerge_required',
				'' !== $custom_error
					? esc_html( $custom_error )
					/* translators: %s: field label */
					: sprintf( __( '%s is required.', 'wprobo-documerge' ), $field_data['label'] )
			);
		}

		// Min length check.
		if ( '' !== $value && ! empty( $field_data['min_length'] ) && mb_strlen( $value ) < (int) $field_data['min_length'] ) {
			return new \WP_Error(
				'wprobo_documerge_min_length',
				'' !== $custom_error
					? esc_html( $custom_error )
					/* translators: 1: field label, 2: minimum length */
					: sprintf( __( '%1$s must be at least %2$d characters.', 'wprobo-documerge' ), $field_data['label'], (int) $field_data['min_length'] )
			);
		}

		// Max length check.
		if ( '' !== $value && ! empty( $field_data['max_length'] ) && mb_strlen( $value ) > (int) $field_data['max_length'] ) {
			return new \WP_Error(
				'wprobo_documerge_max_length',
				'' !== $custom_error
					? esc_html( $custom_error )
					/* translators: 1: field label, 2: maximum length */
					: sprintf( __( '%1$s must be no more than %2$d characters.', 'wprobo-documerge' ), $field_data['label'], (int) $field_data['max_length'] )
			);
		}

		// Confirm password match check.
		if ( '' !== $value && ! empty( $field_data['confirm_password'] ) ) {
			$name = isset( $field_data['name'] ) ? sanitize_key( $field_data['name'] ) : '';

			// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce checked in submission handler.
			$confirm_value = isset( $_POST[ $name . '_confirm' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_confirm' ] ) ) : '';
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			if ( $value !== $confirm_value ) {
				return new \WP_Error(
					'wprobo_documerge_password_mismatch',
					'' !== $custom_error
						? esc_html( $custom_error )
						: __( 'Passwords do not match.', 'wprobo-documerge' )
				);
			}
		}

		return true;
	}
}
