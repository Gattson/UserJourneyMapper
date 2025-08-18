<?php
/**
 * Plugin Name: User Journey Mapper
 * Plugin URI: https://github.com/gattson
 * Description: Track and visualize user navigation paths across your WordPress site.
 * Version: 1.0.0
 * Author: Joseph Saad
 * Author URI: https://github.com/gattson
 * License: GPL2
 * Text Domain: user-journey-mapper
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

//  Activation Hook
function ujm_activate_plugin() {
      global $wpdb;

    $table_name = $wpdb->prefix . 'user_journeys';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
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
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'ujm_activate_plugin');

//  Deactivation Hook
function ujm_deactivate_plugin() {
    // Future: Clean up scheduled tasks or temp data
}
register_deactivation_hook(__FILE__, 'ujm_deactivate_plugin');

//  Enqueue Admin Styles & Scripts
function ujm_enqueue_admin_assets($hook) {
    // Only load on plugin pages
    if (strpos($hook, 'user-journey-mapper') === false) {
        return;
    }

    wp_enqueue_style('ujm-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
	wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array(), null, true);
    wp_enqueue_script('ujm-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', array('jquery'), null, true);
	

}
add_action('admin_enqueue_scripts', 'ujm_enqueue_admin_assets');
//  Enqueue Frontend Tracker Script
function ujm_enqueue_frontend_tracker() {
    wp_enqueue_script('ujm-tracker', plugin_dir_url(__FILE__) . 'assets/js/tracker.js', array(), null, true);

    wp_localize_script('ujm-tracker', 'ujm_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'ujm_enqueue_frontend_tracker');

//  Add Admin Menu
function ujm_add_admin_menu() {
    add_menu_page(
        __('User Journey Mapper', 'user-journey-mapper'),
        'User Journey Mapper',
        'manage_options',
        'user-journey-mapper',
        'ujm_render_dashboard',
        'dashicons-chart-line',
        25
    );
}
add_action('admin_menu', 'ujm_add_admin_menu');

//  Render Dashboard Page
function ujm_render_dashboard() {
    global $wpdb;
    $table = $wpdb->prefix . 'user_journeys';

    // Total sessions
    $total_sessions = $wpdb->get_var("SELECT COUNT(DISTINCT session_id) FROM $table");

    // Average duration
    $average_duration = $wpdb->get_var("SELECT ROUND(AVG(duration), 2) FROM $table");

    // Top exit pages (last page per session)
    $exit_pages = $wpdb->get_results("
        SELECT page_url, COUNT(*) as exits
        FROM (
            SELECT session_id, page_url
            FROM $table
            WHERE timestamp IN (
                SELECT MAX(timestamp)
                FROM $table
                GROUP BY session_id
            )
        ) as last_pages
        GROUP BY page_url
        ORDER BY exits DESC
        LIMIT 5
    ");

    echo '<div class="wrap">';
    echo '<h1><span class="dashicons dashicons-chart-line"></span> User Journey Mapper</h1>';

    echo '<div style="display: flex; gap: 30px; margin-top: 20px;">';
    echo '<div class="card"><h2>Total Sessions</h2><p>' . esc_html($total_sessions) . '</p></div>';
    echo '<div class="card"><h2>Average Duration</h2><p>' . esc_html($average_duration) . ' seconds</p></div>';
    echo '</div>';
	
	echo '<h2 style="margin-top: 40px;">User Journey Timeline</h2>';
	echo '<canvas id="ujmJourneyChart" width="100%" height="300"></canvas>';


    echo '<h2 style="margin-top: 40px;">Top Exit Pages</h2>';
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>Page URL</th><th>Exit Count</th></tr></thead><tbody>';
    foreach ($exit_pages as $page) {
        echo '<tr><td>' . esc_url($page->page_url) . '</td><td>' . intval($page->exits) . '</td></tr>';
    }
    echo '</tbody></table>';

    echo '</div>';
	
	// Get one sample session for demo
	$sample_session = $wpdb->get_var("SELECT session_id FROM $table ORDER BY timestamp DESC LIMIT 1");

	$journey_data = $wpdb->get_results($wpdb->prepare("
		SELECT page_url, timestamp
		FROM $table
		WHERE session_id = %s
		ORDER BY timestamp ASC
	", $sample_session));

	$labels = [];
	foreach ($journey_data as $step) {
		$labels[] = esc_js($step->page_url);
	}

	echo '<script>
		const ujmJourneyLabels = ' . json_encode($labels) . ';
	</script>';

}
//  Handle AJAX Tracking Request
function ujm_handle_track_visit() {
	 // Check for required POST fields
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
	
	// Sanitize and validate input
    $session_id = sanitize_text_field($_POST['session_id']);
    $page_url = esc_url_raw($_POST['page_url']);
    $referrer = esc_url_raw($_POST['referrer']);
    $duration = intval($_POST['duration']);
    $user_id = get_current_user_id();
    $timestamp = current_time('mysql');
	
	// Basic validation
    if (empty($session_id) || empty($page_url)) {
        wp_send_json_error('Invalid session or page URL');
        wp_die();
    }
	
	// Insert into database
    $result = $wpdb->insert(
        $wpdb->prefix . 'user_journeys',
        array(
            'session_id' => $session_id,
            'user_id' => $user_id,
            'page_url' => $page_url,
            'referrer' => $referrer,
            'timestamp' => $timestamp,
            'duration' => $duration,
        ),
        array('%s', '%d', '%s', '%s', '%s', '%d')
    );
	
	if ($result) {
        wp_send_json_success('Journey recorded');
    } else {
        wp_send_json_error('Database insert failed');
    }

    wp_die(); // Required for AJAX
}
add_action('wp_ajax_ujm_track_visit', 'ujm_handle_track_visit');
add_action('wp_ajax_nopriv_ujm_track_visit', 'ujm_handle_track_visit');
