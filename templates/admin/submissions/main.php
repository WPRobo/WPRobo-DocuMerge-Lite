<?php
/**
 * Submissions — main page template.
 *
 * Displays the submissions list with filters, table, pagination,
 * bulk actions, and a slide-in detail panel.
 *
 * Receives:
 *   $forms (array) — Array of form objects for the filter dropdown.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/templates/admin/submissions
 * @author     Ali Shan <hello@wprobo.com>
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Page header variables.
$page_title     = __( 'Submissions', 'wprobo-documerge' );
$page_subtitle  = __( 'All form submissions and generated documents', 'wprobo-documerge' );
$primary_action = array(
    'url'   => '#',
    'label' => __( 'Export CSV', 'wprobo-documerge' ),
    'icon'  => 'dashicons-download',
    'id'    => 'wdm-export-csv',
);
?>
<div class="wdm-admin-wrap">

    <?php include WPROBO_DOCUMERGE_PATH . 'templates/admin/partials/page-header.php'; ?>

    <!-- ── Filter Bar ────────────────────────────────────────────── -->
    <div class="wdm-filter-bar">

        <select id="wdm-filter-form" class="wdm-select">
            <option value=""><?php esc_html_e( 'All Forms', 'wprobo-documerge' ); ?></option>
            <?php if ( ! empty( $forms ) && is_array( $forms ) ) : ?>
                <?php foreach ( $forms as $form ) : ?>
                    <option value="<?php echo esc_attr( $form->id ); ?>">
                        <?php echo esc_html( $form->title ); ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>

        <select id="wdm-filter-status" class="wdm-select">
            <option value=""><?php esc_html_e( 'All Statuses', 'wprobo-documerge' ); ?></option>
            <option value="completed"><?php esc_html_e( 'Completed', 'wprobo-documerge' ); ?></option>
            <option value="pending_payment"><?php esc_html_e( 'Pending Payment', 'wprobo-documerge' ); ?></option>
            <option value="processing"><?php esc_html_e( 'Processing', 'wprobo-documerge' ); ?></option>
            <option value="error"><?php esc_html_e( 'Error', 'wprobo-documerge' ); ?></option>
            <option value="payment_failed"><?php esc_html_e( 'Payment Failed', 'wprobo-documerge' ); ?></option>
        </select>

        <input type="text" id="wdm-filter-from" class="wdm-datepicker wdm-input" placeholder="<?php esc_attr_e( 'From date', 'wprobo-documerge' ); ?>">
        <input type="text" id="wdm-filter-to" class="wdm-datepicker wdm-input" placeholder="<?php esc_attr_e( 'To date', 'wprobo-documerge' ); ?>">

        <button type="button" id="wdm-filter-btn" class="wdm-btn wdm-btn-primary">
            <?php esc_html_e( 'Filter', 'wprobo-documerge' ); ?>
        </button>

    </div>

    <!-- ── Submissions Table ─────────────────────────────────────── -->
    <table class="wdm-table" id="wdm-submissions-table">
        <thead>
            <tr>
                <th class="wdm-col-check"><input type="checkbox" id="wdm-select-all"></th>
                <th><?php esc_html_e( 'Date', 'wprobo-documerge' ); ?></th>
                <th><?php esc_html_e( 'Form', 'wprobo-documerge' ); ?></th>
                <th><?php esc_html_e( 'Email', 'wprobo-documerge' ); ?></th>
                <th><?php esc_html_e( 'Status', 'wprobo-documerge' ); ?></th>
                <th><?php esc_html_e( 'Payment', 'wprobo-documerge' ); ?></th>
                <th><?php esc_html_e( 'Documents', 'wprobo-documerge' ); ?></th>
            </tr>
        </thead>
        <tbody id="wdm-submissions-tbody">
            <!-- Populated by AJAX -->
        </tbody>
    </table>

    <!-- ── Pagination ────────────────────────────────────────────── -->
    <div id="wdm-pagination">
        <!-- Populated by AJAX -->
    </div>

    <!-- ── Bulk Actions ──────────────────────────────────────────── -->
    <div class="wdm-bulk-actions">
        <button type="button" id="wdm-bulk-delete" class="wdm-btn wdm-btn-danger">
            <?php esc_html_e( 'Delete Selected', 'wprobo-documerge' ); ?>
        </button>
    </div>

</div>

<?php
// Slide panel for submission detail.
include WPROBO_DOCUMERGE_PATH . 'templates/admin/submissions/detail-panel.php';
?>

<div class="wdm-overlay" id="wdm-overlay"></div>
