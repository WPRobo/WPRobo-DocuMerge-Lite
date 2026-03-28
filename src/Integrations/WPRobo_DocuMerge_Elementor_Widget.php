<?php
/**
 * Elementor widget for embedding DocuMerge forms.
 *
 * Provides a dedicated Elementor widget that allows users to select
 * and embed a DocuMerge form within the Elementor page builder.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Integrations
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.4.0
 */

namespace WPRobo\DocuMerge\Integrations;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPRobo_DocuMerge_Elementor_Widget
 *
 * Elementor widget for embedding DocuMerge forms with style controls.
 *
 * @since 1.4.0
 */
class WPRobo_DocuMerge_Elementor_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * @since  1.4.0
     * @return string Widget name.
     */
    public function get_name() {
        return 'wprobo-documerge-form';
    }

    /**
     * Get widget title.
     *
     * @since  1.4.0
     * @return string Widget title.
     */
    public function get_title() {
        return __( 'DocuMerge Form', 'wprobo-documerge' );
    }

    /**
     * Get widget icon.
     *
     * @since  1.4.0
     * @return string Widget icon CSS class.
     */
    public function get_icon() {
        return 'eicon-form-horizontal';
    }

    /**
     * Get widget categories.
     *
     * @since  1.4.0
     * @return array Widget categories.
     */
    public function get_categories() {
        return array( 'general' );
    }

    /**
     * Get widget keywords.
     *
     * @since  1.4.0
     * @return array Widget keywords for search.
     */
    public function get_keywords() {
        return array( 'form', 'document', 'documerge', 'pdf', 'merge' );
    }

    /**
     * Register widget controls.
     *
     * Adds the form selector and style controls to the Elementor panel.
     *
     * @since  1.4.0
     * @return void
     */
    protected function register_controls() {
        // ── Content Tab: Form Selection ────────────────────────────────────
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __( 'Form', 'wprobo-documerge' ),
                'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        // Build form options from the database.
        $form_options = array( '0' => __( '— Select a form —', 'wprobo-documerge' ) );
        global $wpdb;
        $forms = $wpdb->get_results(
            "SELECT id, title FROM {$wpdb->prefix}wprdm_forms ORDER BY title ASC"
        );
        if ( $forms ) {
            foreach ( $forms as $form ) {
                $form_options[ $form->id ] = $form->title . ' (ID: ' . $form->id . ')';
            }
        }

        $this->add_control(
            'form_id',
            array(
                'label'       => __( 'Select Form', 'wprobo-documerge' ),
                'type'        => \Elementor\Controls_Manager::SELECT,
                'options'     => $form_options,
                'default'     => '0',
                'render_type' => 'template',
            )
        );

        $this->add_control(
            'form_info',
            array(
                'type'            => \Elementor\Controls_Manager::RAW_HTML,
                'raw'             => '<p style="font-size:12px;color:#6b7280;">' .
                    sprintf(
                        /* translators: 1: opening link tag, 2: closing link tag */
                        __( 'Manage your forms in %1$sDocuMerge &rarr; Forms%2$s', 'wprobo-documerge' ),
                        '<a href="' . esc_url( admin_url( 'admin.php?page=wprobo-documerge-forms' ) ) . '" target="_blank">',
                        '</a>'
                    ) . '</p>',
                'content_classes' => 'elementor-panel-alert',
            )
        );

        $this->end_controls_section();

        // ── Style Tab: Form Container ──────────────────────────────────────
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __( 'Form Style', 'wprobo-documerge' ),
                'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );

        $this->add_control(
            'form_max_width',
            array(
                'label'      => __( 'Max Width', 'wprobo-documerge' ),
                'type'       => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array( 'px', '%' ),
                'range'      => array(
                    'px' => array( 'min' => 200, 'max' => 1200 ),
                    '%'  => array( 'min' => 20, 'max' => 100 ),
                ),
                'selectors'  => array(
                    '{{WRAPPER}} .wdm-form-wrap' => 'max-width: {{SIZE}}{{UNIT}};',
                ),
            )
        );

        $this->add_control(
            'form_alignment',
            array(
                'label'     => __( 'Alignment', 'wprobo-documerge' ),
                'type'      => \Elementor\Controls_Manager::CHOOSE,
                'options'   => array(
                    'left'   => array(
                        'title' => __( 'Left', 'wprobo-documerge' ),
                        'icon'  => 'eicon-text-align-left',
                    ),
                    'center' => array(
                        'title' => __( 'Center', 'wprobo-documerge' ),
                        'icon'  => 'eicon-text-align-center',
                    ),
                    'right'  => array(
                        'title' => __( 'Right', 'wprobo-documerge' ),
                        'icon'  => 'eicon-text-align-right',
                    ),
                ),
                'default'   => 'center',
                'selectors' => array(
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Background::get_type(),
            array(
                'name'     => 'form_background',
                'label'    => __( 'Background', 'wprobo-documerge' ),
                'types'    => array( 'classic', 'gradient' ),
                'selector' => '{{WRAPPER}} .wdm-form-wrap',
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name'     => 'form_border',
                'selector' => '{{WRAPPER}} .wdm-form-wrap',
            )
        );

        $this->add_control(
            'form_border_radius',
            array(
                'label'      => __( 'Border Radius', 'wprobo-documerge' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .wdm-form-wrap' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name'     => 'form_shadow',
                'selector' => '{{WRAPPER}} .wdm-form-wrap',
            )
        );

        $this->add_responsive_control(
            'form_padding',
            array(
                'label'      => __( 'Padding', 'wprobo-documerge' ),
                'type'       => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array( 'px', 'em', '%' ),
                'selectors'  => array(
                    '{{WRAPPER}} .wdm-form-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );

        $this->end_controls_section();
    }

    /**
     * Render the widget output on the frontend.
     *
     * @since  1.4.0
     * @return void
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        $form_id  = absint( $settings['form_id'] ?? 0 );

        if ( ! $form_id ) {
            echo '<div class="wdm-elementor-placeholder">';
            echo '<span class="dashicons dashicons-media-document" style="font-size:32px;color:#042157;"></span>';
            echo '<p>' . esc_html__( 'Select a DocuMerge form from the widget settings.', 'wprobo-documerge' ) . '</p>';
            echo '</div>';
            return;
        }

        // Enqueue frontend CSS so the form is styled in both editor and frontend.
        wp_enqueue_style( 'dashicons' );
        if ( ! wp_style_is( 'wprobo-documerge-frontend', 'enqueued' ) ) {
            wp_enqueue_style(
                'wprobo-documerge-frontend',
                WPROBO_DOCUMERGE_URL . 'assets/css/frontend/form.min.css',
                array( 'dashicons' ),
                WPROBO_DOCUMERGE_VERSION
            );
        }

        $renderer = \WPRobo\DocuMerge\Form\WPRobo_DocuMerge_Form_Renderer::get_instance();
        $html = $renderer->wprobo_documerge_render( $form_id );

        if ( empty( $html ) ) {
            echo '<div class="wdm-elementor-placeholder">';
            echo '<p>' . esc_html__( 'Form could not be loaded. Check if it exists.', 'wprobo-documerge' ) . '</p>';
            echo '</div>';
            return;
        }

        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * Disable JS content template to force server-side rendering.
     *
     * Returning empty string forces Elementor to use the PHP render()
     * method via AJAX for live preview. This ensures the form is always
     * rendered from the database with full field configuration.
     *
     * @since  1.4.0
     * @return void
     */
    protected function content_template() {
        // Intentionally empty — forces server-side render via AJAX.
    }
}
