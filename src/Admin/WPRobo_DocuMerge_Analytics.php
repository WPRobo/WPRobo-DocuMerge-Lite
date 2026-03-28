<?php
/**
 * Form analytics tracker.
 *
 * Records view, start, and completion events for forms
 * aggregated by day. Provides summary and daily stats
 * for admin display.
 *
 * @package    WPRobo_DocuMerge
 * @subpackage WPRobo_DocuMerge/src/Admin
 * @author     Ali Shan <hello@wprobo.com>
 * @link       https://wprobo.com/plugins/wprobo-documerge
 * @since      1.3.0
 */

namespace WPRobo\DocuMerge\Admin;

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class WPRobo_DocuMerge_Analytics
 *
 * Tracks form analytics events (view, start, complete) using
 * daily aggregate counters stored in the wprdm_analytics table.
 *
 * @since 1.3.0
 */
class WPRobo_DocuMerge_Analytics {

    /**
     * Allowed event types.
     *
     * @since 1.3.0
     * @var   array
     */
    private static $wprobo_documerge_allowed_events = array( 'view', 'start', 'complete' );

    /**
     * Register WordPress hooks for AJAX event tracking.
     *
     * @since 1.3.0
     * @return void
     */
    public function wprobo_documerge_init_hooks() {
        add_action( 'wp_ajax_wprobo_documerge_track_event', array( $this, 'wprobo_documerge_ajax_track_event' ) );
        add_action( 'wp_ajax_nopriv_wprobo_documerge_track_event', array( $this, 'wprobo_documerge_ajax_track_event' ) );
    }

    /**
     * AJAX handler — track a form analytics event.
     *
     * Accepts form_id and event_type via POST, validates both,
     * and increments the daily counter for that form/event pair.
     *
     * @since 1.3.0
     * @return void Outputs JSON response and terminates.
     */
    public function wprobo_documerge_ajax_track_event() {
        // Verify nonce.
        check_ajax_referer( 'wprobo_documerge_frontend', 'nonce' );

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
        $form_id    = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified above.
        $event_type = isset( $_POST['event_type'] ) ? sanitize_key( wp_unslash( $_POST['event_type'] ) ) : '';

        if ( ! $form_id || ! in_array( $event_type, self::$wprobo_documerge_allowed_events, true ) ) {
            wp_send_json_error();
            return;
        }

        $this->wprobo_documerge_record_event( $form_id, $event_type );
        wp_send_json_success();
    }

    /**
     * Record an analytics event (increment daily counter).
     *
     * Uses INSERT ... ON DUPLICATE KEY UPDATE for atomic increment
     * against the UNIQUE KEY (form_id, event_type, event_date).
     *
     * @since 1.3.0
     * @param int    $form_id    The form ID.
     * @param string $event_type The event type: view, start, or complete.
     * @return void
     */
    public function wprobo_documerge_record_event( $form_id, $event_type ) {
        global $wpdb;

        $form_id    = absint( $form_id );
        $event_type = sanitize_key( $event_type );

        if ( ! $form_id || ! in_array( $event_type, self::$wprobo_documerge_allowed_events, true ) ) {
            return;
        }

        $table = $wpdb->prefix . 'wprdm_analytics';
        $today = current_time( 'Y-m-d' );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO {$table} (form_id, event_type, event_date, event_count)
                 VALUES (%d, %s, %s, 1)
                 ON DUPLICATE KEY UPDATE event_count = event_count + 1",
                $form_id,
                $event_type,
                $today
            )
        );
    }

    /**
     * Get analytics summary for a form over the last N days.
     *
     * Returns an array with views, starts, completions, and
     * calculated abandonment rate (percentage).
     *
     * @since  1.3.0
     * @param  int $form_id The form ID.
     * @param  int $days    Number of days to look back. Default 30.
     * @return array {
     *     @type int   $views        Total form views.
     *     @type int   $starts       Total form starts (first interaction).
     *     @type int   $completions  Total successful submissions.
     *     @type float $abandonment  Abandonment rate as percentage (0-100).
     * }
     */
    public function wprobo_documerge_get_form_stats( $form_id, $days = 30 ) {
        global $wpdb;

        $table     = $wpdb->prefix . 'wprdm_analytics';
        $date_from = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT event_type, SUM(event_count) AS total
                 FROM {$table}
                 WHERE form_id = %d AND event_date >= %s
                 GROUP BY event_type",
                absint( $form_id ),
                $date_from
            )
        );

        $stats = array(
            'views'       => 0,
            'starts'      => 0,
            'completions' => 0,
            'abandonment' => 0,
        );

        if ( is_array( $results ) ) {
            foreach ( $results as $row ) {
                if ( 'view' === $row->event_type ) {
                    $stats['views'] = (int) $row->total;
                } elseif ( 'start' === $row->event_type ) {
                    $stats['starts'] = (int) $row->total;
                } elseif ( 'complete' === $row->event_type ) {
                    $stats['completions'] = (int) $row->total;
                }
            }
        }

        // Calculate abandonment rate.
        if ( $stats['starts'] > 0 ) {
            $stats['abandonment'] = round(
                ( ( $stats['starts'] - $stats['completions'] ) / $stats['starts'] ) * 100,
                1
            );
        }

        return $stats;
    }

    /**
     * Get daily analytics for a form (for charts).
     *
     * Returns raw rows with event_type, event_date, and event_count
     * ordered by date ascending.
     *
     * @since  1.3.0
     * @param  int $form_id The form ID.
     * @param  int $days    Number of days to look back. Default 7.
     * @return array Array of row objects.
     */
    public function wprobo_documerge_get_daily_stats( $form_id, $days = 7 ) {
        global $wpdb;

        $table     = $wpdb->prefix . 'wprdm_analytics';
        $date_from = gmdate( 'Y-m-d', strtotime( "-{$days} days" ) );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT event_type, event_date, event_count
                 FROM {$table}
                 WHERE form_id = %d AND event_date >= %s
                 ORDER BY event_date ASC",
                absint( $form_id ),
                $date_from
            )
        );
    }
}
