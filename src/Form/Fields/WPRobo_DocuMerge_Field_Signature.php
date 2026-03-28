<?php
/**
 * Signature field type for WPRobo DocuMerge form builder.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.0.0
 */

namespace WPRobo\DocuMerge\Form\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Field_Signature
 *
 * Handles signature canvas fields within the DocuMerge form builder.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Field_Signature {

	/**
	 * Returns the field type slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_type() {
		return 'signature';
	}

	/**
	 * Returns the translated human-readable label.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_label() {
		return __( 'Signature', 'wprobo-documerge' );
	}

	/**
	 * Returns the dashicon class name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_icon() {
		return 'dashicons-art';
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
			'id'            => '',
			'type'          => 'signature',
			'label'         => 'Signature',
			'name'          => 'signature',
			'help_text'     => 'Draw your signature using your mouse or finger.',
			'required'      => true,
			'width'         => 'full',
			'step'          => 1,
			'error_message' => '',
			'conditions'    => array(),
			'canvas_height' => 200,
			'pen_color'     => '#042157',
			'bg_color'      => '#ffffff',
		);

		/** This filter is documented in src/Form/Fields/WPRobo_DocuMerge_Field_Text.php */
		return apply_filters( 'wprobo_documerge_field_default_config', $config, $this->wprobo_documerge_get_type() );
	}

	/**
	 * Renders the admin settings panel HTML for this field.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field_data The field configuration data.
	 * @return string
	 */
	public function wprobo_documerge_render_admin_settings( $field_data ) {
		$field_data = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );

		$id            = esc_attr( $field_data['id'] );
		$label         = esc_attr( $field_data['label'] );
		$help_text     = esc_attr( $field_data['help_text'] );
		$required      = ! empty( $field_data['required'] ) ? 'checked' : '';
		$width         = esc_attr( $field_data['width'] );
		$canvas_height = esc_attr( $field_data['canvas_height'] );
		$pen_color     = esc_attr( $field_data['pen_color'] );
		$bg_color      = esc_attr( $field_data['bg_color'] );

		$html = '';

		// Label.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Label', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="text" data-setting="label" class="wdm-builder-setting-input" value="' . $label . '">';
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

		// Custom Error Message.
		$error_message = esc_attr( $field_data['error_message'] );
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

		// Canvas Height.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Canvas Height (px)', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="number" data-setting="canvas_height" class="wdm-builder-setting-input" value="' . $canvas_height . '">';
		$html .= '</div>';

		// Pen Color.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Pen Color', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="color" data-setting="pen_color" class="wdm-builder-setting-input" value="' . $pen_color . '">';
		$html .= '</div>';

		// Background Color.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Background Color', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="color" data-setting="bg_color" class="wdm-builder-setting-input" value="' . $bg_color . '">';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Renders the frontend form HTML for this field.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $field_data The field configuration data.
	 * @param string $value      The current field value.
	 * @return string
	 */
	public function wprobo_documerge_render_frontend( $field_data, $value = '' ) {
		$field_data = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );

		$name          = esc_attr( $field_data['name'] );
		$label         = esc_html( $field_data['label'] );
		$help_text     = esc_html( $field_data['help_text'] );
		$canvas_height = absint( $field_data['canvas_height'] );
		$pen_color     = esc_attr( $field_data['pen_color'] );
		$bg_color      = esc_attr( $field_data['bg_color'] );

		$html = '<div class="wdm-field-group wdm-field-width-' . esc_attr( $field_data['width'] ) . '">';

		$html .= '<label id="wdm-signature-label-' . $name . '">';
		$html .= $label;
		if ( ! empty( $field_data['required'] ) ) {
			$html .= ' <span class="wdm-required">*</span>';
		}
		$html .= '</label>';

		$html .= '<canvas id="wdm-sig-' . $name . '"';
		$html .= ' class="wdm-signature-canvas"';
		$html .= ' role="img"';
		$html .= ' aria-labelledby="wdm-signature-label-' . $name . '"';
		$html .= ' aria-describedby="wdm-sig-desc-' . $name . '"';
		$html .= ' data-pen-color="' . $pen_color . '"';
		$html .= ' data-bg-color="' . $bg_color . '"';
		$html .= ' style="height:' . esc_attr( $canvas_height ) . 'px;">';
		$html .= '</canvas>';

		$html .= '<p class="wdm-description" id="wdm-sig-desc-' . $name . '">';
		$html .= $help_text;
		$html .= '</p>';

		$html .= '<button type="button" class="wdm-btn wdm-btn-ghost wdm-signature-clear" data-target="wdm-sig-' . $name . '">';
		$html .= esc_html__( 'Clear Signature', 'wprobo-documerge' );
		$html .= '</button>';

		$html .= '<input type="hidden" name="' . $name . '" id="wdm-sig-input-' . $name . '" value="">';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Sanitizes the submitted signature value.
	 *
	 * Validates that the value is a valid base64-encoded PNG data URI
	 * under 2 MB. Does not use sanitize_text_field to preserve base64 data.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value      The raw submitted value.
	 * @param array $field_data The field configuration data.
	 * @return string The sanitized data URI string, or empty string on failure.
	 */
	public function wprobo_documerge_sanitize( $value, $field_data ) {
		if ( ! is_string( $value ) || '' === $value ) {
			return '';
		}

		$prefix = 'data:image/png;base64,';

		// Must start with the expected data URI prefix.
		if ( 0 !== strpos( $value, $prefix ) ) {
			return '';
		}

		// Check total string length < 2 MB.
		if ( strlen( $value ) >= 2 * 1024 * 1024 ) {
			return '';
		}

		// Strip prefix and validate remaining base64.
		$base64_data = substr( $value, strlen( $prefix ) );

		if ( '' === $base64_data || false === base64_decode( $base64_data, true ) ) {
			return '';
		}

		return $value;
	}

	/**
	 * Validates the submitted signature value.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value      The sanitized value.
	 * @param array $field_data The field configuration data.
	 * @return true|\WP_Error
	 */
	public function wprobo_documerge_validate( $value, $field_data ) {
		$field_data   = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );
		$custom_error = ! empty( $field_data['error_message'] ) ? $field_data['error_message'] : '';

		if ( ! empty( $field_data['required'] ) && '' === $value ) {
			return new \WP_Error(
				'wprobo_documerge_required',
				'' !== $custom_error
					? esc_html( $custom_error )
					/* translators: %s: field label */
					: sprintf( __( '%s is required.', 'wprobo-documerge' ), $field_data['label'] )
			);
		}

		return true;
	}
}
