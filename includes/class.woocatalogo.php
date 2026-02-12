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
		add_action( 'wp_ajax_viw_datatables_endpoint_vendor_integration',	array( 'cVendorIntegrationAdmin',	  'fAjaxEndpointWooCatalogo'));
		// Removed: wp_ajax_no_priv_ endpoint — DataTables JSON should not be public
		add_action( 'wp_ajax_save_config_vendor_integration',			array( 'cVendorIntegrationAdmin',   'fSaveConfigGlobalWooCatalogo' ));
		add_action( 'wp_ajax_delete_config_vendor_integration',		array( 'cVendorIntegrationAdmin',   'fDeleteConfigGlobalWooCatalogo' ) );
		add_action( 'wp_ajax_save_license_vendor_integration',		array( 'cVendorIntegrationAdmin',   'fSaveLicenseWooCatalogo' ) );
		add_action( 'wp_ajax_viw_save_config_vendor_integration',		array( 'cVendorIntegrationAdmin',   'fSaveConfigGlobalWooCatalogo' ));
		add_action( 'wp_ajax_viw_delete_config_vendor_integration',	array( 'cVendorIntegrationAdmin',   'fDeleteConfigGlobalWooCatalogo' ) );
		add_action( 'wp_ajax_viw_save_license_vendor_integration',	array( 'cVendorIntegrationAdmin',   'fSaveLicenseWooCatalogo' ) );

		
		//add_filter( 'woocommerce_add_to_cart_validation',				array( 'cVendorIntegrationCatalog',  'fValidateStockWooCatalogo' ),10, 4 );
		add_action( 'wp_ajax_update_json_catalog_vendor_integration',		array( 'cVendorIntegrationCatalog',  'fUpdateJsonCatalog' ) );
		add_action( 'wp_ajax_update_stock_product_vendor_integration',	array( 'cVendorIntegrationCatalog',  'fUpdateStockWooCatalogo' ) );
	    add_action( 'wp_ajax_update_price_product_vendor_integration',	array( 'cVendorIntegrationCatalog',  'fUpdatePriceWooCatalogo' ) );
		add_action( 'wp_ajax_get_csv_vendor_integration',		        array( 'cVendorIntegrationCatalog',  'fGetCatalogCSV' ) );
		add_action( 'wp_ajax_viw_update_json_catalog_vendor_integration',	array( 'cVendorIntegrationCatalog',  'fUpdateJsonCatalog' ) );
		add_action( 'wp_ajax_viw_update_stock_product_vendor_integration',	array( 'cVendorIntegrationCatalog',  'fUpdateStockWooCatalogo' ) );
		add_action( 'wp_ajax_viw_update_price_product_vendor_integration',	array( 'cVendorIntegrationCatalog',  'fUpdatePriceWooCatalogo' ) );
		add_action( 'wp_ajax_viw_get_csv_vendor_integration',			array( 'cVendorIntegrationCatalog',  'fGetCatalogCSV' ) );
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
		add_action( 'wp_ajax_viw_insert_product_vendor_integration',	array( 'cVendorIntegrationProduct', 'fInsertProductWooCatalogo' ) );
		add_action( 'wp_ajax_viw_delete_product_vendor_integration',	array( 'cVendorIntegrationProduct', 'fDeleteProductWooCatalogo' ) );
		add_action( 'wp_ajax_viw_preview_product_vendor_integration',	array( 'cVendorIntegrationProduct', 'fPreviewProductWooCatalogo' ) );
		add_action( 'wp_ajax_viw_insert_attr_product_vendor_integration',	array( 'cVendorIntegrationProduct', 'fInsertAttrProductWooCatalogo' ));
		add_action( 'wp_ajax_viw_show_price_vendor_integration',		array( 'cVendorIntegrationProduct', 'fPriceShowWooCatalogo' ));
		add_action( 'wp_ajax_viw_show_stock_vendor_integration',		array( 'cVendorIntegrationProduct', 'fStockShowWooCatalogo' ));

		//procesar por lotes el stock
		add_action('wp_ajax_procesar_lote_productos_vendor_integration', array( 'cVendorIntegrationCatalog', 'procesar_lote_productos' ));
		add_action('wp_ajax_viw_procesar_lote_productos_vendor_integration', array( 'cVendorIntegrationCatalog', 'procesar_lote_productos' ));



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
	    wp_enqueue_script('viw-jquery.dataTables.min',		VIW_PLUGIN_URL . 'admin/vendor/datatables/jquery.dataTables.min.js', array('jquery'), '1.11.3');
	    wp_enqueue_script('viw-dataTables.responsive.min',	VIW_PLUGIN_URL . 'admin/vendor/datatables/dataTables.responsive.min.js', array('jquery'), '2.2.9');
	    wp_enqueue_script('viw-dataTables.select.min',		VIW_PLUGIN_URL . 'admin/vendor/datatables/dataTables.select.min.js', array('jquery'), '1.3.3');
	    wp_enqueue_script('viw-dataTables.buttons.min',		VIW_PLUGIN_URL . 'admin/vendor/datatables/dataTables.buttons.min.js', array('jquery'), '2.1.0');
	    wp_enqueue_script('viw-pdfmake.min.js',				VIW_PLUGIN_URL . 'admin/vendor/datatables/pdfmake.min.js', array('jquery'), '1.0');
	    wp_enqueue_script('viw-vfs_fonts',					VIW_PLUGIN_URL . 'admin/vendor/datatables/vfs_fonts.js', array('jquery'), '1.0');
	    wp_enqueue_script('viw-jszip.min',					VIW_PLUGIN_URL . 'admin/vendor/datatables/jszip.min.js', array('jquery'), '1.0');
	    wp_enqueue_script('viw-buttons.print.min',			VIW_PLUGIN_URL . 'admin/vendor/datatables/buttons.print.min.js', array('jquery'), '1.0');
	    wp_enqueue_script('viw-buttons.html5.min',			VIW_PLUGIN_URL . 'admin/vendor/datatables/buttons.html5.min.js', array('jquery'), '1.0');
		wp_enqueue_style('viw-jquery.dataTables.min',		VIW_PLUGIN_URL . 'admin/vendor/datatables/jquery.dataTables.min.css', array(), '1.11.3', 'all');
	    wp_enqueue_style('viw-responsive.dataTables.min',	VIW_PLUGIN_URL . 'admin/vendor/datatables/responsive.dataTables.min.css', array(), '2.2.9', 'all');
	    wp_enqueue_style('viw-select.dataTables.min',		VIW_PLUGIN_URL . 'admin/vendor/datatables/select.dataTables.min.css', array(), '1.3.3', 'all');
	    wp_enqueue_style('viw-buttons.dataTables.min',		VIW_PLUGIN_URL . 'admin/vendor/datatables/buttons.dataTables.min.css', array(), '2.1.0', 'all');
	    wp_enqueue_style('viw-font-awesome.min',			VIW_PLUGIN_URL . 'admin/vendor/font-awesome/font-awesome.min.css', array(), '4.7.0', 'all');
        
		//Cargar Resources Admin
        wp_enqueue_style('viw-admin-vendor-integration',		VIW_PLUGIN_URL . 'admin/css/admin-woocatalogo.css', array(), '1.6', 'all');
        wp_enqueue_script('viw-admin-vendor-integration',		VIW_PLUGIN_URL . 'admin/js/admin-woocatalogo.js', array('jquery'), '2.2');

		///funcion para actualizar stock
		wp_enqueue_script('viw-script-actualizacion-stock-vendor-integration', VIW_PLUGIN_URL . 'admin/js/actualizacion-stock.js', array('jquery'), '1.3', true );



        //Funciones personalizadas
		wp_localize_script('viw-admin-vendor-integration','VIW_Global',
			array(
			    'url'    => admin_url( 'admin-ajax.php' ),
			    'nonce'  => wp_create_nonce( 'vendor_integration_admin' ),
				'datatables_action' => 'viw_datatables_endpoint_vendor_integration'
			)
		);


	    ///funcion para actualizar stock
		wp_localize_script('viw-script-actualizacion-stock-vendor-integration', 'VIW_datosActualizacion',
		    array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('vendor_integration_actualizar_stock_nonce'),
				'action'   => 'viw_procesar_lote_productos_vendor_integration'
			)
		);


		wp_localize_script('viw-admin-vendor-integration','VIW_UpdateJsonCatalog',		array('action' => 'viw_update_json_catalog_vendor_integration'));
		wp_localize_script('viw-admin-vendor-integration','VIW_PriceShowWooCatalogo',  array('action' => 'viw_show_price_vendor_integration'));
		wp_localize_script('viw-admin-vendor-integration','VIW_StockShowWooCatalogo',  array('action' => 'viw_show_stock_vendor_integration'));
		wp_localize_script('viw-admin-vendor-integration','VIW_SaveConfigGlobal',      array('action' => 'viw_save_config_vendor_integration'));
		wp_localize_script('viw-admin-vendor-integration','VIW_DeleteConfigGlobal',	array('action' => 'viw_delete_config_vendor_integration'));
		wp_localize_script('viw-admin-vendor-integration','VIW_InsertProductoWooCatalogo', array('action' => 'viw_insert_product_vendor_integration'));
		wp_localize_script('viw-admin-vendor-integration','VIW_DeleteProductoWooCatalogo', array('action' => 'viw_delete_product_vendor_integration'));
		wp_localize_script('viw-admin-vendor-integration','VIW_UpdateStockWooCatalogo', array('action' => 'viw_update_stock_product_vendor_integration'));
		wp_localize_script('viw-admin-vendor-integration','VIW_UpdatPriceCatalogo',	array('action' => 'viw_update_price_product_vendor_integration'));
		wp_localize_script('viw-admin-vendor-integration','VIW_DownLoadCSVWooCatalogo', array('action' => 'viw_get_csv_vendor_integration'));

		wp_localize_script('viw-admin-vendor-integration','VIW_SaveLicenseWooCatalogo', array('action' => 'viw_save_license_vendor_integration'));
		wp_localize_script('viw-admin-vendor-integration','VIW_InsertAttrWooCatalogo',	array('action' => 'viw_insert_attr_product_vendor_integration'));
		wp_localize_script('viw-admin-vendor-integration','VIW_PreviewProductWooCatalogo', array('action' => 'viw_preview_product_vendor_integration'));
		



    }
	
	

}
