<?php
/**
 * Submissions List Table — extends WP_List_Table.
 *
 * Provides a native WordPress admin table with search, sortable
 * columns, per-page screen option, bulk actions, and filters.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Admin
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.2.0
 */

namespace WPRobo\DocuMerge\Admin;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load WP_List_Table if not available.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class WPRobo_DocuMerge_Submissions_List_Table
 *
 * @since 1.2.0
 */
class WPRobo_DocuMerge_Submissions_List_Table extends \WP_List_Table {

	/**
	 * Constructor.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'submission',
				'plural'   => 'submissions',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Define table columns.
	 *
	 * @since  1.2.0
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'              => '<input type="checkbox" />',
			'created_at'      => __( 'Date', 'wprobo-documerge' ),
			'form_title'      => __( 'Form', 'wprobo-documerge' ),
			'submitter_email' => __( 'Email', 'wprobo-documerge' ),
			'status'          => __( 'Status', 'wprobo-documerge' ),
			'documents'       => __( 'Documents', 'wprobo-documerge' ),
		);
	}

	/**
	 * Define sortable columns.
	 *
	 * @since  1.2.0
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'created_at'      => array( 'created_at', true ), // Default sort DESC.
			'form_title'      => array( 'form_title', false ),
			'submitter_email' => array( 'submitter_email', false ),
			'status'          => array( 'status', false ),
		);
	}

	/**
	 * Checkbox column for bulk actions.
	 *
	 * @since  1.2.0
	 * @param  object $item Row data.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="submission_ids[]" value="%d" />',
			absint( $item->id )
		);
	}

	/**
	 * Date column.
	 *
	 * @since  1.2.0
	 * @param  object $item Row data.
	 * @return string
	 */
	public function column_created_at( $item ) {
		$date = date_i18n(
			get_option( 'date_format' ) . ' ' . get_option( 'time_format' ),
			strtotime( $item->created_at )
		);

		return sprintf(
			'<a href="%s"><strong>%s</strong></a>',
			esc_url( admin_url( 'admin.php?page=wprobo-documerge-submissions&view=' . absint( $item->id ) ) ),
			esc_html( $date )
		);
	}

	/**
	 * Form column.
	 *
	 * @since  1.2.0
	 * @param  object $item Row data.
	 * @return string
	 */
	public function column_form_title( $item ) {
		$title = ! empty( $item->form_title ) ? $item->form_title : __( 'Unknown', 'wprobo-documerge' );
		return esc_html( $title );
	}

	/**
	 * Email column.
	 *
	 * @since  1.2.0
	 * @param  object $item Row data.
	 * @return string
	 */
	public function column_submitter_email( $item ) {
		$email = ! empty( $item->submitter_email ) ? $item->submitter_email : '—';
		return esc_html( $email );
	}

	/**
	 * Status column with badge.
	 *
	 * @since  1.2.0
	 * @param  object $item Row data.
	 * @return string
	 */
	public function column_status( $item ) {
		$status_map = array(
			'completed'  => 'success',
			'processing' => 'info',
			'error'      => 'error',
		);

		$badge_class  = isset( $status_map[ $item->status ] ) ? $status_map[ $item->status ] : 'info';
		$status_label = str_replace( '_', ' ', $item->status );

		return sprintf(
			'<span class="wdm-badge wdm-badge-%s">%s</span>',
			esc_attr( $badge_class ),
			esc_html( ucwords( $status_label ) )
		);
	}

	/**
	 * Documents column with download links.
	 *
	 * @since  1.2.0
	 * @param  object $item Row data.
	 * @return string
	 */
	public function column_documents( $item ) {
		$links = array();

		if ( ! empty( $item->doc_path_pdf ) ) {
			$url     = admin_url( 'admin-ajax.php?action=wprobo_documerge_download_document&submission_id=' . absint( $item->id ) . '&format=pdf&nonce=' . wp_create_nonce( 'wprobo_documerge_admin' ) );
			$links[] = '<a href="' . esc_url( $url ) . '" class="wdm-btn wdm-btn-sm" target="_blank" rel="noopener noreferrer">PDF</a>';
		}

		if ( ! empty( $item->doc_path_docx ) ) {
			$url     = admin_url( 'admin-ajax.php?action=wprobo_documerge_download_document&submission_id=' . absint( $item->id ) . '&format=docx&nonce=' . wp_create_nonce( 'wprobo_documerge_admin' ) );
			$links[] = '<a href="' . esc_url( $url ) . '" class="wdm-btn wdm-btn-sm" target="_blank" rel="noopener noreferrer">DOCX</a>';
		}

		if ( empty( $links ) ) {
			return '<span class="wdm-text-muted">—</span>';
		}

		return '<div class="wdm-table-actions">' . implode( ' ', $links ) . '</div>';
	}

	/**
	 * Define bulk actions.
	 *
	 * @since  1.2.0
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'wprobo-documerge' ),
		);
	}

	/**
	 * Extra table navigation — filters above the table.
	 *
	 * @since  1.2.0
	 * @param  string $which 'top' or 'bottom'.
	 * @return void
	 */
	public function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}

		global $wpdb;

		// Get forms for filter dropdown.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$forms = $wpdb->get_results(
			"SELECT id, title FROM {$wpdb->prefix}wprdm_forms ORDER BY title ASC"
		);

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_form = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$current_status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';

		echo '<div class="alignleft actions wdm-list-table-filters">';

		// Form filter.
		echo '<select name="form_id">';
		echo '<option value="">' . esc_html__( 'All Forms', 'wprobo-documerge' ) . '</option>';
		if ( ! empty( $forms ) ) {
			foreach ( $forms as $form ) {
				printf(
					'<option value="%d"%s>%s</option>',
					absint( $form->id ),
					selected( $current_form, absint( $form->id ), false ),
					esc_html( $form->title )
				);
			}
		}
		echo '</select>';

		// Status filter.
		$statuses = array(
			'completed'  => __( 'Completed', 'wprobo-documerge' ),
			'processing' => __( 'Processing', 'wprobo-documerge' ),
			'error'      => __( 'Error', 'wprobo-documerge' ),
		);

		echo '<select name="status">';
		echo '<option value="">' . esc_html__( 'All Statuses', 'wprobo-documerge' ) . '</option>';
		foreach ( $statuses as $value => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $value ),
				selected( $current_status, $value, false ),
				esc_html( $label )
			);
		}
		echo '</select>';

		submit_button( __( 'Filter', 'wprobo-documerge' ), '', 'filter_action', false );

		// Export CSV button.
		echo '<a href="' . esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=wprobo_documerge_export_submissions&form_id=' . $current_form . '&status=' . $current_status ), 'wprobo_documerge_admin', 'nonce' ) ) . '" class="button wdm-export-csv-btn">';
		echo '<span class="dashicons dashicons-download" style="vertical-align:middle;margin-top:-2px;"></span> ';
		echo esc_html__( 'Export CSV', 'wprobo-documerge' );
		echo '</a>';

		echo '</div>';
	}

	/**
	 * Message when no items found.
	 *
	 * @since  1.2.0
	 * @return void
	 */
	public function no_items() {
		echo '<div class="wdm-empty-state" style="padding:40px 20px;">';
		echo '<span class="dashicons dashicons-email-alt"></span>';
		echo '<h3>' . esc_html__( 'No submissions found', 'wprobo-documerge' ) . '</h3>';
		echo '<p>' . esc_html__( 'Submissions will appear here once visitors submit your forms.', 'wprobo-documerge' ) . '</p>';
		echo '</div>';
	}

	/**
	 * Prepare table items — query database with filters, search, sort, pagination.
	 *
	 * @since  1.2.0
	 * @return void
	 */
	public function prepare_items() {
		global $wpdb;

		$per_page = $this->get_items_per_page( 'wprobo_documerge_submissions_per_page', 10 );

		$submissions_table = $wpdb->prefix . 'wprdm_submissions';
		$forms_table       = $wpdb->prefix . 'wprdm_forms';
		$templates_table   = $wpdb->prefix . 'wprdm_templates';

		// Build WHERE clauses from filters.
		$where_clauses = array();
		$where_values  = array();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';

		if ( $form_id > 0 ) {
			$where_clauses[] = 's.form_id = %d';
			$where_values[]  = $form_id;
		}

		if ( ! empty( $status ) ) {
			$where_clauses[] = 's.status = %s';
			$where_values[]  = $status;
		}

		if ( ! empty( $search ) ) {
			$where_clauses[] = '(s.submitter_email LIKE %s OR f.title LIKE %s)';
			$like            = '%' . $wpdb->esc_like( $search ) . '%';
			$where_values[]  = $like;
			$where_values[]  = $like;
		}

		$where_sql = '';
		if ( ! empty( $where_clauses ) ) {
			$where_sql = ' AND ' . implode( ' AND ', $where_clauses );
		}

		// Count total.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$count_query = "SELECT COUNT(*)
			FROM {$submissions_table} s
			LEFT JOIN {$forms_table} f ON s.form_id = f.id
			WHERE 1=1{$where_sql}";

		if ( ! empty( $where_values ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$total_items = (int) $wpdb->get_var( $wpdb->prepare( $count_query, $where_values ) );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$total_items = (int) $wpdb->get_var( $count_query );
		}

		// Sorting.
		$allowed_orderby = array( 'created_at', 'form_title', 'submitter_email', 'status' );
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$orderby = isset( $_GET['orderby'] ) && in_array( sanitize_key( $_GET['orderby'] ), $allowed_orderby, true )
			? sanitize_key( $_GET['orderby'] )
			: 'created_at';

		// Map column names for SQL.
		$orderby_map = array(
			'created_at'      => 's.created_at',
			'form_title'      => 'f.title',
			'submitter_email' => 's.submitter_email',
			'status'          => 's.status',
		);
		$orderby_sql = isset( $orderby_map[ $orderby ] ) ? $orderby_map[ $orderby ] : 's.created_at';

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$order = isset( $_GET['order'] ) && 'asc' === strtolower( sanitize_key( $_GET['order'] ) ) ? 'ASC' : 'DESC';

		// Pagination.
		$current_page = $this->get_pagenum();
		$offset       = ( $current_page - 1 ) * $per_page;

		// Fetch data.
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$select_query = "SELECT s.*, f.title AS form_title, t.name AS template_name
			FROM {$submissions_table} s
			LEFT JOIN {$forms_table} f ON s.form_id = f.id
			LEFT JOIN {$templates_table} t ON s.template_id = t.id
			WHERE 1=1{$where_sql}
			ORDER BY {$orderby_sql} {$order}
			LIMIT %d OFFSET %d";

		$query_values = array_merge( $where_values, array( $per_page, $offset ) );
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$this->items = $wpdb->get_results( $wpdb->prepare( $select_query, $query_values ) );

		if ( null === $this->items ) {
			$this->items = array();
		}

		// Set column headers.
		$this->_column_headers = array(
			$this->get_columns(),
			array(), // Hidden columns.
			$this->get_sortable_columns(),
		);

		// Set pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);
	}
}
