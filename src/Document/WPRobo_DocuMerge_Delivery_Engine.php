<?php
/**
 * Document delivery orchestrator.
 *
 * Handles all post-generation delivery: secure download tokens,
 * email delivery to submitters, admin notifications, and saving
 * generated documents to the WordPress media library.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Document
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.0.0
 */

namespace WPRobo\DocuMerge\Document;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPRobo_DocuMerge_Delivery_Engine
 *
 * Orchestrates document delivery through multiple channels:
 * download links with time-limited tokens, email with optional
 * file attachment, and WordPress media library import.
 *
 * @since 1.0.0
 */
class WPRobo_DocuMerge_Delivery_Engine {

	/**
	 * Default download token expiry in hours.
	 *
	 * @since 1.0.0
	 * @var   int
	 */
	private const WPROBO_DOCUMERGE_DEFAULT_EXPIRY_HOURS = 72;

	/**
	 * Maximum retry attempts for email delivery.
	 *
	 * @since 1.0.0
	 * @var   int
	 */
	private const WPROBO_DOCUMERGE_MAX_EMAIL_RETRIES = 1;

	/**
	 * Deliver a generated document through all configured delivery methods.
	 *
	 * Reads the form's delivery_methods column (comma-separated values:
	 * 'download', 'email', 'media') and dispatches to the appropriate
	 * handler for each method.
	 *
	 * @since  1.0.0
	 * @param  int $submission_id The submission ID to deliver.
	 * @return array|\WP_Error Array with 'download_url' and 'email_sent' on success, WP_Error on failure.
	 */
	public function wprobo_documerge_deliver( $submission_id ) {
		global $wpdb;

		$submission_id = absint( $submission_id );

		// ── Get submission ───────────────────────────────────────────────
		$submissions_table = $wpdb->prefix . 'wprdm_submissions';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$submission = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$submissions_table} WHERE id = %d",
				$submission_id
			)
		);

		if ( ! $submission ) {
			return new \WP_Error(
				'submission_not_found',
				/* translators: %d: submission ID */
				sprintf( __( 'Submission #%d not found.', 'wprobo-documerge' ), $submission_id )
			);
		}

		// ── Get form for delivery methods ────────────────────────────────
		$forms_table = $wpdb->prefix . 'wprdm_forms';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$forms_table} WHERE id = %d",
				absint( $submission->form_id )
			)
		);

		if ( ! $form ) {
			return new \WP_Error(
				'form_not_found',
				/* translators: %d: form ID */
				sprintf( __( 'Form #%d not found.', 'wprobo-documerge' ), absint( $submission->form_id ) )
			);
		}

		// Parse delivery methods — handle both JSON array and comma-separated.
		$raw_methods = $form->delivery_methods;
		$decoded     = json_decode( $raw_methods, true );
		if ( is_array( $decoded ) ) {
			$delivery_methods = array_map( 'sanitize_key', $decoded );
		} elseif ( ! empty( $raw_methods ) ) {
			$delivery_methods = array_map( 'sanitize_key', array_map( 'trim', explode( ',', $raw_methods ) ) );
		} else {
			$delivery_methods = array( 'download' );
		}
		// Default to download if empty after parsing.
		$delivery_methods = array_filter( $delivery_methods );
		if ( empty( $delivery_methods ) ) {
			$delivery_methods = array( 'download' );
		}

		/**
		 * Fires at the start of the delivery process.
		 *
		 * @since 1.1.0
		 *
		 * @param int   $submission_id    The submission ID.
		 * @param array $delivery_methods The delivery method slugs.
		 */
		do_action( 'wprobo_documerge_before_delivery', $submission_id, $delivery_methods );

		/**
		 * Filters the list of delivery methods before processing.
		 *
		 * @since 1.1.0
		 *
		 * @param array $delivery_methods The delivery method slugs.
		 * @param int   $submission_id    The submission ID.
		 * @param int   $form_id          The form ID.
		 */
		$delivery_methods = apply_filters( 'wprobo_documerge_delivery_methods', $delivery_methods, $submission_id, (int) $submission->form_id );

		$errors       = array();
		$download_url = '';
		$email_sent   = false;

		// ── Download ─────────────────────────────────────────────────────
		if ( in_array( 'download', $delivery_methods, true ) ) {
			$download_result = $this->wprobo_documerge_prepare_download( $submission_id );
			if ( is_wp_error( $download_result ) ) {
				$errors[] = $download_result->get_error_message();
			} else {
				$download_url = $download_result;
			}
		}

		// ── Email ────────────────────────────────────────────────────────
		if ( in_array( 'email', $delivery_methods, true ) ) {
			$email_result = $this->wprobo_documerge_send_submitter_email( $submission_id );
			if ( is_wp_error( $email_result ) ) {
				$errors[] = $email_result->get_error_message();
			} else {
				$email_sent = true;
			}
		}

		// ── Media library ────────────────────────────────────────────────
		if ( in_array( 'media', $delivery_methods, true ) ) {
			$attachment = $this->wprobo_documerge_save_to_media( $submission_id );
			if ( is_wp_error( $attachment ) ) {
				$errors[] = $attachment->get_error_message();
			}
		}

		// ── Custom delivery methods ─────────────────────────────────────
		$built_in_methods = array( 'download', 'email', 'media' );
		foreach ( $delivery_methods as $method ) {
			if ( ! in_array( $method, $built_in_methods, true ) ) {
				/**
				 * Fires for a custom (non-built-in) delivery method.
				 *
				 * Dynamic hook name based on the method slug. For example,
				 * a method named 'ftp' fires `wprobo_documerge_custom_delivery_ftp`.
				 *
				 * @since 1.1.0
				 *
				 * @param int    $submission_id The submission ID.
				 * @param object $submission    The submission row object.
				 */
				do_action( "wprobo_documerge_custom_delivery_{$method}", $submission_id, $submission );
			}
		}

		// ── Admin notification ───────────────────────────────────────────
		if ( '1' === get_option( 'wprobo_documerge_notify_new_submission', '1' ) ) {
			$this->wprobo_documerge_send_admin_notification( $submission_id );
		}

		// ── Update delivery status ───────────────────────────────────────
		if ( ! empty( $errors ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$submissions_table,
				array(
					'delivery_status' => 'partial',
					'error_log'       => sanitize_textarea_field( implode( '; ', $errors ) ),
					'updated_at'      => current_time( 'mysql' ),
				),
				array( 'id' => $submission_id ),
				array( '%s', '%s', '%s' ),
				array( '%d' )
			);

			/**
			 * Fires when document delivery fails entirely or partially.
			 *
			 * @since 1.1.0
			 *
			 * @param int       $submission_id The submission ID.
			 * @param \WP_Error $error         The WP_Error with combined error messages.
			 */
			do_action( 'wprobo_documerge_delivery_failed', $submission_id, new \WP_Error( 'delivery_failed', implode( ', ', $errors ) ) );

			return new \WP_Error(
				'delivery_partial',
				__( 'One or more delivery methods failed.', 'wprobo-documerge' )
			);
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$submissions_table,
			array(
				'delivery_status' => 'delivered',
				'updated_at'      => current_time( 'mysql' ),
			),
			array( 'id' => $submission_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		/**
		 * Fires after all delivery methods have completed successfully.
		 *
		 * @since 1.1.0
		 *
		 * @param int   $submission_id The submission ID.
		 * @param array $results       Associative array with 'download' and 'email' boolean statuses.
		 */
		do_action( 'wprobo_documerge_document_delivered', $submission_id, array( 'download' => ! empty( $download_url ), 'email' => $email_sent ) );

		return array(
			'download_url' => $download_url,
			'email_sent'   => $email_sent,
		);
	}

	/**
	 * Prepare a secure download URL for a submission.
	 *
	 * Generates a UUID-based token, stores it in wp_options with an
	 * expiry timestamp, and returns the public download URL.
	 *
	 * @since  1.0.0
	 * @param  int $submission_id The submission ID.
	 * @return string|\WP_Error The download URL on success, WP_Error on failure.
	 */
	public function wprobo_documerge_prepare_download( $submission_id ) {
		$submission_id = absint( $submission_id );

		$token        = wp_generate_uuid4();
		$expiry_hours = absint( get_option( 'wprobo_documerge_download_expiry_hours', self::WPROBO_DOCUMERGE_DEFAULT_EXPIRY_HOURS ) );

		/**
		 * Filters the download token expiry duration in hours.
		 *
		 * @since 1.1.0
		 *
		 * @param int $expiry_hours  The token expiry in hours.
		 * @param int $submission_id The submission ID.
		 */
		$expiry_hours = apply_filters( 'wprobo_documerge_download_token_expiry', $expiry_hours, $submission_id );
		$expiry_hours = absint( $expiry_hours );
		if ( $expiry_hours < 1 ) {
			$expiry_hours = 1;
		}

		$token_data = array(
			'submission_id' => $submission_id,
			'expiry'        => time() + ( $expiry_hours * HOUR_IN_SECONDS ),
			'created_at'    => current_time( 'mysql' ),
		);

		$option_key = 'wprobo_documerge_dl_' . sanitize_key( $token );
		$saved      = update_option( $option_key, $token_data, false );

		if ( ! $saved ) {
			return new \WP_Error(
				'token_save_failed',
				__( 'Failed to save download token.', 'wprobo-documerge' )
			);
		}

		$download_url = add_query_arg(
			array(
				'wpaction' => 'documerge_download',
				'token'    => sanitize_key( $token ),
			),
			home_url( '/' )
		);

		return esc_url_raw( $download_url );
	}

	/**
	 * Serve a file download for a given token.
	 *
	 * Validates the token, checks expiry, resolves the file path,
	 * deletes the token (one-time use), and streams the file to
	 * the browser with appropriate headers.
	 *
	 * @since  1.0.0
	 * @param  string $token  The download token (UUID).
	 * @param  string $format The file format to serve ('pdf' or 'docx'). Default 'pdf'.
	 * @return \WP_Error Only returns on failure; on success the script exits after streaming.
	 */
	public function wprobo_documerge_serve_download( $token, $format = 'pdf' ) {
		$token      = sanitize_key( $token );
		$format     = sanitize_key( $format );
		$option_key = 'wprobo_documerge_dl_' . $token;

		$token_data = get_option( $option_key );

		if ( empty( $token_data ) || ! is_array( $token_data ) ) {
			return new \WP_Error(
				'invalid_token',
				__( 'Invalid or missing download token.', 'wprobo-documerge' )
			);
		}

		// Check expiry.
		if ( time() > absint( $token_data['expiry'] ) ) {
			delete_option( $option_key );

			return new \WP_Error(
				'expired_token',
				__( 'This download link has expired.', 'wprobo-documerge' )
			);
		}

		// Get submission.
		global $wpdb;
		$submissions_table = $wpdb->prefix . 'wprdm_submissions';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$submission = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$submissions_table} WHERE id = %d",
				absint( $token_data['submission_id'] )
			)
		);

		if ( ! $submission ) {
			delete_option( $option_key );

			return new \WP_Error(
				'submission_not_found',
				__( 'Submission not found.', 'wprobo-documerge' )
			);
		}

		// Resolve file path.
		$file_path = 'docx' === $format ? $submission->doc_path_docx : $submission->doc_path_pdf;

		if ( empty( $file_path ) || ! file_exists( $file_path ) ) {
			return new \WP_Error(
				'file_not_found',
				__( 'The requested file does not exist.', 'wprobo-documerge' )
			);
		}

		// Delete token — one-time use.
		delete_option( $option_key );

		// Determine MIME type.
		$mime_types = array(
			'pdf'  => 'application/pdf',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		);
		$content_type = isset( $mime_types[ $format ] ) ? $mime_types[ $format ] : 'application/octet-stream';
		$file_name    = sanitize_file_name( basename( $file_path ) );

		/**
		 * Filters the download filename presented to the browser.
		 *
		 * @since 1.1.0
		 *
		 * @param string $file_name     The filename for the Content-Disposition header.
		 * @param int    $submission_id The submission ID (from token data).
		 * @param string $format        The file format ('pdf' or 'docx').
		 */
		$file_name = apply_filters( 'wprobo_documerge_document_filename', $file_name, absint( $token_data['submission_id'] ), $format );
		$file_name = sanitize_file_name( $file_name );

		// Stream file.
		nocache_headers();
		header( 'Content-Type: ' . $content_type );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
		header( 'Content-Length: ' . filesize( $file_path ) );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		readfile( $file_path );
		exit;
	}

	/**
	 * Send the generated document to the submitter via email.
	 *
	 * Loads the email template from templates/emails/document-delivery.php,
	 * prepares a download link, optionally attaches the file, and sends
	 * via wp_mail(). Schedules a retry on failure.
	 *
	 * @since  1.0.0
	 * @param  int $submission_id The submission ID.
	 * @return true|\WP_Error True on success, WP_Error on failure.
	 */
	public function wprobo_documerge_send_submitter_email( $submission_id ) {
		global $wpdb;

		$submission_id     = absint( $submission_id );
		$submissions_table = $wpdb->prefix . 'wprdm_submissions';
		$forms_table       = $wpdb->prefix . 'wprdm_forms';
		$templates_table   = $wpdb->prefix . 'wprdm_templates';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$submission = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$submissions_table} WHERE id = %d", $submission_id )
		);

		if ( ! $submission ) {
			return new \WP_Error( 'submission_not_found', __( 'Submission not found.', 'wprobo-documerge' ) );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$forms_table} WHERE id = %d", absint( $submission->form_id ) )
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$template = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$templates_table} WHERE id = %d", absint( $submission->template_id ) )
		);

		// Build download URL.
		$download_url = $this->wprobo_documerge_prepare_download( $submission_id );
		if ( is_wp_error( $download_url ) ) {
			return $download_url;
		}

		// Derive submitter name from form data.
		$form_data_decoded = json_decode( $submission->form_data, true );
		$fields_data       = is_array( $form_data_decoded ) && isset( $form_data_decoded['fields'] ) ? $form_data_decoded['fields'] : ( is_array( $form_data_decoded ) ? $form_data_decoded : array() );
		$submitter_name    = '';
		foreach ( array( 'full_name', 'name', 'first_name', 'your_name', 'client_name' ) as $name_key ) {
			if ( ! empty( $fields_data[ $name_key ] ) ) {
				$submitter_name = sanitize_text_field( $fields_data[ $name_key ] );
				break;
			}
		}
		if ( empty( $submitter_name ) ) {
			$submitter_name = ! empty( $submission->submitter_email ) ? explode( '@', $submission->submitter_email )[0] : __( 'there', 'wprobo-documerge' );
		}

		// Prepare email variables for the template.
		$email_vars = array(
			'submission'     => $submission,
			'form'           => $form,
			'template'       => $template,
			'download_url'   => $download_url,
			'site_name'      => wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			'submitter_name' => $submitter_name,
			'form_title'     => ! empty( $form->title ) ? $form->title : __( 'Form', 'wprobo-documerge' ),
			'template_name'  => ! empty( $template->name ) ? $template->name : __( 'Document', 'wprobo-documerge' ),
		);

		// Load email body from template file.
		$template_file = WPROBO_DOCUMERGE_PATH . 'templates/emails/document-delivery.php';
		$body          = '';

		if ( file_exists( $template_file ) ) {
			ob_start();
			// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template variables.
			extract( $email_vars );
			include $template_file;
			$body = ob_get_clean();
		} else {
			$body = sprintf(
				/* translators: 1: site name, 2: download URL */
				__( "Your document from %1\$s is ready.\n\nDownload your document: %2\$s", 'wprobo-documerge' ),
				$email_vars['site_name'],
				esc_url( $download_url )
			);
		}

		// Email headers.
		$from_name  = get_option( 'wprobo_documerge_email_from_name', $email_vars['site_name'] );
		$from_email = get_option( 'wprobo_documerge_email_from', '' );
		if ( empty( $from_email ) ) {
			$from_email = get_option( 'admin_email' );
		}
		$reply_to = get_option( 'wprobo_documerge_email_reply_to', '' );

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			sprintf( 'From: %s <%s>', sanitize_text_field( $from_name ), sanitize_email( $from_email ) ),
		);
		if ( ! empty( $reply_to ) ) {
			$headers[] = sprintf( 'Reply-To: %s', sanitize_email( $reply_to ) );
		}

		$to      = sanitize_email( $submission->submitter_email );
		$subject = sprintf(
			/* translators: %s: site name */
			__( 'Your document from %s is ready', 'wprobo-documerge' ),
			$email_vars['site_name']
		);

		// Optional file attachment.
		$attachments = array();
		$attach_file = get_option( 'wprobo_documerge_email_attach_doc', '1' );
		$max_size_mb = absint( get_option( 'wprobo_documerge_email_max_attach_mb', 10 ) );

		if ( '1' === $attach_file ) {
			$attach_path = ! empty( $submission->doc_path_pdf ) ? $submission->doc_path_pdf : $submission->doc_path_docx;

			if ( ! empty( $attach_path ) && file_exists( $attach_path ) ) {
				$file_size_mb = filesize( $attach_path ) / ( 1024 * 1024 );

				if ( $file_size_mb <= $max_size_mb ) {
					$attachments[] = $attach_path;
				}
			}
		}

		/**
		 * Filters the submitter email subject line.
		 *
		 * @since 1.1.0
		 *
		 * @param string $subject       The email subject.
		 * @param int    $submission_id The submission ID.
		 * @param int    $form_id       The form ID.
		 */
		$subject = apply_filters( 'wprobo_documerge_email_subject', $subject, $submission_id, (int) $submission->form_id );

		/**
		 * Filters the submitter email body content.
		 *
		 * @since 1.1.0
		 *
		 * @param string $body          The email body HTML.
		 * @param int    $submission_id The submission ID.
		 * @param int    $form_id       The form ID.
		 */
		$body = apply_filters( 'wprobo_documerge_email_body', $body, $submission_id, (int) $submission->form_id );

		/**
		 * Filters the submitter email headers.
		 *
		 * @since 1.1.0
		 *
		 * @param array $headers       The email headers.
		 * @param int   $submission_id The submission ID.
		 */
		$headers = apply_filters( 'wprobo_documerge_email_headers', $headers, $submission_id );

		/**
		 * Filters the submitter email file attachments.
		 *
		 * @since 1.1.0
		 *
		 * @param array $attachments   Array of absolute file paths to attach.
		 * @param int   $submission_id The submission ID.
		 */
		$attachments = apply_filters( 'wprobo_documerge_email_attachments', $attachments, $submission_id );

		/**
		 * Fires just before the submitter email is sent via wp_mail().
		 *
		 * @since 1.1.0
		 *
		 * @param int    $submission_id The submission ID.
		 * @param string $to            The recipient email address.
		 * @param string $subject       The email subject.
		 */
		do_action( 'wprobo_documerge_before_email_send', $submission_id, $to, $subject );

		// Set HTML content type filter.
		$content_type_filter = function () {
			return 'text/html';
		};
		add_filter( 'wp_mail_content_type', $content_type_filter );

		$sent = wp_mail( $to, $subject, $body, $headers, $attachments );

		remove_filter( 'wp_mail_content_type', $content_type_filter );

		/**
		 * Fires after the submitter email send attempt.
		 *
		 * @since 1.1.0
		 *
		 * @param int    $submission_id The submission ID.
		 * @param string $to            The recipient email address.
		 * @param bool   $sent          Whether wp_mail() reported success.
		 */
		do_action( 'wprobo_documerge_email_sent', $submission_id, $to, $sent );

		if ( ! $sent ) {
			// Schedule retry.
			wp_schedule_single_event(
				time() + 300,
				'wprobo_documerge_retry_email',
				array( $submission_id )
			);

			return new \WP_Error(
				'email_send_failed',
				__( 'Failed to send document email. A retry has been scheduled.', 'wprobo-documerge' )
			);
		}

		return true;
	}

	/**
	 * Send an admin notification about a new submission.
	 *
	 * Loads the admin notification email template and sends it to
	 * the configured admin email address.
	 *
	 * @since  1.0.0
	 * @param  int $submission_id The submission ID.
	 * @return bool Whether wp_mail() reported success.
	 */
	public function wprobo_documerge_send_admin_notification( $submission_id ) {
		global $wpdb;

		$submission_id     = absint( $submission_id );
		$submissions_table = $wpdb->prefix . 'wprdm_submissions';
		$forms_table       = $wpdb->prefix . 'wprdm_forms';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$submission = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$submissions_table} WHERE id = %d", $submission_id )
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$form = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$forms_table} WHERE id = %d", absint( $submission->form_id ) )
		);

		$site_name = wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );

		// Get template name for the email.
		$templates_table  = $wpdb->prefix . 'wprdm_templates';
		$admin_template   = $wpdb->get_row(
			$wpdb->prepare( "SELECT name FROM {$templates_table} WHERE id = %d", absint( $submission->template_id ) )
		);

		$email_vars = array(
			'submission'    => $submission,
			'form'          => $form,
			'site_name'     => $site_name,
			'admin_url'     => admin_url( 'admin.php?page=wprobo-documerge-submissions&view=' . $submission_id ),
			'form_title'    => ! empty( $form->title ) ? $form->title : __( 'Unknown Form', 'wprobo-documerge' ),
			'template_name' => ! empty( $admin_template->name ) ? $admin_template->name : __( 'Document', 'wprobo-documerge' ),
		);

		// Load email template.
		$template_file = WPROBO_DOCUMERGE_PATH . 'templates/emails/admin-notification.php';
		$body          = '';

		if ( file_exists( $template_file ) ) {
			ob_start();
			// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template variables.
			extract( $email_vars );
			include $template_file;
			$body = ob_get_clean();
		} else {
			$body = sprintf(
				/* translators: 1: submission ID, 2: form title, 3: admin URL */
				__( "New submission #%1\$d received for form \"%2\$s\".\n\nView: %3\$s", 'wprobo-documerge' ),
				$submission_id,
				! empty( $form->title ) ? $form->title : __( 'Unknown', 'wprobo-documerge' ),
				esc_url( $email_vars['admin_url'] )
			);
		}

		$admin_email = get_option( 'wprobo_documerge_notification_email', '' );
		if ( empty( $admin_email ) ) {
			$admin_email = get_option( 'admin_email' );
		}

		/* translators: %s: site name */
		$subject = sprintf( __( '[%s] DocuMerge: New document submission', 'wprobo-documerge' ), $site_name );

		/**
		 * Filters the admin notification email subject line.
		 *
		 * @since 1.1.0
		 *
		 * @param string $subject       The admin email subject.
		 * @param int    $submission_id The submission ID.
		 */
		$subject = apply_filters( 'wprobo_documerge_admin_email_subject', $subject, $submission_id );

		/**
		 * Filters the admin notification email body content.
		 *
		 * @since 1.1.0
		 *
		 * @param string $body          The admin email body.
		 * @param int    $submission_id The submission ID.
		 */
		$body = apply_filters( 'wprobo_documerge_admin_email_body', $body, $submission_id );

		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		return wp_mail( sanitize_email( $admin_email ), $subject, $body, $headers );
	}

	/**
	 * Save generated document files to the WordPress media library.
	 *
	 * Copies each generated file (DOCX and/or PDF) into the standard
	 * uploads directory and creates a media library attachment.
	 *
	 * @since  1.0.0
	 * @param  int $submission_id The submission ID.
	 * @return array|\WP_Error Array of attachment IDs on success, WP_Error on failure.
	 */
	public function wprobo_documerge_save_to_media( $submission_id ) {
		global $wpdb;

		$submission_id     = absint( $submission_id );
		$submissions_table = $wpdb->prefix . 'wprdm_submissions';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$submission = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$submissions_table} WHERE id = %d", $submission_id )
		);

		if ( ! $submission ) {
			return new \WP_Error( 'submission_not_found', __( 'Submission not found.', 'wprobo-documerge' ) );
		}

		$files = array();
		if ( ! empty( $submission->doc_path_docx ) && file_exists( $submission->doc_path_docx ) ) {
			$files['docx'] = $submission->doc_path_docx;
		}
		if ( ! empty( $submission->doc_path_pdf ) && file_exists( $submission->doc_path_pdf ) ) {
			$files['pdf'] = $submission->doc_path_pdf;
		}

		if ( empty( $files ) ) {
			return new \WP_Error( 'no_files', __( 'No generated files found for this submission.', 'wprobo-documerge' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/file.php';
		require_once ABSPATH . 'wp-admin/includes/media.php';
		require_once ABSPATH . 'wp-admin/includes/image.php';

		$upload_dir     = wp_upload_dir();
		$attachment_ids = array();

		$mime_types = array(
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'pdf'  => 'application/pdf',
		);

		foreach ( $files as $format => $source_path ) {
			$file_name = sanitize_file_name( basename( $source_path ) );
			$dest_path = trailingslashit( $upload_dir['path'] ) . $file_name;

			// Copy file to uploads directory.
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_copy
			if ( ! copy( $source_path, $dest_path ) ) {
				$attachment_ids[ $format ] = new \WP_Error(
					'copy_failed',
					/* translators: %s: file name */
					sprintf( __( 'Failed to copy %s to uploads.', 'wprobo-documerge' ), $file_name )
				);
				continue;
			}

			$attachment_data = array(
				'guid'           => trailingslashit( $upload_dir['url'] ) . $file_name,
				'post_mime_type' => isset( $mime_types[ $format ] ) ? $mime_types[ $format ] : 'application/octet-stream',
				'post_title'     => sanitize_text_field( pathinfo( $file_name, PATHINFO_FILENAME ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			);

			$attach_id = wp_insert_attachment( $attachment_data, $dest_path );

			if ( is_wp_error( $attach_id ) ) {
				$attachment_ids[ $format ] = $attach_id;
				continue;
			}

			$attach_meta = wp_generate_attachment_metadata( $attach_id, $dest_path );
			wp_update_attachment_metadata( $attach_id, $attach_meta );

			$attachment_ids[ $format ] = $attach_id;
		}

		return $attachment_ids;
	}

	/**
	 * Register all WordPress hooks for the delivery engine.
	 *
	 * Hooks into 'init' for public download handling, registers AJAX
	 * handlers for admin downloads, and sets up cron event handlers
	 * for email retries and token cleanup.
	 *
	 * @since 1.0.0
	 */
	public function wprobo_documerge_init_hooks() {
		// Public download via query parameter.
		add_action( 'init', array( $this, 'wprobo_documerge_handle_public_download' ) );

		// Admin AJAX download handler.
		add_action( 'wp_ajax_wprobo_documerge_download_document', array( $this, 'wprobo_documerge_ajax_admin_download' ) );
		add_action( 'wp_ajax_nopriv_wprobo_documerge_download_document_public', array( $this, 'wprobo_documerge_ajax_admin_download' ) );

		// Retry email cron handler.
		add_action( 'wprobo_documerge_retry_email', array( $this, 'wprobo_documerge_retry_email_handler' ) );

		// Expired token cleanup cron handler.
		add_action( 'wprobo_documerge_cleanup_expired_tokens', array( $this, 'wprobo_documerge_cleanup_expired_tokens' ) );
	}

	/**
	 * Handle public download requests via the 'wpaction' query parameter.
	 *
	 * Checks if the current request is a document download, sanitises
	 * input, and delegates to the serve_download method. Called on the
	 * WordPress 'init' action.
	 *
	 * @since 1.0.0
	 */
	public function wprobo_documerge_handle_public_download() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Token-based auth, no nonce needed.
		if ( ! isset( $_GET['wpaction'] ) || 'documerge_download' !== $_GET['wpaction'] ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$token = isset( $_GET['token'] ) ? sanitize_key( wp_unslash( $_GET['token'] ) ) : '';

		if ( empty( $token ) ) {
			wp_die(
				esc_html__( 'Missing download token.', 'wprobo-documerge' ),
				esc_html__( 'Download Error', 'wprobo-documerge' ),
				array( 'response' => 400 )
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$format = isset( $_GET['format'] ) ? sanitize_key( wp_unslash( $_GET['format'] ) ) : 'pdf';

		$result = $this->wprobo_documerge_serve_download( $token, $format );

		// serve_download exits on success; we only get here on error.
		if ( is_wp_error( $result ) ) {
			wp_die(
				esc_html( $result->get_error_message() ),
				esc_html__( 'Download Error', 'wprobo-documerge' ),
				array( 'response' => 403 )
			);
		}
	}

	/**
	 * Handle admin AJAX download requests.
	 *
	 * Verifies the nonce and user capability, then streams the
	 * requested file directly without a token.
	 *
	 * @since 1.0.0
	 */
	public function wprobo_documerge_ajax_admin_download() {
		check_ajax_referer( 'wprobo_documerge_admin', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'You do not have permission to download this file.', 'wprobo-documerge' ),
				esc_html__( 'Permission Denied', 'wprobo-documerge' ),
				array( 'response' => 403 )
			);
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Nonce already verified above.
		$submission_id = isset( $_GET['submission_id'] ) ? absint( wp_unslash( $_GET['submission_id'] ) ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$format        = isset( $_GET['format'] ) ? sanitize_key( wp_unslash( $_GET['format'] ) ) : 'pdf';

		if ( ! $submission_id ) {
			wp_die(
				esc_html__( 'Invalid submission ID.', 'wprobo-documerge' ),
				esc_html__( 'Download Error', 'wprobo-documerge' ),
				array( 'response' => 400 )
			);
		}

		global $wpdb;
		$submissions_table = $wpdb->prefix . 'wprdm_submissions';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$submission = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$submissions_table} WHERE id = %d", $submission_id )
		);

		if ( ! $submission ) {
			wp_die(
				esc_html__( 'Submission not found.', 'wprobo-documerge' ),
				esc_html__( 'Download Error', 'wprobo-documerge' ),
				array( 'response' => 404 )
			);
		}

		$file_path = 'docx' === $format ? $submission->doc_path_docx : $submission->doc_path_pdf;

		if ( empty( $file_path ) || ! file_exists( $file_path ) ) {
			wp_die(
				esc_html__( 'The requested file does not exist.', 'wprobo-documerge' ),
				esc_html__( 'Download Error', 'wprobo-documerge' ),
				array( 'response' => 404 )
			);
		}

		$mime_types = array(
			'pdf'  => 'application/pdf',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		);
		$content_type = isset( $mime_types[ $format ] ) ? $mime_types[ $format ] : 'application/octet-stream';
		$file_name    = sanitize_file_name( basename( $file_path ) );

		nocache_headers();
		header( 'Content-Type: ' . $content_type );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );
		header( 'Content-Length: ' . filesize( $file_path ) );

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile
		readfile( $file_path );
		exit;
	}

	/**
	 * Handle email retry for a failed submission email.
	 *
	 * Called by the 'wprobo_documerge_retry_email' cron event.
	 * Increments the retry count and attempts to resend the email.
	 * After exceeding the maximum retries, marks delivery as failed.
	 *
	 * @since 1.0.0
	 * @param int $submission_id The submission ID to retry.
	 */
	public function wprobo_documerge_retry_email_handler( $submission_id ) {
		global $wpdb;

		$submission_id     = absint( $submission_id );
		$submissions_table = $wpdb->prefix . 'wprdm_submissions';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$submission = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$submissions_table} WHERE id = %d", $submission_id )
		);

		if ( ! $submission ) {
			return;
		}

		$retry_count = absint( $submission->retry_count ) + 1;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$submissions_table,
			array(
				'retry_count' => $retry_count,
				'updated_at'  => current_time( 'mysql' ),
			),
			array( 'id' => $submission_id ),
			array( '%d', '%s' ),
			array( '%d' )
		);

		// Exceeded maximum retries.
		if ( $retry_count > self::WPROBO_DOCUMERGE_MAX_EMAIL_RETRIES ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$submissions_table,
				array(
					'delivery_status' => 'email_failed',
					'updated_at'      => current_time( 'mysql' ),
				),
				array( 'id' => $submission_id ),
				array( '%s', '%s' ),
				array( '%d' )
			);
			return;
		}

		// Re-attempt email.
		$result = $this->wprobo_documerge_send_submitter_email( $submission_id );

		if ( ! is_wp_error( $result ) ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update(
				$submissions_table,
				array(
					'delivery_status' => 'delivered',
					'updated_at'      => current_time( 'mysql' ),
				),
				array( 'id' => $submission_id ),
				array( '%s', '%s' ),
				array( '%d' )
			);
		}
	}

	/**
	 * Clean up expired download tokens from wp_options.
	 *
	 * Called by the 'wprobo_documerge_cleanup_expired_tokens' cron event.
	 * Queries all options with the 'wprobo_documerge_dl_' prefix and
	 * deletes those whose expiry timestamp has passed.
	 *
	 * @since 1.0.0
	 */
	public function wprobo_documerge_cleanup_expired_tokens() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE %s",
				$wpdb->esc_like( 'wprobo_documerge_dl_' ) . '%'
			)
		);

		if ( empty( $rows ) ) {
			return;
		}

		$now = time();

		foreach ( $rows as $row ) {
			$token_data = maybe_unserialize( $row->option_value );

			if ( ! is_array( $token_data ) || empty( $token_data['expiry'] ) ) {
				// Malformed token data — delete it.
				delete_option( $row->option_name );
				continue;
			}

			if ( $now > absint( $token_data['expiry'] ) ) {
				delete_option( $row->option_name );
			}
		}
	}
}
