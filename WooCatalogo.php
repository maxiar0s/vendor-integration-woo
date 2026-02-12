<?php
/**
 * Vendor Integration Woo
 *
 * Plugin Name: Vendor Integration Woo
 * Plugin URI:  https://siroe.cl
 * Description: Integración de catálogos de proveedores para WooCommerce.
 * Version:     1.0.0
 * Author:      Siroe
 * Author URI:  https://www.siroe.cl
 * Text Domain: vendor-integration-woo
 * Domain Path: /languages/
 * 
 */


// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

if(!defined('ABSPATH')){die('-1');}

define( 'VENDOR_INTEGRATION_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'VENDOR_INTEGRATION_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'VENDOR_INTEGRATION_DEBUG_MODE', true);

require_once( VENDOR_INTEGRATION_PLUGIN_DIR . '/includes/class.woocatalogo.php' );
require_once( VENDOR_INTEGRATION_PLUGIN_DIR . '/includes/class.woocatalogo.api.php' );
require_once( VENDOR_INTEGRATION_PLUGIN_DIR . '/includes/class.woocatalogo.admin.php' );
require_once( VENDOR_INTEGRATION_PLUGIN_DIR . '/includes/class.woocatalogo.catalog.php' );
require_once( VENDOR_INTEGRATION_PLUGIN_DIR . '/includes/class.woocatalogo.product.php' );
require_once( VENDOR_INTEGRATION_PLUGIN_DIR . '/includes/interfaces/interface.woocatalogo.provider.php' );
require_once( VENDOR_INTEGRATION_PLUGIN_DIR . '/includes/abstracts/abstract.woocatalogo.provider.php' );
require_once( VENDOR_INTEGRATION_PLUGIN_DIR . '/includes/providers/class.provider.nexsys.php' );

// WooCommerce dependency check
add_action('plugins_loaded', function() {
    if ( !class_exists('WooCommerce') ) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Vendor Integration Woo requiere WooCommerce para funcionar. Por favor, instala y activa WooCommerce.', 'vendor-integration-woo');
            echo '</p></div>';
        });
        return;
    }

    // Only init plugin hooks if WooCommerce is active
    add_action( 'init', array( 'cVendorIntegrationWoo', 'init' ));
});

register_activation_hook  ( __FILE__, array( 'cVendorIntegrationAdmin', 'fPluginActivationWooCatalogo'  ));
register_deactivation_hook( __FILE__, array( 'cVendorIntegrationAdmin', 'fPluginDeactivationWooCatalogo'));

/////ACTIVAR LA FUNCION CADA CIERTO TIEMPO
//CREAR EL CRON CUANDO EL PLUGINS SE ACTIVE
add_filter( 'cron_schedules', 'vendor_integration_add_cron_interval' );
function vendor_integration_add_cron_interval( $schedules ) {
    $schedules['every1hrs'] = array(
            'interval'  => 3600, // time in seconds
            'display'   => 'Every 1 hours'
    );
    return $schedules;
}
function vendor_integration_run_on_activate(){

    if( !wp_next_scheduled( 'vendor_integration_cron_update_stock' ) ) {
        wp_schedule_event( time(), 'every1hrs', 'vendor_integration_cron_update_stock' );
    }
    if( !wp_next_scheduled( 'vendor_integration_cron_update_price' ) ) {
        wp_schedule_event( time(), 'every1hrs', 'vendor_integration_cron_update_price' );
    }
    if( !wp_next_scheduled( 'vendor_integration_cron_update_catalog' ) ) {
        wp_schedule_event( time(), 'every1hrs', 'vendor_integration_cron_update_catalog' );
    }

}
register_activation_hook( __FILE__, 'vendor_integration_run_on_activate' );


//DESACTIVA EL CRON CUANDO EL PLUGINS SE DESACTIVA
function vendor_integration_run_on_deactivate() {
    wp_clear_scheduled_hook('vendor_integration_cron_update_stock');
    wp_clear_scheduled_hook('vendor_integration_cron_update_price');
    wp_clear_scheduled_hook('vendor_integration_cron_update_catalog');

}
register_deactivation_hook( __FILE__, 'vendor_integration_run_on_deactivate' );

// Cron handlers — call methods directly with $is_cron = true to skip nonce verification
function vendor_integration_update_price_cron(){
    cVendorIntegrationCatalog::fUpdatePriceWooCatalogo(null, true);
}
add_action ('vendor_integration_cron_update_price', 'vendor_integration_update_price_cron', 10, 0);

function vendor_integration_update_stock_cron(){
    cVendorIntegrationCatalog::fUpdateStockWooCatalogo(null, true);
}
add_action ('vendor_integration_cron_update_stock', 'vendor_integration_update_stock_cron', 10, 0);

function vendor_integration_update_catalog_cron(){
    cVendorIntegrationCatalog::fUpdateJsonCatalog(null, true);
}
add_action ('vendor_integration_cron_update_catalog', 'vendor_integration_update_catalog_cron', 10, 0);
