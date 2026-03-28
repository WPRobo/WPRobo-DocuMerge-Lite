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

// If Pro is already loaded, bail completely — Pro takes priority.
if ( defined( 'WPROBO_DOCUMERGE_PRO' ) ) {
    return;
}

// Lite marker.
if ( ! defined( 'WPROBO_DOCUMERGE_LITE' ) ) {
    define( 'WPROBO_DOCUMERGE_LITE', true );
}

// All constants guarded to prevent collisions during Lite/Pro switch.
if ( ! defined( 'WPROBO_DOCUMERGE_VERSION' ) ) {
    define( 'WPROBO_DOCUMERGE_VERSION', '1.0.0' );
}
if ( ! defined( 'WPROBO_DOCUMERGE_DB_VERSION' ) ) {
    define( 'WPROBO_DOCUMERGE_DB_VERSION', '1.0.0' );
}
if ( ! defined( 'WPROBO_DOCUMERGE_FILE' ) ) {
    define( 'WPROBO_DOCUMERGE_FILE', __FILE__ );
}
if ( ! defined( 'WPROBO_DOCUMERGE_PATH' ) ) {
    define( 'WPROBO_DOCUMERGE_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'WPROBO_DOCUMERGE_URL' ) ) {
    define( 'WPROBO_DOCUMERGE_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'WPROBO_DOCUMERGE_BASENAME' ) ) {
    define( 'WPROBO_DOCUMERGE_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'WPROBO_DOCUMERGE_DOCS_DIR' ) ) {
    define( 'WPROBO_DOCUMERGE_DOCS_DIR', WP_CONTENT_DIR . '/uploads/documerge-docs/' );
}
if ( ! defined( 'WPROBO_DOCUMERGE_TEMP_DIR' ) ) {
    define( 'WPROBO_DOCUMERGE_TEMP_DIR', WP_CONTENT_DIR . '/uploads/documerge-temp/' );
}

// Custom PSR-4 autoloader — no Composer vendor/ needed for Lite.
spl_autoload_register( function ( $class ) {
    $prefix   = 'WPRobo\\DocuMerge\\';
    $base_dir = WPROBO_DOCUMERGE_PATH . 'src/';
    $len      = strlen( $prefix );

    if ( 0 !== strncmp( $prefix, $class, $len ) ) {
        return;
    }

    $relative_class = substr( $class, $len );
    $parts          = explode( '\\', $relative_class );
    $classname      = array_pop( $parts );
    $subdir         = implode( '/', $parts );
    $file           = $base_dir . ( $subdir ? $subdir . '/' : '' ) . $classname . '.php';

    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

// Activation hook — deactivate Pro FIRST, then run installer.
register_activation_hook( __FILE__, function() {
    $pro_plugin = 'wprobo-docu-merge/wprobo-documerge.php';
    if ( ! function_exists( 'deactivate_plugins' ) ) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    deactivate_plugins( $pro_plugin, true );
    \WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Installer::wprobo_documerge_activate();
} );

// Deactivation hook.
register_deactivation_hook(
    __FILE__,
    array( 'WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Deactivator', 'wprobo_documerge_deactivate' )
);

// Bootstrap the plugin.
add_action( 'plugins_loaded', function() {
    // Final safety check — if Pro somehow loaded after us, don't double-bootstrap.
    if ( defined( 'WPROBO_DOCUMERGE_PRO' ) ) {
        return;
    }
    \WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Plugin::get_instance()->wprobo_documerge_run();
} );
