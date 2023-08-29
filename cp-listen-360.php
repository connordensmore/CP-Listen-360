<?php
/*
Plugin Name: Listen360 Reviews
Description: This plugin allows you to fetch and store reviews from Listen360 API. Customize settings, including API key and organization reference, through the WordPress admin.
Version: 2.2
Author: Connor Densmore
*/

// Define plugin constants
define('LISTEN360_PLUGIN_DIR', plugin_dir_path(__FILE__));

// Include necessary files
require_once LISTEN360_PLUGIN_DIR . 'includes/reviews-fetcher.php'; // Create this file for fetching reviews


// Add options page to WordPress settings menu
function listen360_api_plugin_settings_page()
{
  add_options_page(
    'Listen360 API Plugin Settings',
    'Listen360 API Plugin',
    'manage_options',
    'listen360_api_plugin_settings',
    'listen360_api_plugin_render_settings_page'
  );
}
add_action('admin_menu', 'listen360_api_plugin_settings_page');

// Render options page
function listen360_api_plugin_render_settings_page()
{
  ?>
<style>
.api-settings input {
  width: 50%;
}
</style>
<div class="wrap api-settings">
  <h2>Listen360 API Plugin Settings</h2>
  <div>
    <form method="post" action="options.php">
      <?php settings_fields('listen360_api_plugin_settings'); ?>
      <?php do_settings_sections('listen360_api_plugin_settings'); ?>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">Organization Reference ID</th>
          <td><input type="number" name="organization_reference_id"
              value="<?php echo esc_attr(get_option('organization_reference_id')); ?>" /></td>
        </tr>
      </table>
      <table class="form-table">
        <tr valign="top">
          <th scope="row">API Key</th>
          <td><input type="password" name="listen360_api_key"
              value="<?php echo esc_attr(get_option('listen360_api_key')); ?>" /></td>
        </tr>
      </table>
      <?php submit_button(); ?>
    </form>
  </div>
  <div>
    <p>Manually Pull and Update Reviews</p>
    <button type="button" id="manual-update-button" class="button">Manual Update</button>
  </div>
</div>
<script>
document.getElementById('manual-update-button').addEventListener('click', function() {
  alert('Manual update started!');
});
</script>
<?php
}

// Register settings and fields
function listen360_api_plugin_settings()
{
  register_setting('listen360_api_plugin_settings', 'organization_reference_id');
  register_setting('listen360_api_plugin_settings', 'listen360_api_key', 'sanitize_text_field');
}
add_action('admin_init', 'listen360_api_plugin_settings');

// Enqueue JavaScript file
function listen360_enqueue_scripts()
{
  wp_enqueue_script('listen360-admin', plugin_dir_url(__FILE__) . 'js/admin.js', array('jquery'), '1.0', true);
}
add_action('admin_enqueue_scripts', 'listen360_enqueue_scripts');