<?php
/**
 * Submission detail panel — inner content.
 *
 * Rendered via AJAX and injected into #wdm-detail-body.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/templates/admin/submissions
 * @since      1.0.0
 *
 * @var object $submission The submission object with form_title, template_name.
 * @var array  $form_data  Decoded form_data JSON.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$fields = isset( $form_data['fields'] ) ? $form_data['fields'] : $form_data;
$meta   = isset( $form_data['meta'] ) ? $form_data['meta'] : array();
?>

<!-- ── Meta ──────────────────────────────────────────── -->
<div class="wdm-detail-section">
	<h4><?php esc_html_e( 'Details', 'wprobo-documerge-lite' ); ?></h4>
	<table class="wdm-detail-meta">
		<tr>
			<td><strong><?php esc_html_e( 'Date', 'wprobo-documerge-lite' ); ?></strong></td>
			<td><?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $submission->created_at ) ) ); ?></td>
		</tr>
		<tr>
			<td><strong><?php esc_html_e( 'Form', 'wprobo-documerge-lite' ); ?></strong></td>
			<td><?php echo esc_html( $submission->form_title ? $submission->form_title : '—' ); ?></td>
		</tr>
		<tr>
			<td><strong><?php esc_html_e( 'Template', 'wprobo-documerge-lite' ); ?></strong></td>
			<td><?php echo esc_html( $submission->template_name ? $submission->template_name : '—' ); ?></td>
		</tr>
		<tr>
			<td><strong><?php esc_html_e( 'Status', 'wprobo-documerge-lite' ); ?></strong></td>
			<td>
				<?php
				$status_map = array(
					'completed'  => 'success',
					'processing' => 'info',
					'error'      => 'error',
				);
				$badge      = isset( $status_map[ $submission->status ] ) ? $status_map[ $submission->status ] : 'info';
				?>
				<span class="wdm-badge wdm-badge-<?php echo esc_attr( $badge ); ?>">
					<?php echo esc_html( ucwords( str_replace( '_', ' ', $submission->status ) ) ); ?>
				</span>
			</td>
		</tr>
		<tr>
			<td><strong><?php esc_html_e( 'Email', 'wprobo-documerge-lite' ); ?></strong></td>
			<td><?php echo esc_html( $submission->submitter_email ? $submission->submitter_email : '—' ); ?></td>
		</tr>
		<tr>
			<td><strong><?php esc_html_e( 'IP Address', 'wprobo-documerge-lite' ); ?></strong></td>
			<td><?php echo esc_html( $submission->ip_address ? $submission->ip_address : '—' ); ?></td>
		</tr>
	</table>
</div>

<!-- ── Submitted Data ────────────────────────────────── -->
<?php if ( ! empty( $fields ) && is_array( $fields ) ) : ?>
<div class="wdm-detail-section">
	<h4><?php esc_html_e( 'Submitted Data', 'wprobo-documerge-lite' ); ?></h4>
	<table class="wdm-detail-fields">
		<?php foreach ( $fields as $key => $value ) : ?>
			<?php if ( is_string( $value ) && strpos( $value, 'data:image/' ) === 0 ) : ?>
				<tr>
					<td><strong><?php echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ); ?></strong></td>
					<td><img src="<?php echo esc_attr( $value ); ?>" alt="<?php esc_attr_e( 'Signature', 'wprobo-documerge-lite' ); ?>" style="max-width:200px;height:auto;border:1px solid #dde5f0;border-radius:4px;"></td>
				</tr>
			<?php else : ?>
				<tr>
					<td><strong><?php echo esc_html( ucwords( str_replace( '_', ' ', $key ) ) ); ?></strong></td>
					<td><?php echo esc_html( is_string( $value ) ? $value : wp_json_encode( $value ) ); ?></td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>

<!-- ── Documents ─────────────────────────────────────── -->
<div class="wdm-detail-section">
	<h4><?php esc_html_e( 'Documents', 'wprobo-documerge-lite' ); ?></h4>
	<?php if ( ! empty( $submission->doc_path_pdf ) || ! empty( $submission->doc_path_docx ) ) : ?>
		<div class="wdm-detail-docs">
			<?php if ( ! empty( $submission->doc_path_pdf ) ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=wprobo_documerge_download_document&submission_id=' . absint( $submission->id ) . '&format=pdf&nonce=' . wp_create_nonce( 'wprobo_documerge_admin' ) ) ); ?>" class="wdm-btn wdm-btn-primary" target="_blank">
					<span class="dashicons dashicons-download"></span>
					<?php esc_html_e( 'Download PDF', 'wprobo-documerge-lite' ); ?>
				</a>
			<?php endif; ?>
			<?php if ( ! empty( $submission->doc_path_docx ) ) : ?>
				<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=wprobo_documerge_download_document&submission_id=' . absint( $submission->id ) . '&format=docx&nonce=' . wp_create_nonce( 'wprobo_documerge_admin' ) ) ); ?>" class="wdm-btn" target="_blank">
					<span class="dashicons dashicons-download"></span>
					<?php esc_html_e( 'Download DOCX', 'wprobo-documerge-lite' ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<p class="wdm-text-muted"><?php esc_html_e( 'No documents generated yet.', 'wprobo-documerge-lite' ); ?></p>
	<?php endif; ?>
</div>

<!-- ── Admin Notes ──────────────────────────────────────── -->
<div class="wdm-detail-section">
	<h4><?php esc_html_e( 'Admin Notes', 'wprobo-documerge-lite' ); ?></h4>
	<textarea class="wdm-input wdm-admin-notes" id="wdm-admin-note-<?php echo absint( $submission->id ); ?>"
				rows="4" placeholder="<?php esc_attr_e( 'Add internal notes...', 'wprobo-documerge-lite' ); ?>"><?php echo esc_textarea( isset( $submission->admin_notes ) ? $submission->admin_notes : '' ); ?></textarea>
	<button type="button" class="wdm-btn wdm-btn-sm wdm-btn-primary wdm-save-note"
			data-id="<?php echo absint( $submission->id ); ?>" style="margin-top:8px;">
		<?php esc_html_e( 'Save Note', 'wprobo-documerge-lite' ); ?>
	</button>
</div>

<style>
.wdm-detail-section { margin-bottom: 24px; }
.wdm-detail-section h4 { font-size: 13px; font-weight: 700; color: #042157; text-transform: uppercase; letter-spacing: 0.04em; margin: 0 0 12px; padding-bottom: 8px; border-bottom: 1px solid #f0f4fa; }
.wdm-detail-meta td, .wdm-detail-fields td { padding: 6px 12px 6px 0; font-size: 13px; vertical-align: top; border-bottom: 1px solid #f8fafc; }
.wdm-detail-meta td:first-child, .wdm-detail-fields td:first-child { width: 120px; color: #6b7280; }
.wdm-detail-docs { display: flex; gap: 10px; flex-wrap: wrap; }
.wdm-text-muted { color: #6b7280; font-size: 13px; }
</style>

<?php // phpcs:enable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound ?>
