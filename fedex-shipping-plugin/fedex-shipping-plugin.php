<?php

/**
 * Plugin Name: FedEx Shipping Plugin
 * Plugin URI: https://origami99.com/
 * Description: A custom plugin to integrate FedEx shipping services with WooCommerce.
 * Version: 1.0
 * Author: Origami99
 * Author URI: https://origami99.com/
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'includes/class-fedex-api.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-fedex-shipping.php';

// Activation Hook
function fedex_plugin_activate()
{
    // Code to run on activation (if needed)
}
register_activation_hook(__FILE__, 'fedex_plugin_activate');

// Deactivation Hook
function fedex_plugin_deactivate()
{
    // Code to run on deactivation (if needed)
}
register_deactivation_hook(__FILE__, 'fedex_plugin_deactivate');

function fedex_add_shipping_method($methods) {
    $methods['fedex_shipping'] = 'FedEx_Shipping_Method';
    return $methods;
}
add_filter('woocommerce_shipping_methods', 'fedex_add_shipping_method');


// Main Plugin Class
class FedEx_Shipping_Plugin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'fedex_plugin_menu'));
        add_action('admin_init', array($this, 'fedex_register_settings'));
    }

    // Add Settings Menu
    public function fedex_plugin_menu()
    {
        add_menu_page(
            'FedEx Shipping',
            'FedEx Shipping',
            'manage_options',
            'fedex-shipping',
            array($this, 'fedex_plugin_settings_page'),
            'dashicons-admin-generic'
        );
    }

    // Plugin Settings Page
    public function fedex_plugin_settings_page()
    {
?>
        <div class="wrap">
            <h1>FedEx Shipping Plugin Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('fedex_plugin_options_group');
                do_settings_sections('fedex-shipping');
                submit_button();
                ?>
            </form>
        </div>
<?php
    }

    public function fedex_register_settings() {
        register_setting('fedex_plugin_options_group', 'fedex_api_key');
        register_setting('fedex_plugin_options_group', 'fedex_api_secret');
        register_setting('fedex_plugin_options_group', 'fedex_account_number');
        register_setting('fedex_plugin_options_group', 'fedex_meter_number');
    
        add_settings_section('fedex_settings_section', 'API Credentials', null, 'fedex-shipping');
    
        add_settings_field('fedex_api_key', 'FedEx API Key', array($this, 'fedex_api_key_callback'), 'fedex-shipping', 'fedex_settings_section');
        add_settings_field('fedex_api_secret', 'FedEx API Secret', array($this, 'fedex_api_secret_callback'), 'fedex-shipping', 'fedex_settings_section');
        add_settings_field('fedex_account_number', 'FedEx Account Number', array($this, 'fedex_account_number_callback'), 'fedex-shipping', 'fedex_settings_section');
        add_settings_field('fedex_meter_number', 'FedEx Meter Number', array($this, 'fedex_meter_number_callback'), 'fedex-shipping', 'fedex_settings_section');
    }

    public function fedex_api_key_callback() {
        $value = get_option('fedex_api_key', '');
        echo "<input type='text' name='fedex_api_key' value='" . esc_attr($value) . "' />";
    }
    
    public function fedex_api_secret_callback() {
        $value = get_option('fedex_api_secret', '');
        echo "<input type='password' name='fedex_api_secret' value='" . esc_attr($value) . "' />";
    }
    
    public function fedex_account_number_callback() {
        $value = get_option('fedex_account_number', '');
        echo "<input type='text' name='fedex_account_number' value='" . esc_attr($value) . "' />";
    }
    
    public function fedex_meter_number_callback() {
        $value = get_option('fedex_meter_number', '');
        echo "<input type='text' name='fedex_meter_number' value='" . esc_attr($value) . "' />";
    }

    
    
}

add_action('admin_notices', function() {
    $fedex_api = new FedEx_API();
    $token = $fedex_api->get_access_token();
    
    if (is_wp_error($token)) {
        echo '<div class="notice notice-error"><p>FedEx API Error: ' . $token->get_error_message() . '</p></div>';
    } else {
        echo '<div class="notice notice-success"><p>FedEx Access Token: ' . esc_html($token) . '</p></div>';
    }
});



// Initialize Plugin
new FedEx_Shipping_Plugin();
