<?php
/**
 * Integration manager singleton.
 *
 * Registers, initialises, and provides access to all form-plugin
 * integrations. Also exposes AJAX endpoints for the admin UI to
 * query available forms and fields from integrated plugins.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Integrations
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.0.0
 */

namespace WPRobo\DocuMerge\Integrations;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Integration_Manager
 *
 * Singleton that manages the lifecycle of all form-plugin integrations.
 * Handles registration, activation checks, hook wiring, and AJAX
 * endpoints for fetching external forms and fields.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Integration_Manager {

	/**
	 * Singleton instance.
	 *
	 * @since 1.0.0
	 * @var   WPRobo_DocuMerge_Integration_Manager|null
	 */
	private static $wprobo_documerge_instance = null;

	/**
	 * Registered integrations keyed by slug.
	 *
	 * @since 1.0.0
	 * @var   WPRobo_DocuMerge_Integration_Base[]
	 */
	private $wprobo_documerge_integrations = array();

	/**
	 * Get singleton instance.
	 *
	 * @since  1.0.0
	 * @return WPRobo_DocuMerge_Integration_Manager
	 */
	public static function get_instance() {
		if ( null === self::$wprobo_documerge_instance ) {
			self::$wprobo_documerge_instance = new self();
		}
		return self::$wprobo_documerge_instance;
	}

	/**
	 * Constructor -- private for singleton.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		// Intentionally empty -- initialisation happens in wprobo_documerge_init().
	}

	/**
	 * Register an integration.
	 *
	 * @since  1.0.0
	 * @param  WPRobo_DocuMerge_Integration_Base $integration The integration instance to register.
	 * @return void
	 */
	public function wprobo_documerge_register( WPRobo_DocuMerge_Integration_Base $integration ) {
		$this->wprobo_documerge_integrations[ $integration->wprobo_documerge_get_slug() ] = $integration;
	}

	/**
	 * Register all built-in integrations and activate those whose
	 * plugins are currently active.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function wprobo_documerge_init() {
		// Register built-in integrations.
		$this->wprobo_documerge_register( new WPRobo_DocuMerge_Integration_WPForms() );
		$this->wprobo_documerge_register( new WPRobo_DocuMerge_Integration_CF7() );
		$this->wprobo_documerge_register( new WPRobo_DocuMerge_Integration_Gravity() );
		$this->wprobo_documerge_register( new WPRobo_DocuMerge_Integration_Fluent() );

		/**
		 * Fires after built-in integrations are registered, allowing
		 * third-party plugins to register additional integrations.
		 *
		 * @since 1.0.0
		 * @param WPRobo_DocuMerge_Integration_Manager $manager The integration manager instance.
		 */
		do_action( 'wprobo_documerge_register_integrations', $this );

		// Wire hooks for all active integrations.
		foreach ( $this->wprobo_documerge_integrations as $integration ) {
			if ( $integration->wprobo_documerge_is_active() ) {
				$integration->wprobo_documerge_register_hooks();
			}
		}
	}

	/**
	 * Get all integrations whose external plugin is currently active.
	 *
	 * @since  1.0.0
	 * @return WPRobo_DocuMerge_Integration_Base[] Active integrations keyed by slug.
	 */
	public function wprobo_documerge_get_active_integrations() {
		$active = array();
		foreach ( $this->wprobo_documerge_integrations as $slug => $integration ) {
			if ( $integration->wprobo_documerge_is_active() ) {
				$active[ $slug ] = $integration;
			}
		}
		return $active;
	}

	/**
	 * Get all registered integrations regardless of activation status.
	 *
	 * @since  1.0.0
	 * @return WPRobo_DocuMerge_Integration_Base[] All integrations keyed by slug.
	 */
	public function wprobo_documerge_get_all_integrations() {
		return $this->wprobo_documerge_integrations;
	}

	/**
	 * Get a specific integration by its slug.
	 *
	 * @since  1.0.0
	 * @param  string $slug The integration slug.
	 * @return WPRobo_DocuMerge_Integration_Base|null The integration or null if not found.
	 */
	public function wprobo_documerge_get_integration( $slug ) {
		if ( isset( $this->wprobo_documerge_integrations[ $slug ] ) ) {
			return $this->wprobo_documerge_integrations[ $slug ];
		}
		return null;
	}

	/**
	 * Register AJAX action hooks for the admin UI.
	 *
	 * @since  1.0.0
	 * @return void
	 */
	public function wprobo_documerge_init_hooks() {
		add_action( 'wp_ajax_wprobo_documerge_get_integration_forms', array( $this, 'wprobo_documerge_ajax_get_integration_forms' ) );
		add_action( 'wp_ajax_wprobo_documerge_get_integration_fields', array( $this, 'wprobo_documerge_ajax_get_integration_fields' ) );
	}

	/**
	 * AJAX handler: return available forms from an integrated plugin.
	 *
	 * Expects POST parameters:
	 *   - nonce       (string) Security nonce.
	 *   - integration (string) Integration slug.
	 *
	 * @since  1.0.0
	 * @return void Sends JSON response and terminates.
	 */
	public function wprobo_documerge_ajax_get_integration_forms() {
		check_ajax_referer( 'wprobo_documerge_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to perform this action.', 'wprobo-documerge' ) ),
				403
			);
		}

		$slug        = isset( $_POST['integration'] ) ? sanitize_key( wp_unslash( $_POST['integration'] ) ) : '';
		$integration = $this->wprobo_documerge_get_integration( $slug );

		if ( ! $integration || ! $integration->wprobo_documerge_is_active() ) {
			wp_send_json_error(
				array( 'message' => __( 'Integration is not available or not active.', 'wprobo-documerge' ) )
			);
		}

		$forms = $integration->wprobo_documerge_get_available_forms();

		wp_send_json_success( $forms );
	}

	/**
	 * AJAX handler: return fields for a specific external form.
	 *
	 * Expects POST parameters:
	 *   - nonce            (string) Security nonce.
	 *   - integration      (string) Integration slug.
	 *   - external_form_id (int)    The external plugin's form ID.
	 *
	 * @since  1.0.0
	 * @return void Sends JSON response and terminates.
	 */
	public function wprobo_documerge_ajax_get_integration_fields() {
		check_ajax_referer( 'wprobo_documerge_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'message' => __( 'You do not have permission to perform this action.', 'wprobo-documerge' ) ),
				403
			);
		}

		$slug        = isset( $_POST['integration'] ) ? sanitize_key( wp_unslash( $_POST['integration'] ) ) : '';
		$integration = $this->wprobo_documerge_get_integration( $slug );

		if ( ! $integration || ! $integration->wprobo_documerge_is_active() ) {
			wp_send_json_error(
				array( 'message' => __( 'Integration is not available or not active.', 'wprobo-documerge' ) )
			);
		}

		$form_id = isset( $_POST['external_form_id'] ) ? absint( $_POST['external_form_id'] ) : 0;

		if ( ! $form_id ) {
			wp_send_json_error(
				array( 'message' => __( 'Invalid form ID.', 'wprobo-documerge' ) )
			);
		}

		$fields = $integration->wprobo_documerge_get_form_fields( $form_id );

		wp_send_json_success( $fields );
	}
}
