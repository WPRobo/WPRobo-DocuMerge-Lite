<?php
/**
 * Dashboard main page template.
 *
 * Displays plugin overview: stat cards, recent submissions, quick actions.
 *
 * Receives:
 *   $stats              (array)  — Keys: templates, forms, submissions, revenue_formatted, stripe_active.
 *   $recent_submissions (array)  — Array of submission objects.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/templates/admin/dashboard
 * @author     Ali Shan <hello@wprobo.com>
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Page header variables.
$page_title     = __( 'DocuMerge Dashboard', 'wprobo-documerge' );
$page_subtitle  = __( 'Overview of your document automation', 'wprobo-documerge' );
$primary_action = array(
	'url'   => admin_url( 'admin.php?page=wprobo-documerge-forms' ),
	'label' => __( 'New Form', 'wprobo-documerge' ),
	'icon'  => 'dashicons-plus-alt2',
);
?>
<div class="wdm-admin-wrap">

<?php require dirname( __DIR__ ) . '/partials/page-header.php'; ?>

	<!-- ── Stat Cards ────────────────────────────────────────────── -->
	<!-- ── Quick Actions Bar ────────────────────────────────────── -->
	<div class="wdm-quick-bar">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprobo-documerge-templates' ) ); ?>">
			<span class="dashicons dashicons-upload"></span> <?php esc_html_e( 'Upload Template', 'wprobo-documerge' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprobo-documerge-forms&action=new' ) ); ?>">
			<span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'New Form', 'wprobo-documerge' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprobo-documerge-submissions' ) ); ?>">
			<span class="dashicons dashicons-list-view"></span> <?php esc_html_e( 'Submissions', 'wprobo-documerge' ); ?>
		</a>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprobo-documerge-settings' ) ); ?>">
			<span class="dashicons dashicons-admin-generic"></span> <?php esc_html_e( 'Settings', 'wprobo-documerge' ); ?>
		</a>
		<a href="<?php echo esc_url( 'https://wprobo.com/docs/documerge' ); ?>" target="_blank" rel="noopener noreferrer">
			<span class="dashicons dashicons-book"></span> <?php esc_html_e( 'Docs', 'wprobo-documerge' ); ?>
		</a>
	</div>

	<?php
	// Calculate success rate for the 4th card.
	global $wpdb;
	$month_start = gmdate( 'Y-m-01 00:00:00' );
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$total_this_month = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wprdm_submissions WHERE created_at >= %s", $month_start ) );
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$completed_this_month = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wprdm_submissions WHERE status = 'completed' AND created_at >= %s", $month_start ) );
	$success_rate         = $total_this_month > 0 ? round( ( $completed_this_month / $total_this_month ) * 100 ) : 0;
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$error_count = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}wprdm_submissions WHERE status = 'error' AND created_at >= %s", $month_start ) );
	?>

	<!-- ── Stat Cards (always 4) ───────────────────────────────── -->
	<div class="wdm-stat-cards">
		<div class="wdm-stat-card">
			<div class="wdm-stat-card-icon"><span class="dashicons dashicons-media-document"></span></div>
			<div class="wdm-stat-card-content">
				<span class="wdm-stat-value"><?php echo esc_html( $stats['templates'] ); ?></span>
				<span class="wdm-stat-label"><?php esc_html_e( 'Active', 'wprobo-documerge' ); ?></span>
			</div>
			<span class="wdm-stat-card-title"><?php esc_html_e( 'Templates', 'wprobo-documerge' ); ?></span>
		</div>

		<div class="wdm-stat-card">
			<div class="wdm-stat-card-icon"><span class="dashicons dashicons-feedback"></span></div>
			<div class="wdm-stat-card-content">
				<span class="wdm-stat-value"><?php echo esc_html( $stats['forms'] ); ?></span>
				<span class="wdm-stat-label"><?php esc_html_e( 'Active', 'wprobo-documerge' ); ?></span>
			</div>
			<span class="wdm-stat-card-title"><?php esc_html_e( 'Forms', 'wprobo-documerge' ); ?></span>
		</div>

		<div class="wdm-stat-card">
			<div class="wdm-stat-card-icon"><span class="dashicons dashicons-email-alt"></span></div>
			<div class="wdm-stat-card-content">
				<span class="wdm-stat-value"><?php echo esc_html( $stats['submissions'] ); ?></span>
				<span class="wdm-stat-label"><?php esc_html_e( 'This Month', 'wprobo-documerge' ); ?></span>
			</div>
			<span class="wdm-stat-card-title"><?php esc_html_e( 'Submissions', 'wprobo-documerge' ); ?></span>
		</div>

		<div class="wdm-stat-card">
			<div class="wdm-stat-card-icon"><span class="dashicons dashicons-yes-alt"></span></div>
			<div class="wdm-stat-card-content">
				<span class="wdm-stat-value"><?php echo esc_html( $success_rate . '%' ); ?></span>
				<span class="wdm-stat-label">
					<?php
					if ( $error_count > 0 ) {
						/* translators: %d: number of errors */
						printf( esc_html__( '%d error(s)', 'wprobo-documerge' ), $error_count );
					} else {
						esc_html_e( 'This Month', 'wprobo-documerge' );
					}
					?>
				</span>
			</div>
			<span class="wdm-stat-card-title"><?php esc_html_e( 'Success Rate', 'wprobo-documerge' ); ?></span>
		</div>
	</div>

	<!-- ── Charts Row ──────────────────────────────────────────── -->
	<div class="wdm-charts-row">

		<!-- Submissions Trend (Line Chart) -->
		<div class="wdm-card wdm-chart-card">
			<div class="wdm-card-header">
				<h2 class="wdm-card-title"><?php esc_html_e( 'Submissions (Last 7 Days)', 'wprobo-documerge' ); ?></h2>
			</div>
			<div class="wdm-card-body">
				<canvas id="wdm-chart-daily" height="220"></canvas>
			</div>
		</div>

		<!-- Status Breakdown (Doughnut Chart) -->
		<div class="wdm-card wdm-chart-card">
			<div class="wdm-card-header">
				<h2 class="wdm-card-title"><?php esc_html_e( 'Status Breakdown', 'wprobo-documerge' ); ?></h2>
			</div>
			<div class="wdm-card-body">
				<canvas id="wdm-chart-status" height="220"></canvas>
			</div>
		</div>

	</div>

	<!-- ── Row 2: Pro Feature Teasers ─────────────────────────────── -->
	<div class="wdm-charts-row">
		<div class="wdm-card wdm-chart-card">
			<div class="wdm-card-header">
				<h2 class="wdm-card-title"><?php esc_html_e( 'Revenue', 'wprobo-documerge' ); ?> <span class="wdm-pro-badge">PRO</span></h2>
			</div>
			<div class="wdm-card-body" style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:220px;text-align:center;padding:24px;">
				<div style="display:flex;align-items:flex-end;gap:8px;margin-bottom:16px;">
					<?php
					$bar_heights = array( 40, 65, 50, 85, 70, 95, 110 );
					$bar_labels  = array( 'M', 'T', 'W', 'T', 'F', 'S', 'S' );
					foreach ( $bar_heights as $i => $h ) :
						?>
					<div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
						<div style="width:24px;background:linear-gradient(to top,#166441,#1e8a56);border-radius:3px 3px 0 0;opacity:0.2;height:<?php echo absint( $h ); ?>px;"></div>
						<span style="font-size:10px;color:#9ca3af;"><?php echo esc_html( $bar_labels[ $i ] ); ?></span>
					</div>
					<?php endforeach; ?>
				</div>
				<p style="color:#6b7280;font-size:13px;margin:0 0 12px;"><?php esc_html_e( 'Track payments and revenue with Stripe integration.', 'wprobo-documerge' ); ?></p>
				<a href="<?php echo esc_url( \WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Feature_Gate::get_instance()->wprobo_documerge_get_upgrade_url() ); ?>" target="_blank" class="wdm-btn-upgrade" style="font-size:12px;padding:6px 16px;">
					<?php esc_html_e( 'Upgrade to Pro', 'wprobo-documerge' ); ?>
				</a>
			</div>
		</div>

		<div class="wdm-card wdm-chart-card">
			<div class="wdm-card-header">
				<h2 class="wdm-card-title"><?php esc_html_e( 'Analytics', 'wprobo-documerge' ); ?> <span class="wdm-pro-badge">PRO</span></h2>
			</div>
			<div class="wdm-card-body" style="display:flex;flex-direction:column;align-items:center;justify-content:center;min-height:220px;text-align:center;padding:24px;">
				<div style="display:flex;gap:6px;margin-bottom:16px;">
					<?php for ( $b = 0; $b < 5; $b++ ) : ?>
					<div style="width:32px;background:linear-gradient(to top,#042157,#0a3d8f);border-radius:4px 4px 0 0;opacity:<?php echo esc_attr( 0.15 + ( $b * 0.12 ) ); ?>;height:<?php echo esc_attr( 30 + ( $b * 25 ) ); ?>px;"></div>
					<?php endfor; ?>
				</div>
				<p style="color:#6b7280;font-size:13px;margin:0 0 12px;"><?php esc_html_e( 'Form analytics, revenue tracking, and detailed charts.', 'wprobo-documerge' ); ?></p>
				<a href="<?php echo esc_url( \WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Feature_Gate::get_instance()->wprobo_documerge_get_upgrade_url() ); ?>" target="_blank" class="wdm-btn-upgrade" style="font-size:12px;padding:6px 16px;">
					<?php esc_html_e( 'Upgrade to Pro', 'wprobo-documerge' ); ?>
				</a>
			</div>
		</div>
	</div>

	<!-- ── Recent Submissions ────────────────────────────────────── -->
	<div class="wdm-card">
		<div class="wdm-card-header">
			<h2 class="wdm-card-title"><?php esc_html_e( 'Recent Submissions', 'wprobo-documerge' ); ?></h2>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprobo-documerge-submissions' ) ); ?>" class="wdm-card-link">
				<?php esc_html_e( 'View All', 'wprobo-documerge' ); ?> &rarr;
			</a>
		</div>
		<div class="wdm-card-body">
			<?php if ( empty( $recent_submissions ) ) : ?>

				<div class="wdm-empty-state">
					<span class="dashicons dashicons-email-alt"></span>
					<h3><?php esc_html_e( 'No submissions yet', 'wprobo-documerge' ); ?></h3>
					<p><?php esc_html_e( 'Create a form and embed it on a page to start receiving submissions.', 'wprobo-documerge' ); ?></p>
				</div>

			<?php else : ?>

				<table class="wdm-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Date', 'wprobo-documerge' ); ?></th>
							<th><?php esc_html_e( 'Form', 'wprobo-documerge' ); ?></th>
							<th><?php esc_html_e( 'Email', 'wprobo-documerge' ); ?></th>
							<th><?php esc_html_e( 'Status', 'wprobo-documerge' ); ?></th>
							<th><?php esc_html_e( 'Doc', 'wprobo-documerge' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $recent_submissions as $submission ) : ?>
							<tr>
								<td>
									<?php
									printf(
										/* translators: %s: Human-readable time difference. */
										esc_html__( '%s ago', 'wprobo-documerge' ),
										esc_html( human_time_diff( strtotime( $submission->created_at ), time() ) )
									);
									?>
								</td>
								<td><?php echo esc_html( $submission->form_title ); ?></td>
								<td>
									<?php
									$email_display = $submission->submitter_email;
									if ( strlen( $email_display ) > 25 ) {
										$email_display = substr( $email_display, 0, 22 ) . '...';
									}
									echo esc_html( $email_display );
									?>
								</td>
								<td>
									<?php
									$status       = $submission->status;
									$status_map   = array(
										'completed'       => 'success',
										'processing'      => 'info',
										'error'           => 'error',
										'pending_payment' => 'pending',
										'payment_failed'  => 'error',
									);
									$badge_class  = isset( $status_map[ $status ] ) ? $status_map[ $status ] : 'info';
									$status_label = str_replace( '_', ' ', $status );
									?>
									<span class="wdm-badge wdm-badge-<?php echo esc_attr( $badge_class ); ?>">
										<?php echo esc_html( ucwords( $status_label ) ); ?>
									</span>
								</td>
								<td>
									<?php if ( ! empty( $submission->doc_path_pdf ) || ! empty( $submission->doc_path_docx ) ) : ?>
										<?php
										$dl_format = ! empty( $submission->doc_path_pdf ) ? 'pdf' : 'docx';
										$dl_url    = admin_url( 'admin-ajax.php?action=wprobo_documerge_download_document&submission_id=' . absint( $submission->id ) . '&format=' . $dl_format . '&nonce=' . wp_create_nonce( 'wprobo_documerge_admin' ) );
										?>
										<a href="<?php echo esc_url( $dl_url ); ?>" class="wdm-doc-download" title="<?php esc_attr_e( 'Download document', 'wprobo-documerge' ); ?>" target="_blank" rel="noopener noreferrer">
											<span class="dashicons dashicons-download"></span>
										</a>
									<?php else : ?>
										&mdash;
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

			<?php endif; ?>
		</div>
	</div>


</div><!-- .wdm-admin-wrap -->

<script>
document.addEventListener('DOMContentLoaded', function() {
	if (typeof Chart === 'undefined') return;

	// Brand colours
	var blue = '#042157';
	var green = '#166441';
	var lightBlue = '#0a3d8f';
	var lightGreen = '#1e8a56';
	var bgLight = '#f0f4fa';

	// ── Line Chart: Daily Submissions ──────────────────────
	var dailyData = <?php echo wp_json_encode( $chart_data['daily'] ); ?>;
	var dailyLabels = dailyData.map(function(d) { return d.label; });
	var dailyCounts = dailyData.map(function(d) { return d.count; });

	new Chart(document.getElementById('wdm-chart-daily'), {
		type: 'line',
		data: {
			labels: dailyLabels,
			datasets: [{
				label: '<?php echo esc_js( __( 'Submissions', 'wprobo-documerge' ) ); ?>',
				data: dailyCounts,
				borderColor: blue,
				backgroundColor: 'rgba(4, 33, 87, 0.08)',
				borderWidth: 2.5,
				pointBackgroundColor: blue,
				pointBorderColor: '#ffffff',
				pointBorderWidth: 2,
				pointRadius: 5,
				pointHoverRadius: 7,
				fill: true,
				tension: 0.35
			}]
		},
		options: {
			responsive: true,
			maintainAspectRatio: false,
			plugins: {
				legend: { display: false },
				tooltip: {
					backgroundColor: blue,
					titleFont: { size: 13, weight: '600' },
					bodyFont: { size: 12 },
					padding: 10,
					cornerRadius: 6
				}
			},
			scales: {
				y: {
					beginAtZero: true,
					ticks: {
						stepSize: 1,
						font: { size: 11 },
						color: '#6b7280'
					},
					grid: { color: '#f0f4fa' }
				},
				x: {
					ticks: { font: { size: 11 }, color: '#6b7280' },
					grid: { display: false }
				}
			}
		}
	});

	// ── Doughnut Chart: Status Breakdown ───────────────────
	var statusData = <?php echo wp_json_encode( $chart_data['statuses'] ); ?>;
	var statusLabels = [];
	var statusCounts = [];
	var statusColors = {
		'completed': green,
		'processing': '#3b82f6',
		'pending_payment': '#d97706',
		'error': '#dc2626',
		'payment_failed': '#ef4444'
	};
	var bgColors = [];

	statusData.forEach(function(s) {
		var lbl = s.status.replace(/_/g, ' ');
		lbl = lbl.charAt(0).toUpperCase() + lbl.slice(1);
		statusLabels.push(lbl);
		statusCounts.push(parseInt(s.count, 10));
		bgColors.push(statusColors[s.status] || '#94a3b8');
	});

	if (statusLabels.length > 0) {
		new Chart(document.getElementById('wdm-chart-status'), {
			type: 'doughnut',
			data: {
				labels: statusLabels,
				datasets: [{
					data: statusCounts,
					backgroundColor: bgColors,
					borderWidth: 2,
					borderColor: '#ffffff',
					hoverOffset: 6
				}]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				cutout: '65%',
				plugins: {
					legend: {
						position: 'bottom',
						labels: {
							padding: 16,
							usePointStyle: true,
							pointStyle: 'circle',
							font: { size: 12 }
						}
					},
					tooltip: {
						backgroundColor: blue,
						padding: 10,
						cornerRadius: 6
					}
				}
			}
		});
	} else {
		document.getElementById('wdm-chart-status').parentNode.innerHTML =
			'<p style="text-align:center;color:#6b7280;padding:40px 0;">No submissions yet</p>';
	}

});
</script>
