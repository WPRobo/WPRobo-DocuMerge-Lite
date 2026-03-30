<?php
/**
 * Frontend form renderer for WPRobo DocuMerge.
 *
 * Renders published forms on the frontend via shortcode or direct call,
 * handles individual field rendering with conditional logic data attributes,
 * and registers the [documerge_form] shortcode.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage Form
 * @since      1.0.0
 */

namespace WPRobo\DocuMerge\Form;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;

// phpcs:disable WordPress.DB.DirectDatabaseQuery, WordPress.DB.PreparedSQL, WordPress.Security.NonceVerification.Recommended
}

use WPRobo\DocuMerge\Helpers\WPRobo_DocuMerge_Logger;

/**
 * Class WPRobo_DocuMerge_Form_Renderer
 *
 * Singleton responsible for rendering forms on the frontend.
 * Outputs form HTML via an output buffer that includes the frontend
 * form template, and provides field-level rendering with width classes
 * and conditional-logic data attributes.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Form_Renderer {

	/**
	 * The single instance of this class.
	 *
	 * @since 1.0.0
	 * @var WPRobo_DocuMerge_Form_Renderer|null
	 */
	private static $wprobo_documerge_instance = null;

	/**
	 * Private constructor to enforce singleton pattern.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {}

	/**
	 * Retrieve the singleton instance.
	 *
	 * @since 1.0.0
	 *
	 * @return WPRobo_DocuMerge_Form_Renderer The singleton instance.
	 */
	public static function get_instance() {
		if ( null === self::$wprobo_documerge_instance ) {
			self::$wprobo_documerge_instance = new self();
		}

		return self::$wprobo_documerge_instance;
	}

	/**
	 * Render a complete form by its ID.
	 *
	 * Loads the form record from the database, decodes its fields and
	 * settings JSON columns, and includes the frontend form template
	 * inside an output buffer.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id The form ID to render.
	 * @return string The rendered form HTML, or an empty string on failure.
	 */
	public function wprobo_documerge_render( $form_id ) {
		$form_id = absint( $form_id );

		if ( 0 === $form_id ) {
			return '';
		}

		$form_builder = new WPRobo_DocuMerge_Form_Builder();
		$form         = $form_builder->wprobo_documerge_get_form( $form_id );

		if ( null === $form ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				WPRobo_DocuMerge_Logger::wprobo_documerge_log(
					/* translators: %d: form ID */
					sprintf( 'Form #%d not found during render.', $form_id ),
					'warning',
					array( 'form_id' => $form_id )
				);
			}

			return '';
		}

		$fields   = json_decode( $form->fields, true );
		$settings = isset( $form->settings ) ? json_decode( $form->settings, true ) : array();

		if ( ! is_array( $fields ) ) {
			$fields = array();
		}

		if ( ! is_array( $settings ) ) {
			$settings = array();
		}

		/**
		 * Filters the form fields array before rendering.
		 *
		 * Allows reordering, removing, or injecting fields dynamically
		 * before rendering. Enables context-aware forms (e.g., different
		 * fields for logged-in users).
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields  Array of field configuration objects.
		 * @param int   $form_id The form ID.
		 */
		$fields = apply_filters( 'wprobo_documerge_form_fields_before_render', $fields, $form_id );

		/**
		 * Filters the form settings before rendering.
		 *
		 * Allows modification of the decoded form settings array
		 * before it is used to render the form.
		 *
		 * @since 1.2.0
		 *
		 * @param array $settings The decoded form settings.
		 * @param int   $form_id  The form ID.
		 */
		$settings = apply_filters( 'wprobo_documerge_form_settings', $settings, $form_id );

		/**
		 * Filters the CSS classes applied to the form wrapper element.
		 *
		 * @since 1.1.0
		 *
		 * @param array $classes An array of CSS class names.
		 * @param int   $form_id The form ID.
		 */
		$classes = array( 'wdm-form-wrap' );
		$classes = apply_filters( 'wprobo_documerge_form_classes', $classes, $form_id );
		$classes = array_map( 'sanitize_html_class', (array) $classes );

		/**
		 * Filters the submit button label text.
		 *
		 * @since 1.1.0
		 *
		 * @param string $submit_label The submit button label.
		 * @param int    $form_id      The form ID.
		 */
		$submit_label = ! empty( $form->submit_label ) ? $form->submit_label : ( ! empty( $settings['submit_label'] ) ? $settings['submit_label'] : __( 'Submit', 'wprobo-documerge-lite' ) );
		$submit_label = apply_filters( 'wprobo_documerge_submit_button_label', $submit_label, $form_id );

		// Check entry limit before rendering the form.
		$entry_limit = isset( $settings['entry_limit'] ) ? absint( $settings['entry_limit'] ) : 0;

		if ( $entry_limit > 0 ) {
			global $wpdb;
			$submission_count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->prefix}wprdm_submissions WHERE form_id = %d AND status != 'error'",
					$form_id
				)
			);

			if ( $submission_count >= $entry_limit ) {
				$closed_message = ! empty( $settings['closed_message'] )
					? $settings['closed_message']
					: __( 'This form is no longer accepting submissions.', 'wprobo-documerge-lite' );

				return '<div class="wdm-form-wrap wdm-form-closed">' .
					'<div class="wdm-form-closed-message">' .
						'<span class="dashicons dashicons-lock"></span>' .
						'<p>' . esc_html( $closed_message ) . '</p>' .
					'</div>' .
				'</div>';
			}
		}

		/**
		 * Fires before the form HTML is rendered.
		 *
		 * Runs just before the output buffer is opened, allowing
		 * third-party code to output additional markup or enqueue
		 * assets specific to this form.
		 *
		 * @since 1.2.0
		 *
		 * @param int    $form_id The form ID.
		 * @param object $form    The form database row object.
		 * @param array  $fields  The decoded form fields array.
		 */
		do_action( 'wprobo_documerge_before_form_render', $form_id, $form, $fields );

		ob_start();

		// Make variables available to the template.
		$template_path = WPROBO_DOCUMERGE_PATH . 'templates/frontend/form.php';

		if ( file_exists( $template_path ) ) {
			include $template_path;
		}

		$html = ob_get_clean();

		/**
		 * Fires after the form HTML has been captured from the output buffer.
		 *
		 * Allows inspection or side-effects based on the rendered
		 * form HTML before the final output filter is applied.
		 *
		 * @since 1.2.0
		 *
		 * @param int    $form_id The form ID.
		 * @param object $form    The form database row object.
		 * @param string $html    The rendered form HTML.
		 */
		do_action( 'wprobo_documerge_after_form_render', $form_id, $form, $html );

		/**
		 * Filters the complete rendered form HTML output.
		 *
		 * @since 1.1.0
		 *
		 * @param string $html    The rendered form HTML.
		 * @param int    $form_id The form ID.
		 */
		$html = apply_filters( 'wprobo_documerge_form_output', $html, $form_id );

		// Prepend global custom styles from Settings > Styles tab.
		$custom_styles = $this->wprobo_documerge_get_custom_styles();
		if ( ! empty( $custom_styles ) ) {
			$html = $custom_styles . $html;
		}

		return $html;
	}

	/**
	 * Render a single form field with wrapper markup.
	 *
	 * Retrieves the field class from the registry, calls its frontend
	 * render method, and wraps the output in a container div with width
	 * class, data attributes for field name/type/id, and optional
	 * conditional-logic data.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field   The field configuration array.
	 * @param int   $form_id The parent form ID.
	 * @return string The rendered field HTML, or an empty string for unknown types.
	 */
	public function wprobo_documerge_render_field( $field, $form_id ) {
		$registry    = WPRobo_DocuMerge_Field_Registry::get_instance();
		$field_type  = isset( $field['type'] ) ? $field['type'] : '';
		$field_class = $registry->wprobo_documerge_get_field( $field_type );

		if ( null === $field_class ) {
			return '';
		}

		$field_html = $field_class->wprobo_documerge_render_frontend( $field, '' );

		// Apply smart placeholders for fields without a custom placeholder.
		$field_html = $this->wprobo_documerge_apply_smart_placeholders( $field_html, $field );

		$width = isset( $field['width'] ) ? $field['width'] : 'full';
		$name  = isset( $field['name'] ) ? $field['name'] : '';
		$type  = isset( $field['type'] ) ? $field['type'] : '';
		$id    = isset( $field['id'] ) ? $field['id'] : '';

		$css_class = isset( $field['css_class'] ) && '' !== $field['css_class'] ? ' ' . esc_attr( $field['css_class'] ) : '';
		$css_id    = isset( $field['css_id'] ) && '' !== $field['css_id'] ? $field['css_id'] : '';

		// Display-only fields (no form input) get a modifier class to strip background.
		$display_only_types = array( 'section_divider', 'html' );
		if ( in_array( $type, $display_only_types, true ) ) {
			$css_class .= ' wdm-field-display-only';
		}

		$html = '<div class="wdm-field-group wdm-field-width-' . esc_attr( $width ) . $css_class . '"';
		if ( '' !== $css_id ) {
			$html .= ' id="' . esc_attr( $css_id ) . '"';
		}
		$html .= ' data-field-name="' . esc_attr( $name ) . '"';
		$html .= ' data-field-type="' . esc_attr( $type ) . '"';
		$html .= ' data-field-id="' . esc_attr( $id ) . '"';

		if ( ! empty( $field['conditions'] ) && is_array( $field['conditions'] ) ) {
			$conditions_json = wp_json_encode( $field['conditions'] );
			$html           .= ' data-conditions="' . esc_attr( $conditions_json ) . '"';
		}

		if ( ! empty( $field['error_message'] ) ) {
			$html .= ' data-error-message="' . esc_attr( $field['error_message'] ) . '"';
		}

		if ( isset( $field['min_length'] ) && '' !== $field['min_length'] ) {
			$html .= ' data-min-length="' . esc_attr( $field['min_length'] ) . '"';
		}

		if ( isset( $field['max_length'] ) && '' !== $field['max_length'] ) {
			$html .= ' data-max-length="' . esc_attr( $field['max_length'] ) . '"';
		}

		if ( isset( $field['min_value'] ) && '' !== $field['min_value'] ) {
			$html .= ' data-min-value="' . esc_attr( $field['min_value'] ) . '"';
		}

		if ( isset( $field['max_value'] ) && '' !== $field['max_value'] ) {
			$html .= ' data-max-value="' . esc_attr( $field['max_value'] ) . '"';
		}

		if ( isset( $field['date_format'] ) && '' !== $field['date_format'] ) {
			$html .= ' data-date-format="' . esc_attr( $field['date_format'] ) . '"';
		}

		if ( isset( $field['min_date'] ) && '' !== $field['min_date'] ) {
			$html .= ' data-min-date="' . esc_attr( $field['min_date'] ) . '"';
		}

		if ( isset( $field['max_date'] ) && '' !== $field['max_date'] ) {
			$html .= ' data-max-date="' . esc_attr( $field['max_date'] ) . '"';
		}

		if ( isset( $field['min_selections'] ) && '' !== $field['min_selections'] ) {
			$html .= ' data-min-selections="' . esc_attr( $field['min_selections'] ) . '"';
		}

		if ( isset( $field['max_selections'] ) && '' !== $field['max_selections'] ) {
			$html .= ' data-max-selections="' . esc_attr( $field['max_selections'] ) . '"';
		}

		if ( ! empty( $field['label_position'] ) && 'top' !== $field['label_position'] ) {
			$html .= ' data-label-position="' . esc_attr( $field['label_position'] ) . '"';
		}

		$html .= '>';

		// Apply label position from field settings.
		$label_position = isset( $field['label_position'] ) ? $field['label_position'] : 'top';
		$field_html     = $this->wprobo_documerge_apply_label_position( $field_html, $label_position );

		$html .= $field_html;
		$html .= '</div>';

		/**
		 * Filters the rendered HTML for a single form field.
		 *
		 * @since 1.1.0
		 *
		 * @param string $html    The rendered field HTML including wrapper.
		 * @param array  $field   The field configuration array.
		 * @param int    $form_id The parent form ID.
		 */
		$html = apply_filters( 'wprobo_documerge_field_output', $html, $field, $form_id );

		return $html;
	}

	/**
	 * Register the [documerge_form] shortcode.
	 *
	 * Hooks into WordPress to provide the shortcode that renders a
	 * DocuMerge form on any page or post via [documerge_form id="123"].
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function wprobo_documerge_register_shortcode() {
		add_shortcode( 'documerge_form', array( $this, 'wprobo_documerge_shortcode_callback' ) );
	}

	/**
	 * Shortcode callback for [documerge_form].
	 *
	 * Parses the shortcode attributes and delegates rendering to
	 * the wprobo_documerge_render() method.
	 *
	 * @since 1.0.0
	 *
	 * @param array|string $atts Shortcode attributes.
	 * @return string The rendered form HTML.
	 */
	public function wprobo_documerge_shortcode_callback( $atts ) {
		$atts = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts,
			'documerge_form'
		);

		$form_id = absint( $atts['id'] );
		$html    = $this->wprobo_documerge_render( $form_id );

		/**
		 * Filters the shortcode output for [documerge_form].
		 *
		 * Applied after the form has been fully rendered, allowing
		 * modification of the final shortcode HTML output.
		 *
		 * @since 1.2.0
		 *
		 * @param string $html    The rendered form HTML.
		 * @param int    $form_id The form ID.
		 */
		return apply_filters( 'wprobo_documerge_shortcode_output', $html, $form_id );
	}

	/**
	 * Build inline CSS from the global form styles option.
	 *
	 * Reads the JSON saved by Settings > Styles tab and produces a
	 * <style> block that overrides default frontend form appearance.
	 * Only non-default values are emitted to keep the output minimal.
	 *
	 * @since  1.5.0
	 *
	 * @return string The <style> tag with custom CSS, or empty string.
	 */
	private function wprobo_documerge_get_custom_styles() {
		$raw = get_option( 'wprobo_documerge_form_styles', '' );
		if ( empty( $raw ) ) {
			return '';
		}

		$s = json_decode( $raw, true );
		if ( ! is_array( $s ) || empty( $s ) ) {
			return '';
		}

		$css = '';

		// Form container.
		$form_rules = '';
		if ( ! empty( $s['form_max_width'] ) && '640' !== $s['form_max_width'] ) {
			$form_rules .= 'max-width:' . absint( $s['form_max_width'] ) . 'px;';
		}
		if ( ! empty( $s['form_bg'] ) && '#ffffff' !== $s['form_bg'] ) {
			$form_rules .= 'background-color:' . esc_attr( $s['form_bg'] ) . ';';
		}
		if ( ! empty( $s['form_border_width'] ) && '0' !== $s['form_border_width'] ) {
			$border_color = ! empty( $s['form_border_color'] ) ? $s['form_border_color'] : '#dde5f0';
			$form_rules  .= 'border:' . absint( $s['form_border_width'] ) . 'px solid ' . esc_attr( $border_color ) . ';';
		}
		if ( ! empty( $s['form_border_radius'] ) && '0' !== $s['form_border_radius'] ) {
			$form_rules .= 'border-radius:' . absint( $s['form_border_radius'] ) . 'px;';
		}
		if ( ! empty( $s['form_padding'] ) && '0' !== $s['form_padding'] ) {
			$form_rules .= 'padding:' . absint( $s['form_padding'] ) . 'px;';
		}
		if ( ! empty( $s['form_shadow'] ) && '1' === $s['form_shadow'] ) {
			$shadow_color = ! empty( $s['form_shadow_color'] ) ? $s['form_shadow_color'] : '#000000';
			$form_rules  .= 'box-shadow:0 4px 20px ' . esc_attr( $shadow_color ) . '1a;';
		}
		if ( ! empty( $s['form_font_family'] ) && 'system' !== $s['form_font_family'] ) {
			$google_fonts = array(
				'inter'        => array( 'Inter', 'Inter:wght@400;500;600;700' ),
				'roboto'       => array( 'Roboto', 'Roboto:wght@400;500;700' ),
				'opensans'     => array( 'Open Sans', 'Open+Sans:wght@400;600;700' ),
				'lato'         => array( 'Lato', 'Lato:wght@400;700' ),
				'montserrat'   => array( 'Montserrat', 'Montserrat:wght@400;500;600;700' ),
				'poppins'      => array( 'Poppins', 'Poppins:wght@400;500;600;700' ),
				'nunito'       => array( 'Nunito', 'Nunito:wght@400;600;700' ),
				'raleway'      => array( 'Raleway', 'Raleway:wght@400;500;600;700' ),
				'ubuntu'       => array( 'Ubuntu', 'Ubuntu:wght@400;500;700' ),
				'merriweather' => array( 'Merriweather', 'Merriweather:wght@400;700' ),
				'playfair'     => array( 'Playfair Display', 'Playfair+Display:wght@400;600;700' ),
				'lora'         => array( 'Lora', 'Lora:wght@400;500;600;700' ),
				'sourcesans'   => array( 'Source Sans 3', 'Source+Sans+3:wght@400;600;700' ),
				'dmsans'       => array( 'DM Sans', 'DM+Sans:wght@400;500;600;700' ),
				'worksans'     => array( 'Work Sans', 'Work+Sans:wght@400;500;600;700' ),
				'firasans'     => array( 'Fira Sans', 'Fira+Sans:wght@400;500;600;700' ),
				'mulish'       => array( 'Mulish', 'Mulish:wght@400;600;700' ),
				'cabin'        => array( 'Cabin', 'Cabin:wght@400;500;600;700' ),
				'karla'        => array( 'Karla', 'Karla:wght@400;500;600;700' ),
				'barlow'       => array( 'Barlow', 'Barlow:wght@400;500;600;700' ),
				'josefinsans'  => array( 'Josefin Sans', 'Josefin+Sans:wght@400;600;700' ),
				'quicksand'    => array( 'Quicksand', 'Quicksand:wght@400;500;600;700' ),
				'rubik'        => array( 'Rubik', 'Rubik:wght@400;500;600;700' ),
				'manrope'      => array( 'Manrope', 'Manrope:wght@400;500;600;700' ),
				'spacegrotesk' => array( 'Space Grotesk', 'Space+Grotesk:wght@400;500;600;700' ),
			);
			$fkey         = $s['form_font_family'];
			if ( isset( $google_fonts[ $fkey ] ) ) {
				$form_rules .= 'font-family:"' . $google_fonts[ $fkey ][0] . '", sans-serif;';
				// Enqueue Google Font stylesheet.
				wp_enqueue_style(
					'wprobo-documerge-google-font',
					'https://fonts.googleapis.com/css2?family=' . $google_fonts[ $fkey ][1] . '&display=swap',
					array(),
					null // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
				);
			}
		}
		if ( '' !== $form_rules ) {
			$css .= '.wdm-form-wrap{' . $form_rules . '}';
		}

		// Labels.
		$label_rules = '';
		if ( ! empty( $s['label_size'] ) && '14' !== $s['label_size'] ) {
			$label_rules .= 'font-size:' . absint( $s['label_size'] ) . 'px;';
		}
		if ( ! empty( $s['label_weight'] ) && '600' !== $s['label_weight'] ) {
			$label_rules .= 'font-weight:' . absint( $s['label_weight'] ) . ';';
		}
		if ( ! empty( $s['label_color'] ) && '#1a1a1a' !== $s['label_color'] ) {
			$label_rules .= 'color:' . esc_attr( $s['label_color'] ) . ';';
		}
		if ( ! empty( $s['label_margin'] ) && '6' !== $s['label_margin'] ) {
			$label_rules .= 'margin-bottom:' . absint( $s['label_margin'] ) . 'px;';
		}
		if ( '' !== $label_rules ) {
			$css .= '.wdm-form-wrap .wdm-field-group label{' . $label_rules . '}';
		}

		// Inputs.
		$input_sel   = '.wdm-form-wrap input[type="text"],.wdm-form-wrap input[type="email"],.wdm-form-wrap input[type="tel"],.wdm-form-wrap input[type="number"],.wdm-form-wrap input[type="url"],.wdm-form-wrap input[type="password"],.wdm-form-wrap input[type="date"],.wdm-form-wrap select,.wdm-form-wrap textarea';
		$input_rules = '';
		if ( ! empty( $s['input_bg'] ) && '#ffffff' !== $s['input_bg'] ) {
			$input_rules .= 'background-color:' . esc_attr( $s['input_bg'] ) . ';';
		}
		if ( ! empty( $s['input_border_color'] ) && '#dde5f0' !== $s['input_border_color'] ) {
			$input_rules .= 'border-color:' . esc_attr( $s['input_border_color'] ) . ';';
		}
		if ( ! empty( $s['input_border_width'] ) && '1' !== $s['input_border_width'] ) {
			$input_rules .= 'border-width:' . absint( $s['input_border_width'] ) . 'px;';
		}
		if ( ! empty( $s['input_border_radius'] ) && '6' !== $s['input_border_radius'] ) {
			$input_rules .= 'border-radius:' . absint( $s['input_border_radius'] ) . 'px;';
		}
		if ( ! empty( $s['input_padding'] ) && '12' !== $s['input_padding'] ) {
			$input_rules .= 'padding:' . absint( $s['input_padding'] ) . 'px;';
		}
		if ( ! empty( $s['input_font_size'] ) && '14' !== $s['input_font_size'] ) {
			$input_rules .= 'font-size:' . absint( $s['input_font_size'] ) . 'px;';
		}
		if ( ! empty( $s['input_color'] ) && '#1a1a1a' !== $s['input_color'] ) {
			$input_rules .= 'color:' . esc_attr( $s['input_color'] ) . ';';
		}
		if ( '' !== $input_rules ) {
			$css .= $input_sel . '{' . $input_rules . '}';
		}

		// Input focus.
		if ( ! empty( $s['input_focus_color'] ) && '#042157' !== $s['input_focus_color'] ) {
			$css .= $input_sel . ':focus{border-color:' . esc_attr( $s['input_focus_color'] ) . ';box-shadow:0 0 0 3px ' . esc_attr( $s['input_focus_color'] ) . '1a;}';
		}

		// Placeholder.
		if ( ! empty( $s['input_placeholder_color'] ) && '#9ca3af' !== $s['input_placeholder_color'] ) {
			$css .= $input_sel . '::placeholder{color:' . esc_attr( $s['input_placeholder_color'] ) . ';}';
		}

		// Submit button.
		$btn_rules = '';
		if ( ! empty( $s['btn_bg'] ) && '#042157' !== $s['btn_bg'] ) {
			$btn_rules .= 'background-color:' . esc_attr( $s['btn_bg'] ) . ';';
		}
		if ( ! empty( $s['btn_color'] ) && '#ffffff' !== $s['btn_color'] ) {
			$btn_rules .= 'color:' . esc_attr( $s['btn_color'] ) . ';';
		}
		if ( ! empty( $s['btn_radius'] ) && '6' !== $s['btn_radius'] ) {
			$btn_rules .= 'border-radius:' . absint( $s['btn_radius'] ) . 'px;';
		}
		if ( ! empty( $s['btn_font_size'] ) && '14' !== $s['btn_font_size'] ) {
			$btn_rules .= 'font-size:' . absint( $s['btn_font_size'] ) . 'px;';
		}
		$pv = ! empty( $s['btn_padding_v'] ) ? $s['btn_padding_v'] : '12';
		$ph = ! empty( $s['btn_padding_h'] ) ? $s['btn_padding_h'] : '24';
		if ( '12' !== $pv || '24' !== $ph ) {
			$btn_rules .= 'padding:' . absint( $pv ) . 'px ' . absint( $ph ) . 'px;';
		}
		if ( '' !== $btn_rules ) {
			$css .= '.wdm-form-wrap .wdm-submit-btn{' . $btn_rules . '}';
		}

		// Submit button hover.
		$btn_hover = '';
		if ( ! empty( $s['btn_hover_bg'] ) && '#0a3d8f' !== $s['btn_hover_bg'] ) {
			$btn_hover .= 'background-color:' . esc_attr( $s['btn_hover_bg'] ) . ';';
		}
		if ( ! empty( $s['btn_hover_color'] ) && '#ffffff' !== $s['btn_hover_color'] ) {
			$btn_hover .= 'color:' . esc_attr( $s['btn_hover_color'] ) . ';';
		}
		if ( '' !== $btn_hover ) {
			$css .= '.wdm-form-wrap .wdm-submit-btn:hover{' . $btn_hover . '}';
		}

		// Error states.
		if ( ! empty( $s['error_color'] ) && '#dc2626' !== $s['error_color'] ) {
			$css .= '.wdm-form-wrap .wdm-field-error-msg{color:' . esc_attr( $s['error_color'] ) . ';}';
			$css .= '.wdm-form-wrap .wdm-required{color:' . esc_attr( $s['error_color'] ) . ';}';
		}
		if ( ! empty( $s['error_border_color'] ) && '#dc2626' !== $s['error_border_color'] ) {
			$css .= '.wdm-form-wrap .wdm-field-has-error input,.wdm-form-wrap .wdm-field-has-error select,.wdm-form-wrap .wdm-field-has-error textarea{border-color:' . esc_attr( $s['error_border_color'] ) . ';}';
		}
		if ( ! empty( $s['success_color'] ) && '#166441' !== $s['success_color'] ) {
			$css .= '.wdm-form-wrap .wdm-success-icon .dashicons{color:' . esc_attr( $s['success_color'] ) . ';}';
		}

		if ( empty( $css ) ) {
			return '';
		}

		return '<style id="wdm-custom-form-styles">' . $css . '</style>';
	}

	/**
	 * Apply label position to field HTML.
	 *
	 * Moves or hides the <label> element within the field HTML
	 * based on the label_position setting:
	 *  - 'top'    — label stays above the input (default, no change).
	 *  - 'bottom' — label moved below the input.
	 *  - 'hidden' — label hidden visually but kept for screen readers.
	 *
	 * @since  1.2.0
	 *
	 * @param string $html           The field HTML.
	 * @param string $label_position One of 'top', 'bottom', 'hidden'.
	 * @return string Modified HTML.
	 */
	private function wprobo_documerge_apply_label_position( $html, $label_position ) {
		if ( 'top' === $label_position || empty( $label_position ) ) {
			return $html;
		}

		// Match the <label ...>...</label> tag (first occurrence).
		if ( ! preg_match( '/<label[^>]*>.*?<\/label>/s', $html, $matches ) ) {
			return $html;
		}

		$label_tag = $matches[0];

		if ( 'hidden' === $label_position ) {
			// Keep label for accessibility but hide visually.
			$sr_label = str_replace( '<label', '<label class="wdm-sr-only"', $label_tag );
			// If it already has a class attribute, merge.
			if ( false !== strpos( $label_tag, 'class="' ) ) {
				$sr_label = preg_replace( '/class="([^"]*)"/', 'class="$1 wdm-sr-only"', $label_tag );
			}
			$html = str_replace( $label_tag, $sr_label, $html );
			return $html;
		}

		if ( 'bottom' === $label_position ) {
			// Remove label from its current position.
			$html_without_label = str_replace( $label_tag, '', $html );

			// Find the closing </div> of the inner wdm-field-group wrapper
			// and insert the label just before it.
			$last_div_pos = strrpos( $html_without_label, '</div>' );
			if ( false !== $last_div_pos ) {
				$html = substr_replace( $html_without_label, $label_tag . '</div>', $last_div_pos );
			}
			return $html;
		}

		return $html;
	}

	/**
	 * Apply smart placeholder text to fields that have no custom placeholder.
	 *
	 * Generates contextual placeholder text based on the field type
	 * (e.g. "name@example.com" for email fields) so users see helpful
	 * hints even when the form builder leaves the placeholder empty.
	 *
	 * @since  1.3.0
	 *
	 * @param string $html  The rendered field HTML.
	 * @param array  $field The field configuration array.
	 * @return string Modified HTML with smart placeholder if applicable.
	 */
	private function wprobo_documerge_apply_smart_placeholders( $html, $field ) {
		// Only add smart placeholder if no custom placeholder is set.
		if ( ! empty( $field['placeholder'] ) ) {
			return $html;
		}

		$type        = isset( $field['type'] ) ? $field['type'] : '';
		$placeholder = '';

		switch ( $type ) {
			case 'email':
				$placeholder = 'name@example.com';
				break;
			case 'phone':
				$placeholder = '+1 (555) 000-0000';
				break;
			case 'url':
				$placeholder = 'https://example.com';
				break;
			case 'date':
				$format      = isset( $field['date_format'] ) ? $field['date_format'] : 'Y-m-d';
				$placeholder = gmdate( $format );
				break;
			case 'number':
				$min = isset( $field['min_value'] ) && '' !== $field['min_value'] ? $field['min_value'] : '';
				$max = isset( $field['max_value'] ) && '' !== $field['max_value'] ? $field['max_value'] : '';
				if ( '' !== $min && '' !== $max ) {
					$placeholder = $min . ' - ' . $max;
				}
				break;
			case 'password':
				/* translators: password field placeholder */
				$placeholder = str_repeat( "\xE2\x80\xA2", 8 );
				break;
		}

		if ( empty( $placeholder ) ) {
			return $html;
		}

		$safe_placeholder = esc_attr( $placeholder );

		// Replace empty placeholder="" with the smart placeholder.
		$html = str_replace( 'placeholder=""', 'placeholder="' . $safe_placeholder . '"', $html );

		return $html;
	}
}
