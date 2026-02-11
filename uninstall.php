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
$table_name = $wpdb->prefix . 'woocatalogo';
$wpdb->query("DROP TABLE IF EXISTS {$table_name}");

// 2. Delete plugin options
delete_option('woocatalogo_nexsys_email');
delete_option('woocatalogo_nexsys_password');
delete_option('woocatalogo_nexsys_country');

// 3. Delete transients (and legacy option key)
delete_option('woocatalogo_nexsys_token');
delete_transient('woocatalogo_nexsys_token');

// 4. Clear scheduled cron hooks
wp_clear_scheduled_hook('CronActualizarCatalogoStock');
wp_clear_scheduled_hook('CronActualizarCatalogoPrice');
wp_clear_scheduled_hook('CronActualizarCatalogo');
