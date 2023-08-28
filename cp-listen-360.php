<?php
/*
Plugin Name: Listen360 Reviews Integration
Description: Custom plugin to integrate Listen360 reviews into WordPress.
Version: 1.0
*/

// Define plugin constants
define('LISTEN360_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include necessary files
require_once LISTEN360_PLUGIN_DIR . 'includes/reviews-fetcher.php'; // Create this file for fetching reviews
