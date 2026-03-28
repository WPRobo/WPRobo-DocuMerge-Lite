<?php
/**
 * Hidden field type for WPRobo DocuMerge form builder.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.3.0
 */

namespace WPRobo\DocuMerge\Form\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Field_Hidden
 *
 * Handles hidden fields that pass pre-defined or dynamic values
 * without any visible UI on the frontend form.
 *
 * @since 1.3.0
 */
class WPRobo_DocuMerge_Field_Hidden {

	/**
	 * Returns the field type slug.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_type() {
		return 'hidden';
	}

	/**
	 * Returns the translated human-readable label.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_label() {
		return __( 'Hidden Field', 'wprobo-documerge' );
	}

	/**
	 * Returns the dashicon class name.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_icon() {
		return 'dashicons-hidden';
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
			'type'          => 'hidden',
			'label'         => 'Hidden Field',
			'name'          => '',
			'width'         => 'full',
			'default_value' => '',
			'dynamic_value' => 'none',
			'custom_value'  => '',
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

		$label         = esc_attr( $field_data['label'] );
		$default_value = esc_attr( $field_data['default_value'] );
		$dynamic_value = esc_attr( $field_data['dynamic_value'] );
		$custom_value  = esc_attr( $field_data['custom_value'] );

		$html = '';

		// Label (admin reference only).
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Label', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="text" data-setting="label" class="wdm-builder-setting-input" value="' . $label . '">';
		$html .= '<span class="wdm-description">' . esc_html__( 'For admin reference only. Not shown on the frontend.', 'wprobo-documerge' ) . '</span>';
		$html .= '</div>';

		// Default Value.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Default Value', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="text" data-setting="default_value" class="wdm-builder-setting-input wdm-input" value="' . $default_value . '">';
		$html .= '<span class="wdm-description">' . esc_html__( 'Static value used when Dynamic Value is set to None.', 'wprobo-documerge' ) . '</span>';
		$html .= '</div>';

		// Dynamic Value.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Dynamic Value', 'wprobo-documerge' ) . '</label>';
		$html .= '<select data-setting="dynamic_value" class="wdm-builder-setting-input">';
		$html .= '<option value="none"' . selected( $dynamic_value, 'none', false ) . '>' . esc_html__( 'None', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="user_id"' . selected( $dynamic_value, 'user_id', false ) . '>' . esc_html__( 'User ID', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="user_email"' . selected( $dynamic_value, 'user_email', false ) . '>' . esc_html__( 'User Email', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="user_name"' . selected( $dynamic_value, 'user_name', false ) . '>' . esc_html__( 'User Display Name', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="page_url"' . selected( $dynamic_value, 'page_url', false ) . '>' . esc_html__( 'Current Page URL', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="page_title"' . selected( $dynamic_value, 'page_title', false ) . '>' . esc_html__( 'Page Title', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="referrer"' . selected( $dynamic_value, 'referrer', false ) . '>' . esc_html__( 'Referrer', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="custom"' . selected( $dynamic_value, 'custom', false ) . '>' . esc_html__( 'Custom', 'wprobo-documerge' ) . '</option>';
		$html .= '</select>';
		$html .= '</div>';

		// Custom Value (shown only when dynamic = custom).
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Custom Value', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="text" data-setting="custom_value" class="wdm-builder-setting-input wdm-input" value="' . $custom_value . '">';
		$html .= '<span class="wdm-description">' . esc_html__( 'Used when Dynamic Value is set to Custom.', 'wprobo-documerge' ) . '</span>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Renders the frontend form HTML for this field.
	 *
	 * Outputs only a hidden input element. No label, wrapper, or
	 * visible UI is rendered. Dynamic values that require JavaScript
	 * (page_url, page_title, referrer) are populated via data attributes.
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
		$dynamic_value = $field_data['dynamic_value'];
		$resolved      = $this->wprobo_documerge_resolve_value( $field_data );

		$html = '<input type="hidden" id="wdm-field-' . $id . '" name="' . $name . '" value="' . esc_attr( $resolved ) . '"';

		// For JS-based dynamic values, add a data attribute so frontend
		// JavaScript can populate them on page load.
		if ( in_array( $dynamic_value, array( 'page_url', 'page_title', 'referrer' ), true ) ) {
			$html .= ' data-wdm-dynamic="' . esc_attr( $dynamic_value ) . '"';
		}

		$html .= '>';

		return $html;
	}

	/**
	 * Resolves the hidden field value based on its dynamic_value setting.
	 *
	 * Server-side values (user_id, user_email, user_name) are resolved
	 * here. Client-side values (page_url, page_title, referrer) return
	 * an empty string and are populated by JavaScript.
	 *
	 * @since 1.3.0
	 *
	 * @param array $field_data The field configuration data.
	 * @return string
	 */
	private function wprobo_documerge_resolve_value( $field_data ) {
		$dynamic = isset( $field_data['dynamic_value'] ) ? $field_data['dynamic_value'] : 'none';

		switch ( $dynamic ) {
			case 'user_id':
				$user_id = get_current_user_id();
				return $user_id > 0 ? (string) $user_id : '';

			case 'user_email':
				$user = wp_get_current_user();
				return ( $user && $user->exists() ) ? $user->user_email : '';

			case 'user_name':
				$user = wp_get_current_user();
				return ( $user && $user->exists() ) ? $user->display_name : '';

			case 'custom':
				return isset( $field_data['custom_value'] ) ? sanitize_text_field( $field_data['custom_value'] ) : '';

			case 'page_url':
			case 'page_title':
			case 'referrer':
				// These are resolved client-side via JavaScript.
				return '';

			case 'none':
			default:
				return isset( $field_data['default_value'] ) ? sanitize_text_field( $field_data['default_value'] ) : '';
		}
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
	 * Hidden fields always pass validation as they are not user-editable.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $value      The sanitized value.
	 * @param array $field_data The field configuration data.
	 * @return true
	 */
	public function wprobo_documerge_validate( $value, $field_data ) {
		return true;
	}
}
