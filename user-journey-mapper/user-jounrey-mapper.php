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

// Load plugin classes
require_once plugin_dir_path(__FILE__) . 'includes/class-ujm-tracker.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ujm-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-ujm-ajax.php';

// Activation and deactivation hooks
register_activation_hook(__FILE__, ['UJM_Tracker', 'activate']);
register_deactivation_hook(__FILE__, ['UJM_Tracker', 'deactivate']);

// Initialize plugin components
add_action('init', ['UJM_Tracker', 'init']);
add_action('admin_menu', ['UJM_Admin', 'add_menu']);
add_action('admin_enqueue_scripts', ['UJM_Admin', 'enqueue_assets']);
add_action('wp_enqueue_scripts', ['UJM_Tracker', 'enqueue_frontend']);
add_action('wp_ajax_ujm_track_visit', ['UJM_Ajax', 'handle_tracking']);
add_action('wp_ajax_nopriv_ujm_track_visit', ['UJM_Ajax', 'handle_tracking']);
