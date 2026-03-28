<?php
/**
 * Address field type for WPRobo DocuMerge form builder.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.3.0
 */

namespace WPRobo\DocuMerge\Form\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Field_Address
 *
 * Handles composite address fields within the DocuMerge form builder.
 *
 * @since 1.3.0
 */
class WPRobo_DocuMerge_Field_Address {

	/**
	 * Returns the field type slug.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_type() {
		return 'address';
	}

	/**
	 * Returns the translated human-readable label.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_label() {
		return __( 'Address', 'wprobo-documerge' );
	}

	/**
	 * Returns the dashicon class name.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_icon() {
		return 'dashicons-location';
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
			'id'              => '',
			'type'            => 'address',
			'label'           => 'Address',
			'name'            => '',
			'help_text'       => '',
			'required'        => false,
			'width'           => 'full',
			'show_line2'      => true,
			'show_country'    => true,
			'default_country' => '',
			'error_message'   => '',
			'conditions'      => array(),
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

		$id              = esc_attr( $field_data['id'] );
		$label           = esc_attr( $field_data['label'] );
		$help_text       = esc_attr( $field_data['help_text'] );
		$required        = ! empty( $field_data['required'] ) ? 'checked' : '';
		$width           = esc_attr( $field_data['width'] );
		$show_line2      = ! empty( $field_data['show_line2'] ) ? 'checked' : '';
		$show_country    = ! empty( $field_data['show_country'] ) ? 'checked' : '';
		$default_country = esc_attr( $field_data['default_country'] );
		$error_message   = esc_attr( $field_data['error_message'] );

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

		// Show Address Line 2.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Show Address Line 2', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="checkbox" data-setting="show_line2" class="wdm-builder-setting-input" ' . $show_line2 . '>';
		$html .= '</div>';

		// Show Country.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Show Country', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="checkbox" data-setting="show_country" class="wdm-builder-setting-input" ' . $show_country . '>';
		$html .= '</div>';

		// Default Country.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Default Country', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="text" data-setting="default_country" class="wdm-builder-setting-input wdm-input" value="' . $default_country . '" placeholder="' . esc_attr__( 'e.g. United Kingdom', 'wprobo-documerge' ) . '">';
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
	 * Outputs a composite address block with multiple sub-fields for
	 * address line 1, line 2, city, state, postcode, and country.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $field_data The field configuration data.
	 * @param string $value      The current field value.
	 * @return string
	 */
	public function wprobo_documerge_render_frontend( $field_data, $value = '' ) {
		$field_data = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );

		$id              = esc_attr( $field_data['id'] );
		$name            = esc_attr( $field_data['name'] );
		$label           = esc_html( $field_data['label'] );
		$help_text       = esc_html( $field_data['help_text'] );
		$required        = ! empty( $field_data['required'] ) ? 'required' : '';
		$show_line2      = ! empty( $field_data['show_line2'] );
		$show_country    = ! empty( $field_data['show_country'] );
		$default_country = esc_attr( $field_data['default_country'] );

		$html = '<div class="wdm-field-group" data-field-type="address">';
		$html .= '<label>' . $label;
		if ( ! empty( $field_data['required'] ) ) {
			$html .= ' <span class="wdm-required">*</span>';
		}
		$html .= '</label>';
		$html .= '<div class="wdm-address-fields">';

		// Address Line 1.
		$html .= '<input type="text" id="wdm-field-' . $id . '-line1" name="' . $name . '_line1" class="wdm-input" placeholder="' . esc_attr__( 'Address Line 1', 'wprobo-documerge' ) . '" ' . $required . '>';

		// Address Line 2.
		if ( $show_line2 ) {
			$html .= '<input type="text" id="wdm-field-' . $id . '-line2" name="' . $name . '_line2" class="wdm-input" placeholder="' . esc_attr__( 'Address Line 2', 'wprobo-documerge' ) . '">';
		}

		// City + State row.
		$html .= '<div class="wdm-address-row">';
		$html .= '<input type="text" id="wdm-field-' . $id . '-city" name="' . $name . '_city" class="wdm-input" placeholder="' . esc_attr__( 'City', 'wprobo-documerge' ) . '" ' . $required . '>';
		$html .= '<input type="text" id="wdm-field-' . $id . '-state" name="' . $name . '_state" class="wdm-input" placeholder="' . esc_attr__( 'State / County', 'wprobo-documerge' ) . '">';
		$html .= '</div>';

		// Postcode + Country row.
		$html .= '<div class="wdm-address-row">';
		$html .= '<input type="text" id="wdm-field-' . $id . '-postcode" name="' . $name . '_postcode" class="wdm-input" placeholder="' . esc_attr__( 'Postcode / ZIP', 'wprobo-documerge' ) . '">';
		if ( $show_country ) {
			$html .= '<input type="text" id="wdm-field-' . $id . '-country" name="' . $name . '_country" class="wdm-input" placeholder="' . esc_attr__( 'Country', 'wprobo-documerge' ) . '" value="' . $default_country . '">';
		}
		$html .= '</div>';

		$html .= '</div>'; // .wdm-address-fields

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
	 * Accepts either a pre-combined string or individual sub-field values
	 * from $_POST (line1, line2, city, state, postcode, country) and
	 * returns a single combined address string.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $value      The raw submitted value.
	 * @param array $field_data The field configuration data.
	 * @return string
	 */
	public function wprobo_documerge_sanitize( $value, $field_data ) {
		// If the value is already a string (pre-combined), sanitize and return.
		if ( is_string( $value ) && ! empty( $value ) ) {
			return sanitize_text_field( $value );
		}

		// Otherwise, build from sub-fields in $_POST.
		$name = isset( $field_data['name'] ) ? sanitize_key( $field_data['name'] ) : '';

		if ( empty( $name ) ) {
			return '';
		}

		$parts = array();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce checked in submission handler.
		$line1    = isset( $_POST[ $name . '_line1' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_line1' ] ) ) : '';
		$line2    = isset( $_POST[ $name . '_line2' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_line2' ] ) ) : '';
		$city     = isset( $_POST[ $name . '_city' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_city' ] ) ) : '';
		$state    = isset( $_POST[ $name . '_state' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_state' ] ) ) : '';
		$postcode = isset( $_POST[ $name . '_postcode' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_postcode' ] ) ) : '';
		$country  = isset( $_POST[ $name . '_country' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_country' ] ) ) : '';
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if ( '' !== $line1 ) {
			$parts[] = $line1;
		}
		if ( '' !== $line2 ) {
			$parts[] = $line2;
		}
		if ( '' !== $city ) {
			$parts[] = $city;
		}
		if ( '' !== $state ) {
			$parts[] = $state;
		}
		if ( '' !== $postcode ) {
			$parts[] = $postcode;
		}
		if ( '' !== $country ) {
			$parts[] = $country;
		}

		return implode( ', ', $parts );
	}

	/**
	 * Validates the submitted value.
	 *
	 * When required, checks that at least address line 1 and city are filled.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $value      The sanitized combined address string.
	 * @param array $field_data The field configuration data.
	 * @return true|\WP_Error
	 */
	public function wprobo_documerge_validate( $value, $field_data ) {
		$field_data   = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );
		$custom_error = ! empty( $field_data['error_message'] ) ? $field_data['error_message'] : '';

		if ( ! empty( $field_data['required'] ) ) {
			$name = isset( $field_data['name'] ) ? sanitize_key( $field_data['name'] ) : '';

			// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce checked in submission handler.
			$line1 = isset( $_POST[ $name . '_line1' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_line1' ] ) ) : '';
			$city  = isset( $_POST[ $name . '_city' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_city' ] ) ) : '';
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			if ( '' === trim( $line1 ) || '' === trim( $city ) ) {
				return new \WP_Error(
					'wprobo_documerge_required',
					'' !== $custom_error
						? esc_html( $custom_error )
						/* translators: %s: field label */
						: sprintf( __( '%s requires at least an address line and city.', 'wprobo-documerge' ), $field_data['label'] )
				);
			}
		}

		return true;
	}
}
