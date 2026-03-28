<?php
/**
 * Elementor integration loader.
 *
 * Checks if Elementor is active and registers the DocuMerge widget
 * and any editor-specific styles.
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
 * Class WPRobo_DocuMerge_Elementor_Loader
 *
 * Handles Elementor integration detection and widget registration.
 *
 * @since 1.4.0
 */
class WPRobo_DocuMerge_Elementor_Loader {

    /**
     * Register WordPress hooks for Elementor integration.
     *
     * @since  1.4.0
     * @return void
     */
    public function wprobo_documerge_init_hooks() {
        add_action( 'elementor/widgets/register', array( $this, 'wprobo_documerge_register_widget' ) );
        add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'wprobo_documerge_editor_styles' ) );
        // Enqueue frontend CSS in Elementor's preview iframe so forms are styled.
        add_action( 'elementor/preview/enqueue_styles', array( $this, 'wprobo_documerge_preview_styles' ) );
    }

    /**
     * Register the DocuMerge form widget with Elementor.
     *
     * @since  1.4.0
     * @param  \Elementor\Widgets_Manager $widgets_manager The Elementor widgets manager instance.
     * @return void
     */
    public function wprobo_documerge_register_widget( $widgets_manager ) {
        $widgets_manager->register( new WPRobo_DocuMerge_Elementor_Widget() );
    }

    /**
     * Enqueue custom styles for the Elementor editor.
     *
     * @since  1.4.0
     * @return void
     */
    public function wprobo_documerge_editor_styles() {
        wp_enqueue_style(
            'wprobo-documerge-elementor-editor',
            WPROBO_DOCUMERGE_URL . 'assets/css/admin/elementor-editor.css',
            array(),
            WPROBO_DOCUMERGE_VERSION
        );
    }

    /**
     * Enqueue frontend CSS in Elementor's preview iframe.
     *
     * @since  1.4.0
     * @return void
     */
    public function wprobo_documerge_preview_styles() {
        wp_enqueue_style( 'dashicons' );
        wp_enqueue_style(
            'wprobo-documerge-frontend',
            WPROBO_DOCUMERGE_URL . 'assets/css/frontend/form.min.css',
            array( 'dashicons' ),
            WPROBO_DOCUMERGE_VERSION
        );
    }
}
