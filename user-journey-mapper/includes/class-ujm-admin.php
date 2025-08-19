<?php
class UJM_Admin {
    public static function add_menu() {
        add_menu_page(
            __('User Journey Mapper', 'user-journey-mapper'),
            'User Journey Mapper',
            'manage_options',
            'user-journey-mapper',
            [self::class, 'render_dashboard'],
            'dashicons-chart-line',
            25
        );
    }

    public static function enqueue_assets($hook) {
        if (strpos($hook, 'user-journey-mapper') === false) return;

        wp_enqueue_style('ujm-admin-style', plugin_dir_url(__DIR__) . 'assets/css/admin-style.css');
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
        wp_enqueue_script('ujm-admin-script', plugin_dir_url(__DIR__) . 'assets/js/admin-script.js', ['jquery', 'chartjs'], null, true);
    }

    public static function render_dashboard() {
		global $wpdb;
		$table = $wpdb->prefix . 'user_journeys';

		$total_sessions = $wpdb->get_var("SELECT COUNT(DISTINCT session_id) FROM $table");
		$average_duration = $wpdb->get_var("SELECT ROUND(AVG(duration), 2) FROM $table");

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

		$sample_session = $wpdb->get_var("SELECT session_id FROM $table ORDER BY timestamp DESC LIMIT 1");
		$journey_data = $wpdb->get_results($wpdb->prepare("
			SELECT page_url FROM $table WHERE session_id = %s ORDER BY timestamp ASC
		", $sample_session));

		$labels = array_map(fn($step) => esc_js($step->page_url), $journey_data);

		include plugin_dir_path(__FILE__) . 'dashboard-template.php';
		echo '<script>const ujmJourneyLabels = ' . json_encode($labels) . ';</script>';
	}

}
