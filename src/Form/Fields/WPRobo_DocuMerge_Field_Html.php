<?php
/**
 * HTML Block field type for WPRobo DocuMerge form builder.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.3.0
 */

namespace WPRobo\DocuMerge\Form\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Field_Html
 *
 * Handles display-only HTML content blocks within the DocuMerge
 * form builder. This field has no form input and collects no data.
 *
 * @since 1.3.0
 */
class WPRobo_DocuMerge_Field_Html {

	/**
	 * Returns the field type slug.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_type() {
		return 'html';
	}

	/**
	 * Returns the translated human-readable label.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_label() {
		return __( 'HTML Block', 'wprobo-documerge' );
	}

	/**
	 * Returns the dashicon class name.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_icon() {
		return 'dashicons-editor-code';
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
			'id'         => '',
			'type'       => 'html',
			'label'      => 'HTML Block',
			'name'       => '',
			'width'      => 'full',
			'content'    => '<p>Add your content here.</p>',
			'conditions' => array(),
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

		$id      = esc_attr( $field_data['id'] );
		$label   = esc_attr( $field_data['label'] );
		$content = esc_textarea( $field_data['content'] );
		$width   = esc_attr( $field_data['width'] );

		$html = '';

		// Label (admin reference only).
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Label', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="text" data-setting="label" class="wdm-builder-setting-input" value="' . $label . '">';
		$html .= '<span class="wdm-description">' . esc_html__( 'For admin reference only. Not shown on the frontend.', 'wprobo-documerge' ) . '</span>';
		$html .= '</div>';

		// Content.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Content', 'wprobo-documerge' ) . '</label>';
		$html .= '<textarea data-setting="content" class="wdm-builder-setting-input wdm-textarea" rows="6">' . $content . '</textarea>';
		$html .= '<span class="wdm-description">' . esc_html__( 'HTML content to display. Safe HTML tags are allowed.', 'wprobo-documerge' ) . '</span>';
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
	 * Outputs display-only HTML content. No form input, label, or
	 * name attribute is rendered. The content is sanitized with
	 * wp_kses_post to allow safe HTML.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $field_data The field configuration data.
	 * @param string $value      The current field value (unused).
	 * @return string
	 */
	public function wprobo_documerge_render_frontend( $field_data, $value = '' ) {
		$field_data = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );

		$content = isset( $field_data['content'] ) ? $field_data['content'] : '';

		$html = '<div class="wdm-field-group wdm-html-block">';
		$html .= wp_kses_post( $content );
		$html .= '</div>';

		return $html;
	}

	/**
	 * Sanitizes the submitted value.
	 *
	 * HTML blocks are display-only and collect no user input, so this
	 * always returns an empty string.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $value      The raw submitted value.
	 * @param array $field_data The field configuration data.
	 * @return string
	 */
	public function wprobo_documerge_sanitize( $value, $field_data ) {
		return '';
	}

	/**
	 * Validates the submitted value.
	 *
	 * HTML blocks are display-only and always pass validation.
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
