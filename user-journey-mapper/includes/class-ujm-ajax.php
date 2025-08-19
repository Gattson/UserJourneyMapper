<?php
class UJM_Ajax {
    public static function handle_tracking() {
        if (
            !isset($_POST['session_id']) ||
            !isset($_POST['page_url']) ||
            !isset($_POST['referrer']) ||
            !isset($_POST['duration'])
        ) {
            wp_send_json_error('Missing required fields');
            wp_die();
        }

        global $wpdb;

        $session_id = sanitize_text_field($_POST['session_id']);
        $page_url   = esc_url_raw($_POST['page_url']);
        $referrer   = esc_url_raw($_POST['referrer']);
        $duration   = intval($_POST['duration']);
        $user_id    = get_current_user_id();
        $timestamp  = current_time('mysql');

        if (empty($session_id) || empty($page_url)) {
            wp_send_json_error('Invalid session or page URL');
            wp_die();
        }

        $result = $wpdb->insert(
            $wpdb->prefix . 'user_journeys',
            compact('session_id', 'user_id', 'page_url', 'referrer', 'timestamp', 'duration'),
            ['%s', '%d', '%s', '%s', '%s', '%d']
        );

        $result ? wp_send_json_success('Journey recorded') : wp_send_json_error('Database insert failed');
        wp_die();
    }
}
