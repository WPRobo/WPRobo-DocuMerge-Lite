<?php
/**
 * File Upload field type for WPRobo DocuMerge form builder.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.3.0
 */

namespace WPRobo\DocuMerge\Form\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Field_File_Upload
 *
 * Handles file upload fields within the DocuMerge form builder.
 *
 * @since 1.3.0
 */
class WPRobo_DocuMerge_Field_File_Upload {

	/**
	 * Returns the field type slug.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_type() {
		return 'file_upload';
	}

	/**
	 * Returns the translated human-readable label.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_label() {
		return __( 'File Upload', 'wprobo-documerge' );
	}

	/**
	 * Returns the dashicon class name.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_icon() {
		return 'dashicons-upload';
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
			'type'          => 'file_upload',
			'label'         => 'File Upload',
			'name'          => '',
			'help_text'     => '',
			'required'      => false,
			'width'         => 'full',
			'allowed_types' => 'jpg,jpeg,png,gif,pdf,doc,docx',
			'max_file_size' => 5,
			'multiple'      => false,
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
		$allowed_types = esc_attr( $field_data['allowed_types'] );
		$max_file_size = esc_attr( $field_data['max_file_size'] );
		$multiple      = ! empty( $field_data['multiple'] ) ? 'checked' : '';
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

		// Allowed File Types.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Allowed File Types', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="text" data-setting="allowed_types" class="wdm-builder-setting-input wdm-input" value="' . $allowed_types . '">';
		$html .= '<span class="wdm-description">' . esc_html__( 'Comma-separated extensions, e.g. jpg,png,pdf,docx', 'wprobo-documerge' ) . '</span>';
		$html .= '</div>';

		// Max File Size.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Max File Size (MB)', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="number" data-setting="max_file_size" class="wdm-builder-setting-input" value="' . $max_file_size . '" min="1" step="1">';
		$html .= '</div>';

		// Multiple Files.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Allow Multiple Files', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="checkbox" data-setting="multiple" class="wdm-builder-setting-input" ' . $multiple . '>';
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
	 * @since 1.3.0
	 *
	 * @param array  $field_data The field configuration data.
	 * @param string $value      The current field value.
	 * @return string
	 */
	public function wprobo_documerge_render_frontend( $field_data, $value = '' ) {
		$field_data = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );

		$id            = esc_attr( $field_data['id'] );
		$name          = esc_attr( $field_data['name'] );
		$label         = esc_html( $field_data['label'] );
		$help_text     = esc_html( $field_data['help_text'] );
		$required      = ! empty( $field_data['required'] ) ? 'required' : '';
		$multiple      = ! empty( $field_data['multiple'] ) ? 'multiple' : '';
		$allowed_types = sanitize_text_field( $field_data['allowed_types'] );
		$max_file_size = absint( $field_data['max_file_size'] );

		// Build accept attribute from allowed types.
		$accept = '';
		if ( ! empty( $allowed_types ) ) {
			$types  = array_map( 'trim', explode( ',', $allowed_types ) );
			$accept = '.' . implode( ',.', $types );
		}

		$html = '<div class="wdm-field-group" data-field-type="file_upload">';
		$html .= '<label for="wdm-field-' . $id . '">' . $label;
		if ( ! empty( $field_data['required'] ) ) {
			$html .= ' <span class="wdm-required">*</span>';
		}
		$html .= '</label>';

		// Drop zone area with hidden file input.
		$html .= '<div class="wdm-file-upload-area">';
		$html .= '<input type="file" id="wdm-field-' . $id . '" name="' . $name . ( ! empty( $multiple ) ? '[]' : '' ) . '" class="wdm-file-input-hidden"';
		if ( ! empty( $accept ) ) {
			$html .= ' accept="' . esc_attr( $accept ) . '"';
		}
		if ( ! empty( $multiple ) ) {
			$html .= ' multiple';
		}
		if ( ! empty( $required ) ) {
			$html .= ' required';
		}
		$html .= '>';
		$html .= '<label for="wdm-field-' . $id . '" class="wdm-file-dropzone">';
		$html .= '<span class="wdm-file-dropzone-icon dashicons dashicons-cloud-upload"></span>';
		$html .= '<span class="wdm-file-dropzone-text">';
		$html .= ! empty( $multiple )
			? esc_html__( 'Drag files here or click to browse', 'wprobo-documerge' )
			: esc_html__( 'Drag file here or click to browse', 'wprobo-documerge' );
		$html .= '</span>';
		$html .= '<span class="wdm-file-dropzone-info">';
		/* translators: 1: allowed file types, 2: max file size in MB */
		$html .= esc_html( sprintf( __( 'Allowed: %1$s — Max: %2$dMB', 'wprobo-documerge' ), strtoupper( $allowed_types ), $max_file_size ) );
		$html .= '</span>';
		$html .= '</label>';
		$html .= '<div class="wdm-file-list"></div>';
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
	 * For file uploads the actual value stored is the sanitized file name
	 * (or the uploaded file path set during processing). The raw $_FILES
	 * data is handled separately by the submission processor.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $value      The raw submitted value (file path or file name).
	 * @param array $field_data The field configuration data.
	 * @return string
	 */
	public function wprobo_documerge_sanitize( $value, $field_data ) {
		if ( is_array( $value ) ) {
			return implode( ', ', array_map( 'sanitize_file_name', $value ) );
		}

		return sanitize_file_name( $value );
	}

	/**
	 * Validates the submitted value.
	 *
	 * Checks that the file is present when required, that the file
	 * extension matches the allowed types, and that the file size
	 * does not exceed the configured maximum.
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
		if ( ! empty( $field_data['required'] ) && empty( $value ) ) {
			return new \WP_Error(
				'wprobo_documerge_required',
				'' !== $custom_error
					? esc_html( $custom_error )
					/* translators: %s: field label */
					: sprintf( __( '%s is required.', 'wprobo-documerge' ), $field_data['label'] )
			);
		}

		if ( empty( $value ) ) {
			return true;
		}

		// Validate file extension against allowed types.
		$allowed_types = array_map( 'trim', explode( ',', strtolower( $field_data['allowed_types'] ) ) );
		$filenames     = is_array( $value ) ? $value : array( $value );

		foreach ( $filenames as $filename ) {
			$ext = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
			if ( ! in_array( $ext, $allowed_types, true ) ) {
				return new \WP_Error(
					'wprobo_documerge_invalid_file_type',
					'' !== $custom_error
						? esc_html( $custom_error )
						/* translators: 1: file name, 2: allowed types */
						: sprintf( __( '%1$s: File type not allowed. Allowed types: %2$s', 'wprobo-documerge' ), $filename, $field_data['allowed_types'] )
				);
			}
		}

		return true;
	}
}
