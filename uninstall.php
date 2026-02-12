<?php
/**
 * Vendor Integration Woo — Uninstall
 *
 * Fired when the plugin is deleted through the WordPress admin.
 * Cleans up all plugin data (tables, options, transients, crons).
 *
 * @package vendor-integration-woo
 */

// Exit if not called by WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

// 1. Drop custom table
$table_name = $wpdb->prefix . 'vendor_integration';
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

// 2. Delete plugin options
delete_option('vendor_integration_nexsys_email');
delete_option('vendor_integration_nexsys_password');
delete_option('vendor_integration_nexsys_country');

// 3. Delete transients (and legacy option key)
delete_option('vendor_integration_nexsys_token');
delete_transient('vendor_integration_nexsys_token');
delete_transient('vendor_integration_nexsys_auth_error');

// 4. Clear scheduled cron hooks
wp_clear_scheduled_hook('vendor_integration_cron_update_stock');
wp_clear_scheduled_hook('vendor_integration_cron_update_price');
wp_clear_scheduled_hook('vendor_integration_cron_update_catalog');
