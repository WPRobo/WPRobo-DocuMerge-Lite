<?php
/**
 * Section Divider field type for WPRobo DocuMerge form builder.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.3.0
 */

namespace WPRobo\DocuMerge\Form\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Field_Section_Divider
 *
 * Handles display-only section divider elements within the DocuMerge
 * form builder. This field has no form input and collects no data.
 *
 * @since 1.3.0
 */
class WPRobo_DocuMerge_Field_Section_Divider {

	/**
	 * Returns the field type slug.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_type() {
		return 'section_divider';
	}

	/**
	 * Returns the translated human-readable label.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_label() {
		return __( 'Section Divider', 'wprobo-documerge' );
	}

	/**
	 * Returns the dashicon class name.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_icon() {
		return 'dashicons-minus';
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
			'type'            => 'section_divider',
			'label'           => 'Section',
			'width'           => 'full',
			'divider_style'   => 'line',
			'show_title'      => true,
			'title_alignment' => 'left',
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
		$divider_style   = esc_attr( $field_data['divider_style'] );
		$show_title      = ! empty( $field_data['show_title'] ) ? 'checked' : '';
		$title_alignment = esc_attr( $field_data['title_alignment'] );
		$width           = esc_attr( $field_data['width'] );

		$html = '';

		// Label / Title text.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Title Text', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="text" data-setting="label" class="wdm-builder-setting-input" value="' . $label . '">';
		$html .= '</div>';

		// Show Title.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Show Title', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="checkbox" data-setting="show_title" class="wdm-builder-setting-input" ' . $show_title . '>';
		$html .= '</div>';

		// Title Alignment.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Title Alignment', 'wprobo-documerge' ) . '</label>';
		$html .= '<select data-setting="title_alignment" class="wdm-builder-setting-input">';
		$html .= '<option value="left"' . selected( $title_alignment, 'left', false ) . '>' . esc_html__( 'Left', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="center"' . selected( $title_alignment, 'center', false ) . '>' . esc_html__( 'Center', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="right"' . selected( $title_alignment, 'right', false ) . '>' . esc_html__( 'Right', 'wprobo-documerge' ) . '</option>';
		$html .= '</select>';
		$html .= '</div>';

		// Divider Style.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Divider Style', 'wprobo-documerge' ) . '</label>';
		$html .= '<select data-setting="divider_style" class="wdm-builder-setting-input">';
		$html .= '<option value="line"' . selected( $divider_style, 'line', false ) . '>' . esc_html__( 'Solid Line', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="dashed"' . selected( $divider_style, 'dashed', false ) . '>' . esc_html__( 'Dashed', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="dotted"' . selected( $divider_style, 'dotted', false ) . '>' . esc_html__( 'Dotted', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="double"' . selected( $divider_style, 'double', false ) . '>' . esc_html__( 'Double', 'wprobo-documerge' ) . '</option>';
		$html .= '<option value="none"' . selected( $divider_style, 'none', false ) . '>' . esc_html__( 'No Line', 'wprobo-documerge' ) . '</option>';
		$html .= '</select>';
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
	 * Outputs a display-only section divider with optional title and
	 * configurable divider line style. No form input is rendered.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $field_data The field configuration data.
	 * @param string $value      The current field value (unused).
	 * @return string
	 */
	public function wprobo_documerge_render_frontend( $field_data, $value = '' ) {
		$field_data = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );

		$label           = esc_html( $field_data['label'] );
		$divider_style   = esc_attr( $field_data['divider_style'] );
		$show_title      = ! empty( $field_data['show_title'] );
		$title_alignment = esc_attr( $field_data['title_alignment'] );

		// The renderer wraps output in <div class="wdm-field-group ..."> so we
		// only output inner content. The CSS targets .wdm-section-divider inside the wrapper.
		$html = '<div class="wdm-section-divider wdm-divider-' . $divider_style . '">';

		if ( $show_title && '' !== trim( $field_data['label'] ) ) {
			$html .= '<h3 class="wdm-divider-title wdm-align-' . $title_alignment . '">' . $label . '</h3>';
		}

		if ( 'none' !== $divider_style ) {
			$html .= '<hr class="wdm-divider-line">';
		}

		$html .= '</div>';

		return $html;
	}

	/**
	 * Sanitizes the submitted value.
	 *
	 * Section dividers are display-only and collect no user input, so
	 * this always returns an empty string.
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
	 * Section dividers are display-only and always pass validation.
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
