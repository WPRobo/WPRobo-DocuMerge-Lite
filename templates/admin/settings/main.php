<?php
/**
 * Settings page template — 7 tabs.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/templates/admin/settings
 * @author     Ali Shan <hello@wprobo.com>
 * @since      1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<div class="wdm-admin-wrap">

    <?php
    $page_title     = __( 'Settings', 'wprobo-documerge' );
    $page_subtitle  = __( 'Configure WPRobo DocuMerge', 'wprobo-documerge' );
    $primary_action = array();
    include WPROBO_DOCUMERGE_PATH . 'templates/admin/partials/page-header.php';
    ?>

    <div class="wdm-settings-wrap">

        <div class="wdm-settings-tabs">
            <button type="button" class="wdm-settings-tab wdm-tab-active" data-tab="general"><?php esc_html_e( 'General', 'wprobo-documerge' ); ?></button>
            <button type="button" class="wdm-settings-tab" data-tab="stripe"><?php esc_html_e( 'Stripe', 'wprobo-documerge' ); ?></button>
            <button type="button" class="wdm-settings-tab" data-tab="email"><?php esc_html_e( 'Email', 'wprobo-documerge' ); ?></button>
            <button type="button" class="wdm-settings-tab" data-tab="captcha"><?php esc_html_e( 'reCAPTCHA', 'wprobo-documerge' ); ?></button>
            <button type="button" class="wdm-settings-tab" data-tab="styles"><?php esc_html_e( 'Styles', 'wprobo-documerge' ); ?></button>
            <button type="button" class="wdm-settings-tab" data-tab="customcss"><?php esc_html_e( 'Custom CSS', 'wprobo-documerge' ); ?></button>
            <button type="button" class="wdm-settings-tab" data-tab="advanced"><?php esc_html_e( 'Advanced', 'wprobo-documerge' ); ?></button>
            <button type="button" class="wdm-settings-tab" data-tab="importexport"><?php esc_html_e( 'Import / Export', 'wprobo-documerge' ); ?></button>
            <button type="button" class="wdm-settings-tab wdm-tab-danger" data-tab="dangerzone"><?php esc_html_e( 'Danger Zone', 'wprobo-documerge' ); ?></button>
        </div>

        <!-- ══════════════ GENERAL ══════════════ -->
        <div class="wdm-settings-panel wdm-panel-active" data-tab="general">

            <!-- ── Document Output Card ─────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-media-document"></span>
                    <div>
                        <h3><?php esc_html_e( 'Document Output', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Default format and delivery settings for generated documents.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-field-group">
                        <label><?php esc_html_e( 'Output Format', 'wprobo-documerge' ); ?></label>
                        <?php $fmt = get_option( 'wprobo_documerge_default_output_format', 'pdf' ); ?>
                        <div class="wdm-radio-group">
                            <label class="wdm-radio-label"><input type="radio" name="wprobo_documerge_default_output_format" value="pdf" <?php checked( $fmt, 'pdf' ); ?>> <?php esc_html_e( 'PDF only', 'wprobo-documerge' ); ?></label>
                            <label class="wdm-radio-label"><input type="radio" name="wprobo_documerge_default_output_format" value="docx" <?php checked( $fmt, 'docx' ); ?>> <?php esc_html_e( 'DOCX only', 'wprobo-documerge' ); ?></label>
                            <label class="wdm-radio-label"><input type="radio" name="wprobo_documerge_default_output_format" value="both" <?php checked( $fmt, 'both' ); ?>> <?php esc_html_e( 'Both DOCX and PDF', 'wprobo-documerge' ); ?></label>
                        </div>
                    </div>

                    <div class="wdm-field-group">
                        <label><?php esc_html_e( 'Delivery Method', 'wprobo-documerge' ); ?></label>
                        <span class="wdm-description"><?php esc_html_e( 'Can be overridden per form.', 'wprobo-documerge' ); ?></span>
                        <div class="wdm-checkbox-group">
                            <label class="wdm-checkbox-label"><input type="checkbox" name="wprobo_documerge_delivery_download" value="1" <?php checked( get_option( 'wprobo_documerge_delivery_download', '1' ), '1' ); ?>> <?php esc_html_e( 'Download in browser', 'wprobo-documerge' ); ?></label>
                            <?php $settings_gate = \WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Feature_Gate::get_instance(); ?>
                            <?php if ( $settings_gate->wprobo_documerge_can( 'email_delivery' ) ) : ?>
                            <label class="wdm-checkbox-label"><input type="checkbox" name="wprobo_documerge_delivery_email" value="1" <?php checked( get_option( 'wprobo_documerge_delivery_email', '1' ), '1' ); ?>> <?php esc_html_e( 'Email to submitter', 'wprobo-documerge' ); ?></label>
                            <?php else : ?>
                            <label class="wdm-checkbox-label wdm-pro-disabled-setting"><input type="checkbox" disabled> <?php esc_html_e( 'Email to submitter', 'wprobo-documerge' ); ?> <span class="wdm-pro-badge">PRO</span></label>
                            <?php endif; ?>
                            <?php if ( $settings_gate->wprobo_documerge_can( 'media_delivery' ) ) : ?>
                            <label class="wdm-checkbox-label"><input type="checkbox" name="wprobo_documerge_delivery_media" value="1" <?php checked( get_option( 'wprobo_documerge_delivery_media', '0' ), '1' ); ?>> <?php esc_html_e( 'Save to Media Library', 'wprobo-documerge' ); ?></label>
                            <?php else : ?>
                            <label class="wdm-checkbox-label wdm-pro-disabled-setting"><input type="checkbox" disabled> <?php esc_html_e( 'Save to Media Library', 'wprobo-documerge' ); ?> <span class="wdm-pro-badge">PRO</span></label>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="wdm-field-group">
                        <label for="wdm-expiry"><?php esc_html_e( 'Download Link Expiry', 'wprobo-documerge' ); ?></label>
                        <div class="wdm-input-row">
                            <input type="number" id="wdm-expiry" name="wprobo_documerge_download_expiry_hours" class="wdm-input wdm-input-small" value="<?php echo esc_attr( get_option( 'wprobo_documerge_download_expiry_hours', 72 ) ); ?>" min="0">
                            <span class="wdm-input-suffix"><?php esc_html_e( 'hours (0 = never)', 'wprobo-documerge' ); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Form Configuration Card ──────────────────────── -->
            <div class="wdm-settings-card" id="wdm-form-mode-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-feedback"></span>
                    <div>
                        <h3><?php esc_html_e( 'Form Configuration', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'How forms work and display date/time values.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-field-group">
                        <label><?php esc_html_e( 'Form Mode', 'wprobo-documerge' ); ?></label>
                        <?php $mode = get_option( 'wprobo_documerge_form_mode', 'standalone' ); ?>
                        <div class="wdm-radio-group">
                            <label class="wdm-radio-label"><input type="radio" name="wprobo_documerge_form_mode" value="standalone" <?php checked( $mode, 'standalone' ); ?>> <?php esc_html_e( 'Standalone (built-in form builder)', 'wprobo-documerge' ); ?></label>
                            <?php
                            $gate_mode = \WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Feature_Gate::get_instance();
                            if ( $gate_mode->wprobo_documerge_is_pro() ) :
                            ?>
                            <label class="wdm-radio-label"><input type="radio" name="wprobo_documerge_form_mode" value="integrated" <?php checked( $mode, 'integrated' ); ?>> <?php esc_html_e( 'Integrated (WPForms / CF7 / Gravity Forms etc.)', 'wprobo-documerge' ); ?></label>
                            <?php else : ?>
                            <label class="wdm-radio-label wdm-pro-disabled-toggle"><input type="radio" disabled="disabled"> <?php esc_html_e( 'Integrated (WPForms / CF7 / Gravity Forms etc.)', 'wprobo-documerge' ); ?> <?php echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_badge(); ?></label>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="wdm-field-group wdm-integration-field-group"<?php echo ( 'integrated' !== $mode ) ? ' style="display:none;"' : ''; ?>>
                        <label for="wdm-integration"><?php esc_html_e( 'Active Integration', 'wprobo-documerge' ); ?></label>
                        <?php
                        $integ = get_option( 'wprobo_documerge_active_integration', '' );
                        $available_plugins = array(
                            'wpforms' => array( 'label' => 'WPForms', 'active' => function_exists( 'wpforms' ) ),
                            'cf7'     => array( 'label' => 'Contact Form 7', 'active' => class_exists( 'WPCF7' ) ),
                            'gravity' => array( 'label' => 'Gravity Forms', 'active' => class_exists( 'GFForms' ) ),
                            'fluent'  => array( 'label' => 'Fluent Forms', 'active' => defined( 'FLUENTFORM' ) ),
                        );
                        $has_any_active = false;
                        foreach ( $available_plugins as $p ) {
                            if ( $p['active'] ) { $has_any_active = true; break; }
                        }
                        ?>
                        <select id="wdm-integration" name="wprobo_documerge_active_integration" class="wdm-select">
                            <option value=""><?php esc_html_e( '— Select —', 'wprobo-documerge' ); ?></option>
                            <?php foreach ( $available_plugins as $slug => $plugin ) : ?>
                                <?php if ( $plugin['active'] ) : ?>
                                    <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $integ, $slug ); ?>><?php echo esc_html( $plugin['label'] ); ?></option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                        <?php if ( ! $has_any_active ) : ?>
                            <span class="wdm-description" style="color:#d97706;"><?php esc_html_e( 'No supported form plugins detected. Install WPForms, Contact Form 7, Gravity Forms, or Fluent Forms.', 'wprobo-documerge' ); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="wdm-settings-row-2col">
                        <div class="wdm-field-group">
                            <label for="wdm-date-fmt"><?php esc_html_e( 'Date Format', 'wprobo-documerge' ); ?></label>
                            <?php $df = get_option( 'wprobo_documerge_date_format', 'd/m/Y' ); ?>
                            <select id="wdm-date-fmt" name="wprobo_documerge_date_format" class="wdm-select">
                                <option value="d/m/Y" <?php selected( $df, 'd/m/Y' ); ?>>DD/MM/YYYY</option>
                                <option value="m/d/Y" <?php selected( $df, 'm/d/Y' ); ?>>MM/DD/YYYY</option>
                                <option value="Y-m-d" <?php selected( $df, 'Y-m-d' ); ?>>YYYY-MM-DD</option>
                            </select>
                        </div>
                        <div class="wdm-field-group">
                            <label for="wdm-time-fmt"><?php esc_html_e( 'Time Format', 'wprobo-documerge' ); ?></label>
                            <?php $tf = get_option( 'wprobo_documerge_time_format', 'H:i' ); ?>
                            <select id="wdm-time-fmt" name="wprobo_documerge_time_format" class="wdm-select">
                                <option value="H:i" <?php selected( $tf, 'H:i' ); ?>><?php esc_html_e( '24-hour', 'wprobo-documerge' ); ?></option>
                                <option value="g:i A" <?php selected( $tf, 'g:i A' ); ?>><?php esc_html_e( '12-hour', 'wprobo-documerge' ); ?></option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Notifications Card ───────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-bell"></span>
                    <div>
                        <h3><?php esc_html_e( 'Admin Notifications', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Email alerts when submissions arrive or errors occur.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-field-group">
                        <div class="wdm-checkbox-group">
                            <label class="wdm-checkbox-label"><input type="checkbox" name="wprobo_documerge_notify_new_submission" value="1" <?php checked( get_option( 'wprobo_documerge_notify_new_submission', '1' ), '1' ); ?>> <?php esc_html_e( 'Send notification on new submission', 'wprobo-documerge' ); ?></label>
                            <label class="wdm-checkbox-label"><input type="checkbox" name="wprobo_documerge_notify_on_error" value="1" <?php checked( get_option( 'wprobo_documerge_notify_on_error', '1' ), '1' ); ?>> <?php esc_html_e( 'Send notification on generation error', 'wprobo-documerge' ); ?></label>
                        </div>
                    </div>

                    <div class="wdm-field-group">
                        <label for="wdm-notif-email"><?php esc_html_e( 'Notification Email', 'wprobo-documerge' ); ?></label>
                        <input type="email" id="wdm-notif-email" name="wprobo_documerge_notification_email" class="wdm-input" value="<?php echo esc_attr( get_option( 'wprobo_documerge_notification_email', '' ) ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>">
                        <span class="wdm-description"><?php esc_html_e( 'Defaults to WordPress admin email if left blank.', 'wprobo-documerge' ); ?></span>
                    </div>
                </div>
            </div>

            <div class="wdm-settings-actions">
                <button type="button" class="wdm-btn wdm-btn-primary wdm-settings-save" data-tab="general"><?php esc_html_e( 'Save General Settings', 'wprobo-documerge' ); ?></button>
            </div>
        </div>

        <!-- ══════════════ STRIPE ══════════════ -->
        <div class="wdm-settings-panel" data-tab="stripe">

            <?php
            echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_overlay(
                    __( 'Stripe Payments', 'wprobo-documerge' ),
                    __( 'Accept payments before delivering documents. Supports test & live mode.', 'wprobo-documerge' )
                );
            ?>
        </div>

        <!-- ══════════════ EMAIL ══════════════ -->
        <div class="wdm-settings-panel" data-tab="email">

            <?php
            echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_overlay(
                    __( 'Email Delivery', 'wprobo-documerge' ),
                    __( 'Customise sender details, email templates, and document delivery emails.', 'wprobo-documerge' )
                );
            ?>
        </div>

        <!-- ══════════════ RECAPTCHA ══════════════ -->
        <div class="wdm-settings-panel" data-tab="captcha">
            <?php
            echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_overlay(
                    __( 'CAPTCHA / Spam Protection', 'wprobo-documerge' ),
                    __( 'Protect your forms with Google reCAPTCHA v2, v3, or hCaptcha.', 'wprobo-documerge' )
                );
            ?>
        </div>

        <!-- ══════════════ ADVANCED ══════════════ -->
        <div class="wdm-settings-panel" data-tab="advanced">

            <!-- ── Storage & Cleanup Card ───────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-cloud-saved"></span>
                    <div>
                        <h3><?php esc_html_e( 'Storage & Cleanup', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Automatic cleanup of generated documents and log files.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-settings-row-2col">
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Auto-delete documents after', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_auto_delete_days" class="wdm-input wdm-input-small" value="<?php echo esc_attr( get_option( 'wprobo_documerge_auto_delete_days', 0 ) ); ?>" min="0">
                                <span class="wdm-input-suffix"><?php esc_html_e( 'days', 'wprobo-documerge' ); ?></span>
                            </div>
                            <span class="wdm-description"><?php esc_html_e( '0 = never delete automatically.', 'wprobo-documerge' ); ?></span>
                        </div>
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Keep error logs for', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_log_retention_days" class="wdm-input wdm-input-small" value="<?php echo esc_attr( get_option( 'wprobo_documerge_log_retention_days', 30 ) ); ?>" min="1">
                                <span class="wdm-input-suffix"><?php esc_html_e( 'days', 'wprobo-documerge' ); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Developer Options Card ───────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-editor-code"></span>
                    <div>
                        <h3><?php esc_html_e( 'Developer Options', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Debug logging and data retention on uninstall.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-field-group">
                        <label class="wdm-checkbox-label"><input type="checkbox" name="wprobo_documerge_debug_logging" value="1" <?php checked( get_option( 'wprobo_documerge_debug_logging', '0' ), '1' ); ?>> <?php esc_html_e( 'Enable debug logging', 'wprobo-documerge' ); ?></label>
                        <span class="wdm-description"><?php esc_html_e( 'Only works when WP_DEBUG is true. Logs are stored in wp-content/uploads/documerge-logs/.', 'wprobo-documerge' ); ?></span>
                    </div>

                    <div class="wdm-field-group">
                        <label class="wdm-checkbox-label"><input type="checkbox" name="wprobo_documerge_uninstall_data" value="1" <?php checked( get_option( 'wprobo_documerge_uninstall_data', '0' ), '1' ); ?>> <?php esc_html_e( 'Delete ALL plugin data when uninstalling', 'wprobo-documerge' ); ?></label>
                        <div class="wdm-notice wdm-notice-warning" style="margin-top:8px;">
                            <span class="wdm-notice-icon dashicons dashicons-warning"></span>
                            <span class="wdm-notice-text"><?php esc_html_e( 'This removes all database tables, generated documents, and settings permanently.', 'wprobo-documerge' ); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Setup Wizard Card ────────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-admin-tools"></span>
                    <div>
                        <h3><?php esc_html_e( 'Setup Wizard', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Re-run the initial setup wizard to reconfigure plugin defaults.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <button type="button" class="wdm-btn wdm-btn-secondary" id="wdm-rerun-wizard">
                        <span class="dashicons dashicons-update"></span>
                        <?php esc_html_e( 'Re-run Setup Wizard', 'wprobo-documerge' ); ?>
                    </button>
                </div>
            </div>

            <div class="wdm-settings-actions">
                <button type="button" class="wdm-btn wdm-btn-primary wdm-settings-save" data-tab="advanced"><?php esc_html_e( 'Save Advanced Settings', 'wprobo-documerge' ); ?></button>
            </div>
        </div>

        <!-- ══════════════ STYLES ══════════════ -->
        <div class="wdm-settings-panel" data-tab="styles">
            <?php
            echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_overlay(
                    __( 'Form Styles', 'wprobo-documerge' ),
                    __( 'Customise colours, fonts, spacing, and the overall look of your frontend forms.', 'wprobo-documerge' )
                );
            ?>
        </div>

        <!-- ══════════════ CUSTOM CSS ══════════════ -->
        <div class="wdm-settings-panel" data-tab="customcss">
            <?php
            echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_overlay(
                    __( 'Custom CSS', 'wprobo-documerge' ),
                    __( 'Add your own CSS to fully customise the appearance of frontend forms.', 'wprobo-documerge' )
                );
            ?>

        </div>

        <!-- ══════════════ IMPORT / EXPORT ══════════════ -->
        <div class="wdm-settings-panel" data-tab="importexport">

            <div class="wdm-import-export-row">

            <!-- ── Export Card ─────────────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-upload"></span>
                    <div>
                        <h3><?php esc_html_e( 'Export Data', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Download your DocuMerge data as a JSON file. Select which data to include.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <?php
                    global $wpdb;
                    $ie_tpl_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wprdm_templates" );
                    $ie_form_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wprdm_forms" );
                    $ie_sub_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wprdm_submissions" );

                    $ie_analytics_exists = ( $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $wpdb->prefix . 'wprdm_analytics' ) ) !== null );
                    $ie_analytics_count  = $ie_analytics_exists ? (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wprdm_analytics" ) : 0;
                    ?>
                    <div class="wdm-export-options">
                        <label class="wdm-checkbox-label">
                            <input type="checkbox" class="wdm-export-checkbox" value="templates" checked>
                            <?php
                            printf(
                                /* translators: %s: number of templates */
                                esc_html__( 'Templates (%s)', 'wprobo-documerge' ),
                                esc_html( number_format_i18n( $ie_tpl_count ) )
                            );
                            ?>
                        </label>
                        <label class="wdm-checkbox-label">
                            <input type="checkbox" class="wdm-export-checkbox" value="forms" checked>
                            <?php
                            printf(
                                /* translators: %s: number of forms */
                                esc_html__( 'Forms (%s)', 'wprobo-documerge' ),
                                esc_html( number_format_i18n( $ie_form_count ) )
                            );
                            ?>
                        </label>
                        <label class="wdm-checkbox-label">
                            <input type="checkbox" class="wdm-export-checkbox" value="submissions" checked>
                            <?php
                            printf(
                                /* translators: %s: number of submissions */
                                esc_html__( 'Submissions (%s)', 'wprobo-documerge' ),
                                esc_html( number_format_i18n( $ie_sub_count ) )
                            );
                            ?>
                        </label>
                        <label class="wdm-checkbox-label">
                            <input type="checkbox" class="wdm-export-checkbox" value="settings" checked>
                            <?php esc_html_e( 'Settings', 'wprobo-documerge' ); ?>
                        </label>
                        <?php if ( $ie_analytics_exists ) : ?>
                        <label class="wdm-checkbox-label">
                            <input type="checkbox" class="wdm-export-checkbox" value="analytics" checked>
                            <?php
                            printf(
                                /* translators: %s: number of analytics records */
                                esc_html__( 'Analytics (%s)', 'wprobo-documerge' ),
                                esc_html( number_format_i18n( $ie_analytics_count ) )
                            );
                            ?>
                        </label>
                        <?php endif; ?>
                    </div>
                    <div class="wdm-export-actions">
                        <button type="button" class="wdm-btn wdm-btn-primary" id="wdm-export-selected">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e( 'Export Selected', 'wprobo-documerge' ); ?>
                        </button>
                        <button type="button" class="wdm-btn wdm-btn-secondary" id="wdm-export-all">
                            <span class="dashicons dashicons-download"></span>
                            <?php esc_html_e( 'Export All', 'wprobo-documerge' ); ?>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── Import Card ─────────────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-download"></span>
                    <div>
                        <h3><?php esc_html_e( 'Import Data', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Restore data from a previously exported DocuMerge JSON file.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">

                    <!-- Drop zone -->
                    <div class="wdm-import-dropzone" id="wdm-import-dropzone">
                        <div class="wdm-import-dropzone-inner">
                            <span class="dashicons dashicons-upload"></span>
                            <p><?php esc_html_e( 'Drag & drop your .json file here', 'wprobo-documerge' ); ?></p>
                            <span class="wdm-import-dropzone-or"><?php esc_html_e( 'or', 'wprobo-documerge' ); ?></span>
                            <button type="button" class="wdm-btn wdm-btn-secondary wdm-btn-sm" id="wdm-import-browse">
                                <?php esc_html_e( 'Browse Files', 'wprobo-documerge' ); ?>
                            </button>
                            <input type="file" id="wdm-import-file" accept=".json" style="display:none;">
                        </div>
                    </div>

                    <!-- Preview (hidden until file loaded) -->
                    <div class="wdm-import-preview" id="wdm-import-preview" style="display:none;">
                        <div class="wdm-import-file-info">
                            <span class="dashicons dashicons-media-code"></span>
                            <span class="wdm-import-filename" id="wdm-import-filename"></span>
                            <button type="button" class="wdm-btn wdm-btn-ghost wdm-btn-sm" id="wdm-import-clear" aria-label="<?php esc_attr_e( 'Remove file', 'wprobo-documerge' ); ?>">
                                &times;
                            </button>
                        </div>
                        <div class="wdm-import-summary" id="wdm-import-summary"></div>

                        <div class="wdm-import-select-items" id="wdm-import-select-items"></div>

                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Import Mode', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-radio-group">
                                <label class="wdm-radio-label">
                                    <input type="radio" name="wdm_import_mode" value="merge" checked>
                                    <?php esc_html_e( 'Merge with existing data (default)', 'wprobo-documerge' ); ?>
                                </label>
                                <label class="wdm-radio-label">
                                    <input type="radio" name="wdm_import_mode" value="replace">
                                    <?php esc_html_e( 'Replace all (destructive — removes existing data first)', 'wprobo-documerge' ); ?>
                                </label>
                            </div>
                        </div>

                        <div class="wdm-import-actions">
                            <button type="button" class="wdm-btn wdm-btn-primary" id="wdm-import-run" disabled>
                                <span class="dashicons dashicons-upload"></span>
                                <?php esc_html_e( 'Import Selected', 'wprobo-documerge' ); ?>
                            </button>
                        </div>
                    </div>

                    <!-- Result (hidden until import completes) -->
                    <div class="wdm-import-result" id="wdm-import-result" style="display:none;"></div>

                </div>
            </div>

            </div><!-- /.wdm-import-export-row -->

        </div>

        <!-- ══════════════ DANGER ZONE ══════════════ -->
        <div class="wdm-settings-panel wdm-danger-zone-panel" data-tab="dangerzone">

            <div class="wdm-notice wdm-notice-error" style="margin-bottom:24px;">
                <span class="wdm-notice-icon dashicons dashicons-warning"></span>
                <span class="wdm-notice-text">
                    <strong><?php esc_html_e( 'Danger Zone', 'wprobo-documerge' ); ?></strong> —
                    <?php esc_html_e( 'These actions are destructive and cannot be undone. Proceed with extreme caution.', 'wprobo-documerge' ); ?>
                </span>
            </div>

            <?php
            global $wpdb;
            $sub_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wprdm_submissions" );
            $form_count = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wprdm_forms" );
            $tpl_count  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}wprdm_templates" );
            ?>

            <!-- ── Group 1: Data Cleanup ──────────────────────── -->
            <div class="wdm-settings-card" style="border-left:3px solid #d97706;">
                <div class="wdm-settings-card-header" style="background:rgba(217,119,6,0.04);">
                    <span class="dashicons dashicons-trash" style="color:#d97706;"></span>
                    <div>
                        <h3><?php esc_html_e( 'Data Cleanup', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Selectively remove specific types of data. Other data remains intact.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-danger-card">
                        <div class="wdm-danger-card-info">
                            <h4><?php esc_html_e( 'Delete All Submissions', 'wprobo-documerge' ); ?></h4>
                            <p><?php esc_html_e( 'Removes all submission records and generated documents (PDF/DOCX).', 'wprobo-documerge' ); ?></p>
                            <span class="wdm-danger-count"><?php echo esc_html( number_format_i18n( $sub_count ) ); ?> <?php esc_html_e( 'submissions', 'wprobo-documerge' ); ?></span>
                        </div>
                        <button type="button" class="wdm-btn wdm-btn-danger wdm-danger-action" data-action="delete_submissions"><?php esc_html_e( 'Delete', 'wprobo-documerge' ); ?></button>
                    </div>
                    <div class="wdm-danger-card">
                        <div class="wdm-danger-card-info">
                            <h4><?php esc_html_e( 'Delete All Forms', 'wprobo-documerge' ); ?></h4>
                            <p><?php esc_html_e( 'Removes all form configurations and field layouts. Templates are not affected.', 'wprobo-documerge' ); ?></p>
                            <span class="wdm-danger-count"><?php echo esc_html( number_format_i18n( $form_count ) ); ?> <?php esc_html_e( 'forms', 'wprobo-documerge' ); ?></span>
                        </div>
                        <button type="button" class="wdm-btn wdm-btn-danger wdm-danger-action" data-action="delete_forms"><?php esc_html_e( 'Delete', 'wprobo-documerge' ); ?></button>
                    </div>
                    <div class="wdm-danger-card">
                        <div class="wdm-danger-card-info">
                            <h4><?php esc_html_e( 'Delete All Templates', 'wprobo-documerge' ); ?></h4>
                            <p><?php esc_html_e( 'Removes all templates and their uploaded DOCX files.', 'wprobo-documerge' ); ?></p>
                            <span class="wdm-danger-count"><?php echo esc_html( number_format_i18n( $tpl_count ) ); ?> <?php esc_html_e( 'templates', 'wprobo-documerge' ); ?></span>
                        </div>
                        <button type="button" class="wdm-btn wdm-btn-danger wdm-danger-action" data-action="delete_templates"><?php esc_html_e( 'Delete', 'wprobo-documerge' ); ?></button>
                    </div>
                    <div class="wdm-danger-card" style="border-bottom:none;">
                        <div class="wdm-danger-card-info">
                            <h4><?php esc_html_e( 'Delete Generated Documents Only', 'wprobo-documerge' ); ?></h4>
                            <p><?php esc_html_e( 'Deletes PDF/DOCX files only. Submission records are kept but downloads stop working.', 'wprobo-documerge' ); ?></p>
                        </div>
                        <button type="button" class="wdm-btn wdm-btn-danger wdm-danger-action" data-action="delete_documents"><?php esc_html_e( 'Delete', 'wprobo-documerge' ); ?></button>
                    </div>
                </div>
            </div>

            <!-- ── Group 2: Reset & Restore ───────────────────── -->
            <div class="wdm-settings-card" style="border-left:3px solid #dc2626;">
                <div class="wdm-settings-card-header" style="background:rgba(220,38,38,0.04);">
                    <span class="dashicons dashicons-image-rotate" style="color:#dc2626;"></span>
                    <div>
                        <h3><?php esc_html_e( 'Reset & Restore', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Reset settings, analytics, or perform a complete factory reset.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-danger-card">
                        <div class="wdm-danger-card-info">
                            <h4><?php esc_html_e( 'Reset All Settings', 'wprobo-documerge' ); ?></h4>
                            <p><?php esc_html_e( 'Resets General, Stripe, Email, reCAPTCHA, Styles, and Custom CSS to defaults. Data is not affected.', 'wprobo-documerge' ); ?></p>
                        </div>
                        <button type="button" class="wdm-btn wdm-btn-danger wdm-danger-action" data-action="reset_settings"><?php esc_html_e( 'Reset', 'wprobo-documerge' ); ?></button>
                    </div>
                    <div class="wdm-danger-card">
                        <div class="wdm-danger-card-info">
                            <h4><?php esc_html_e( 'Reset Analytics', 'wprobo-documerge' ); ?></h4>
                            <p><?php esc_html_e( 'Clears all form analytics (views, starts, completions). Dashboard and form stats reset to zero.', 'wprobo-documerge' ); ?></p>
                        </div>
                        <button type="button" class="wdm-btn wdm-btn-danger wdm-danger-action" data-action="reset_analytics"><?php esc_html_e( 'Reset', 'wprobo-documerge' ); ?></button>
                    </div>
                    <div class="wdm-danger-card wdm-danger-card-critical" style="border-bottom:none;">
                        <div class="wdm-danger-card-info">
                            <h4><?php esc_html_e( 'Full Factory Reset', 'wprobo-documerge' ); ?></h4>
                            <p><?php esc_html_e( 'Wipes EVERYTHING — submissions, forms, templates, documents, settings, analytics, logs. Returns the plugin to a freshly-installed state. You must type "RESET" to confirm.', 'wprobo-documerge' ); ?></p>
                        </div>
                        <button type="button" class="wdm-btn wdm-btn-danger wdm-danger-action" data-action="factory_reset">
                            <span class="dashicons dashicons-warning"></span>
                            <?php esc_html_e( 'Factory Reset', 'wprobo-documerge' ); ?>
                        </button>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
