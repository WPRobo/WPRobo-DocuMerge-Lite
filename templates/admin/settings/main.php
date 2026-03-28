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
                            <label class="wdm-checkbox-label"><input type="checkbox" name="wprobo_documerge_delivery_email" value="1" <?php checked( get_option( 'wprobo_documerge_delivery_email', '1' ), '1' ); ?>> <?php esc_html_e( 'Email to submitter', 'wprobo-documerge' ); ?></label>
                            <label class="wdm-checkbox-label"><input type="checkbox" name="wprobo_documerge_delivery_media" value="1" <?php checked( get_option( 'wprobo_documerge_delivery_media', '0' ), '1' ); ?>> <?php esc_html_e( 'Save to Media Library', 'wprobo-documerge' ); ?></label>
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
            $gate = \WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Feature_Gate::get_instance();
            if ( ! $gate->wprobo_documerge_is_pro() ) {
                echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_overlay(
                    __( 'Stripe Payments', 'wprobo-documerge' ),
                    __( 'Accept payments before delivering documents. Supports test & live mode.', 'wprobo-documerge' )
                );
            } else {
            ?>

            <div class="wdm-notice wdm-notice-info" style="margin-bottom:20px;">
                <span class="wdm-notice-icon dashicons dashicons-info"></span>
                <span class="wdm-notice-text">
                    <?php
                    /* translators: 1: opening link tag, 2: closing link tag, 3: opening link tag, 4: closing link tag */
                    printf(
                        esc_html__( 'Get your API keys from %1$sStripe Dashboard → API Keys%2$s. Set up webhooks at %3$sStripe → Webhooks%4$s.', 'wprobo-documerge' ),
                        '<a href="https://dashboard.stripe.com/apikeys" target="_blank" rel="noopener noreferrer">',
                        '</a>',
                        '<a href="https://dashboard.stripe.com/webhooks" target="_blank" rel="noopener noreferrer">',
                        '</a>'
                    );
                    ?>
                </span>
            </div>

            <?php
            // Decrypt stored keys for display.
            $stripe_keys = array(
                'test_pub'    => \WPRobo\DocuMerge\Helpers\WPRobo_DocuMerge_Encryptor::wprobo_documerge_decrypt( get_option( 'wprobo_documerge_stripe_test_publishable_key', '' ) ),
                'test_secret' => \WPRobo\DocuMerge\Helpers\WPRobo_DocuMerge_Encryptor::wprobo_documerge_decrypt( get_option( 'wprobo_documerge_stripe_test_secret_key', '' ) ),
                'live_pub'    => \WPRobo\DocuMerge\Helpers\WPRobo_DocuMerge_Encryptor::wprobo_documerge_decrypt( get_option( 'wprobo_documerge_stripe_live_publishable_key', '' ) ),
                'live_secret' => \WPRobo\DocuMerge\Helpers\WPRobo_DocuMerge_Encryptor::wprobo_documerge_decrypt( get_option( 'wprobo_documerge_stripe_live_secret_key', '' ) ),
                'webhook'     => \WPRobo\DocuMerge\Helpers\WPRobo_DocuMerge_Encryptor::wprobo_documerge_decrypt( get_option( 'wprobo_documerge_stripe_webhook_secret', '' ) ),
            );
            ?>

            <!-- ── Mode & API Keys Card ─────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-lock"></span>
                    <div>
                        <h3><?php esc_html_e( 'API Keys & Mode', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Toggle between test and live mode. Only the active mode keys are used.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-field-group">
                        <label><?php esc_html_e( 'Mode', 'wprobo-documerge' ); ?></label>
                        <?php $smode = get_option( 'wprobo_documerge_stripe_mode', 'test' ); ?>
                        <div class="wdm-radio-group">
                            <label class="wdm-radio-label"><input type="radio" name="wprobo_documerge_stripe_mode" value="test" <?php checked( $smode, 'test' ); ?>> <?php esc_html_e( 'Test Mode', 'wprobo-documerge' ); ?></label>
                            <label class="wdm-radio-label"><input type="radio" name="wprobo_documerge_stripe_mode" value="live" <?php checked( $smode, 'live' ); ?>> <?php esc_html_e( 'Live Mode', 'wprobo-documerge' ); ?></label>
                        </div>
                        <div class="wdm-notice wdm-notice-warning wdm-stripe-test-notice" style="margin-top:12px;<?php echo ( 'test' !== $smode ) ? 'display:none;' : ''; ?>">
                            <span class="wdm-notice-icon dashicons dashicons-info"></span>
                            <span class="wdm-notice-text"><?php esc_html_e( 'Test Mode — no real charges will be made. Use Stripe test card numbers to simulate payments.', 'wprobo-documerge' ); ?></span>
                        </div>
                        <div class="wdm-notice wdm-notice-success wdm-stripe-live-warning" style="margin-top:12px;<?php echo ( 'live' !== $smode ) ? 'display:none;' : ''; ?>">
                            <span class="wdm-notice-icon dashicons dashicons-yes-alt"></span>
                            <span class="wdm-notice-text"><?php esc_html_e( 'Live Mode — real payments will be processed and charged to customers.', 'wprobo-documerge' ); ?></span>
                        </div>
                    </div>

                    <div class="wdm-stripe-mode-fields" data-stripe-mode="test"<?php if ( 'test' !== $smode ) : ?> style="display:none;"<?php endif; ?>>
                        <div class="wdm-settings-row-2col">
                            <div class="wdm-field-group">
                                <label><?php esc_html_e( 'Test Publishable Key', 'wprobo-documerge' ); ?></label>
                                <div class="wdm-input-row">
                                    <input type="password" name="wprobo_documerge_stripe_test_publishable_key" class="wdm-input" value="<?php echo esc_attr( $stripe_keys['test_pub'] ); ?>" placeholder="pk_test_...">
                                    <button type="button" class="wdm-btn wdm-toggle-password"><span class="dashicons dashicons-visibility"></span></button>
                                </div>
                            </div>
                            <div class="wdm-field-group">
                                <label><?php esc_html_e( 'Test Secret Key', 'wprobo-documerge' ); ?></label>
                                <div class="wdm-input-row">
                                    <input type="password" name="wprobo_documerge_stripe_test_secret_key" class="wdm-input" value="<?php echo esc_attr( $stripe_keys['test_secret'] ); ?>" placeholder="sk_test_...">
                                    <button type="button" class="wdm-btn wdm-toggle-password"><span class="dashicons dashicons-visibility"></span></button>
                                </div>
                            </div>
                        </div>
                        <?php if ( ! empty( $stripe_keys['test_pub'] ) || ! empty( $stripe_keys['test_secret'] ) ) : ?>
                            <span class="wdm-description"><?php esc_html_e( 'Keys are saved. Leave blank to keep existing.', 'wprobo-documerge' ); ?></span>
                        <?php else : ?>
                            <span class="wdm-description"><?php esc_html_e( 'Enter your test API keys from the Stripe Dashboard.', 'wprobo-documerge' ); ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="wdm-stripe-mode-fields" data-stripe-mode="live"<?php if ( 'live' !== $smode ) : ?> style="display:none;"<?php endif; ?>>
                        <div class="wdm-settings-row-2col">
                            <div class="wdm-field-group">
                                <label><?php esc_html_e( 'Live Publishable Key', 'wprobo-documerge' ); ?></label>
                                <div class="wdm-input-row">
                                    <input type="password" name="wprobo_documerge_stripe_live_publishable_key" class="wdm-input" value="<?php echo esc_attr( $stripe_keys['live_pub'] ); ?>" placeholder="pk_live_...">
                                    <button type="button" class="wdm-btn wdm-toggle-password"><span class="dashicons dashicons-visibility"></span></button>
                                </div>
                            </div>
                            <div class="wdm-field-group">
                                <label><?php esc_html_e( 'Live Secret Key', 'wprobo-documerge' ); ?></label>
                                <div class="wdm-input-row">
                                    <input type="password" name="wprobo_documerge_stripe_live_secret_key" class="wdm-input" value="<?php echo esc_attr( $stripe_keys['live_secret'] ); ?>" placeholder="sk_live_...">
                                    <button type="button" class="wdm-btn wdm-toggle-password"><span class="dashicons dashicons-visibility"></span></button>
                                </div>
                            </div>
                        </div>
                        <?php if ( ! empty( $stripe_keys['live_pub'] ) || ! empty( $stripe_keys['live_secret'] ) ) : ?>
                            <span class="wdm-description"><?php esc_html_e( 'Keys are saved. Leave blank to keep existing.', 'wprobo-documerge' ); ?></span>
                        <?php else : ?>
                            <span class="wdm-description"><?php esc_html_e( 'Enter your live API keys from the Stripe Dashboard.', 'wprobo-documerge' ); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- ── Webhook Card ─────────────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-rest-api"></span>
                    <div>
                        <h3><?php esc_html_e( 'Webhook', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Stripe sends payment confirmations to this URL.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-field-group">
                        <label><?php esc_html_e( 'Webhook Secret', 'wprobo-documerge' ); ?></label>
                        <div class="wdm-input-row">
                            <input type="password" name="wprobo_documerge_stripe_webhook_secret" class="wdm-input" value="<?php echo esc_attr( $stripe_keys['webhook'] ); ?>" placeholder="whsec_...">
                            <button type="button" class="wdm-btn wdm-toggle-password"><span class="dashicons dashicons-visibility"></span></button>
                        </div>
                        <span class="wdm-description"><?php esc_html_e( 'Leave blank to keep existing.', 'wprobo-documerge' ); ?></span>
                    </div>
                    <div class="wdm-field-group">
                        <label><?php esc_html_e( 'Your Webhook URL', 'wprobo-documerge' ); ?></label>
                        <div class="wdm-input-row">
                            <input type="text" class="wdm-input wdm-webhook-url" value="<?php echo esc_attr( site_url( '?wprdm_webhook=stripe' ) ); ?>" readonly>
                            <button type="button" class="wdm-btn" id="wdm-copy-webhook"><span class="dashicons dashicons-clipboard"></span></button>
                        </div>
                        <span class="wdm-description"><?php esc_html_e( 'Copy and paste this into Stripe Dashboard → Webhooks.', 'wprobo-documerge' ); ?></span>
                    </div>
                </div>
            </div>

            <!-- ── Currency Card ─────────────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-money-alt"></span>
                    <div>
                        <h3><?php esc_html_e( 'Currency', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Default currency for payment amounts.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-field-group">
                        <?php $cur = get_option( 'wprobo_documerge_stripe_currency', 'GBP' ); ?>
                        <?php
                        /** This filter is documented above. */
                        $currencies = apply_filters( 'wprobo_documerge_stripe_currencies', array(
                            'GBP' => 'GBP — British Pound',
                            'USD' => 'USD — US Dollar',
                            'EUR' => 'EUR — Euro',
                            'CAD' => 'CAD — Canadian Dollar',
                            'AUD' => 'AUD — Australian Dollar',
                        ) );
                        ?>
                        <select id="wdm-currency" name="wprobo_documerge_stripe_currency" class="wdm-select">
                            <?php foreach ( $currencies as $code => $label ) : ?>
                                <option value="<?php echo esc_attr( $code ); ?>" <?php selected( $cur, $code ); ?>><?php echo esc_html( $label ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- ── Card Display Card ───────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-admin-appearance"></span>
                    <div>
                        <h3><?php esc_html_e( 'Card Field Display', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Customise how the Stripe card input appears on the frontend.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-field-group">
                        <label><?php esc_html_e( 'Card Layout', 'wprobo-documerge' ); ?></label>
                        <?php $card_layout = get_option( 'wprobo_documerge_stripe_card_layout', 'single' ); ?>
                        <div class="wdm-radio-group">
                            <label class="wdm-radio-label">
                                <input type="radio" name="wprobo_documerge_stripe_card_layout" value="single" <?php checked( $card_layout, 'single' ); ?>>
                                <?php esc_html_e( 'Single line — card number, expiry, CVC all in one row', 'wprobo-documerge' ); ?>
                            </label>
                            <label class="wdm-radio-label">
                                <input type="radio" name="wprobo_documerge_stripe_card_layout" value="multi" <?php checked( $card_layout, 'multi' ); ?>>
                                <?php esc_html_e( 'Multi-line — separate fields for number, expiry, CVC', 'wprobo-documerge' ); ?>
                            </label>
                        </div>
                    </div>

                    <div class="wdm-field-group">
                        <label><?php esc_html_e( 'Show Postal/ZIP Code', 'wprobo-documerge' ); ?></label>
                        <?php $hide_postal = get_option( 'wprobo_documerge_stripe_hide_postal', '0' ); ?>
                        <div class="wdm-radio-group">
                            <label class="wdm-radio-label">
                                <input type="radio" name="wprobo_documerge_stripe_hide_postal" value="0" <?php checked( $hide_postal, '0' ); ?>>
                                <?php esc_html_e( 'Show — display postal/ZIP code field (recommended for fraud prevention)', 'wprobo-documerge' ); ?>
                            </label>
                            <label class="wdm-radio-label">
                                <input type="radio" name="wprobo_documerge_stripe_hide_postal" value="1" <?php checked( $hide_postal, '1' ); ?>>
                                <?php esc_html_e( 'Hide — do not ask for postal/ZIP code', 'wprobo-documerge' ); ?>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wdm-settings-actions">
                <button type="button" class="wdm-btn wdm-btn-primary wdm-settings-save" data-tab="stripe"><?php esc_html_e( 'Save Stripe Settings', 'wprobo-documerge' ); ?></button>
            </div>

            <?php } // End Pro gate for Stripe. ?>
        </div>

        <!-- ══════════════ EMAIL ══════════════ -->
        <div class="wdm-settings-panel" data-tab="email">

            <?php
            $gate = \WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Feature_Gate::get_instance();
            if ( ! $gate->wprobo_documerge_is_pro() ) {
                echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_overlay(
                    __( 'Email Delivery', 'wprobo-documerge' ),
                    __( 'Customise sender details, email templates, and document delivery emails.', 'wprobo-documerge' )
                );
            } else {
            ?>

            <!-- ── Sender Configuration Card ──────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-admin-users"></span>
                    <div>
                        <h3><?php esc_html_e( 'Sender Configuration', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'From name, email address, and reply-to settings.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-field-group">
                        <label for="wdm-from-name"><?php esc_html_e( 'From Name', 'wprobo-documerge' ); ?></label>
                        <input type="text" id="wdm-from-name" name="wprobo_documerge_email_from_name" class="wdm-input" value="<?php echo esc_attr( get_option( 'wprobo_documerge_email_from_name', get_bloginfo( 'name' ) ) ); ?>">
                    </div>

                    <div class="wdm-field-group">
                        <label for="wdm-from-email"><?php esc_html_e( 'From Email', 'wprobo-documerge' ); ?></label>
                        <input type="email" id="wdm-from-email" name="wprobo_documerge_email_from" class="wdm-input" value="<?php echo esc_attr( get_option( 'wprobo_documerge_email_from', '' ) ); ?>">
                    </div>

                    <div class="wdm-field-group">
                        <label for="wdm-reply-to"><?php esc_html_e( 'Reply-To Email', 'wprobo-documerge' ); ?></label>
                        <input type="email" id="wdm-reply-to" name="wprobo_documerge_email_reply_to" class="wdm-input" value="<?php echo esc_attr( get_option( 'wprobo_documerge_email_reply_to', '' ) ); ?>">
                    </div>

                    <div class="wdm-field-group">
                        <label class="wdm-checkbox-label"><input type="checkbox" name="wprobo_documerge_email_attach_doc" value="1" <?php checked( get_option( 'wprobo_documerge_email_attach_doc', '1' ), '1' ); ?>> <?php esc_html_e( 'Attach document to email', 'wprobo-documerge' ); ?></label>
                        <div class="wdm-input-row">
                            <span class="wdm-input-prefix"><?php esc_html_e( 'Max attachment size:', 'wprobo-documerge' ); ?></span>
                            <input type="number" name="wprobo_documerge_email_max_attach_size" class="wdm-input wdm-input-small" value="<?php echo esc_attr( get_option( 'wprobo_documerge_email_max_attach_size', 5 ) ); ?>" min="1" max="25">
                            <span class="wdm-input-suffix">MB</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Delivery Email Template Card ───────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-email-alt"></span>
                    <div>
                        <h3><?php esc_html_e( 'Delivery Email Template', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Customize the email sent to submitters with their document.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-field-group">
                        <label for="wdm-email-subject-template"><?php esc_html_e( 'Email Subject', 'wprobo-documerge' ); ?></label>
                        <input type="text" id="wdm-email-subject-template" name="wprobo_documerge_email_subject_template" class="wdm-input"
                               value="<?php echo esc_attr( get_option( 'wprobo_documerge_email_subject_template', 'Your document is ready — {site_name}' ) ); ?>"
                               placeholder="<?php esc_attr_e( 'Your document is ready — {site_name}', 'wprobo-documerge' ); ?>">
                        <span class="wdm-description"><?php esc_html_e( 'Available tags: {site_name}, {form_name}, {submitter_name}, {submitter_email}', 'wprobo-documerge' ); ?></span>
                    </div>
                    <div class="wdm-field-group">
                        <label for="wprobo_documerge_email_body_template"><?php esc_html_e( 'Email Body', 'wprobo-documerge' ); ?></label>
                        <?php
                        $email_body = get_option( 'wprobo_documerge_email_body_template', '' );
                        if ( empty( $email_body ) ) {
                            $email_body = '<p>' . __( 'Hi {submitter_name},', 'wprobo-documerge' ) . '</p>' .
                                '<p>' . __( 'Thank you for submitting the <strong>{form_name}</strong>. Your document has been generated and is attached to this email.', 'wprobo-documerge' ) . '</p>' .
                                '<p>' . __( 'If you have any questions, please reply to this email.', 'wprobo-documerge' ) . '</p>' .
                                '<p>' . __( 'Best regards,', 'wprobo-documerge' ) . '<br>{site_name}</p>';
                        }
                        wp_editor( $email_body, 'wprobo_documerge_email_body_template', array(
                            'textarea_name' => 'wprobo_documerge_email_body_template',
                            'textarea_rows' => 12,
                            'media_buttons' => false,
                            'teeny'         => true,
                            'quicktags'     => true,
                        ) );
                        ?>
                        <span class="wdm-description"><?php esc_html_e( 'Available tags: {submitter_name}, {submitter_email}, {form_name}, {site_name}, {download_link}, {document_name}', 'wprobo-documerge' ); ?></span>
                    </div>
                </div>
            </div>

            <div class="wdm-settings-actions">
                <button type="button" class="wdm-btn wdm-btn-primary wdm-settings-save" data-tab="email"><?php esc_html_e( 'Save Email Settings', 'wprobo-documerge' ); ?></button>
            </div>

            <?php } // End Pro gate for Email. ?>
        </div>

        <!-- ══════════════ RECAPTCHA ══════════════ -->
        <div class="wdm-settings-panel" data-tab="captcha">
            <?php
            $gate = \WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Feature_Gate::get_instance();
            if ( ! $gate->wprobo_documerge_is_pro() ) {
                echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_overlay(
                    __( 'CAPTCHA / Spam Protection', 'wprobo-documerge' ),
                    __( 'Protect your forms with Google reCAPTCHA v2, v3, or hCaptcha.', 'wprobo-documerge' )
                );
            } else {
            ?>
            <h3 class="wdm-section-heading"><?php esc_html_e( 'CAPTCHA Configuration', 'wprobo-documerge' ); ?></h3>

            <div class="wdm-notice wdm-notice-info" style="margin-bottom:20px;">
                <span class="wdm-notice-icon dashicons dashicons-info"></span>
                <span class="wdm-notice-text">
                    <?php
                    printf(
                        /* translators: 1-6: opening/closing link tags */
                        esc_html__( 'Get your keys: %1$sGoogle reCAPTCHA v2%2$s (checkbox) or %3$sreCAPTCHA v3%4$s (invisible) from the Google reCAPTCHA admin. For hCaptcha, go to %5$shCaptcha Dashboard%6$s.', 'wprobo-documerge' ),
                        '<a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener noreferrer">',
                        '</a>',
                        '<a href="https://www.google.com/recaptcha/admin/create" target="_blank" rel="noopener noreferrer">',
                        '</a>',
                        '<a href="https://dashboard.hcaptcha.com/signup" target="_blank" rel="noopener noreferrer">',
                        '</a>'
                    );
                    ?>
                </span>
            </div>

            <div class="wdm-field-group">
                <label><?php esc_html_e( 'Default CAPTCHA Type', 'wprobo-documerge' ); ?></label>
                <?php $ct = get_option( 'wprobo_documerge_captcha_type', 'recaptcha_v2' ); ?>
                <div class="wdm-radio-group">
                    <label class="wdm-radio-label"><input type="radio" name="wprobo_documerge_captcha_type" value="recaptcha_v2" <?php checked( $ct, 'recaptcha_v2' ); ?>> <?php esc_html_e( 'reCAPTCHA v2 (Checkbox)', 'wprobo-documerge' ); ?></label>
                    <label class="wdm-radio-label"><input type="radio" name="wprobo_documerge_captcha_type" value="recaptcha_v3" <?php checked( $ct, 'recaptcha_v3' ); ?>> <?php esc_html_e( 'reCAPTCHA v3 (Invisible)', 'wprobo-documerge' ); ?></label>
                    <label class="wdm-radio-label"><input type="radio" name="wprobo_documerge_captcha_type" value="hcaptcha" <?php checked( $ct, 'hcaptcha' ); ?>> <?php esc_html_e( 'hCaptcha', 'wprobo-documerge' ); ?></label>
                </div>
            </div>

            <div class="wdm-captcha-fields" data-captcha-type="recaptcha_v2"<?php if ( 'recaptcha_v2' !== $ct ) : ?> style="display:none;"<?php endif; ?>>
                <h3 class="wdm-section-heading"><?php esc_html_e( 'reCAPTCHA v2', 'wprobo-documerge' ); ?></h3>
                <div class="wdm-field-group">
                    <label><?php esc_html_e( 'Site Key', 'wprobo-documerge' ); ?></label>
                    <input type="text" name="wprobo_documerge_recaptcha_v2_site_key" class="wdm-input" value="<?php echo esc_attr( get_option( 'wprobo_documerge_recaptcha_v2_site_key', '' ) ); ?>">
                </div>
                <div class="wdm-field-group">
                    <label><?php esc_html_e( 'Secret Key', 'wprobo-documerge' ); ?></label>
                    <input type="text" name="wprobo_documerge_recaptcha_v2_secret_key" class="wdm-input" value="<?php echo esc_attr( get_option( 'wprobo_documerge_recaptcha_v2_secret_key', '' ) ); ?>">
                </div>
            </div>

            <div class="wdm-captcha-fields" data-captcha-type="recaptcha_v3"<?php if ( 'recaptcha_v3' !== $ct ) : ?> style="display:none;"<?php endif; ?>>
                <h3 class="wdm-section-heading"><?php esc_html_e( 'reCAPTCHA v3', 'wprobo-documerge' ); ?></h3>
                <div class="wdm-field-group">
                    <label><?php esc_html_e( 'Site Key', 'wprobo-documerge' ); ?></label>
                    <input type="text" name="wprobo_documerge_recaptcha_v3_site_key" class="wdm-input" value="<?php echo esc_attr( get_option( 'wprobo_documerge_recaptcha_v3_site_key', '' ) ); ?>">
                </div>
                <div class="wdm-field-group">
                    <label><?php esc_html_e( 'Secret Key', 'wprobo-documerge' ); ?></label>
                    <input type="text" name="wprobo_documerge_recaptcha_v3_secret_key" class="wdm-input" value="<?php echo esc_attr( get_option( 'wprobo_documerge_recaptcha_v3_secret_key', '' ) ); ?>">
                </div>
                <div class="wdm-field-group">
                    <label><?php esc_html_e( 'Score Threshold', 'wprobo-documerge' ); ?></label>
                    <input type="number" name="wprobo_documerge_recaptcha_v3_threshold" class="wdm-input wdm-input-small" value="<?php echo esc_attr( get_option( 'wprobo_documerge_recaptcha_v3_threshold', '0.5' ) ); ?>" min="0" max="1" step="0.1">
                    <span class="wdm-description"><?php esc_html_e( '0.0 = bot, 1.0 = human. Default: 0.5', 'wprobo-documerge' ); ?></span>
                </div>
            </div>

            <div class="wdm-captcha-fields" data-captcha-type="hcaptcha"<?php if ( 'hcaptcha' !== $ct ) : ?> style="display:none;"<?php endif; ?>>
                <h3 class="wdm-section-heading"><?php esc_html_e( 'hCaptcha', 'wprobo-documerge' ); ?></h3>
                <div class="wdm-field-group">
                    <label><?php esc_html_e( 'Site Key', 'wprobo-documerge' ); ?></label>
                    <input type="text" name="wprobo_documerge_hcaptcha_site_key" class="wdm-input" value="<?php echo esc_attr( get_option( 'wprobo_documerge_hcaptcha_site_key', '' ) ); ?>">
                </div>
                <div class="wdm-field-group">
                    <label><?php esc_html_e( 'Secret Key', 'wprobo-documerge' ); ?></label>
                    <input type="text" name="wprobo_documerge_hcaptcha_secret_key" class="wdm-input" value="<?php echo esc_attr( get_option( 'wprobo_documerge_hcaptcha_secret_key', '' ) ); ?>">
                </div>
            </div>

            <div class="wdm-settings-actions">
                <button type="button" class="wdm-btn wdm-btn-primary wdm-settings-save" data-tab="captcha"><?php esc_html_e( 'Save CAPTCHA Settings', 'wprobo-documerge' ); ?></button>
            </div>
            <?php } // End Pro gate for CAPTCHA. ?>
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
            $gate = \WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Feature_Gate::get_instance();
            if ( ! $gate->wprobo_documerge_is_pro() ) {
                echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_overlay(
                    __( 'Form Styles', 'wprobo-documerge' ),
                    __( 'Customise colours, fonts, spacing, and the overall look of your frontend forms.', 'wprobo-documerge' )
                );
            } else {
            ?>
            <?php
            $styles_json = get_option( 'wprobo_documerge_form_styles', '' );
            $styles      = ! empty( $styles_json ) ? json_decode( $styles_json, true ) : array();
            if ( ! is_array( $styles ) ) {
                $styles = array();
            }
            ?>

            <!-- ── Form Container Card ──────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-layout"></span>
                    <div>
                        <h3><?php esc_html_e( 'Form Container', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Background, border, padding and shadow for the form wrapper.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-color-row">
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Background', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_form_bg" value="<?php echo esc_attr( $styles['form_bg'] ?? '#ffffff' ); ?>">
                        </div>
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Border', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_form_border_color" value="<?php echo esc_attr( $styles['form_border_color'] ?? '#dde5f0' ); ?>">
                        </div>
                    </div>
                    <div class="wdm-settings-row-2col">
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Border Width', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_style_form_border_width" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['form_border_width'] ?? '0' ); ?>" min="0" max="10">
                                <span class="wdm-input-suffix">px</span>
                            </div>
                        </div>
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Border Radius', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_style_form_border_radius" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['form_border_radius'] ?? '0' ); ?>" min="0" max="50">
                                <span class="wdm-input-suffix">px</span>
                            </div>
                        </div>
                    </div>
                    <div class="wdm-field-group">
                        <label><?php esc_html_e( 'Padding', 'wprobo-documerge' ); ?></label>
                        <div class="wdm-input-row">
                            <input type="number" name="wprobo_documerge_style_form_padding" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['form_padding'] ?? '0' ); ?>" min="0" max="80">
                            <span class="wdm-input-suffix">px</span>
                        </div>
                    </div>
                    <div class="wdm-field-group">
                        <label class="wdm-checkbox-label">
                            <input type="checkbox" name="wprobo_documerge_style_form_shadow" value="1" <?php checked( $styles['form_shadow'] ?? '0', '1' ); ?>>
                            <?php esc_html_e( 'Enable box shadow', 'wprobo-documerge' ); ?>
                        </label>
                        <div class="wdm-color-row" style="margin-top:10px;">
                            <div class="wdm-color-field">
                                <span><?php esc_html_e( 'Shadow Color', 'wprobo-documerge' ); ?></span>
                                <input type="color" name="wprobo_documerge_style_form_shadow_color" value="<?php echo esc_attr( $styles['form_shadow_color'] ?? '#000000' ); ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Labels Card ──────────────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-tag"></span>
                    <div>
                        <h3><?php esc_html_e( 'Labels', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Typography settings for form field labels.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-color-row">
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Color', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_label_color" value="<?php echo esc_attr( $styles['label_color'] ?? '#1a1a1a' ); ?>">
                        </div>
                    </div>
                    <div class="wdm-settings-row-2col">
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Font Size', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_style_label_size" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['label_size'] ?? '14' ); ?>" min="10" max="24">
                                <span class="wdm-input-suffix">px</span>
                            </div>
                        </div>
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Font Weight', 'wprobo-documerge' ); ?></label>
                            <select name="wprobo_documerge_style_label_weight" class="wdm-select">
                                <?php $lw = $styles['label_weight'] ?? '600'; ?>
                                <option value="400" <?php selected( $lw, '400' ); ?>>400 (Normal)</option>
                                <option value="500" <?php selected( $lw, '500' ); ?>>500 (Medium)</option>
                                <option value="600" <?php selected( $lw, '600' ); ?>>600 (Semibold)</option>
                                <option value="700" <?php selected( $lw, '700' ); ?>>700 (Bold)</option>
                            </select>
                        </div>
                    </div>
                    <div class="wdm-field-group">
                        <label><?php esc_html_e( 'Margin Bottom', 'wprobo-documerge' ); ?></label>
                        <div class="wdm-input-row">
                            <input type="number" name="wprobo_documerge_style_label_margin" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['label_margin'] ?? '6' ); ?>" min="0" max="24">
                            <span class="wdm-input-suffix">px</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Input Fields Card ────────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-editor-textcolor"></span>
                    <div>
                        <h3><?php esc_html_e( 'Input Fields', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Styling for text inputs, selects, and textareas.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-color-row">
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Background', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_input_bg" value="<?php echo esc_attr( $styles['input_bg'] ?? '#ffffff' ); ?>">
                        </div>
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Border', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_input_border_color" value="<?php echo esc_attr( $styles['input_border_color'] ?? '#dde5f0' ); ?>">
                        </div>
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Text', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_input_color" value="<?php echo esc_attr( $styles['input_color'] ?? '#1a1a1a' ); ?>">
                        </div>
                    </div>
                    <div class="wdm-color-row">
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Focus Border', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_input_focus_color" value="<?php echo esc_attr( $styles['input_focus_color'] ?? '#042157' ); ?>">
                        </div>
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Placeholder', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_input_placeholder_color" value="<?php echo esc_attr( $styles['input_placeholder_color'] ?? '#9ca3af' ); ?>">
                        </div>
                    </div>
                    <div class="wdm-settings-row-2col">
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Border Width', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_style_input_border_width" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['input_border_width'] ?? '1' ); ?>" min="0" max="5">
                                <span class="wdm-input-suffix">px</span>
                            </div>
                        </div>
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Border Radius', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_style_input_border_radius" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['input_border_radius'] ?? '6' ); ?>" min="0" max="50">
                                <span class="wdm-input-suffix">px</span>
                            </div>
                        </div>
                    </div>
                    <div class="wdm-settings-row-2col">
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Padding', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_style_input_padding" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['input_padding'] ?? '12' ); ?>" min="4" max="30">
                                <span class="wdm-input-suffix">px</span>
                            </div>
                        </div>
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Font Size', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_style_input_font_size" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['input_font_size'] ?? '14' ); ?>" min="10" max="24">
                                <span class="wdm-input-suffix">px</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Submit Button Card ───────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-button"></span>
                    <div>
                        <h3><?php esc_html_e( 'Submit Button', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Colors, sizing and hover state for the submit button.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-color-row">
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Background', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_btn_bg" value="<?php echo esc_attr( $styles['btn_bg'] ?? '#042157' ); ?>">
                        </div>
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Text', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_btn_color" value="<?php echo esc_attr( $styles['btn_color'] ?? '#ffffff' ); ?>">
                        </div>
                    </div>
                    <div class="wdm-color-row">
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Hover Background', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_btn_hover_bg" value="<?php echo esc_attr( $styles['btn_hover_bg'] ?? '#0a3d8f' ); ?>">
                        </div>
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Hover Text', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_btn_hover_color" value="<?php echo esc_attr( $styles['btn_hover_color'] ?? '#ffffff' ); ?>">
                        </div>
                    </div>
                    <div class="wdm-settings-row-2col">
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Border Radius', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_style_btn_radius" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['btn_radius'] ?? '6' ); ?>" min="0" max="50">
                                <span class="wdm-input-suffix">px</span>
                            </div>
                        </div>
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Font Size', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_style_btn_font_size" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['btn_font_size'] ?? '14' ); ?>" min="10" max="24">
                                <span class="wdm-input-suffix">px</span>
                            </div>
                        </div>
                    </div>
                    <div class="wdm-settings-row-2col">
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Padding Vertical', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_style_btn_padding_v" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['btn_padding_v'] ?? '12' ); ?>" min="4" max="30">
                                <span class="wdm-input-suffix">px</span>
                            </div>
                        </div>
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Padding Horizontal', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_style_btn_padding_h" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['btn_padding_h'] ?? '24' ); ?>" min="4" max="60">
                                <span class="wdm-input-suffix">px</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Error States Card ────────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-warning"></span>
                    <div>
                        <h3><?php esc_html_e( 'Error States', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Colors for validation errors and success messages.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-color-row">
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Error Color', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_error_color" value="<?php echo esc_attr( $styles['error_color'] ?? '#dc2626' ); ?>">
                        </div>
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Error Border', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_error_border_color" value="<?php echo esc_attr( $styles['error_border_color'] ?? '#dc2626' ); ?>">
                        </div>
                        <div class="wdm-color-field">
                            <span><?php esc_html_e( 'Success Color', 'wprobo-documerge' ); ?></span>
                            <input type="color" name="wprobo_documerge_style_success_color" value="<?php echo esc_attr( $styles['success_color'] ?? '#166441' ); ?>">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Form Wrapper Card ────────────────────────────── -->
            <div class="wdm-settings-card">
                <div class="wdm-settings-card-header">
                    <span class="dashicons dashicons-editor-expand"></span>
                    <div>
                        <h3><?php esc_html_e( 'Form Wrapper', 'wprobo-documerge' ); ?></h3>
                        <p><?php esc_html_e( 'Max width and font family for the overall form.', 'wprobo-documerge' ); ?></p>
                    </div>
                </div>
                <div class="wdm-settings-card-body">
                    <div class="wdm-settings-row-2col">
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Max Width', 'wprobo-documerge' ); ?></label>
                            <div class="wdm-input-row">
                                <input type="number" name="wprobo_documerge_style_form_max_width" class="wdm-input wdm-input-small" value="<?php echo esc_attr( $styles['form_max_width'] ?? '640' ); ?>" min="320" max="1200">
                                <span class="wdm-input-suffix">px</span>
                            </div>
                        </div>
                        <div class="wdm-field-group">
                            <label><?php esc_html_e( 'Font Family', 'wprobo-documerge' ); ?></label>
                            <?php
                            $ff    = $styles['form_font_family'] ?? 'system';
                            $fonts = array(
                                'system'       => __( 'System Default', 'wprobo-documerge' ),
                                'inter'        => 'Inter',
                                'roboto'       => 'Roboto',
                                'opensans'     => 'Open Sans',
                                'lato'         => 'Lato',
                                'montserrat'   => 'Montserrat',
                                'poppins'      => 'Poppins',
                                'nunito'       => 'Nunito',
                                'raleway'      => 'Raleway',
                                'ubuntu'       => 'Ubuntu',
                                'merriweather' => 'Merriweather',
                                'playfair'     => 'Playfair Display',
                                'lora'         => 'Lora',
                                'sourcesans'   => 'Source Sans 3',
                                'dmsans'       => 'DM Sans',
                                'worksans'     => 'Work Sans',
                                'firasans'     => 'Fira Sans',
                                'mulish'       => 'Mulish',
                                'cabin'        => 'Cabin',
                                'karla'        => 'Karla',
                                'barlow'       => 'Barlow',
                                'josefinsans'  => 'Josefin Sans',
                                'quicksand'    => 'Quicksand',
                                'rubik'        => 'Rubik',
                                'manrope'      => 'Manrope',
                                'spacegrotesk' => 'Space Grotesk',
                            );
                            ?>
                            <select name="wprobo_documerge_style_form_font_family" class="wdm-select wdm-select2" id="wdm-font-family-select">
                                <?php foreach ( $fonts as $val => $label ) : ?>
                                    <option value="<?php echo esc_attr( $val ); ?>" <?php selected( $ff, $val ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="wdm-description"><?php esc_html_e( 'Google Fonts are loaded automatically on the frontend.', 'wprobo-documerge' ); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="wdm-settings-actions">
                <button type="button" class="wdm-btn wdm-btn-danger" id="wdm-reset-styles">
                    <span class="dashicons dashicons-image-rotate"></span>
                    <?php esc_html_e( 'Reset to Defaults', 'wprobo-documerge' ); ?>
                </button>
                <button type="button" class="wdm-btn wdm-btn-primary wdm-settings-save" data-tab="styles"><?php esc_html_e( 'Save Styles', 'wprobo-documerge' ); ?></button>
            </div>
            <?php } // End Pro gate for Styles. ?>
        </div>

        <!-- ══════════════ CUSTOM CSS ══════════════ -->
        <div class="wdm-settings-panel" data-tab="customcss">
            <?php
            $gate = \WPRobo\DocuMerge\Core\WPRobo_DocuMerge_Feature_Gate::get_instance();
            if ( ! $gate->wprobo_documerge_is_pro() ) {
                echo \WPRobo\DocuMerge\Admin\WPRobo_DocuMerge_Pro_Upsell::wprobo_documerge_render_overlay(
                    __( 'Custom CSS', 'wprobo-documerge' ),
                    __( 'Add your own CSS to fully customise the appearance of frontend forms.', 'wprobo-documerge' )
                );
            } else {
            ?>
            <h3 class="wdm-section-heading"><?php esc_html_e( 'Custom Frontend CSS', 'wprobo-documerge' ); ?></h3>

            <p class="wdm-description" style="margin-bottom:16px;">
                <?php esc_html_e( 'Add custom CSS to style your frontend forms. Click any class below to insert it into the editor.', 'wprobo-documerge' ); ?>
            </p>

            <div class="wdm-css-editor-wrap">
                <div class="wdm-css-editor-header">
                    <span class="wdm-css-editor-label"><span class="dashicons dashicons-editor-code"></span> CSS</span>
                </div>
                <textarea id="wdm-custom-css" name="wprobo_documerge_custom_css" class="wdm-css-textarea" rows="16" spellcheck="false" placeholder="/* Your custom CSS here */"><?php echo esc_textarea( get_option( 'wprobo_documerge_custom_css', '' ) ); ?></textarea>
            </div>

            <!-- Plugin Classes -->
            <div class="wdm-css-classes-section">
                <h4 class="wdm-css-classes-title"><?php esc_html_e( 'Form Structure', 'wprobo-documerge' ); ?></h4>
                <div class="wdm-css-class-pills">
                    <code class="wdm-css-class-pill" data-class=".wdm-form-wrap">.wdm-form-wrap</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-form-fields">.wdm-form-fields</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-field-group">.wdm-field-group</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-form-submit">.wdm-form-submit</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-form-success">.wdm-form-success</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-form-error">.wdm-form-error</code>
                </div>

                <h4 class="wdm-css-classes-title"><?php esc_html_e( 'Input Elements', 'wprobo-documerge' ); ?></h4>
                <div class="wdm-css-class-pills">
                    <code class="wdm-css-class-pill" data-class=".wdm-input">.wdm-input</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-select">.wdm-select</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-textarea">.wdm-textarea</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-radio-group">.wdm-radio-group</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-checkbox-group">.wdm-checkbox-group</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-datepicker">.wdm-datepicker</code>
                </div>

                <h4 class="wdm-css-classes-title"><?php esc_html_e( 'Labels & Text', 'wprobo-documerge' ); ?></h4>
                <div class="wdm-css-class-pills">
                    <code class="wdm-css-class-pill" data-class=".wdm-field-group label">.wdm-field-group label</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-required">.wdm-required</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-help-text">.wdm-help-text</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-field-error-msg">.wdm-field-error-msg</code>
                </div>

                <h4 class="wdm-css-classes-title"><?php esc_html_e( 'Buttons & Special', 'wprobo-documerge' ); ?></h4>
                <div class="wdm-css-class-pills">
                    <code class="wdm-css-class-pill" data-class=".wdm-submit-btn">.wdm-submit-btn</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-btn-primary">.wdm-btn-primary</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-signature-canvas">.wdm-signature-canvas</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-stripe-card-element">.wdm-stripe-card-element</code>
                </div>

                <h4 class="wdm-css-classes-title"><?php esc_html_e( 'Width Variants', 'wprobo-documerge' ); ?></h4>
                <div class="wdm-css-class-pills">
                    <code class="wdm-css-class-pill" data-class=".wdm-field-width-full">.wdm-field-width-full</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-field-width-half">.wdm-field-width-half</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-field-width-third">.wdm-field-width-third</code>
                </div>

                <h4 class="wdm-css-classes-title"><?php esc_html_e( 'States', 'wprobo-documerge' ); ?></h4>
                <div class="wdm-css-class-pills">
                    <code class="wdm-css-class-pill" data-class=".wdm-field-has-error">.wdm-field-has-error</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-loading">.wdm-loading</code>
                    <code class="wdm-css-class-pill" data-class=".wdm-step-active">.wdm-step-active</code>
                </div>
            </div>

            <?php
            // Scan all forms for custom CSS classes — grouped by form.
            $form_builder  = new \WPRobo\DocuMerge\Form\WPRobo_DocuMerge_Form_Builder();
            $all_forms     = $form_builder->wprobo_documerge_get_all_forms();
            $forms_classes = array();

            if ( is_array( $all_forms ) ) {
                foreach ( $all_forms as $form ) {
                    $fields = json_decode( $form->fields, true );
                    if ( ! is_array( $fields ) ) {
                        continue;
                    }
                    $form_items = array();
                    foreach ( $fields as $field ) {
                        if ( ! empty( $field['css_class'] ) ) {
                            $parts = preg_split( '/\s+/', trim( $field['css_class'] ) );
                            foreach ( $parts as $cls ) {
                                $cls = sanitize_html_class( $cls );
                                if ( ! empty( $cls ) ) {
                                    $form_items[] = '.' . $cls;
                                }
                            }
                        }
                        if ( ! empty( $field['css_id'] ) ) {
                            $cid = sanitize_html_class( $field['css_id'] );
                            if ( ! empty( $cid ) ) {
                                $form_items[] = '#' . $cid;
                            }
                        }
                    }
                    if ( ! empty( $form_items ) ) {
                        $forms_classes[] = array(
                            'id'      => absint( $form->id ),
                            'title'   => ! empty( $form->title ) ? $form->title : __( 'Untitled', 'wprobo-documerge' ),
                            'classes' => array_unique( $form_items ),
                        );
                    }
                }
            }
            ?>

            <?php if ( ! empty( $forms_classes ) ) : ?>
                <h4 class="wdm-css-classes-title">
                    <span class="dashicons dashicons-admin-customizer" style="font-size:14px;width:14px;height:14px;margin-right:4px;"></span>
                    <?php esc_html_e( 'Your Custom Classes', 'wprobo-documerge' ); ?>
                </h4>
                <?php foreach ( $forms_classes as $fg ) : ?>
                    <div class="wdm-custom-classes-form-group">
                        <p class="wdm-custom-classes-form-title">
                            <span class="dashicons dashicons-feedback"></span>
                            <?php echo esc_html( $fg['title'] ); ?>
                            <span style="color:#9ca3af;font-weight:400;font-size:11px;">(ID: <?php echo absint( $fg['id'] ); ?>)</span>
                        </p>
                        <div class="wdm-css-class-pills">
                            <?php foreach ( $fg['classes'] as $cls ) : ?>
                                <code class="wdm-css-class-pill wdm-css-class-custom" data-class="<?php echo esc_attr( $cls ); ?>"><?php echo esc_html( $cls ); ?></code>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="wdm-settings-actions">
                <button type="button" class="wdm-btn wdm-btn-primary wdm-settings-save" data-tab="customcss"><?php esc_html_e( 'Save Custom CSS', 'wprobo-documerge' ); ?></button>
            </div>
            <?php } // End Pro gate for Custom CSS. ?>
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
