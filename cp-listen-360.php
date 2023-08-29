<?php
/*
Plugin Name: Listen360 Reviews
Description: Custom plugin to integrate Listen360 reviews into WordPress.
Version: 1.11
Author: Connor Densmore
*/

// Define plugin constants
define('LISTEN360_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include necessary files
require_once LISTEN360_PLUGIN_DIR . 'includes/reviews-fetcher.php'; // Create this file for fetching reviews


// Add admin menu item
function listen360_add_admin_menu()
{
  add_menu_page(
    'Listen360 Settings',
    'Listen360 Settings',
    'manage_options',
    'listen360-settings',
    'listen360_settings_page'
  );
}
add_action('admin_menu', 'listen360_add_admin_menu');

// Callback function for settings page
function listen360_settings_page()
{
  ?>
  <div class="wrap">
    <h1>Listen360 Settings</h1>
    <form method="post" action="options.php">
      <?php
      settings_fields('listen360_settings');
      do_settings_sections('listen360-settings');
      submit_button();
      ?>
    </form>

    <!-- Add a manual update button -->
    <form method="post" action="">
      <?php wp_nonce_field('listen360_manual_update', 'listen360_manual_update_nonce'); ?>
      <input type="hidden" name="listen360_manual_update" value="true">
      <button type="submit" class="button">Manual Update</button>
    </form>
  </div>
  <?php
}


// Register settings and fields
function listen360_register_settings()
{
  register_setting('listen360_settings', 'listen360_api_key');
  register_setting('listen360_settings', 'listen360_organization_reference');

  add_settings_section(
    'listen360_section',
    'Listen360 API Settings',
    'listen360_section_callback',
    'listen360-settings'
  );

  add_settings_field(
    'listen360_api_key',
    'API Key',
    'listen360_api_key_field',
    'listen360-settings',
    'listen360_section'
  );

  add_settings_field(
    'listen360_organization_reference',
    'Organization Reference',
    'listen360_organization_reference_field',
    'listen360-settings',
    'listen360_section'
  );
}
add_action('admin_init', 'listen360_register_settings');

// Callback for settings section
function listen360_section_callback()
{
  echo 'Enter your Listen360 API settings below:';
}

// Callback for API key field
function listen360_api_key_field()
{
  $api_key = get_option('listen360_api_key');
  echo "<input type='text' name='listen360_api_key' value='$api_key' />";
}

// Callback for Organization Reference field
function listen360_organization_reference_field()
{
  $organization_reference = get_option('listen360_organization_reference');
  echo "<input type='text' name='listen360_organization_reference' value='$organization_reference' />";
}