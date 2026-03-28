<?php
/**
 * QR code image generator.
 *
 * Generates QR code PNG images from arbitrary string data using the
 * Google Charts API and saves them to the plugin temp directory for
 * embedding into DOCX documents.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Document
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.3.0
 */

namespace WPRobo\DocuMerge\Document;

use WP_Error;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPRobo_DocuMerge_QR_Generator
 *
 * Generates QR code PNG images via the Google Charts API. The images
 * are written to the documerge-temp directory and are intended for
 * short-lived use during DOCX merge operations. Temporary files are
 * cleaned up by the hourly cron or immediately after document save.
 *
 * @since 1.3.0
 */
class WPRobo_DocuMerge_QR_Generator {

    /**
     * Google Charts API base URL for QR code generation.
     *
     * @since 1.3.0
     * @var   string
     */
    private $wprobo_documerge_api_url = 'https://chart.googleapis.com/chart';

    /**
     * Generate a QR code PNG file for the given data string.
     *
     * Uses the Google Charts API to generate the QR code image and
     * saves it to the specified path or a default temp location.
     *
     * @since  1.3.0
     * @param  string $data    The data to encode (URL, text, reference number, etc.).
     * @param  int    $size    The image size in pixels (default 200).
     * @param  string $save_to Absolute path to save the PNG file. If empty, a temp path is generated.
     * @return string|WP_Error The absolute file path on success, or WP_Error on failure.
     */
    public function wprobo_documerge_generate( $data, $size = 200, $save_to = '' ) {

        if ( empty( $data ) ) {
            return new WP_Error(
                'wprobo_documerge_qr_empty_data',
                __( 'No data provided for QR code generation.', 'wprobo-documerge' )
            );
        }

        // Build the output path if not provided.
        if ( empty( $save_to ) ) {
            $temp_dir = defined( 'WPROBO_DOCUMERGE_TEMP_DIR' )
                ? WPROBO_DOCUMERGE_TEMP_DIR
                : WP_CONTENT_DIR . '/uploads/documerge-temp/';

            $save_to = $temp_dir . 'qr_' . md5( $data . microtime( true ) ) . '.png';
        }

        // Ensure the target directory exists.
        $dir = dirname( $save_to );
        if ( ! is_dir( $dir ) ) {
            wp_mkdir_p( $dir );
        }

        // Build Google Charts API URL.
        $api_url = add_query_arg(
            array(
                'chs'  => absint( $size ) . 'x' . absint( $size ),
                'cht'  => 'qr',
                'chl'  => rawurlencode( $data ),
                'choe' => 'UTF-8',
            ),
            $this->wprobo_documerge_api_url
        );

        // Fetch the QR code image.
        $response = wp_remote_get(
            $api_url,
            array(
                'timeout' => 15,
            )
        );

        if ( is_wp_error( $response ) ) {
            return new WP_Error(
                'wprobo_documerge_qr_request_failed',
                sprintf(
                    /* translators: %s: HTTP error message. */
                    __( 'QR code request failed: %s', 'wprobo-documerge' ),
                    $response->get_error_message()
                )
            );
        }

        $response_code = wp_remote_retrieve_response_code( $response );
        $body          = wp_remote_retrieve_body( $response );

        if ( 200 !== $response_code || empty( $body ) ) {
            return new WP_Error(
                'wprobo_documerge_qr_generation_failed',
                sprintf(
                    /* translators: %d: HTTP response code. */
                    __( 'QR code generation failed with HTTP status %d.', 'wprobo-documerge' ),
                    $response_code
                )
            );
        }

        // Write the PNG image using WP Filesystem API.
        global $wp_filesystem;

        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $written = $wp_filesystem->put_contents( $save_to, $body, FS_CHMOD_FILE );

        if ( ! $written || ! file_exists( $save_to ) ) {
            return new WP_Error(
                'wprobo_documerge_qr_save_failed',
                __( 'Failed to save QR code image to disk.', 'wprobo-documerge' )
            );
        }

        return $save_to;
    }
}
