<?php
/**
 * Encryption helper.
 *
 * Provides AES-256-CBC encryption and decryption for sensitive
 * option values such as API keys.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Helpers
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.0.0
 */

namespace WPRobo\DocuMerge\Helpers;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPRobo_DocuMerge_Encryptor
 *
 * Encrypts and decrypts strings using OpenSSL AES-256-CBC.
 * The encryption key is derived from the WordPress auth salt.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Encryptor {

    /**
     * The OpenSSL cipher method.
     *
     * @since 1.0.0
     * @var   string
     */
    private const WPROBO_DOCUMERGE_CIPHER = 'AES-256-CBC';

    /**
     * IV length in bytes for AES-256-CBC.
     *
     * @since 1.0.0
     * @var   int
     */
    private const WPROBO_DOCUMERGE_IV_LENGTH = 16;

    /**
     * Encrypt a plain-text value.
     *
     * Uses AES-256-CBC with a random IV prepended to the ciphertext.
     * The key is derived from wp_salt('auth') via SHA-256 hashing.
     *
     * @since  1.0.0
     * @param  string $value The plain-text value to encrypt.
     * @return string Base64-encoded string containing IV + ciphertext, or empty string on failure.
     */
    public static function wprobo_documerge_encrypt( $value ) {
        if ( empty( $value ) || ! function_exists( 'openssl_encrypt' ) ) {
            return '';
        }

        $key = hash( 'sha256', wp_salt( 'auth' ), true );

        /**
         * Filters the encryption key used for encrypting sensitive values.
         *
         * Allows overriding the default key derived from the WordPress
         * auth salt. The key must be a raw binary string of 32 bytes
         * (for AES-256-CBC).
         *
         * @since 1.2.0
         *
         * @param string $key The raw binary encryption key (32 bytes).
         */
        $key = apply_filters( 'wprobo_documerge_encryption_key', $key );

        $iv  = openssl_random_pseudo_bytes( self::WPROBO_DOCUMERGE_IV_LENGTH );

        if ( false === $iv ) {
            return '';
        }

        $ciphertext = openssl_encrypt( $value, self::WPROBO_DOCUMERGE_CIPHER, $key, OPENSSL_RAW_DATA, $iv );

        if ( false === $ciphertext ) {
            return '';
        }

        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
        return base64_encode( $iv . $ciphertext );
    }

    /**
     * Decrypt a previously encrypted value.
     *
     * Reverses the encryption performed by wprobo_documerge_encrypt().
     * Extracts the first 16 bytes as the IV and decrypts the remainder.
     *
     * @since  1.0.0
     * @param  string $value The base64-encoded encrypted string.
     * @return string The decrypted plain-text value, or empty string on failure.
     */
    public static function wprobo_documerge_decrypt( $value ) {
        if ( empty( $value ) || ! function_exists( 'openssl_decrypt' ) ) {
            return '';
        }

        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
        $decoded = base64_decode( $value, true );

        if ( false === $decoded || strlen( $decoded ) <= self::WPROBO_DOCUMERGE_IV_LENGTH ) {
            return '';
        }

        $key        = hash( 'sha256', wp_salt( 'auth' ), true );

        /** This filter is documented in src/Helpers/WPRobo_DocuMerge_Encryptor.php */
        $key = apply_filters( 'wprobo_documerge_encryption_key', $key );

        $iv         = substr( $decoded, 0, self::WPROBO_DOCUMERGE_IV_LENGTH );
        $ciphertext = substr( $decoded, self::WPROBO_DOCUMERGE_IV_LENGTH );

        $plaintext = openssl_decrypt( $ciphertext, self::WPROBO_DOCUMERGE_CIPHER, $key, OPENSSL_RAW_DATA, $iv );

        if ( false === $plaintext ) {
            return '';
        }

        return $plaintext;
    }
}
