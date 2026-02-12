<?php
if (!defined('ABSPATH')) exit;
/**
 * Archivo de inicialización de Hooks
 * @link        https://siroe.cl
 * @since       1.2.0
 * 
 * @package     base
 * @subpackage  base/include
 */


class cVendorIntegrationWoo {

    private static $initiated = false;

	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		self::$initiated = true;



		add_action( 'admin_enqueue_scripts',								array( 'cVendorIntegrationWoo',		  'load_resources' ) );
        add_action( 'admin_menu',											array( 'cVendorIntegrationAdmin',	  'fCreateMenuWooCatalogo' ));
        add_action( 'admin_menu',											array( 'cVendorIntegrationAdmin',	  'fCreateSubMenuWooCatalogo' ));

		add_action( 'wp_ajax_datatables_endpoint_vendor_integration',		array( 'cVendorIntegrationAdmin',	  'fAjaxEndpointWooCatalogo'));
		// Removed: wp_ajax_no_priv_ endpoint — DataTables JSON should not be public
		add_action( 'wp_ajax_save_config_vendor_integration',			array( 'cVendorIntegrationAdmin',   'fSaveConfigGlobalWooCatalogo' ));
		add_action( 'wp_ajax_delete_config_vendor_integration',		array( 'cVendorIntegrationAdmin',   'fDeleteConfigGlobalWooCatalogo' ) );
		add_action( 'wp_ajax_save_license_vendor_integration',		array( 'cVendorIntegrationAdmin',   'fSaveLicenseWooCatalogo' ) );

		
		//add_filter( 'woocommerce_add_to_cart_validation',				array( 'cVendorIntegrationCatalog',  'fValidateStockWooCatalogo' ),10, 4 );
		add_action( 'wp_ajax_update_json_catalog_vendor_integration',		array( 'cVendorIntegrationCatalog',  'fUpdateJsonCatalog' ) );
		add_action( 'wp_ajax_update_stock_product_vendor_integration',	array( 'cVendorIntegrationCatalog',  'fUpdateStockWooCatalogo' ) );
	    add_action( 'wp_ajax_update_price_product_vendor_integration',	array( 'cVendorIntegrationCatalog',  'fUpdatePriceWooCatalogo' ) );
		add_action( 'wp_ajax_get_csv_vendor_integration',		        array( 'cVendorIntegrationCatalog',  'fGetCatalogCSV' ) );
		//add_action( 'wp_ajax_update_json_catalog_extend_vendor_integration',	array( 'cVendorIntegrationCatalog',  'fUpdateJsonExtendCatalogWooCatalogo' ));

		add_action( 'wp_ajax_insert_product_vendor_integration',		array( 'cVendorIntegrationProduct', 'fInsertProductWooCatalogo' ) );
		//add_action( 'wp_ajax_multi_insert_product_vendor_integration',		array( 'cVendorIntegrationProduct', 'fMultiInsertProducttWooCatalogo' ) );
		add_action( 'wp_ajax_delete_product_vendor_integration',		array( 'cVendorIntegrationProduct', 'fDeleteProductWooCatalogo' ) );
		//add_action( 'wp_ajax_multi_delete_product_vendor_integration',		array( 'cVendorIntegrationProduct', 'fMultiDeleteProductWooCatalogo' ) );
        add_action( 'wp_ajax_preview_product_vendor_integration',		array( 'cVendorIntegrationProduct', 'fPreviewProductWooCatalogo' ) );
		//add_action( 'wp_ajax_create_link_product_vendor_integration',		array( 'cVendorIntegrationProduct', 'fCreateLinkProductWooCatalogo' ) );
		//add_action( 'wp_ajax_insert_product_by_categories_vendor_integration',	array( 'cVendorIntegrationProduct', 'fInsertProductByCategoryWooCatalogo' ));
		//add_action( 'wp_ajax_unpublish_product_vendor_integration',		array( 'cVendorIntegrationProduct', 'fUnpublishProductWoCatalogo' ));
		add_action( 'wp_ajax_insert_attr_product_vendor_integration',	array( 'cVendorIntegrationProduct', 'fInsertAttrProductWooCatalogo' ));
		add_action( 'wp_ajax_show_price_vendor_integration',			array( 'cVendorIntegrationProduct', 'fPriceShowWooCatalogo' ));
		add_action( 'wp_ajax_show_stock_vendor_integration',			array( 'cVendorIntegrationProduct', 'fStockShowWooCatalogo' ));

		//procesar por lotes el stock
		add_action('wp_ajax_procesar_lote_productos_vendor_integration', array( 'cVendorIntegrationCatalog', 'procesar_lote_productos' ));



		add_action( 'woocommerce_product_options_general_product_data',	array( 'cVendorIntegrationProduct', 'agregar_campo_proveedor' ));
		add_action( 'woocommerce_process_product_meta',					array( 'cVendorIntegrationProduct', 'guardar_valor_proveedor' ));
		add_action( 'woocommerce_after_product_name',					array( 'cVendorIntegrationProduct', 'mostrar_valor_proveedor' ));
		
		

	}


	//Cargar Resources Vendor — only on plugin pages
	public static function load_resources( $hook ) {

		// Only load on plugin admin pages
		if ( strpos($hook, 'vendor_integration') === false ) {
			return;
		}

		//Cargar Resources Vendor
	    wp_enqueue_script('viw-jquery.dataTables.min',		VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/datatables/jquery.dataTables.min.js', array('jquery'), '1.11.3');
	    wp_enqueue_script('viw-dataTables.responsive.min',	VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/datatables/dataTables.responsive.min.js', array('jquery'), '2.2.9');
	    wp_enqueue_script('viw-dataTables.select.min',		VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/datatables/dataTables.select.min.js', array('jquery'), '1.3.3');
	    wp_enqueue_script('viw-dataTables.buttons.min',		VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/datatables/dataTables.buttons.min.js', array('jquery'), '2.1.0');
	    wp_enqueue_script('viw-pdfmake.min.js',				VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/datatables/pdfmake.min.js', array('jquery'), '1.0');
	    wp_enqueue_script('viw-vfs_fonts',					VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/datatables/vfs_fonts.js', array('jquery'), '1.0');
	    wp_enqueue_script('viw-jszip.min',					VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/datatables/jszip.min.js', array('jquery'), '1.0');
	    wp_enqueue_script('viw-buttons.print.min',			VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/datatables/buttons.print.min.js', array('jquery'), '1.0');
	    wp_enqueue_script('viw-buttons.html5.min',			VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/datatables/buttons.html5.min.js', array('jquery'), '1.0');
		wp_enqueue_style('viw-jquery.dataTables.min',		VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/datatables/jquery.dataTables.min.css', array(), '1.11.3', 'all');
	    wp_enqueue_style('viw-responsive.dataTables.min',	VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/datatables/responsive.dataTables.min.css', array(), '2.2.9', 'all');
	    wp_enqueue_style('viw-select.dataTables.min',		VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/datatables/select.dataTables.min.css', array(), '1.3.3', 'all');
	    wp_enqueue_style('viw-buttons.dataTables.min',		VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/datatables/buttons.dataTables.min.css', array(), '2.1.0', 'all');
	    wp_enqueue_style('viw-font-awesome.min',			VENDOR_INTEGRATION_PLUGIN_URL . 'admin/vendor/font-awesome/font-awesome.min.css', array(), '4.7.0', 'all');
        
		//Cargar Resources Admin
        wp_enqueue_style('admin-vendor-integration',		VENDOR_INTEGRATION_PLUGIN_URL . 'admin/css/admin-woocatalogo.css', array(), '1.0', 'all');
        wp_enqueue_script('admin-vendor-integration',		VENDOR_INTEGRATION_PLUGIN_URL . 'admin/js/admin-woocatalogo.js', array('jquery'), '1.4');

		///funcion para actualizar stock
		wp_enqueue_script('script-actualizacion-stock-vendor-integration', VENDOR_INTEGRATION_PLUGIN_URL . 'admin/js/actualizacion-stock.js', array('jquery'), '1.0', true );



        //Funciones personalizadas
		wp_localize_script('admin-vendor-integration','Global',
			array(
			    'url'    => admin_url( 'admin-ajax.php' ),
			    'nonce'  => wp_create_nonce( 'vendor_integration_admin' )
			)
		);


	    ///funcion para actualizar stock
		wp_localize_script('script-actualizacion-stock-vendor-integration', 'datosActualizacion',
		    array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('vendor_integration_actualizar_stock_nonce')
			)
		);


		wp_localize_script('admin-vendor-integration','aUpdateJsonCatalog',		array('action' => 'update_json_catalog_vendor_integration'));
		wp_localize_script('admin-vendor-integration','aPriceShowWooCatalogo',  array('action' => 'show_price_vendor_integration'));
		wp_localize_script('admin-vendor-integration','aStockShowWooCatalogo',  array('action' => 'show_stock_vendor_integration'));
		wp_localize_script('admin-vendor-integration','aSaveConfigGlobal',      array('action' => 'save_config_vendor_integration'));
		wp_localize_script('admin-vendor-integration','aDeleteConfigGlobal',	array('action' => 'delete_config_vendor_integration'));
		wp_localize_script('admin-vendor-integration','aInsertProductoWooCatalogo', array('action' => 'insert_product_vendor_integration'));
		wp_localize_script('admin-vendor-integration','aDeleteProductoWooCatalogo', array('action' => 'delete_product_vendor_integration'));
		wp_localize_script('admin-vendor-integration','aUpdateStockWooCatalogo', array('action' => 'update_stock_product_vendor_integration'));
		wp_localize_script('admin-vendor-integration','aUpdatPriceCatalogo',	array('action' => 'update_price_product_vendor_integration'));
		wp_localize_script('admin-vendor-integration','aDownLoadCSVWooCatalogo', array('action' => 'get_csv_vendor_integration'));

		wp_localize_script('admin-vendor-integration','aSaveLicenseWooCatalogo', array('action' => 'save_license_vendor_integration'));
		wp_localize_script('admin-vendor-integration','aInsertAttrWooCatalogo',	array('action' => 'insert_attr_product_vendor_integration'));
		wp_localize_script('admin-vendor-integration','aPreviewProductWooCatalogo', array('action' => 'preview_product_vendor_integration'));
		



    }
	
	

}
