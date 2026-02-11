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

define( 'WOOCATALOGO__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WOOCATALOGO__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WOOCATALOGO_DEBUG_MODE', true);

require_once( WOOCATALOGO__PLUGIN_DIR . '/includes/class.woocatalogo.php' );
require_once( WOOCATALOGO__PLUGIN_DIR . '/includes/class.woocatalogo.api.php' );
require_once( WOOCATALOGO__PLUGIN_DIR . '/includes/class.woocatalogo.admin.php' );
require_once( WOOCATALOGO__PLUGIN_DIR . '/includes/class.woocatalogo.catalog.php' );
require_once( WOOCATALOGO__PLUGIN_DIR . '/includes/class.woocatalogo.product.php' );
require_once( WOOCATALOGO__PLUGIN_DIR . '/includes/interfaces/interface.woocatalogo.provider.php' );
require_once( WOOCATALOGO__PLUGIN_DIR . '/includes/abstracts/abstract.woocatalogo.provider.php' );
require_once( WOOCATALOGO__PLUGIN_DIR . '/includes/providers/class.provider.nexsys.php' );

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
    add_action( 'init', array( 'cWooCatalogo', 'init' ));
});

register_activation_hook  ( __FILE__, array( 'cWooCatalogoAdmin', 'fPluginActivationWooCatalogo'  ));
register_deactivation_hook( __FILE__, array( 'cWooCatalogoAdmin', 'fPluginDeactivationWooCatalogo'));

/////ACTIVAR LA FUNCION CADA CIERTO TIEMPO
//CREAR EL CRON CUANDO EL PLUGINS SE ACTIVE
add_filter( 'cron_schedules', 'wpshout_add_cron_interval2' );
function wpshout_add_cron_interval2( $schedules ) {
    $schedules['every1hrs'] = array(
            'interval'  => 3600, // time in seconds
            'display'   => 'Every 1 hours'
    );
    return $schedules;
}
function run_on_activate2(){

    if( !wp_next_scheduled( 'CronActualizarCatalogoStock' ) ) {
        wp_schedule_event( time(), 'every1hrs', 'CronActualizarCatalogoStock' );
    }
    if( !wp_next_scheduled( 'CronActualizarCatalogoPrice' ) ) {
        wp_schedule_event( time(), 'every1hrs', 'CronActualizarCatalogoPrice' );
    }
    if( !wp_next_scheduled( 'CronActualizarCatalogo' ) ) {
        wp_schedule_event( time(), 'every1hrs', 'CronActualizarCatalogo' );
    }

}
register_activation_hook( __FILE__, 'run_on_activate2' );


//DESACTIVA EL CRON CUANDO EL PLUGINS SE DESACTIVA
function run_on_deactivate2() {
    wp_clear_scheduled_hook('CronActualizarCatalogoStock');
    wp_clear_scheduled_hook('CronActualizarCatalogoPrice');
    wp_clear_scheduled_hook('CronActualizarCatalogo');

}
register_deactivation_hook( __FILE__, 'run_on_deactivate2' );

// Cron handlers — call methods directly with $is_cron = true to skip nonce verification
function ActualizarPrecioCatalogCreateNonce(){
    cCatalogWooCatalog::fUpdatePriceWooCatalogo(null, true);
}
add_action ('CronActualizarCatalogoPrice', 'ActualizarPrecioCatalogCreateNonce', 10, 0);

function ActualizarStockCatalogoCreateNonce(){
    cCatalogWooCatalog::fUpdateStockWooCatalogo(null, true);
}
add_action ('CronActualizarCatalogoStock', 'ActualizarStockCatalogoCreateNonce', 10, 0);

function ActualizarCatalogoCreateNonce(){
    cCatalogWooCatalog::fUpdateJsonCatalog(null, true);
}
add_action ('CronActualizarCatalogo', 'ActualizarCatalogoCreateNonce', 10, 0);