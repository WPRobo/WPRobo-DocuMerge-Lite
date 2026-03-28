<?php
/**
 * Rating/Stars field type for WPRobo DocuMerge form builder.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.3.0
 */

namespace WPRobo\DocuMerge\Form\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Field_Rating
 *
 * Handles star rating input fields within the DocuMerge form builder.
 * Renders clickable star icons that set a hidden input value.
 *
 * @since 1.3.0
 */
class WPRobo_DocuMerge_Field_Rating {

	/**
	 * Returns the field type slug.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_type() {
		return 'rating';
	}

	/**
	 * Returns the translated human-readable label.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_label() {
		return __( 'Rating', 'wprobo-documerge' );
	}

	/**
	 * Returns the dashicon class name.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_icon() {
		return 'dashicons-star-filled';
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
			'id'            => '',
			'type'          => 'rating',
			'label'         => 'Rating',
			'name'          => '',
			'help_text'     => '',
			'required'      => false,
			'width'         => 'full',
			'max_stars'     => 5,
			'error_message' => '',
			'conditions'    => array(),
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

		$id            = esc_attr( $field_data['id'] );
		$label         = esc_attr( $field_data['label'] );
		$help_text     = esc_attr( $field_data['help_text'] );
		$required      = ! empty( $field_data['required'] ) ? 'checked' : '';
		$width         = esc_attr( $field_data['width'] );
		$max_stars     = absint( $field_data['max_stars'] );
		$error_message = esc_attr( $field_data['error_message'] );

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

		// Max Stars.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Max Stars', 'wprobo-documerge' ) . '</label>';
		$html .= '<select data-setting="max_stars" class="wdm-builder-setting-input">';
		$html .= '<option value="3"' . selected( $max_stars, 3, false ) . '>3</option>';
		$html .= '<option value="5"' . selected( $max_stars, 5, false ) . '>5</option>';
		$html .= '<option value="10"' . selected( $max_stars, 10, false ) . '>10</option>';
		$html .= '</select>';
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
	 * Outputs clickable star icons (dashicons-star-empty) with a hidden
	 * input that stores the selected value. JavaScript on the frontend
	 * handles click events to fill stars and update the hidden input.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $field_data The field configuration data.
	 * @param string $value      The current field value.
	 * @return string
	 */
	public function wprobo_documerge_render_frontend( $field_data, $value = '' ) {
		$field_data = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );

		$id        = esc_attr( $field_data['id'] );
		$name      = esc_attr( $field_data['name'] );
		$label     = esc_html( $field_data['label'] );
		$help_text = esc_html( $field_data['help_text'] );
		$max_stars = absint( $field_data['max_stars'] );

		// Ensure max_stars is one of the allowed values.
		if ( ! in_array( $max_stars, array( 3, 5, 10 ), true ) ) {
			$max_stars = 5;
		}

		$html = '<div class="wdm-field-group" data-field-type="rating">';
		$html .= '<label>' . $label;
		if ( ! empty( $field_data['required'] ) ) {
			$html .= ' <span class="wdm-required">*</span>';
		}
		$html .= '</label>';

		$html .= '<div class="wdm-rating-wrap" data-max="' . esc_attr( $max_stars ) . '">';
		$html .= '<input type="hidden" name="' . $name . '" class="wdm-rating-value" value="' . esc_attr( $value ) . '">';

		for ( $i = 1; $i <= $max_stars; $i++ ) {
			$filled_class = ( '' !== $value && (int) $value >= $i ) ? 'dashicons-star-filled' : 'dashicons-star-empty';
			$html        .= '<span class="wdm-star" data-value="' . esc_attr( $i ) . '" role="button" tabindex="0" aria-label="' . esc_attr(
				/* translators: 1: star number, 2: total stars */
				sprintf( __( '%1$d of %2$d stars', 'wprobo-documerge' ), $i, $max_stars )
			) . '">';
			$html .= '<span class="dashicons ' . esc_attr( $filled_class ) . '"></span>';
			$html .= '</span>';
		}

		$html .= '</div>';

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
	 * Ensures the value is a positive integer clamped between 0 and
	 * the configured max_stars.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $value      The raw submitted value.
	 * @param array $field_data The field configuration data.
	 * @return string
	 */
	public function wprobo_documerge_sanitize( $value, $field_data ) {
		$field_data = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );
		$max_stars  = absint( $field_data['max_stars'] );

		if ( ! in_array( $max_stars, array( 3, 5, 10 ), true ) ) {
			$max_stars = 5;
		}

		$sanitized = absint( $value );

		// Clamp to valid range.
		if ( $sanitized > $max_stars ) {
			$sanitized = $max_stars;
		}

		return (string) $sanitized;
	}

	/**
	 * Validates the submitted value.
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
		$max_stars    = absint( $field_data['max_stars'] );

		if ( ! in_array( $max_stars, array( 3, 5, 10 ), true ) ) {
			$max_stars = 5;
		}

		// Required check — value must be greater than 0.
		if ( ! empty( $field_data['required'] ) && ( '' === $value || 0 === (int) $value ) ) {
			return new \WP_Error(
				'wprobo_documerge_required',
				'' !== $custom_error
					? esc_html( $custom_error )
					/* translators: %s: field label */
					: sprintf( __( '%s is required.', 'wprobo-documerge' ), $field_data['label'] )
			);
		}

		// Range check.
		if ( '' !== $value && (int) $value > $max_stars ) {
			return new \WP_Error(
				'wprobo_documerge_invalid_rating',
				'' !== $custom_error
					? esc_html( $custom_error )
					/* translators: 1: field label, 2: maximum stars */
					: sprintf( __( '%1$s must be between 1 and %2$d.', 'wprobo-documerge' ), $field_data['label'], $max_stars )
			);
		}

		return true;
	}
}
