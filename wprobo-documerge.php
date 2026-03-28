<?php
/**
 * WPRobo DocuMerge Lite
 *
 * @package           WPRobo_DocuMerge
 * @author            Ali Shan
 * @copyright         2026 WPRobo Limited
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WPRobo DocuMerge Lite
 * Plugin URI:        https://wprobo.com/plugins/wprobo-documerge
 * Description:       Automate document generation from form submissions. Upload Word/DOCX templates, collect data via forms, and deliver personalised documents automatically. <a href="https://wprobo.com/plugins/wprobo-documerge/?utm_source=lite&utm_medium=plugin&utm_campaign=upgrade">Upgrade to Pro</a> for signature fields, Stripe payments, conditional logic, and more.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Ali Shan
 * Author URI:        https://wprobo.com
 * Text Domain:       wprobo-documerge
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants.
define( 'WPROBO_DOCUMERGE_VERSION',    '1.0.0' );
define( 'WPROBO_DOCUMERGE_DB_VERSION', '1.0.0' );
define( 'WPROBO_DOCUMERGE_FILE',       __FILE__ );
define( 'WPROBO_DOCUMERGE_PATH',       plugin_dir_path( __FILE__ ) );
define( 'WPROBO_DOCUMERGE_URL',        plugin_dir_url( __FILE__ ) );
define( 'WPROBO_DOCUMERGE_BASENAME',   plugin_basename( __FILE__ ) );
define( 'WPROBO_DOCUMERGE_DOCS_DIR',   WP_CONTENT_DIR . '/uploads/documerge-docs/' );
define( 'WPROBO_DOCUMERGE_TEMP_DIR',   WP_CONTENT_DIR . '/uploads/documerge-temp/' );
define( 'WPROBO_DOCUMERGE_LITE',       true );

// Autoloader.
if ( file_exists( WPROBO_DOCUMERGE_PATH . 'vendor/autoload.php' ) ) {
    require_once WPROBO_DOCUMERGE_PATH . 'vendor/autoload.php';
}

// Activation hook — deactivate Pro if active, then run installer.
register_activation_hook( WPROBO_DOCUMERGE_FILE, function() {
    // Deactivate Pro if active.
    $pro_plugin = 'wprobo-docu-merge/wprobo-documerge.php';
    if ( ! function_exists( 'is_plugin_active' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    if ( is_plugin_active( $pro_plugin ) ) {
        deactivate_plugins( $pro_plugin );
    }
    // Run installer.
    \WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Installer::wprobo_documerge_activate();
} );

// Deactivation hook.
register_deactivation_hook(
    WPROBO_DOCUMERGE_FILE,
    array( 'WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Deactivator', 'wprobo_documerge_deactivate' )
);

// Bootstrap the plugin.
add_action( 'plugins_loaded', function() {
    WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Plugin::get_instance()->wprobo_documerge_run();
} );
