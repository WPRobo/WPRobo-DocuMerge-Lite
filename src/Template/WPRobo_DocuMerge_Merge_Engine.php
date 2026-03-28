<?php
/**
 * Template merge-tag replacement engine.
 *
 * Processes DOCX templates by replacing merge tags with field data,
 * evaluating conditional blocks, embedding signature and other images,
 * processing repeater/table rows, and applying format modifiers.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Template
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.0.0
 */

namespace WPRobo\DocuMerge\Template;

use PhpOffice\PhpWord\TemplateProcessor;
use WPRobo\DocuMerge\Document\WPRobo_DocuMerge_QR_Generator;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPRobo_DocuMerge_Merge_Engine
 *
 * Core tag replacement engine that processes DOCX templates by
 * injecting system auto-tags, applying modifiers, handling
 * conditionals, processing repeater rows, embedding images,
 * and embedding signature images.
 *
 * This class is NOT a singleton; create a new instance per merge operation.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Merge_Engine {

    /**
     * Temporary file paths created during processing.
     *
     * Stores paths to temp files (e.g. decoded images) so the caller
     * can clean them up after saveAs().
     *
     * @since 1.3.0
     * @var   array
     */
    private $wprobo_documerge_temp_files = array();

    /**
     * Run the full merge pipeline on a template.
     *
     * Executes every processing step in the correct order:
     * 1. Format / modifier pipes (direct XML replacement)
     * 2. Conditional blocks — enhanced syntax (XML manipulation)
     * 3. Conditional blocks — legacy syntax (XML manipulation)
     * 4. Table / repeater tags (cloneRow)
     * 5. Image tags (setImageValue)
     * 6. Standard tag replacement (setValue) — includes QR, signature, system tags
     *
     * @since  1.3.0
     * @param  TemplateProcessor $template_processor The PhpWord template processor instance.
     * @param  array             $field_data         Associative array of tag_name => value pairs.
     * @return TemplateProcessor The modified template processor instance.
     */
    public function wprobo_documerge_run_pipeline( TemplateProcessor $template_processor, array $field_data ) {

        // Step 1: Process format / modifier pipes directly in the XML.
        $field_data = $this->wprobo_documerge_process_format_pipes( $template_processor, $field_data );

        // Step 2: Enhanced conditional blocks ({if:field=value}...{/if:field}).
        $this->wprobo_documerge_process_enhanced_conditionals( $template_processor, $field_data );

        // Step 3: Legacy conditional blocks ({if:field == value}...{/if}).
        $this->wprobo_documerge_process_conditionals( $template_processor, $field_data );

        // Step 4: Table / repeater tags.
        $this->wprobo_documerge_process_repeater_tags( $template_processor, $field_data );

        // Step 5: Image tags.
        $this->wprobo_documerge_process_image_tags( $template_processor, $field_data );

        // Step 6: Standard tag replacement (includes QR, signature, system tags).
        $this->wprobo_documerge_replace_tags( $template_processor, $field_data );

        return $template_processor;
    }

    /**
     * Get temporary file paths created during processing.
     *
     * The caller should delete these files after calling saveAs()
     * on the template processor.
     *
     * @since  1.3.0
     * @return array List of absolute file paths.
     */
    public function wprobo_documerge_get_temp_files() {
        return $this->wprobo_documerge_temp_files;
    }

    /**
     * Replace merge tags in a template with field data.
     *
     * Injects system auto-tags (current_date, current_time, site_name,
     * site_url), then processes each user-supplied field value. Supports
     * modifiers (|upper, |lower, |ucfirst, |ucwords, |format:X) and
     * signature embedding. All values are XML-escaped before insertion.
     * Any remaining unfilled tags are stripped after processing.
     *
     * @since  1.0.0
     * @param  TemplateProcessor $template_processor The PhpWord template processor instance.
     * @param  array             $field_data         Associative array of tag_name => value pairs.
     * @return TemplateProcessor The modified template processor instance.
     */
    public function wprobo_documerge_replace_tags( TemplateProcessor $template_processor, array $field_data ) {

        // Process QR code tags before standard replacement.
        $this->wprobo_documerge_process_qr_tags( $template_processor, $field_data );

        // Inject system auto-tags.
        $date_format = get_option( 'wprobo_documerge_date_format', 'd/m/Y' );
        $time_format = get_option( 'wprobo_documerge_time_format', 'H:i' );

        $system_tags = array(
            'current_date' => gmdate( $date_format ),
            'current_time' => gmdate( $time_format ),
            'site_name'    => get_bloginfo( 'name' ),
            'site_url'     => home_url(),
        );

        foreach ( $system_tags as $tag => $value ) {
            $escaped = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
            $template_processor->setValue( $tag, $escaped );
        }

        // Process each user-supplied field.
        foreach ( $field_data as $tag => $value ) {

            // Skip repeater arrays — already handled by process_repeater_tags.
            if ( is_array( $value ) ) {
                continue;
            }

            // Handle signature tag separately.
            if ( 'signature' === $tag ) {
                $this->wprobo_documerge_embed_signature( $template_processor, $value );
                continue;
            }

            // Parse modifiers from the tag name.
            $raw_tag  = $tag;
            $modifier = '';

            if ( false !== strpos( $tag, '|' ) ) {
                $parts    = explode( '|', $tag, 2 );
                $raw_tag  = $parts[0];
                $modifier = $parts[1];
            }

            // Apply modifiers to the value.
            $value = $this->wprobo_documerge_apply_modifier( (string) $value, $modifier );

            // XML-escape the value for safe insertion into DOCX XML.
            $escaped_value = htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );

            $template_processor->setValue( $raw_tag, $escaped_value );
        }

        // Strip any remaining unfilled tags.
        $remaining = $template_processor->getVariables();

        foreach ( $remaining as $unfilled_tag ) {
            $template_processor->setValue( $unfilled_tag, '' );
        }

        return $template_processor;
    }

    /**
     * Process format/modifier pipe syntax in the template XML.
     *
     * Scans the template for tags containing pipe modifiers such as
     * `{date_signed|format:d M Y}`, `{name|upper}`, `{name|lower}`,
     * `{name|ucfirst}`, or `{name|ucwords}`. These are replaced
     * directly in the XML because PHPWord's getVariables() does not
     * always return pipe-containing variable names reliably.
     *
     * Must be called BEFORE standard tag replacement.
     *
     * @since  1.3.0
     * @param  TemplateProcessor $template_processor The PhpWord template processor instance.
     * @param  array             $field_data         Associative array of tag_name => value pairs.
     * @return array The field_data array (unmodified — pipes are resolved in XML).
     */
    public function wprobo_documerge_process_format_pipes( TemplateProcessor $template_processor, array $field_data ) {

        $reflection = new \ReflectionClass( $template_processor );
        $property   = $reflection->getProperty( 'tempDocumentMainPart' );
        $property->setAccessible( true );

        $xml = $property->getValue( $template_processor );

        // Match tags with pipes: ${field|modifier} or ${field|format:pattern}
        // PHPWord stores variables as ${var} in the XML.
        $pattern = '/\$\{([a-zA-Z0-9_]+)\|([^}]+)\}/';

        $xml = preg_replace_callback( $pattern, function ( $matches ) use ( $field_data ) {

            $field_name = $matches[1];
            $modifier   = $matches[2];
            $value      = isset( $field_data[ $field_name ] ) ? (string) $field_data[ $field_name ] : '';

            $value = $this->wprobo_documerge_apply_modifier( $value, $modifier );

            return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );

        }, $xml );

        $property->setValue( $template_processor, $xml );

        return $field_data;
    }

    /**
     * Apply a modifier string to a value.
     *
     * Supported modifiers:
     * - `upper`          — Convert to uppercase.
     * - `lower`          — Convert to lowercase.
     * - `ucfirst`        — Uppercase first character.
     * - `ucwords`        — Uppercase first character of each word.
     * - `format:PATTERN` — Format a date value using PHP gmdate() syntax.
     *
     * Returns the original value unchanged if the modifier is empty
     * or unrecognised.
     *
     * @since  1.3.0
     * @param  string $value    The raw field value.
     * @param  string $modifier The modifier string (e.g. "upper", "format:d M Y").
     * @return string The modified value.
     */
    public function wprobo_documerge_apply_modifier( $value, $modifier ) {

        $modifier = trim( $modifier );

        if ( empty( $modifier ) ) {
            return $value;
        }

        if ( 'upper' === $modifier ) {
            return strtoupper( $value );
        }

        if ( 'lower' === $modifier ) {
            return strtolower( $value );
        }

        if ( 'ucfirst' === $modifier ) {
            return ucfirst( $value );
        }

        if ( 'ucwords' === $modifier ) {
            return ucwords( $value );
        }

        if ( 0 === strpos( $modifier, 'format:' ) ) {
            $format_string = substr( $modifier, 7 );
            $timestamp     = strtotime( $value );

            if ( false !== $timestamp ) {
                return gmdate( $format_string, $timestamp );
            }
        }

        return $value;
    }

    /**
     * Process repeater/table merge tags in the template.
     *
     * Handles repeating table rows where the template contains
     * `${fieldname.column}` variables in a table row. The merge_data
     * value for the repeater field must be a JSON-encoded string or
     * a PHP array of associative row arrays.
     *
     * Template table row example:
     *   ${items.item} | ${items.qty} | ${items.price}
     *
     * PHPWord's cloneRow() duplicates the row for each data entry,
     * then setValue() fills each cloned row using `column#row_index`.
     *
     * @since  1.3.0
     * @param  TemplateProcessor $template_processor The PhpWord template processor instance.
     * @param  array             $field_data         Associative array of tag_name => value pairs.
     * @return TemplateProcessor The modified template processor instance.
     */
    public function wprobo_documerge_process_repeater_tags( TemplateProcessor $template_processor, array $field_data ) {

        foreach ( $field_data as $tag => $value ) {

            // Determine if this field contains repeater data.
            $rows = $this->wprobo_documerge_parse_repeater_value( $value );

            if ( false === $rows || empty( $rows ) ) {
                continue;
            }

            // Verify the first row is associative to get column names.
            $first_row = reset( $rows );

            if ( ! is_array( $first_row ) ) {
                continue;
            }

            $columns = array_keys( $first_row );

            if ( empty( $columns ) ) {
                continue;
            }

            // The cloneRow anchor is the first column: "tag.column".
            $anchor = sanitize_text_field( $tag ) . '.' . sanitize_text_field( $columns[0] );

            // Check if the anchor variable exists in the template.
            $variables = $template_processor->getVariables();

            if ( ! in_array( $anchor, $variables, true ) ) {
                continue;
            }

            $row_count = count( $rows );

            // Clone the table row.
            $template_processor->cloneRow( $anchor, $row_count );

            // Fill each cloned row (1-indexed).
            foreach ( $rows as $index => $row ) {
                $row_num = $index + 1;

                foreach ( $columns as $column ) {
                    $cell_value = isset( $row[ $column ] ) ? sanitize_text_field( (string) $row[ $column ] ) : '';
                    $cell_value = htmlspecialchars( $cell_value, ENT_QUOTES, 'UTF-8' );

                    $variable_name = sanitize_text_field( $tag ) . '.' . sanitize_text_field( $column ) . '#' . $row_num;
                    $template_processor->setValue( $variable_name, $cell_value );
                }
            }
        }

        return $template_processor;
    }

    /**
     * Parse a repeater field value into an array of rows.
     *
     * Accepts either a JSON-encoded string or a PHP array. Returns
     * the decoded array of rows, or false if the value is not valid
     * repeater data.
     *
     * @since  1.3.0
     * @param  mixed $value The field value (string or array).
     * @return array|false Array of associative row arrays, or false.
     */
    public function wprobo_documerge_parse_repeater_value( $value ) {

        if ( is_array( $value ) ) {
            // Already an array — check it looks like rows.
            if ( ! empty( $value ) && is_array( reset( $value ) ) ) {
                return $value;
            }
            return false;
        }

        if ( ! is_string( $value ) ) {
            return false;
        }

        // Attempt JSON decode.
        $decoded = json_decode( $value, true );

        if ( ! is_array( $decoded ) || empty( $decoded ) ) {
            return false;
        }

        // Must be a numerically-indexed array of associative arrays.
        $first = reset( $decoded );

        if ( ! is_array( $first ) ) {
            return false;
        }

        return $decoded;
    }

    /**
     * Process image merge tags in the template.
     *
     * Supports two syntax forms:
     * - `{photo:WIDTHxHEIGHT}` — embed image at specified dimensions.
     * - `{photo}` where the value is a file path or base64 data URI.
     *
     * Also handles base64 data URIs by decoding to a temporary file
     * before embedding with PHPWord's setImageValue().
     *
     * The signature tag is excluded from image processing here as it
     * is handled by the dedicated embed_signature method.
     *
     * Must be called BEFORE standard tag replacement to ensure image
     * tags are processed and not treated as text.
     *
     * @since  1.3.0
     * @param  TemplateProcessor $template_processor The PhpWord template processor instance.
     * @param  array             $field_data         Associative array of tag_name => value pairs.
     * @return TemplateProcessor The modified template processor instance.
     */
    public function wprobo_documerge_process_image_tags( TemplateProcessor $template_processor, array $field_data ) {

        $variables = $template_processor->getVariables();

        // Collect image tags: both `field:WxH` and plain `field` where value is an image.
        $image_tags = array();

        foreach ( $variables as $var ) {

            // Skip the signature tag — handled separately.
            if ( 'signature' === $var ) {
                continue;
            }

            // Skip QR tags — handled by process_qr_tags.
            if ( 0 === strpos( $var, 'qr:' ) ) {
                continue;
            }

            // Check for dimension syntax: field:WIDTHxHEIGHT.
            if ( preg_match( '/^([a-zA-Z0-9_]+):(\d+)x(\d+)$/', $var, $dim_match ) ) {
                $field_name = $dim_match[1];
                $width      = (int) $dim_match[2];
                $height     = (int) $dim_match[3];

                if ( isset( $field_data[ $field_name ] ) && ! empty( $field_data[ $field_name ] ) && ! is_array( $field_data[ $field_name ] ) ) {
                    $image_tags[] = array(
                        'variable'   => $var,
                        'field_name' => $field_name,
                        'width'      => $width,
                        'height'     => $height,
                    );
                }
                continue;
            }

            // Check plain variable names where the value looks like an image.
            if ( isset( $field_data[ $var ] ) && is_string( $field_data[ $var ] ) ) {
                $val = $field_data[ $var ];

                if ( $this->wprobo_documerge_is_image_value( $val ) ) {
                    $image_tags[] = array(
                        'variable'   => $var,
                        'field_name' => $var,
                        'width'      => 200,
                        'height'     => 150,
                    );
                }
            }
        }

        // Process each image tag.
        foreach ( $image_tags as $img ) {

            $value      = $field_data[ $img['field_name'] ];
            $image_path = $this->wprobo_documerge_resolve_image_path( $value );

            if ( empty( $image_path ) || ! file_exists( $image_path ) ) {
                // Remove the tag placeholder if image cannot be resolved.
                $template_processor->setValue( $img['variable'], '' );
                continue;
            }

            try {
                $template_processor->setImageValue( $img['variable'], array(
                    'path'   => $image_path,
                    'width'  => $img['width'],
                    'height' => $img['height'],
                    'ratio'  => true,
                ) );
            } catch ( \Exception $e ) {
                // Fallback: replace with empty string if image embedding fails.
                $template_processor->setValue( $img['variable'], '' );
            }
        }

        return $template_processor;
    }

    /**
     * Determine if a field value represents an image.
     *
     * Checks for base64 data URIs with image MIME types and for
     * file paths ending with common image extensions.
     *
     * @since  1.3.0
     * @param  string $value The field value to inspect.
     * @return bool True if the value appears to be an image.
     */
    public function wprobo_documerge_is_image_value( $value ) {

        if ( empty( $value ) || ! is_string( $value ) ) {
            return false;
        }

        // Base64 data URI with image type.
        if ( preg_match( '/^data:image\/(png|jpe?g|gif|webp|bmp);base64,/', $value ) ) {
            return true;
        }

        // File path with image extension.
        $extension = strtolower( pathinfo( $value, PATHINFO_EXTENSION ) );

        return in_array( $extension, array( 'png', 'jpg', 'jpeg', 'gif', 'webp', 'bmp' ), true );
    }

    /**
     * Resolve an image value to an absolute file path.
     *
     * If the value is a base64 data URI, it is decoded and written
     * to a temporary file. If it is already a file path, it is
     * returned as-is after validation.
     *
     * @since  1.3.0
     * @param  string $value A base64 data URI or absolute file path.
     * @return string The absolute path to the image file, or empty string on failure.
     */
    public function wprobo_documerge_resolve_image_path( $value ) {

        if ( empty( $value ) ) {
            return '';
        }

        // Handle base64 data URI.
        if ( preg_match( '/^data:image\/(png|jpe?g|gif|webp|bmp);base64,(.+)$/s', $value, $matches ) ) {

            $extension = $matches[1];
            $base64    = $matches[2];

            // Normalise jpeg extension.
            if ( 'jpeg' === $extension ) {
                $extension = 'jpg';
            }

            // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
            $image_data = base64_decode( $base64, true );

            if ( false === $image_data ) {
                return '';
            }

            // Ensure temp directory exists.
            $temp_dir = defined( 'WPROBO_DOCUMERGE_TEMP_DIR' ) ? WPROBO_DOCUMERGE_TEMP_DIR : sys_get_temp_dir() . '/';

            if ( ! is_dir( $temp_dir ) ) {
                wp_mkdir_p( $temp_dir );
            }

            $temp_path = $temp_dir . 'img_' . uniqid() . '.' . sanitize_file_name( $extension );

            global $wp_filesystem;

            if ( empty( $wp_filesystem ) ) {
                require_once ABSPATH . 'wp-admin/includes/file.php';
                WP_Filesystem();
            }

            $wp_filesystem->put_contents( $temp_path, $image_data, FS_CHMOD_FILE );

            $this->wprobo_documerge_temp_files[] = $temp_path;

            return $temp_path;
        }

        // Already a file path — validate it exists.
        if ( file_exists( $value ) ) {
            return $value;
        }

        return '';
    }

    /**
     * Process enhanced conditional blocks in the template XML.
     *
     * Supports the compact conditional syntax:
     * - `{if:field_name=value}...content...{/if:field_name}`  — equals
     * - `{if:field_name!=value}...content...{/if:field_name}` — not equals
     * - `{if:field_name}...content...{/if:field_name}`        — is not empty
     * - `{if:!field_name}...content...{/if:field_name}`       — is empty
     *
     * If the condition is TRUE the content is kept and the markers
     * are removed. If FALSE the entire block including content and
     * markers is removed.
     *
     * This method should be called BEFORE the legacy process_conditionals
     * and BEFORE standard tag replacement.
     *
     * @since  1.3.0
     * @param  TemplateProcessor $template_processor The PhpWord template processor instance.
     * @param  array             $field_data         Associative array of tag_name => value pairs.
     * @return TemplateProcessor The modified template processor instance.
     */
    public function wprobo_documerge_process_enhanced_conditionals( TemplateProcessor $template_processor, array $field_data ) {

        $reflection = new \ReflectionClass( $template_processor );
        $property   = $reflection->getProperty( 'tempDocumentMainPart' );
        $property->setAccessible( true );

        $xml = $property->getValue( $template_processor );

        // Pattern 1: {if:field=value} or {if:field!=value} with closing {/if:field}.
        // The closing tag includes the field name for unambiguous nesting.
        $pattern_eq = '/\{if:(\w+)(!=|=)([^}]*)\}(.*?)\{\/if:\1\}/s';

        $xml = preg_replace_callback( $pattern_eq, function ( $matches ) use ( $field_data ) {

            $field_name = $matches[1];
            $operator   = $matches[2];
            $test_value = $matches[3];
            $content    = $matches[4];

            $field_value = isset( $field_data[ $field_name ] ) ? (string) $field_data[ $field_name ] : '';

            $condition_met = false;

            if ( '=' === $operator ) {
                $condition_met = ( $field_value === $test_value );
            } elseif ( '!=' === $operator ) {
                $condition_met = ( $field_value !== $test_value );
            }

            return $condition_met ? $content : '';

        }, $xml );

        // Pattern 2: {if:field} — field is not empty.
        $pattern_notempty = '/\{if:(\w+)\}(.*?)\{\/if:\1\}/s';

        $xml = preg_replace_callback( $pattern_notempty, function ( $matches ) use ( $field_data ) {

            $field_name = $matches[1];
            $content    = $matches[2];

            $field_value = isset( $field_data[ $field_name ] ) ? $field_data[ $field_name ] : '';

            return ! empty( $field_value ) ? $content : '';

        }, $xml );

        // Pattern 3: {if:!field} — field is empty.
        $pattern_empty = '/\{if:!(\w+)\}(.*?)\{\/if:\1\}/s';

        $xml = preg_replace_callback( $pattern_empty, function ( $matches ) use ( $field_data ) {

            $field_name = $matches[1];
            $content    = $matches[2];

            $field_value = isset( $field_data[ $field_name ] ) ? $field_data[ $field_name ] : '';

            return empty( $field_value ) ? $content : '';

        }, $xml );

        $property->setValue( $template_processor, $xml );

        return $template_processor;
    }

    /**
     * Process conditional blocks in a template.
     *
     * Evaluates conditional patterns in the template XML of the form:
     * - {if:field_name == value}...content...{/if}
     * - {if:field_name != value}...content...{/if}
     * - {if:field_name empty}...content...{/if}
     * - {if:field_name !empty}...content...{/if}
     *
     * If the condition is true the content is kept and markers removed;
     * if false the entire block including content is removed.
     *
     * This method should be called BEFORE wprobo_documerge_replace_tags()
     * so that conditionals are evaluated before tag replacement occurs.
     *
     * @since  1.0.0
     * @param  TemplateProcessor $template_processor The PhpWord template processor instance.
     * @param  array             $field_data         Associative array of tag_name => value pairs.
     * @return TemplateProcessor The modified template processor instance.
     */
    public function wprobo_documerge_process_conditionals( TemplateProcessor $template_processor, array $field_data ) {

        // Access the internal document XML via reflection.
        $reflection = new \ReflectionClass( $template_processor );

        $property = $reflection->getProperty( 'tempDocumentMainPart' );
        $property->setAccessible( true );

        $xml = $property->getValue( $template_processor );

        // Match conditional blocks: {if:field_name operator value}...{/if}.
        // The pattern accounts for possible XML tags interleaved with the markers.
        $pattern = '/\{if:(\w+)\s+(==|!=|empty|!empty)\s*(.*?)\}(.*?)\{\/if\}/s';

        $xml = preg_replace_callback( $pattern, function ( $matches ) use ( $field_data ) {

            $field_name = $matches[1];
            $operator   = $matches[2];
            $test_value = trim( $matches[3] );
            $content    = $matches[4];

            $field_value = isset( $field_data[ $field_name ] ) ? $field_data[ $field_name ] : '';

            $condition_met = $this->wprobo_documerge_evaluate_condition(
                $field_value,
                $operator,
                $test_value
            );

            if ( $condition_met ) {
                // Keep content, remove only the if/endif markers.
                return $content;
            }

            // Condition false: remove entire block.
            return '';

        }, $xml );

        // Write the modified XML back into the template processor.
        $property->setValue( $template_processor, $xml );

        return $template_processor;
    }

    /**
     * Embed a signature image into the template.
     *
     * Decodes a base64-encoded PNG image and writes it to a temporary
     * file, then injects it into the template at the {signature} tag
     * position using PhpWord's setImageValue().
     *
     * @since  1.0.0
     * @param  TemplateProcessor $template_processor The PhpWord template processor instance.
     * @param  string            $base64_data        Base64-encoded PNG image data (with or without data URI prefix).
     * @return string The path to the temporary signature image file. The caller is responsible for cleanup after save.
     */
    public function wprobo_documerge_embed_signature( TemplateProcessor $template_processor, $base64_data ) {

        // Strip data URI prefix if present.
        if ( 0 === strpos( $base64_data, 'data:image/png;base64,' ) ) {
            $base64_data = substr( $base64_data, strlen( 'data:image/png;base64,' ) );
        }

        // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
        $image_data = base64_decode( $base64_data, true );

        if ( false === $image_data ) {
            return '';
        }

        // Ensure the temp directory exists.
        $temp_dir = WPROBO_DOCUMERGE_TEMP_DIR;

        if ( ! file_exists( $temp_dir ) ) {
            wp_mkdir_p( $temp_dir );
        }

        $temp_path = $temp_dir . 'sig_' . uniqid() . '.png';

        // Write signature image to temp file using WP Filesystem.
        global $wp_filesystem;

        if ( empty( $wp_filesystem ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        $wp_filesystem->put_contents( $temp_path, $image_data, FS_CHMOD_FILE );

        $this->wprobo_documerge_temp_files[] = $temp_path;

        // Embed the image in the template at the signature placeholder.
        $template_processor->setImageValue( 'signature', array(
            'path'  => $temp_path,
            'width' => 200,
            'height' => 50,
            'ratio' => true,
        ) );

        return $temp_path;
    }

    /**
     * Process QR code merge tags in the template.
     *
     * Scans the template for variables matching the pattern `qr:field_name`.
     * For each match, the referenced field value is used to generate a QR
     * code PNG image which is then embedded into the document at the tag
     * position via PHPWord's setImageValue(). If the referenced field is
     * empty or QR generation fails, the tag is replaced with plain text.
     *
     * This method MUST be called BEFORE standard setValue() tag replacement,
     * because setImageValue() requires the tag to still be present in the
     * template XML.
     *
     * @since  1.3.0
     * @param  TemplateProcessor $template_processor The PhpWord template processor instance.
     * @param  array             $field_data         Associative array of tag_name => value pairs.
     * @return void
     */
    public function wprobo_documerge_process_qr_tags( TemplateProcessor $template_processor, array $field_data ) {

        $variables = $template_processor->getVariables();

        foreach ( $variables as $var ) {

            // Only process tags that start with 'qr:'.
            if ( 0 !== strpos( $var, 'qr:' ) ) {
                continue;
            }

            // Extract the field name after the 'qr:' prefix.
            $field_name = substr( $var, 3 );

            // Look up the field value in the merge data.
            $value = isset( $field_data[ $field_name ] ) ? $field_data[ $field_name ] : '';

            if ( empty( $value ) ) {
                // No data to encode — remove the tag.
                $template_processor->setValue( $var, '' );
                continue;
            }

            // Generate the QR code image.
            $generator = new WPRobo_DocuMerge_QR_Generator();
            $qr_path   = $generator->wprobo_documerge_generate( $value, 200 );

            if ( is_wp_error( $qr_path ) ) {
                $template_processor->setValue( $var, '' );
                continue;
            }

            // Embed the QR code image into the document.
            try {
                $template_processor->setImageValue( $var, array(
                    'path'   => $qr_path,
                    'width'  => 150,
                    'height' => 150,
                ) );
            } catch ( \Exception $e ) {
                // Fallback: replace with empty string if image embedding fails.
                $template_processor->setValue( $var, '' );
            }
        }
    }

    /**
     * Evaluate a single conditional expression.
     *
     * Compares a field value against a test value using the specified
     * operator. Supports equality, inequality, and empty checks.
     *
     * @since  1.0.0
     * @param  string $field_value The actual value of the field from field data.
     * @param  string $operator    The comparison operator: ==, !=, empty, or !empty.
     * @param  string $test_value  The value to test against (unused for empty/!empty operators).
     * @return bool True if the condition is met, false otherwise.
     */
    public function wprobo_documerge_evaluate_condition( $field_value, $operator, $test_value ) {

        switch ( $operator ) {
            case '==':
                return $field_value === $test_value;

            case '!=':
                return $field_value !== $test_value;

            case 'empty':
                return empty( $field_value );

            case '!empty':
                return ! empty( $field_value );

            default:
                return false;
        }
    }
}
