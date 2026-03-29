<?php
/**
 * WPRobo DocuMerge uninstall handler.
 *
 * Fired when the plugin is deleted via WP Admin → Plugins → Delete.
 * Removes ALL plugin data: database tables, options, files, and cron events.
 *
 * @package    WPRobo_DocuMerge
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.0.0
 */

// Only run if WordPress initiated the uninstall.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// 1. Drop custom DB tables.
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wprdm_submissions" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wprdm_forms" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}wprdm_templates" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

// 2. Delete all plugin options.
$options = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
	"SELECT option_name FROM {$wpdb->options}
     WHERE option_name LIKE 'wprobo_documerge_%'"
);
foreach ( $options as $option ) {
	delete_option( $option );
}

// 3. Delete all generated document files.
$upload_dir = wp_upload_dir();
$docs_dir   = $upload_dir['basedir'] . '/documerge-docs/';
$temp_dir   = $upload_dir['basedir'] . '/documerge-temp/';
$logs_dir   = $upload_dir['basedir'] . '/documerge-logs/';

/**
 * Recursively delete a directory and all its contents.
 *
 * @since 1.0.0
 * @param string $dir Absolute path to directory.
 */
function wprobo_documerge_delete_directory( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return;
	}
	$files = array_diff( scandir( $dir ), array( '.', '..' ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	foreach ( $files as $file ) {
		$path = $dir . DIRECTORY_SEPARATOR . $file;
		if ( is_dir( $path ) ) {
			wprobo_documerge_delete_directory( $path );
		} else {
			unlink( $path ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}
	}
	rmdir( $dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions
}

wprobo_documerge_delete_directory( $docs_dir );
wprobo_documerge_delete_directory( $temp_dir );
wprobo_documerge_delete_directory( $logs_dir );

// 4. Clear scheduled cron events.
wp_clear_scheduled_hook( 'wprobo_documerge_cleanup_temp_files' );
wp_clear_scheduled_hook( 'wprobo_documerge_cleanup_log_files' );
wp_clear_scheduled_hook( 'wprobo_documerge_retry_failed_emails' );
wp_clear_scheduled_hook( 'wprobo_documerge_cleanup_expired_tokens' );
