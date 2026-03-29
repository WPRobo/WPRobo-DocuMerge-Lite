<?php
/**
 * WPRobo DocuMerge Lite — Demo Data Seeder
 *
 * Creates realistic demo data for screenshots and testing.
 * Run via WP-CLI: wp eval-file scripts/seed-demo-data.php
 * Or visit: /wp-admin/admin.php?page=wprobo-documerge&seed_demo=1
 *
 * IMPORTANT: This file is for development only.
 * It is excluded from the production ZIP by deploy.sh.
 *
 * @package WPRobo_DocuMerge
 * @since   1.0.0
 */

// Allow running via direct include from WP admin (with nonce check).
if ( defined( 'ABSPATH' ) && is_admin() ) {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( ! isset( $_GET['seed_demo'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'Unauthorized.' );
	}
}

// When running via WP-CLI, ABSPATH is already defined.
if ( ! defined( 'ABSPATH' ) ) {
	echo "ERROR: This script must be run within WordPress.\n";
	echo "Usage: wp eval-file scripts/seed-demo-data.php\n";
	exit( 1 );
}

global $wpdb;

$templates_table   = $wpdb->prefix . 'wprdm_templates';
$forms_table       = $wpdb->prefix . 'wprdm_forms';
$submissions_table = $wpdb->prefix . 'wprdm_submissions';

// ── Helper: output message ────────────────────────────────────
function seed_msg( $msg ) {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::log( $msg );
	} else {
		echo esc_html( $msg ) . '<br>';
	}
}

seed_msg( '🌱 WPRobo DocuMerge Lite — Seeding demo data...' );
seed_msg( '' );

// ── 1. Templates ──────────────────────────────────────────────
seed_msg( '── Creating Templates ──' );

$templates = array(
	array(
		'name'          => 'Client Service Agreement',
		'description'   => 'Standard service agreement for consulting clients. Includes terms, scope, and signature.',
		'file_path'     => '/demo/client-service-agreement.docx',
		'file_name'     => 'client-service-agreement.docx',
		'output_format' => 'pdf',
		'merge_tags'    => wp_json_encode( array( 'client_name', 'email', 'phone', 'service_type', 'start_date', 'current_date' ) ),
	),
	array(
		'name'          => 'Student Enrolment Letter',
		'description'   => 'Welcome letter for newly enrolled students with course details.',
		'file_path'     => '/demo/student-enrolment-letter.docx',
		'file_name'     => 'student-enrolment-letter.docx',
		'output_format' => 'pdf',
		'merge_tags'    => wp_json_encode( array( 'student_name', 'email', 'course_name', 'start_date', 'current_date' ) ),
	),
	array(
		'name'          => 'General NDA',
		'description'   => 'Non-disclosure agreement template for business partnerships.',
		'file_path'     => '/demo/general-nda.docx',
		'file_name'     => 'general-nda.docx',
		'output_format' => 'both',
		'merge_tags'    => wp_json_encode( array( 'party_name', 'company', 'email', 'current_date' ) ),
	),
	array(
		'name'          => 'Invoice Template',
		'description'   => 'Professional invoice for freelancers and agencies.',
		'file_path'     => '/demo/invoice-template.docx',
		'file_name'     => 'invoice-template.docx',
		'output_format' => 'pdf',
		'merge_tags'    => wp_json_encode( array( 'client_name', 'email', 'project_name', 'amount', 'due_date', 'current_date' ) ),
	),
);

$template_ids = array();

foreach ( $templates as $tpl ) {
	$now = current_time( 'mysql' );

	// Check if already exists.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$exists = $wpdb->get_var(
		$wpdb->prepare( "SELECT id FROM {$templates_table} WHERE name = %s", $tpl['name'] )
	);

	if ( $exists ) {
		$template_ids[] = (int) $exists;
		seed_msg( "  ⏭ Template already exists: {$tpl['name']} (ID: {$exists})" );
		continue;
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->insert(
		$templates_table,
		array(
			'name'          => $tpl['name'],
			'description'   => $tpl['description'],
			'file_path'     => $tpl['file_path'],
			'file_name'     => $tpl['file_name'],
			'output_format' => $tpl['output_format'],
			'merge_tags'    => $tpl['merge_tags'],
			'created_at'    => $now,
			'updated_at'    => $now,
		),
		array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
	);

	$template_ids[] = (int) $wpdb->insert_id;
	seed_msg( "  ✓ Created template: {$tpl['name']} (ID: {$wpdb->insert_id})" );
}

// ── 2. Forms ──────────────────────────────────────────────────
seed_msg( '' );
seed_msg( '── Creating Forms ──' );

$forms = array(
	array(
		'title'            => 'Client Intake Form',
		'template_id'      => isset( $template_ids[0] ) ? $template_ids[0] : 1,
		'mode'             => 'standalone',
		'integration'      => '',
		'output_format'    => 'pdf',
		'delivery_methods' => 'download',
		'submit_label'     => 'Submit & Get Document',
		'success_message'  => 'Thank you! Your document has been generated and is ready for download.',
		'redirect_url'     => '',
		'fields'           => wp_json_encode(
			array(
				array(
					'id'          => 'field_1',
					'type'        => 'text',
					'label'       => 'Full Name',
					'name'        => 'client_name',
					'placeholder' => 'Enter your full name',
					'required'    => true,
					'width'       => 'full',
					'conditions'  => array(),
					'step'        => 1,
				),
				array(
					'id'          => 'field_2',
					'type'        => 'email',
					'label'       => 'Email Address',
					'name'        => 'email',
					'placeholder' => 'you@example.com',
					'required'    => true,
					'width'       => 'half',
					'conditions'  => array(),
					'step'        => 1,
				),
				array(
					'id'          => 'field_3',
					'type'        => 'phone',
					'label'       => 'Phone Number',
					'name'        => 'phone',
					'placeholder' => '+1 (555) 000-0000',
					'required'    => false,
					'width'       => 'half',
					'conditions'  => array(),
					'step'        => 1,
				),
				array(
					'id'          => 'field_4',
					'type'        => 'dropdown',
					'label'       => 'Service Type',
					'name'        => 'service_type',
					'placeholder' => 'Select a service...',
					'required'    => true,
					'width'       => 'full',
					'options'     => array(
						array( 'label' => 'Consultation', 'value' => 'consultation' ),
						array( 'label' => 'Document Review', 'value' => 'review' ),
						array( 'label' => 'Full Drafting', 'value' => 'drafting' ),
					),
					'conditions'  => array(),
					'step'        => 1,
				),
				array(
					'id'          => 'field_5',
					'type'        => 'date',
					'label'       => 'Preferred Start Date',
					'name'        => 'start_date',
					'placeholder' => 'Select a date',
					'required'    => true,
					'width'       => 'half',
					'date_format' => 'Y-m-d',
					'conditions'  => array(),
					'step'        => 1,
				),
				array(
					'id'          => 'field_6',
					'type'        => 'textarea',
					'label'       => 'Additional Notes',
					'name'        => 'notes',
					'placeholder' => 'Any additional information...',
					'required'    => false,
					'width'       => 'full',
					'conditions'  => array(),
					'step'        => 1,
				),
			)
		),
		'settings'         => wp_json_encode(
			array(
				'submit_label'    => 'Submit & Get Document',
				'success_message' => 'Thank you! Your document has been generated.',
				'output_format'   => 'pdf',
				'delivery_methods' => array( 'download' ),
			)
		),
	),
	array(
		'title'            => 'Student Enrolment',
		'template_id'      => isset( $template_ids[1] ) ? $template_ids[1] : 2,
		'mode'             => 'standalone',
		'integration'      => '',
		'output_format'    => 'pdf',
		'delivery_methods' => 'download',
		'submit_label'     => 'Enrol Now',
		'success_message'  => 'Welcome! Your enrolment letter is ready for download.',
		'redirect_url'     => '',
		'fields'           => wp_json_encode(
			array(
				array(
					'id'          => 'field_1',
					'type'        => 'text',
					'label'       => 'Student Name',
					'name'        => 'student_name',
					'placeholder' => 'Enter your full name',
					'required'    => true,
					'width'       => 'full',
					'conditions'  => array(),
					'step'        => 1,
				),
				array(
					'id'          => 'field_2',
					'type'        => 'email',
					'label'       => 'Email Address',
					'name'        => 'email',
					'placeholder' => 'you@university.edu',
					'required'    => true,
					'width'       => 'full',
					'conditions'  => array(),
					'step'        => 1,
				),
				array(
					'id'          => 'field_3',
					'type'        => 'dropdown',
					'label'       => 'Course Name',
					'name'        => 'course_name',
					'placeholder' => 'Select your course...',
					'required'    => true,
					'width'       => 'full',
					'options'     => array(
						array( 'label' => 'Business Management', 'value' => 'business_management' ),
						array( 'label' => 'Computer Science', 'value' => 'computer_science' ),
						array( 'label' => 'Data Analytics', 'value' => 'data_analytics' ),
						array( 'label' => 'Digital Marketing', 'value' => 'digital_marketing' ),
					),
					'conditions'  => array(),
					'step'        => 1,
				),
				array(
					'id'          => 'field_4',
					'type'        => 'date',
					'label'       => 'Start Date',
					'name'        => 'start_date',
					'placeholder' => 'Select start date',
					'required'    => true,
					'width'       => 'half',
					'date_format' => 'Y-m-d',
					'conditions'  => array(),
					'step'        => 1,
				),
			)
		),
		'settings'         => wp_json_encode(
			array(
				'submit_label'     => 'Enrol Now',
				'success_message'  => 'Welcome! Your enrolment letter is ready.',
				'output_format'    => 'pdf',
				'delivery_methods' => array( 'download' ),
			)
		),
	),
);

$form_ids = array();

foreach ( $forms as $frm ) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$exists = $wpdb->get_var(
		$wpdb->prepare( "SELECT id FROM {$forms_table} WHERE title = %s", $frm['title'] )
	);

	if ( $exists ) {
		$form_ids[] = (int) $exists;
		seed_msg( "  ⏭ Form already exists: {$frm['title']} (ID: {$exists})" );
		continue;
	}

	$now = current_time( 'mysql' );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->insert(
		$forms_table,
		array(
			'title'            => $frm['title'],
			'template_id'      => $frm['template_id'],
			'mode'             => $frm['mode'],
			'integration'      => $frm['integration'],
			'fields'           => $frm['fields'],
			'settings'         => $frm['settings'],
			'output_format'    => $frm['output_format'],
			'delivery_methods' => $frm['delivery_methods'],
			'submit_label'     => $frm['submit_label'],
			'success_message'  => $frm['success_message'],
			'redirect_url'     => $frm['redirect_url'],
			'created_at'       => $now,
			'updated_at'       => $now,
		),
		array( '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
	);

	$form_ids[] = (int) $wpdb->insert_id;
	seed_msg( "  ✓ Created form: {$frm['title']} (ID: {$wpdb->insert_id})" );
}

// ── 3. Submissions ────────────────────────────────────────────
seed_msg( '' );
seed_msg( '── Creating Submissions ──' );

$people = array(
	array( 'name' => 'Alice Johnson', 'email' => 'alice.johnson@example.com', 'phone' => '+1 (555) 123-4567', 'service' => 'Consultation', 'course' => 'Business Management' ),
	array( 'name' => 'Bob Martinez', 'email' => 'bob.martinez@example.com', 'phone' => '+44 7700 900123', 'service' => 'Document Review', 'course' => 'Computer Science' ),
	array( 'name' => 'Carol Chen', 'email' => 'carol.chen@example.com', 'phone' => '+1 (555) 234-5678', 'service' => 'Full Drafting', 'course' => 'Data Analytics' ),
	array( 'name' => 'David Okafor', 'email' => 'david.okafor@example.com', 'phone' => '+44 7700 900456', 'service' => 'Consultation', 'course' => 'Digital Marketing' ),
	array( 'name' => 'Emma Wilson', 'email' => 'emma.wilson@example.com', 'phone' => '+1 (555) 345-6789', 'service' => 'Document Review', 'course' => 'Business Management' ),
	array( 'name' => 'Frank Dubois', 'email' => 'frank.dubois@example.com', 'phone' => '+33 6 12 34 56 78', 'service' => 'Full Drafting', 'course' => 'Computer Science' ),
	array( 'name' => 'Grace Kim', 'email' => 'grace.kim@example.com', 'phone' => '+82 10 1234 5678', 'service' => 'Consultation', 'course' => 'Data Analytics' ),
	array( 'name' => 'Henry Patel', 'email' => 'henry.patel@example.com', 'phone' => '+91 98765 43210', 'service' => 'Document Review', 'course' => 'Digital Marketing' ),
	array( 'name' => 'Irene Kowalski', 'email' => 'irene.kowalski@example.com', 'phone' => '+48 501 234 567', 'service' => 'Full Drafting', 'course' => 'Business Management' ),
	array( 'name' => 'Jack Thompson', 'email' => 'jack.thompson@example.com', 'phone' => '+1 (555) 456-7890', 'service' => 'Consultation', 'course' => 'Computer Science' ),
	array( 'name' => 'Karen Nakamura', 'email' => 'karen.nakamura@example.com', 'phone' => '+81 90 1234 5678', 'service' => 'Document Review', 'course' => 'Data Analytics' ),
	array( 'name' => 'Leo Rossi', 'email' => 'leo.rossi@example.com', 'phone' => '+39 333 123 4567', 'service' => 'Full Drafting', 'course' => 'Digital Marketing' ),
	array( 'name' => 'Maria Santos', 'email' => 'maria.santos@example.com', 'phone' => '+55 11 91234-5678', 'service' => 'Consultation', 'course' => 'Business Management' ),
	array( 'name' => 'Nikolai Petrov', 'email' => 'nikolai.petrov@example.com', 'phone' => '+7 916 123 4567', 'service' => 'Document Review', 'course' => 'Computer Science' ),
	array( 'name' => 'Olivia Brown', 'email' => 'olivia.brown@example.com', 'phone' => '+61 400 123 456', 'service' => 'Full Drafting', 'course' => 'Data Analytics' ),
);

$statuses       = array( 'completed', 'completed', 'completed', 'completed', 'completed', 'completed', 'completed', 'completed', 'completed', 'completed', 'error', 'completed', 'processing', 'completed', 'completed' );
$ips            = array( '192.168.1.10', '10.0.0.25', '172.16.0.50', '192.168.2.100', '10.0.1.15', '172.16.1.30', '192.168.3.45', '10.0.2.80', '172.16.2.60', '192.168.4.20', '10.0.3.55', '172.16.3.75', '192.168.5.90', '10.0.4.35', '172.16.4.10' );
$created_count  = 0;
$skipped_count  = 0;

for ( $i = 0; $i < count( $people ); $i++ ) {
	$person    = $people[ $i ];
	$status    = $statuses[ $i ];
	$form_idx  = ( $i % 2 === 0 ) ? 0 : 1; // Alternate between forms.
	$form_id   = isset( $form_ids[ $form_idx ] ) ? $form_ids[ $form_idx ] : 1;
	$tpl_id    = isset( $template_ids[ $form_idx ] ) ? $template_ids[ $form_idx ] : 1;
	$days_ago  = count( $people ) - $i; // Spread across the last 15 days.
	$created   = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days_ago} days" ) + wp_rand( 28800, 64800 ) );

	// Build form_data JSON.
	if ( 0 === $form_idx ) {
		// Client Intake Form.
		$form_data = wp_json_encode(
			array(
				'fields' => array(
					'client_name'  => $person['name'],
					'email'        => $person['email'],
					'phone'        => $person['phone'],
					'service_type' => $person['service'],
					'start_date'   => gmdate( 'Y-m-d', strtotime( "+{$i} weeks" ) ),
					'notes'        => 'Demo submission #' . ( $i + 1 ) . '. Generated by seed script.',
				),
				'meta'   => array(
					'ip_address' => $ips[ $i ],
					'user_agent' => 'Mozilla/5.0 (demo-seed)',
					'page_url'   => home_url( '/client-intake/' ),
					'referrer'   => home_url( '/' ),
				),
			)
		);
	} else {
		// Student Enrolment Form.
		$form_data = wp_json_encode(
			array(
				'fields' => array(
					'student_name' => $person['name'],
					'email'        => $person['email'],
					'course_name'  => $person['course'],
					'start_date'   => gmdate( 'Y-m-d', strtotime( "+{$i} weeks" ) ),
				),
				'meta'   => array(
					'ip_address' => $ips[ $i ],
					'user_agent' => 'Mozilla/5.0 (demo-seed)',
					'page_url'   => home_url( '/student-enrolment/' ),
					'referrer'   => home_url( '/' ),
				),
			)
		);
	}

	// Check if this person's submission already exists.
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$exists = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT id FROM {$submissions_table} WHERE submitter_email = %s AND form_id = %d",
			$person['email'],
			$form_id
		)
	);

	if ( $exists ) {
		++$skipped_count;
		continue;
	}

	$error_log       = ( 'error' === $status ) ? 'Template file not found at demo path. This is expected for demo data.' : '';
	$delivery_status = ( 'completed' === $status ) ? 'delivered' : 'pending';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	$wpdb->insert(
		$submissions_table,
		array(
			'form_id'         => $form_id,
			'template_id'     => $tpl_id,
			'submitter_email' => $person['email'],
			'form_data'       => $form_data,
			'doc_path_docx'   => '',
			'doc_path_pdf'    => '',
			'status'          => $status,
			'error_log'       => $error_log,
			'retry_count'     => 0,
			'delivery_status' => $delivery_status,
			'ip_address'      => $ips[ $i ],
			'created_at'      => $created,
			'updated_at'      => $created,
		),
		array( '%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s' )
	);

	++$created_count;
}

seed_msg( "  ✓ Created {$created_count} submissions" );
if ( $skipped_count > 0 ) {
	seed_msg( "  ⏭ Skipped {$skipped_count} (already exist)" );
}

// ── 4. Bust caches ────────────────────────────────────────────
delete_transient( 'wprobo_documerge_templates_list' );
delete_transient( 'wprobo_documerge_templates_count' );
delete_transient( 'wprobo_documerge_forms_count' );

// ── Summary ───────────────────────────────────────────────────
seed_msg( '' );
seed_msg( '════════════════════════════════════════' );
seed_msg( '  ✓ Demo data seeding complete!' );
seed_msg( '' );
seed_msg( '  Templates:   ' . count( $template_ids ) );
seed_msg( '  Forms:       ' . count( $form_ids ) );
seed_msg( '  Submissions: ' . $created_count . ' created' );
seed_msg( '' );
seed_msg( '  Dashboard:   /wp-admin/admin.php?page=wprobo-documerge' );
seed_msg( '  Templates:   /wp-admin/admin.php?page=wprobo-documerge-templates' );
seed_msg( '  Forms:       /wp-admin/admin.php?page=wprobo-documerge-forms' );
seed_msg( '  Submissions: /wp-admin/admin.php?page=wprobo-documerge-submissions' );
seed_msg( '════════════════════════════════════════' );

// If running in browser, redirect to dashboard.
if ( ! defined( 'WP_CLI' ) && is_admin() ) {
	seed_msg( '' );
	seed_msg( 'Redirecting to dashboard in 3 seconds...' );
	echo '<script>setTimeout(function(){ window.location.href = "' . esc_url( admin_url( 'admin.php?page=wprobo-documerge' ) ) . '"; }, 3000);</script>';
}
