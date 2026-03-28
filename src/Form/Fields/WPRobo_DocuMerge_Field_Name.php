<?php
/**
 * Name field type for WPRobo DocuMerge form builder.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.3.0
 */

namespace WPRobo\DocuMerge\Form\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Field_Name
 *
 * Handles composite name fields (first, middle, last, title) within
 * the DocuMerge form builder.
 *
 * @since 1.3.0
 */
class WPRobo_DocuMerge_Field_Name {

	/**
	 * Returns the field type slug.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_type() {
		return 'name';
	}

	/**
	 * Returns the translated human-readable label.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_label() {
		return __( 'Name', 'wprobo-documerge' );
	}

	/**
	 * Returns the dashicon class name.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_icon() {
		return 'dashicons-admin-users';
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
			'type'          => 'name',
			'label'         => 'Full Name',
			'name'          => '',
			'help_text'     => '',
			'required'      => false,
			'width'         => 'full',
			'format'        => 'first_last',
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
		$format        = esc_attr( $field_data['format'] );
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

		// Name Format.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Name Format', 'wprobo-documerge' ) . '</label>';
		$html .= '<select data-setting="format" class="wdm-builder-setting-input">';
		$html .= '<option value="first_last"' . selected( $format, 'first_last', false ) . '>' . esc_html__( 'First + Last', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="first_middle_last"' . selected( $format, 'first_middle_last', false ) . '>' . esc_html__( 'First + Middle + Last', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="title_first_last"' . selected( $format, 'title_first_last', false ) . '>' . esc_html__( 'Title + First + Last', 'wprobo-documerge' ) . '</option>';
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
	 * Outputs sub-fields for name parts based on the configured format
	 * (first_last, first_middle_last, or title_first_last).
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
		$required  = ! empty( $field_data['required'] ) ? 'required' : '';
		$format    = $field_data['format'];

		$html = '<div class="wdm-field-group" data-field-type="name">';
		$html .= '<label>' . $label;
		if ( ! empty( $field_data['required'] ) ) {
			$html .= ' <span class="wdm-required">*</span>';
		}
		$html .= '</label>';
		$html .= '<div class="wdm-name-fields">';

		// Title dropdown (only for title_first_last format).
		if ( 'title_first_last' === $format ) {
			$html .= '<select id="wdm-field-' . $id . '-title" name="' . $name . '_title" class="wdm-select wdm-name-title">';
			$html .= '<option value="">' . esc_html__( 'Title', 'wprobo-documerge' ) . '</option>';
			$titles = array( 'Mr', 'Mrs', 'Ms', 'Miss', 'Dr', 'Prof' );
			foreach ( $titles as $title ) {
				$html .= '<option value="' . esc_attr( $title ) . '">' . esc_html( $title ) . '</option>';
			}
			$html .= '</select>';
		}

		// First Name.
		$html .= '<input type="text" id="wdm-field-' . $id . '-first" name="' . $name . '_first" class="wdm-input" placeholder="' . esc_attr__( 'First Name', 'wprobo-documerge' ) . '" ' . $required . '>';

		// Middle Name (only for first_middle_last format).
		if ( 'first_middle_last' === $format ) {
			$html .= '<input type="text" id="wdm-field-' . $id . '-middle" name="' . $name . '_middle" class="wdm-input" placeholder="' . esc_attr__( 'Middle Name', 'wprobo-documerge' ) . '">';
		}

		// Last Name.
		$html .= '<input type="text" id="wdm-field-' . $id . '-last" name="' . $name . '_last" class="wdm-input" placeholder="' . esc_attr__( 'Last Name', 'wprobo-documerge' ) . '" ' . $required . '>';

		$html .= '</div>'; // .wdm-name-fields

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
	 * Combines individual name sub-fields from $_POST into a single
	 * trimmed string such as "Mr John Michael Doe".
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $value      The raw submitted value.
	 * @param array $field_data The field configuration data.
	 * @return string
	 */
	public function wprobo_documerge_sanitize( $value, $field_data ) {
		// If value is already a combined string, return it sanitized.
		if ( is_string( $value ) && ! empty( $value ) ) {
			return sanitize_text_field( $value );
		}

		$name   = isset( $field_data['name'] ) ? sanitize_key( $field_data['name'] ) : '';
		$format = isset( $field_data['format'] ) ? $field_data['format'] : 'first_last';

		if ( empty( $name ) ) {
			return '';
		}

		$parts = array();

		// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce checked in submission handler.
		if ( 'title_first_last' === $format ) {
			$title = isset( $_POST[ $name . '_title' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_title' ] ) ) : '';
			if ( '' !== $title ) {
				$parts[] = $title;
			}
		}

		$first = isset( $_POST[ $name . '_first' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_first' ] ) ) : '';
		if ( '' !== $first ) {
			$parts[] = $first;
		}

		if ( 'first_middle_last' === $format ) {
			$middle = isset( $_POST[ $name . '_middle' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_middle' ] ) ) : '';
			if ( '' !== $middle ) {
				$parts[] = $middle;
			}
		}

		$last = isset( $_POST[ $name . '_last' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_last' ] ) ) : '';
		if ( '' !== $last ) {
			$parts[] = $last;
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		return trim( implode( ' ', $parts ) );
	}

	/**
	 * Validates the submitted value.
	 *
	 * When required, checks that at least the first name and last name
	 * sub-fields are filled.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $value      The sanitized combined name string.
	 * @param array $field_data The field configuration data.
	 * @return true|\WP_Error
	 */
	public function wprobo_documerge_validate( $value, $field_data ) {
		$field_data   = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );
		$custom_error = ! empty( $field_data['error_message'] ) ? $field_data['error_message'] : '';

		if ( ! empty( $field_data['required'] ) ) {
			$name = isset( $field_data['name'] ) ? sanitize_key( $field_data['name'] ) : '';

			// phpcs:disable WordPress.Security.NonceVerification.Missing -- nonce checked in submission handler.
			$first = isset( $_POST[ $name . '_first' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_first' ] ) ) : '';
			$last  = isset( $_POST[ $name . '_last' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $name . '_last' ] ) ) : '';
			// phpcs:enable WordPress.Security.NonceVerification.Missing

			if ( '' === trim( $first ) || '' === trim( $last ) ) {
				return new \WP_Error(
					'wprobo_documerge_required',
					'' !== $custom_error
						? esc_html( $custom_error )
						/* translators: %s: field label */
						: sprintf( __( '%s requires both first and last name.', 'wprobo-documerge' ), $field_data['label'] )
				);
			}
		}

		return true;
	}
}
