<?php
/**
 * Repeater field type for WPRobo DocuMerge form builder.
 *
 * @package   WPRobo_DocuMerge
 * @since     1.3.0
 */

namespace WPRobo\DocuMerge\Form\Fields;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Field_Repeater
 *
 * Handles repeater ("add another row") fields within the DocuMerge
 * form builder. Each row contains configurable sub-columns rendered
 * as text inputs in a table layout.
 *
 * @since 1.3.0
 */
class WPRobo_DocuMerge_Field_Repeater {

	/**
	 * Returns the field type slug.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_type() {
		return 'repeater';
	}

	/**
	 * Returns the translated human-readable label.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_label() {
		return __( 'Repeater', 'wprobo-documerge' );
	}

	/**
	 * Returns the dashicon class name.
	 *
	 * @since 1.3.0
	 *
	 * @return string
	 */
	public function wprobo_documerge_get_icon() {
		return 'dashicons-plus-alt';
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
			'type'          => 'repeater',
			'label'         => 'Items',
			'name'          => '',
			'help_text'     => '',
			'required'      => false,
			'width'         => 'full',
			'columns'       => array(
				array(
					'label' => 'Item',
					'name'  => 'item',
				),
				array(
					'label' => 'Quantity',
					'name'  => 'qty',
				),
			),
			'min_rows'      => 1,
			'max_rows'      => 10,
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
		$min_rows      = absint( $field_data['min_rows'] );
		$max_rows      = absint( $field_data['max_rows'] );
		$columns       = is_array( $field_data['columns'] ) ? $field_data['columns'] : array();
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

		// Min Rows.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Min Rows', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="number" data-setting="min_rows" class="wdm-builder-setting-input" value="' . esc_attr( $min_rows ) . '" min="1">';
		$html .= '</div>';

		// Max Rows.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Max Rows', 'wprobo-documerge' ) . '</label>';
		$html .= '<input type="number" data-setting="max_rows" class="wdm-builder-setting-input" value="' . esc_attr( $max_rows ) . '" min="1">';
		$html .= '</div>';

		// Columns.
		$html .= '<div class="wdm-builder-field-setting">';
		$html .= '<label>' . esc_html__( 'Columns', 'wprobo-documerge' ) . '</label>';
		$html .= '<div class="wdm-repeater-columns-manager" data-setting="columns">';

		foreach ( $columns as $index => $column ) {
			$col_label = isset( $column['label'] ) ? esc_attr( $column['label'] ) : '';
			$col_name  = isset( $column['name'] ) ? esc_attr( $column['name'] ) : '';

			$html .= '<div class="wdm-repeater-column-row" data-index="' . esc_attr( $index ) . '">';
			$html .= '<input type="text" class="wdm-builder-setting-input wdm-repeater-col-label" value="' . $col_label . '" placeholder="' . esc_attr__( 'Column Label', 'wprobo-documerge' ) . '">';
			$html .= '<input type="text" class="wdm-builder-setting-input wdm-repeater-col-name" value="' . $col_name . '" placeholder="' . esc_attr__( 'Column Name', 'wprobo-documerge' ) . '">';
			$html .= '<button type="button" class="wdm-repeater-remove-column" aria-label="' . esc_attr__( 'Remove column', 'wprobo-documerge' ) . '">';
			$html .= '<span class="dashicons dashicons-no-alt"></span>';
			$html .= '</button>';
			$html .= '</div>';
		}

		$html .= '</div>';
		$html .= '<button type="button" class="wdm-repeater-add-column wdm-btn wdm-btn-ghost">';
		$html .= '<span class="dashicons dashicons-plus-alt2"></span> ' . esc_html__( 'Add Column', 'wprobo-documerge' );
		$html .= '</button>';
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
	 * Outputs a table with configurable columns and an "Add Row" button.
	 * JavaScript on the frontend handles adding/removing rows dynamically.
	 *
	 * @since 1.3.0
	 *
	 * @param array  $field_data The field configuration data.
	 * @param string $value      The current field value (unused for initial render).
	 * @return string
	 */
	public function wprobo_documerge_render_frontend( $field_data, $value = '' ) {
		$field_data = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );

		$id        = esc_attr( $field_data['id'] );
		$name      = esc_attr( $field_data['name'] );
		$label     = esc_html( $field_data['label'] );
		$help_text = esc_html( $field_data['help_text'] );
		$columns   = is_array( $field_data['columns'] ) ? $field_data['columns'] : array();
		$min_rows  = max( 1, absint( $field_data['min_rows'] ) );
		$max_rows  = max( $min_rows, absint( $field_data['max_rows'] ) );

		$html = '<div class="wdm-field-group" data-field-type="repeater">';
		$html .= '<label>' . $label;
		if ( ! empty( $field_data['required'] ) ) {
			$html .= ' <span class="wdm-required">*</span>';
		}
		$html .= '</label>';

		$html .= '<div class="wdm-repeater-wrap" data-min="' . esc_attr( $min_rows ) . '" data-max="' . esc_attr( $max_rows ) . '" data-name="' . $name . '">';
		$html .= '<table class="wdm-repeater-table">';

		// Table header.
		$html .= '<thead><tr>';
		foreach ( $columns as $column ) {
			$col_label = isset( $column['label'] ) ? esc_html( $column['label'] ) : '';
			$html     .= '<th>' . $col_label . '</th>';
		}
		$html .= '<th class="wdm-repeater-actions-header"></th>';
		$html .= '</tr></thead>';

		// Table body with initial rows.
		$html .= '<tbody>';
		for ( $i = 0; $i < $min_rows; $i++ ) {
			$html .= $this->wprobo_documerge_render_row( $name, $columns, $i );
		}
		$html .= '</tbody>';

		$html .= '</table>';

		// Add Row button.
		$html .= '<button type="button" class="wdm-repeater-add">';
		$html .= '<span class="dashicons dashicons-plus-alt2" aria-hidden="true"></span> ';
		$html .= esc_html__( 'Add Row', 'wprobo-documerge' );
		$html .= '</button>';

		$html .= '</div>'; // .wdm-repeater-wrap

		if ( ! empty( $field_data['help_text'] ) ) {
			$html .= '<p class="wdm-help-text">' . $help_text . '</p>';
		}
		$html .= '<span class="wdm-field-error-msg" role="alert"></span>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Renders a single repeater table row.
	 *
	 * @since 1.3.0
	 *
	 * @param string $name    The field name prefix.
	 * @param array  $columns The column definitions.
	 * @param int    $index   The row index.
	 * @return string
	 */
	private function wprobo_documerge_render_row( $name, $columns, $index ) {
		$name  = esc_attr( $name );
		$index = absint( $index );

		$html = '<tr class="wdm-repeater-row">';

		foreach ( $columns as $column ) {
			$col_name  = isset( $column['name'] ) ? esc_attr( sanitize_key( $column['name'] ) ) : '';
			$col_label = isset( $column['label'] ) ? esc_attr( $column['label'] ) : '';

			$html .= '<td>';
			$html .= '<input type="text" name="' . $name . '[' . $index . '][' . $col_name . ']" class="wdm-input wdm-repeater-input" placeholder="' . $col_label . '">';
			$html .= '</td>';
		}

		$html .= '<td class="wdm-repeater-actions">';
		$html .= '<button type="button" class="wdm-repeater-remove" aria-label="' . esc_attr__( 'Remove row', 'wprobo-documerge' ) . '">';
		$html .= '<span class="dashicons dashicons-no-alt" aria-hidden="true"></span>';
		$html .= '</button>';
		$html .= '</td>';

		$html .= '</tr>';

		return $html;
	}

	/**
	 * Sanitizes the submitted value.
	 *
	 * Loops through the submitted array of rows, sanitizes each cell
	 * value, and returns a JSON-encoded string of the row data.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $value      The raw submitted value (expected array of rows).
	 * @param array $field_data The field configuration data.
	 * @return string JSON-encoded array of sanitized rows.
	 */
	public function wprobo_documerge_sanitize( $value, $field_data ) {
		if ( ! is_array( $value ) ) {
			return wp_json_encode( array() );
		}

		$field_data    = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );
		$max_rows      = max( 1, absint( $field_data['max_rows'] ) );
		$columns       = is_array( $field_data['columns'] ) ? $field_data['columns'] : array();
		$allowed_names = array();

		foreach ( $columns as $column ) {
			if ( isset( $column['name'] ) ) {
				$allowed_names[] = sanitize_key( $column['name'] );
			}
		}

		$sanitized_rows = array();
		$row_count      = 0;

		foreach ( $value as $row ) {
			if ( ! is_array( $row ) || $row_count >= $max_rows ) {
				break;
			}

			$sanitized_row = array();
			foreach ( $row as $col_name => $col_value ) {
				$col_name = sanitize_key( $col_name );

				// Only allow known column names.
				if ( in_array( $col_name, $allowed_names, true ) ) {
					$sanitized_row[ $col_name ] = sanitize_text_field( $col_value );
				}
			}

			if ( ! empty( $sanitized_row ) ) {
				$sanitized_rows[] = $sanitized_row;
				$row_count++;
			}
		}

		return wp_json_encode( $sanitized_rows );
	}

	/**
	 * Validates the submitted value.
	 *
	 * When required, checks that at least min_rows rows exist with a
	 * non-empty first column. Also enforces the max_rows limit.
	 *
	 * @since 1.3.0
	 *
	 * @param mixed $value      The sanitized JSON-encoded value.
	 * @param array $field_data The field configuration data.
	 * @return true|\WP_Error
	 */
	public function wprobo_documerge_validate( $value, $field_data ) {
		$field_data   = wp_parse_args( $field_data, $this->wprobo_documerge_get_default_config() );
		$custom_error = ! empty( $field_data['error_message'] ) ? $field_data['error_message'] : '';
		$min_rows     = max( 1, absint( $field_data['min_rows'] ) );
		$max_rows     = max( $min_rows, absint( $field_data['max_rows'] ) );
		$columns      = is_array( $field_data['columns'] ) ? $field_data['columns'] : array();

		$rows = json_decode( $value, true );

		if ( ! is_array( $rows ) ) {
			$rows = array();
		}

		// Count rows with non-empty first column.
		$first_col_name = '';
		if ( ! empty( $columns[0]['name'] ) ) {
			$first_col_name = sanitize_key( $columns[0]['name'] );
		}

		$filled_rows = 0;
		foreach ( $rows as $row ) {
			if ( is_array( $row ) && ! empty( $first_col_name ) && isset( $row[ $first_col_name ] ) && '' !== trim( $row[ $first_col_name ] ) ) {
				$filled_rows++;
			}
		}

		// Required check.
		if ( ! empty( $field_data['required'] ) && $filled_rows < $min_rows ) {
			return new \WP_Error(
				'wprobo_documerge_required',
				'' !== $custom_error
					? esc_html( $custom_error )
					/* translators: 1: field label, 2: minimum row count */
					: sprintf( __( '%1$s requires at least %2$d row(s).', 'wprobo-documerge' ), $field_data['label'], $min_rows )
			);
		}

		// Max rows check.
		if ( count( $rows ) > $max_rows ) {
			return new \WP_Error(
				'wprobo_documerge_max_rows',
				'' !== $custom_error
					? esc_html( $custom_error )
					/* translators: 1: field label, 2: maximum row count */
					: sprintf( __( '%1$s allows a maximum of %2$d rows.', 'wprobo-documerge' ), $field_data['label'], $max_rows )
			);
		}

		return true;
	}
}
