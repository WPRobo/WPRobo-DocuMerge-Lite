<?php
/**
 * Form Builder — two-panel builder UI template.
 *
 * Displays the drag-and-drop form builder with a left sidebar
 * for field types and a right panel for the canvas and settings.
 *
 * Receives:
 *   $form      (object|null) — Form object for editing, or null for a new form.
 *                              Properties: id, title, template_id, fields (JSON string),
 *                              output_format, submit_label, success_message, multistep.
 *   $templates (array)       — Array of template objects. Each has: id, name.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/templates/admin/form-builder
 * @author     Ali Shan <hello@wprobo.com>
 * @since      1.0.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Extract form data for pre-population.
$form_id        = isset( $form->id ) ? absint( $form->id ) : 0;
$form_title     = isset( $form->title ) ? $form->title : '';
$form_template  = isset( $form->template_id ) ? absint( $form->template_id ) : 0;
$form_fields    = isset( $form->fields ) ? $form->fields : '[]';
$form_output    = isset( $form->output_format ) ? $form->output_format : 'pdf';
$form_submit    = isset( $form->submit_label ) ? $form->submit_label : '';
$form_success   = isset( $form->success_message ) ? $form->success_message : '';
$form_multistep = isset( $form->multistep_enabled ) ? absint( $form->multistep_enabled ) : ( isset( $form->multistep ) ? absint( $form->multistep ) : 0 );
?>
<div class="wdm-admin-wrap">

	<!-- ── Builder Header ──────────────────────────────────────── -->
	<div class="wdm-builder-header">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprobo-documerge-forms' ) ); ?>" class="wdm-back-link">
			<span class="dashicons dashicons-arrow-left-alt"></span>
			<?php esc_html_e( 'Back to Forms', 'wprobo-documerge' ); ?>
		</a>
		<input
			type="text"
			id="wdm-form-title"
			class="wdm-form-title-input"
			value="<?php echo esc_attr( $form_title ? $form_title : __( 'Untitled Form', 'wprobo-documerge' ) ); ?>"
			placeholder="<?php esc_attr_e( 'Form Title', 'wprobo-documerge' ); ?>"
		>
		<button type="button" class="wdm-btn wdm-btn-secondary" id="wdm-create-page" <?php echo $form_id ? '' : 'disabled'; ?>>
			<span class="dashicons dashicons-admin-page"></span>
			<?php esc_html_e( 'Create Page', 'wprobo-documerge' ); ?>
		</button>
		<button type="button" class="wdm-btn wdm-btn-primary" id="wdm-save-form">
			<?php esc_html_e( 'Save Form', 'wprobo-documerge' ); ?>
		</button>
	</div>

	<input type="hidden" id="wdm-form-id" value="<?php echo esc_attr( $form_id ); ?>">
	<?php
	// Global settings are the source of truth for mode/integration.
	$global_mode        = get_option( 'wprobo_documerge_form_mode', 'standalone' );
	$global_integration = get_option( 'wprobo_documerge_active_integration', '' );
	$form_mode          = $global_mode;
	$form_integration   = $global_integration;
	?>
	<input type="hidden" id="wdm-form-mode" value="<?php echo esc_attr( $form_mode ); ?>">
	<input type="hidden" id="wdm-form-integration" value="<?php echo esc_attr( $form_integration ); ?>">

	<div id="wdm-notices">
		<?php if ( isset( $_GET['saved'] ) && '1' === $_GET['saved'] ) : // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>
			<div class="wdm-notice wdm-notice-success" role="alert">
				<span class="wdm-notice-icon dashicons dashicons-yes-alt"></span>
				<span class="wdm-notice-text"><?php esc_html_e( 'Form saved successfully.', 'wprobo-documerge' ); ?></span>
				<button class="wdm-notice-dismiss" onclick="this.parentElement.remove();">&times;</button>
			</div>
		<?php endif; ?>
	</div>

	<div class="wdm-builder-wrap<?php echo ( 'integrated' === $form_mode ) ? ' wdm-builder-integrated' : ''; ?>">

		<?php if ( 'integrated' !== $form_mode ) : ?>
		<!-- ── LEFT PANEL: Field Types (standalone mode only) ──── -->
		<div class="wdm-builder-sidebar">

			<h3><?php esc_html_e( 'Basic Fields', 'wprobo-documerge' ); ?></h3>
			<div class="wdm-field-type-grid">
				<button type="button" class="wdm-field-type-btn" data-type="text">
					<span class="dashicons dashicons-editor-textcolor"></span>
					<?php esc_html_e( 'Text', 'wprobo-documerge' ); ?>
				</button>
				<button type="button" class="wdm-field-type-btn" data-type="textarea">
					<span class="dashicons dashicons-editor-paragraph"></span>
					<?php esc_html_e( 'Textarea', 'wprobo-documerge' ); ?>
				</button>
				<button type="button" class="wdm-field-type-btn" data-type="email">
					<span class="dashicons dashicons-email"></span>
					<?php esc_html_e( 'Email', 'wprobo-documerge' ); ?>
				</button>
				<button type="button" class="wdm-field-type-btn" data-type="phone">
					<span class="dashicons dashicons-phone"></span>
					<?php esc_html_e( 'Phone', 'wprobo-documerge' ); ?>
				</button>
				<button type="button" class="wdm-field-type-btn" data-type="number">
					<span class="dashicons dashicons-editor-ol"></span>
					<?php esc_html_e( 'Number', 'wprobo-documerge' ); ?>
				</button>
				<button type="button" class="wdm-field-type-btn" data-type="date">
					<span class="dashicons dashicons-calendar-alt"></span>
					<?php esc_html_e( 'Date', 'wprobo-documerge' ); ?>
				</button>
			</div>

			<h3><?php esc_html_e( 'Choice Fields', 'wprobo-documerge' ); ?></h3>
			<div class="wdm-field-type-grid">
				<button type="button" class="wdm-field-type-btn" data-type="dropdown">
					<span class="dashicons dashicons-arrow-down-alt2"></span>
					<?php esc_html_e( 'Dropdown', 'wprobo-documerge' ); ?>
				</button>
				<button type="button" class="wdm-field-type-btn" data-type="radio">
					<span class="dashicons dashicons-marker"></span>
					<?php esc_html_e( 'Radio', 'wprobo-documerge' ); ?>
				</button>
				<button type="button" class="wdm-field-type-btn" data-type="checkbox">
					<span class="dashicons dashicons-yes"></span>
					<?php esc_html_e( 'Checkbox', 'wprobo-documerge' ); ?>
				</button>
			</div>

			<h3><?php esc_html_e( 'Special Fields', 'wprobo-documerge' ); ?></h3>
			<div class="wdm-field-type-grid">
				<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_field_type_locked( 'dashicons-art', __( 'Signature', 'wprobo-documerge' ) ); ?>
				<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_field_type_locked( 'dashicons-money-alt', __( 'Payment', 'wprobo-documerge' ) ); ?>
				<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_field_type_locked( 'dashicons-shield', __( 'CAPTCHA', 'wprobo-documerge' ) ); ?>
			</div>

			<h3><?php esc_html_e( 'Advanced Fields', 'wprobo-documerge' ); ?></h3>
			<div class="wdm-field-type-grid">
				<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_field_type_locked( 'dashicons-upload', __( 'File Upload', 'wprobo-documerge' ) ); ?>
				<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_field_type_locked( 'dashicons-location', __( 'Address', 'wprobo-documerge' ) ); ?>
				<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_field_type_locked( 'dashicons-admin-users', __( 'Name', 'wprobo-documerge' ) ); ?>
				<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_field_type_locked( 'dashicons-hidden', __( 'Hidden', 'wprobo-documerge' ) ); ?>
				<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_field_type_locked( 'dashicons-lock', __( 'Password', 'wprobo-documerge' ) ); ?>
				<button type="button" class="wdm-field-type-btn" data-type="url">
					<span class="dashicons dashicons-admin-links"></span>
					<?php esc_html_e( 'Website', 'wprobo-documerge' ); ?>
				</button>
				<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_field_type_locked( 'dashicons-star-filled', __( 'Rating', 'wprobo-documerge' ) ); ?>
				<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_field_type_locked( 'dashicons-plus-alt', __( 'Repeater', 'wprobo-documerge' ) ); ?>
				<button type="button" class="wdm-field-type-btn" data-type="ip_address">
					<span class="dashicons dashicons-admin-site-alt3"></span>
					<?php esc_html_e( 'IP Address', 'wprobo-documerge' ); ?>
				</button>
				<button type="button" class="wdm-field-type-btn" data-type="tracking">
					<span class="dashicons dashicons-chart-area"></span>
					<?php esc_html_e( 'Tracking', 'wprobo-documerge' ); ?>
				</button>
			</div>

			<h3><?php esc_html_e( 'Layout Elements', 'wprobo-documerge' ); ?></h3>
			<div class="wdm-field-type-grid">
				<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_field_type_locked( 'dashicons-editor-code', __( 'HTML Block', 'wprobo-documerge' ) ); ?>
				<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_field_type_locked( 'dashicons-minus', __( 'Divider', 'wprobo-documerge' ) ); ?>
			</div>

		</div>
		<?php endif; // End standalone sidebar. ?>

		<!-- ── RIGHT PANEL: Canvas + Settings ──────────────────── -->
		<div class="wdm-builder-main">

			<!-- Builder Main Tabs -->
			<div class="wdm-builder-main-tabs">
				<?php if ( 'integrated' !== $form_mode ) : ?>
					<button type="button" class="wdm-builder-main-tab wdm-builder-main-tab-active" data-tab="fields"><?php esc_html_e( 'Fields', 'wprobo-documerge' ); ?></button>
					<button type="button" class="wdm-builder-main-tab" data-tab="settings"><?php esc_html_e( 'Settings', 'wprobo-documerge' ); ?></button>
				<?php else : ?>
					<button type="button" class="wdm-builder-main-tab" data-tab="fields"><?php esc_html_e( 'Fields', 'wprobo-documerge' ); ?></button>
					<button type="button" class="wdm-builder-main-tab wdm-builder-main-tab-active" data-tab="settings"><?php esc_html_e( 'Settings', 'wprobo-documerge' ); ?></button>
				<?php endif; ?>
			</div>

			<!-- Fields Tab Content -->
			<div class="wdm-builder-main-content<?php echo ( 'integrated' !== $form_mode ) ? ' wdm-builder-main-content-active' : ''; ?>" data-tab="fields">
				<?php if ( 'integrated' === $form_mode ) : ?>
					<!-- Integrated mode: fields managed in external plugin -->
					<?php
					$int_plugin_name = '';
					if ( ! empty( $form_integration ) ) {
						$int_names       = array(
							'wpforms' => 'WPForms',
							'cf7'     => 'Contact Form 7',
							'gravity' => 'Gravity Forms',
							'fluent'  => 'Fluent Forms',
						);
						$int_plugin_name = isset( $int_names[ $form_integration ] ) ? $int_names[ $form_integration ] : ucfirst( $form_integration );
					}
					?>
					<div class="wdm-integrated-notice">
						<div class="wdm-integrated-notice-icon">
							<span class="dashicons dashicons-admin-links"></span>
						</div>
						<h3>
							<?php
							/* translators: %s: form plugin name */
							printf( esc_html__( 'Fields are managed in %s', 'wprobo-documerge' ), esc_html( $int_plugin_name ) );
							?>
						</h3>
						<p>
							<?php
							printf(
								/* translators: %s: form plugin name */
								esc_html__( 'This form is running in integrated mode. Form fields are built and managed inside %s — not here in DocuMerge.', 'wprobo-documerge' ),
								esc_html( $int_plugin_name )
							);
							?>
						</p>
						<div class="wdm-integrated-notice-steps">
							<div class="wdm-integrated-step">
								<span class="wdm-integrated-step-num">1</span>
								<span>
									<?php
									/* translators: %s: form plugin name */
									printf( esc_html__( 'Build your form fields in %s', 'wprobo-documerge' ), esc_html( $int_plugin_name ) );
									?>
								</span>
							</div>
							<div class="wdm-integrated-step">
								<span class="wdm-integrated-step-num">2</span>
								<span><?php esc_html_e( 'Go to the Settings tab → select your template and external form', 'wprobo-documerge' ); ?></span>
							</div>
							<div class="wdm-integrated-step">
								<span class="wdm-integrated-step-num">3</span>
								<span><?php esc_html_e( 'Map your merge tags to the external form fields', 'wprobo-documerge' ); ?></span>
							</div>
						</div>
						<div class="wdm-integrated-notice-actions">
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=wprobo-documerge-settings&highlight=form-mode' ) ); ?>" class="wdm-btn wdm-btn-sm">
								<span class="dashicons dashicons-admin-generic"></span>
								<?php esc_html_e( 'Change Mode', 'wprobo-documerge' ); ?>
							</a>
						</div>
					</div>
				<?php else : ?>
					<!-- Standalone mode: drag & drop canvas -->
					<div class="wdm-builder-canvas" id="wdm-builder-canvas">
						<div class="wdm-canvas-placeholder" id="wdm-canvas-placeholder">
							<span class="dashicons dashicons-plus-alt2"></span>
							<p><?php esc_html_e( 'Click a field type or drag here', 'wprobo-documerge' ); ?></p>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<!-- Settings Tab Content -->
			<div class="wdm-builder-main-content<?php echo ( 'integrated' === $form_mode ) ? ' wdm-builder-main-content-active' : ''; ?>" data-tab="settings">
				<!-- Form Settings -->
				<div class="wdm-builder-settings">
					<h3><?php esc_html_e( 'Form Settings', 'wprobo-documerge' ); ?></h3>

					<?php
					// ── Integration Configuration (stays above sub-tabs) ──────
					if ( 'integrated' === $form_mode ) :
						$btn_settings_int = ! empty( $form ) && ! empty( $form->settings ) ? json_decode( $form->settings, true ) : array();
						$external_form_id = isset( $btn_settings_int['external_form_id'] ) ? absint( $btn_settings_int['external_form_id'] ) : 0;
						$field_map        = isset( $btn_settings_int['field_map'] ) ? $btn_settings_int['field_map'] : array();
						$integration_slug = ! empty( $form_integration ) ? $form_integration : '';

						// Integrations are Pro-only — set empty defaults.
						$external_forms  = array();
						$external_fields = array();

						// Get merge tags from the selected template.
						$int_merge_tags = array();
						if ( ! empty( $form_template ) ) {
							$tmpl_mgr = new \WPRobo\DocuMerge\Template\WPRobo_DocuMerge_Template_Manager();
							$tmpl     = $tmpl_mgr->wprobo_documerge_get_template( $form_template );
							if ( $tmpl && ! empty( $tmpl->merge_tags ) ) {
								$int_merge_tags = json_decode( $tmpl->merge_tags, true );
								if ( ! is_array( $int_merge_tags ) ) {
									$int_merge_tags = array();
								}
							}
						}

						// Integration label for UI.
						$integration_label = $active_integration ? $active_integration->wprobo_documerge_get_name() : ucfirst( $integration_slug );
						?>

					<div class="wdm-integration-config-section">
						<h4 class="wdm-section-heading">
							<span class="dashicons dashicons-admin-links"></span>
							<?php
							/* translators: %s: integration name */
							printf( esc_html__( '%s Integration', 'wprobo-documerge' ), esc_html( $integration_label ) );
							?>
						</h4>

						<?php if ( $active_integration && $active_integration->wprobo_documerge_is_active() ) : ?>

							<div class="wdm-field-group">
								<label for="wdm-external-form-id">
									<?php
									/* translators: %s: integration name */
									printf( esc_html__( '%s Form', 'wprobo-documerge' ), esc_html( $integration_label ) );
									?>
								</label>
								<select id="wdm-external-form-id" class="wdm-select">
									<option value="">
										<?php
										/* translators: %s: integration name */
										printf( esc_html__( '— Select %s Form —', 'wprobo-documerge' ), esc_html( $integration_label ) );
										?>
									</option>
									<?php foreach ( $external_forms as $ef ) : ?>
										<option value="<?php echo absint( $ef['id'] ); ?>" <?php selected( $external_form_id, $ef['id'] ); ?>>
											<?php echo esc_html( $ef['title'] . ' (ID: ' . $ef['id'] . ')' ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<span class="wdm-description">
									<?php
									/* translators: %s: integration name */
									printf( esc_html__( 'Select the %s form that will trigger document generation.', 'wprobo-documerge' ), esc_html( $integration_label ) );
									?>
								</span>
							</div>

							<div class="wdm-field-group" id="wdm-field-map-wrap">
							<?php if ( ! empty( $int_merge_tags ) && ! empty( $external_fields ) ) : ?>
								<label><?php esc_html_e( 'Field Mapping', 'wprobo-documerge' ); ?></label>
								<span class="wdm-description" style="margin-bottom:12px;display:block;">
									<?php esc_html_e( 'Map each template merge tag to a field from your external form.', 'wprobo-documerge' ); ?>
								</span>
								<table class="wdm-field-map-table">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Template Merge Tag', 'wprobo-documerge' ); ?></th>
											<th>&rarr;</th>
											<th>
												<?php
												/* translators: %s: integration name */
												printf( esc_html__( '%s Field', 'wprobo-documerge' ), esc_html( $integration_label ) );
												?>
											</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$system_tags = array( 'current_date', 'current_time', 'site_name' );
										foreach ( $int_merge_tags as $tag ) :
											if ( in_array( $tag, $system_tags, true ) ) {
												continue;
											}
											$mapped_to = isset( $field_map[ $tag ] ) ? $field_map[ $tag ] : '';
											?>
											<tr class="wdm-field-map-row">
												<td><code>{<?php echo esc_html( $tag ); ?>}</code></td>
												<td>&rarr;</td>
												<td>
													<select class="wdm-field-map-select wdm-select" data-merge-tag="<?php echo esc_attr( $tag ); ?>">
														<option value=""><?php esc_html_e( '— Not mapped —', 'wprobo-documerge' ); ?></option>
														<?php foreach ( $external_fields as $efield ) : ?>
															<option value="<?php echo esc_attr( $efield['key'] ); ?>" <?php selected( $mapped_to, $efield['key'] ); ?>>
																<?php echo esc_html( $efield['label'] . ' (' . $efield['type'] . ')' ); ?>
															</option>
														<?php endforeach; ?>
													</select>
												</td>
											</tr>
										<?php endforeach; ?>
									</tbody>
								</table>
							<?php elseif ( empty( $form_template ) && $external_form_id > 0 ) : ?>
								<div class="wdm-notice wdm-notice-warning" role="alert" style="margin-top:12px;">
									<span class="wdm-notice-icon dashicons dashicons-info"></span>
									<span class="wdm-notice-text">
										<?php esc_html_e( 'Select a template below, then save the form to configure field mapping.', 'wprobo-documerge' ); ?>
									</span>
								</div>
							<?php elseif ( ! empty( $form_template ) && empty( $external_form_id ) ) : ?>
								<p class="wdm-text-muted"><?php esc_html_e( 'Select an external form to see field mapping.', 'wprobo-documerge' ); ?></p>
							<?php endif; ?>
							</div>

						<?php else : ?>
							<div class="wdm-notice wdm-notice-error" role="alert">
								<span class="wdm-notice-icon dashicons dashicons-warning"></span>
								<span class="wdm-notice-text">
									<?php
									/* translators: %s: integration name */
									printf( esc_html__( '%s is not currently active. Please install and activate it to use this integration.', 'wprobo-documerge' ), esc_html( $integration_label ) );
									?>
								</span>
							</div>
						<?php endif; ?>
					</div>

					<?php endif; // End integration config. ?>

					<!-- Settings Sub-tabs -->
					<div class="wdm-settings-subtabs">
						<button type="button" class="wdm-settings-subtab wdm-settings-subtab-active" data-subtab="general"><?php esc_html_e( 'General', 'wprobo-documerge' ); ?></button>
						<button type="button" class="wdm-settings-subtab" data-subtab="button"><?php esc_html_e( 'Button', 'wprobo-documerge' ); ?></button>
						<button type="button" class="wdm-settings-subtab" data-subtab="limits"><?php esc_html_e( 'Limits', 'wprobo-documerge' ); ?></button>
						<button type="button" class="wdm-settings-subtab" data-subtab="payment"><?php esc_html_e( 'Payment', 'wprobo-documerge' ); ?></button>
						<button type="button" class="wdm-settings-subtab" data-subtab="notifications"><?php esc_html_e( 'Notifications', 'wprobo-documerge' ); ?></button>
					</div>

					<!-- ── General Sub-tab ─────────────────────────────────── -->
					<div class="wdm-settings-subtab-content wdm-settings-subtab-active" data-subtab="general">

						<div class="wdm-field-group">
							<label for="wdm-form-template"><?php esc_html_e( 'Template', 'wprobo-documerge' ); ?></label>
							<select id="wdm-form-template" class="wdm-select">
								<option value=""><?php esc_html_e( '— Select Template —', 'wprobo-documerge' ); ?></option>
								<?php if ( ! empty( $templates ) ) : ?>
									<?php foreach ( $templates as $template ) : ?>
										<option
											value="<?php echo esc_attr( $template->id ); ?>"
											<?php selected( $form_template, absint( $template->id ) ); ?>
										>
											<?php echo esc_html( $template->name ); ?>
										</option>
									<?php endforeach; ?>
								<?php endif; ?>
							</select>
						</div>

						<div class="wdm-field-group">
							<label for="wdm-form-output"><?php esc_html_e( 'Output Format', 'wprobo-documerge' ); ?></label>
							<select id="wdm-form-output" class="wdm-select">
								<option value="pdf" <?php selected( $form_output, 'pdf' ); ?>><?php esc_html_e( 'PDF', 'wprobo-documerge' ); ?></option>
								<option value="docx" <?php selected( $form_output, 'docx' ); ?>><?php esc_html_e( 'DOCX', 'wprobo-documerge' ); ?></option>
								<option value="both" <?php selected( $form_output, 'both' ); ?>><?php esc_html_e( 'Both', 'wprobo-documerge' ); ?></option>
							</select>
						</div>

						<div class="wdm-field-group">
							<label><?php esc_html_e( 'Delivery Methods', 'wprobo-documerge' ); ?></label>
							<?php
							$raw_dm     = isset( $form->delivery_methods ) ? $form->delivery_methods : '["download"]';
							$decoded_dm = json_decode( $raw_dm, true );
							if ( ! is_array( $decoded_dm ) ) {
								$decoded_dm = array( 'download' );
							}
							?>
							<div class="wdm-checkbox-group">
								<label class="wdm-checkbox-label">
									<input type="checkbox" class="wdm-delivery-method" value="download" <?php checked( in_array( 'download', $decoded_dm, true ) ); ?>>
									<?php esc_html_e( 'Download in browser', 'wprobo-documerge' ); ?>
								</label>
								<label class="wdm-checkbox-label wdm-pro-disabled-toggle">
									<input type="checkbox" disabled="disabled">
									<?php esc_html_e( 'Email to submitter', 'wprobo-documerge' ); ?>
									<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_badge(); ?>
								</label>
								<label class="wdm-checkbox-label wdm-pro-disabled-toggle">
									<input type="checkbox" disabled="disabled">
									<?php esc_html_e( 'Save to Media Library', 'wprobo-documerge' ); ?>
									<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_badge(); ?>
								</label>
							</div>
							<span class="wdm-description"><?php esc_html_e( 'How the generated document is delivered after submission. Email settings are configured in Settings → Email.', 'wprobo-documerge' ); ?></span>
						</div>

						<div class="wdm-field-group">
							<label for="wdm-success-message"><?php esc_html_e( 'Success Message', 'wprobo-documerge' ); ?></label>
							<textarea
								id="wdm-success-message"
								class="wdm-textarea"
								rows="3"
								placeholder="<?php esc_attr_e( 'Thank you! Your document is ready.', 'wprobo-documerge' ); ?>"
							><?php echo esc_textarea( $form_success ); ?></textarea>
						</div>

						<div class="wdm-field-group">
							<?php
							echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_disabled_toggle(
								__( 'Enable multi-step form', 'wprobo-documerge' )
							);
							?>
							<span class="wdm-description">
								<?php esc_html_e( 'Split your form into multiple steps. Drag fields between steps on the Fields tab.', 'wprobo-documerge' ); ?>
							</span>
						</div>

					</div>

					<!-- ── Button Sub-tab ──────────────────────────────────── -->
					<div class="wdm-settings-subtab-content" data-subtab="button">

						<div class="wdm-field-group">
							<label for="wdm-submit-label"><?php esc_html_e( 'Submit Button Label', 'wprobo-documerge' ); ?></label>
							<input
								type="text"
								id="wdm-submit-label"
								class="wdm-input"
								value="<?php echo esc_attr( $form_submit ? $form_submit : __( 'Submit', 'wprobo-documerge' ) ); ?>"
								placeholder="<?php esc_attr_e( 'Submit', 'wprobo-documerge' ); ?>"
							>
						</div>

						<?php
						$btn_settings   = ! empty( $form ) && ! empty( $form->settings ) ? json_decode( $form->settings, true ) : array();
						$btn_width      = isset( $btn_settings['btn_width'] ) ? $btn_settings['btn_width'] : 'auto';
						$btn_align      = isset( $btn_settings['btn_align'] ) ? $btn_settings['btn_align'] : 'right';
						$btn_style      = isset( $btn_settings['btn_style'] ) ? $btn_settings['btn_style'] : 'filled';
						$btn_size       = isset( $btn_settings['btn_size'] ) ? $btn_settings['btn_size'] : 'medium';
						$btn_radius     = isset( $btn_settings['btn_radius'] ) ? $btn_settings['btn_radius'] : '6';
						$btn_bg_color   = isset( $btn_settings['btn_bg_color'] ) ? $btn_settings['btn_bg_color'] : '#042157';
						$btn_text_color = isset( $btn_settings['btn_text_color'] ) ? $btn_settings['btn_text_color'] : '#ffffff';
						$btn_hover_bg   = isset( $btn_settings['btn_hover_bg'] ) ? $btn_settings['btn_hover_bg'] : '#0a3d8f';
						$btn_hover_text = isset( $btn_settings['btn_hover_text'] ) ? $btn_settings['btn_hover_text'] : '#ffffff';
						?>

						<div class="wdm-field-group">
							<label><?php esc_html_e( 'Button Width', 'wprobo-documerge' ); ?></label>
							<select id="wdm-btn-width" class="wdm-select">
								<option value="auto" <?php selected( $btn_width, 'auto' ); ?>><?php esc_html_e( 'Auto (fit content)', 'wprobo-documerge' ); ?></option>
								<option value="full" <?php selected( $btn_width, 'full' ); ?>><?php esc_html_e( 'Full width', 'wprobo-documerge' ); ?></option>
								<option value="half" <?php selected( $btn_width, 'half' ); ?>><?php esc_html_e( 'Half width', 'wprobo-documerge' ); ?></option>
							</select>
						</div>

						<div class="wdm-field-group">
							<label><?php esc_html_e( 'Button Alignment', 'wprobo-documerge' ); ?></label>
							<div class="wdm-btn-align-selector">
								<label class="wdm-align-option"><input type="radio" name="wdm_btn_align" value="left" <?php checked( $btn_align, 'left' ); ?>> <span class="dashicons dashicons-editor-alignleft"></span></label>
								<label class="wdm-align-option"><input type="radio" name="wdm_btn_align" value="center" <?php checked( $btn_align, 'center' ); ?>> <span class="dashicons dashicons-editor-aligncenter"></span></label>
								<label class="wdm-align-option"><input type="radio" name="wdm_btn_align" value="right" <?php checked( $btn_align, 'right' ); ?>> <span class="dashicons dashicons-editor-alignright"></span></label>
							</div>
						</div>

						<div class="wdm-field-group">
							<label><?php esc_html_e( 'Button Style', 'wprobo-documerge' ); ?></label>
							<select id="wdm-btn-style" class="wdm-select">
								<option value="filled" <?php selected( $btn_style, 'filled' ); ?>><?php esc_html_e( 'Filled (solid background)', 'wprobo-documerge' ); ?></option>
								<option value="outline" <?php selected( $btn_style, 'outline' ); ?>><?php esc_html_e( 'Outline (border only)', 'wprobo-documerge' ); ?></option>
								<option value="rounded" <?php selected( $btn_style, 'rounded' ); ?>><?php esc_html_e( 'Rounded (pill shape)', 'wprobo-documerge' ); ?></option>
							</select>
						</div>

						<div class="wdm-field-group">
							<label><?php esc_html_e( 'Button Size', 'wprobo-documerge' ); ?></label>
							<select id="wdm-btn-size" class="wdm-select">
								<option value="small" <?php selected( $btn_size, 'small' ); ?>><?php esc_html_e( 'Small', 'wprobo-documerge' ); ?></option>
								<option value="medium" <?php selected( $btn_size, 'medium' ); ?>><?php esc_html_e( 'Medium', 'wprobo-documerge' ); ?></option>
								<option value="large" <?php selected( $btn_size, 'large' ); ?>><?php esc_html_e( 'Large', 'wprobo-documerge' ); ?></option>
							</select>
						</div>

						<div class="wdm-field-group">
							<label><?php esc_html_e( 'Button Colors', 'wprobo-documerge' ); ?></label>
							<div class="wdm-color-row">
								<div class="wdm-color-field">
									<span><?php esc_html_e( 'Background', 'wprobo-documerge' ); ?></span>
									<input type="color" id="wdm-btn-bg-color" value="<?php echo esc_attr( $btn_bg_color ); ?>">
								</div>
								<div class="wdm-color-field">
									<span><?php esc_html_e( 'Text', 'wprobo-documerge' ); ?></span>
									<input type="color" id="wdm-btn-text-color" value="<?php echo esc_attr( $btn_text_color ); ?>">
								</div>
							</div>
						</div>

						<div class="wdm-field-group">
							<label><?php esc_html_e( 'Hover Colors', 'wprobo-documerge' ); ?></label>
							<div class="wdm-color-row">
								<div class="wdm-color-field">
									<span><?php esc_html_e( 'Background', 'wprobo-documerge' ); ?></span>
									<input type="color" id="wdm-btn-hover-bg" value="<?php echo esc_attr( $btn_hover_bg ); ?>">
								</div>
								<div class="wdm-color-field">
									<span><?php esc_html_e( 'Text', 'wprobo-documerge' ); ?></span>
									<input type="color" id="wdm-btn-hover-text" value="<?php echo esc_attr( $btn_hover_text ); ?>">
								</div>
							</div>
						</div>

						<div class="wdm-field-group">
							<label for="wdm-btn-radius"><?php esc_html_e( 'Border Radius (px)', 'wprobo-documerge' ); ?></label>
							<input type="number" id="wdm-btn-radius" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $btn_radius ); ?>" min="0" max="50" placeholder="6">
						</div>

					</div>

					<!-- ── Limits Sub-tab ──────────────────────────────────── -->
					<div class="wdm-settings-subtab-content" data-subtab="limits">

						<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_overlay( esc_html__( 'Entry Limits', 'wprobo-documerge' ), esc_html__( 'Limit submissions per form, email, IP address, or user.', 'wprobo-documerge' ) ); ?>

					</div>

					<!-- ── Payment Sub-tab ────────────────────────────────── -->
					<div class="wdm-settings-subtab-content" data-subtab="payment">

						<?php
						echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_overlay(
							__( 'Payment Settings', 'wprobo-documerge' ),
							__( 'Require Stripe payment before delivering documents. Configure amount, currency, and more.', 'wprobo-documerge' )
						);
						?>

					</div>

					<!-- ── Notifications Sub-tab ───────────────────────────── -->
					<div class="wdm-settings-subtab-content" data-subtab="notifications">

						<?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_overlay( esc_html__( 'Webhooks', 'wprobo-documerge' ), esc_html__( 'Send submission data to external services like Zapier, Make, and n8n.', 'wprobo-documerge' ) ); ?>

					</div>

				</div>
			</div>

		</div>

	</div>

</div>

<?php if ( $form && ! empty( $form_fields ) ) : ?>
<script type="text/javascript">
	var wprobo_documerge_form_fields = <?php echo wp_json_encode( json_decode( $form_fields, true ) ); ?>;
	<?php
	$form_settings_decoded = ! empty( $form ) && ! empty( $form->settings ) ? json_decode( $form->settings, true ) : array();
	if ( ! empty( $form_settings_decoded ) ) :
		?>
	var wprobo_documerge_form_settings = <?php echo wp_json_encode( $form_settings_decoded ); ?>;
	<?php endif; ?>
</script>
<?php endif; ?>
