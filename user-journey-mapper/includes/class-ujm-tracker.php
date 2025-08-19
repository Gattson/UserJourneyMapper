<?php
class UJM_Tracker {
    public static function activate() {
        global $wpdb;
        $table = $wpdb->prefix . 'user_journeys';
        $charset = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            session_id VARCHAR(64) NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
            page_url TEXT NOT NULL,
            referrer TEXT DEFAULT NULL,
            timestamp DATETIME NOT NULL,
            duration INT UNSIGNED DEFAULT 0,
            PRIMARY KEY (id),
            INDEX session_idx (session_id),
            INDEX user_idx (user_id),
            INDEX timestamp_idx (timestamp)
        ) $charset;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function deactivate() {
        // Optional cleanup
    }

    public static function init() {
        // Reserved for future use
    }

    public static function enqueue_frontend() {
        wp_enqueue_script('ujm-tracker', plugin_dir_url(__DIR__) . 'assets/js/tracker.js', [], null, true);
        wp_localize_script('ujm-tracker', 'ujm_ajax', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }
}
