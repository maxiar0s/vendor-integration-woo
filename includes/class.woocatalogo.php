<?php
/**
 * Archivo de inicialización de Hooks
 * @link        https://siroe.cl
 * @since       1.2.0
 * 
 * @package     base
 * @subpackage  base/include
 */


class cWooCatalogo {

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



		add_action( 'admin_enqueue_scripts',							    array( 'cWooCatalogo',		  'load_resources' ) );
        add_action( 'admin_menu',										array( 'cWooCatalogoAdmin',	  'fCreateMenuWooCatalogo' ));
        add_action( 'admin_menu',										array( 'cWooCatalogoAdmin',	  'fCreateSubMenuWooCatalogo' ));

		add_action( 'cron_update_price_woocatalogo',					    array( 'cWooCatalogoAdmin',	  'fCreateNonceUpdatePriceWooCatalogo'));
		add_action( 'cron_update_stock_woocatalogo',					    array( 'cWooCatalogoAdmin',	  'fCreateNonceUpdateStockWooCatalogo'));
		add_action( 'wp_ajax_datatables_endpoint_woocatalogo',			array( 'cWooCatalogoAdmin',	  'fAjaxEndpointWooCatalogo'));
		add_action( 'wp_ajax_no_priv_datatables_endpoint_woocatalogo', 	array( 'cWooCatalogoAdmin',	  'fAjaxEndpointWooCatalogo')); 
		add_action( 'wp_ajax_save_config_woocatalogo',					array( 'cWooCatalogoAdmin',   'fSaveConfigGlobalWooCatalogo' ));
		add_action( 'wp_ajax_delete_config_woocatalogo',				    array( 'cWooCatalogoAdmin',   'fDeleteConfigGlobalWooCatalogo' ) );
		add_action( 'wp_ajax_save_license_woocatalogo',					array( 'cWooCatalogoAdmin',   'fSaveLicenseWooCatalogo' ) );

		
		//add_filter( 'woocommerce_add_to_cart_validation',				array( 'cCatalogWooCatalog',  'fValidateStockWooCatalogo' ),10, 4 );
		add_action( 'wp_ajax_update_json_catalog_woocatalogo',			array( 'cCatalogWooCatalog',  'fUpdateJsonCatalog' ) );
		add_action( 'wp_ajax_update_stock_product_woocatalogo',			array( 'cCatalogWooCatalog',  'fUpdateStockWooCatalogo' ) );
	    add_action( 'wp_ajax_update_price_product_woocatalogo',			array( 'cCatalogWooCatalog',  'fUpdatePriceWooCatalogo' ) );
		add_action( 'wp_ajax_get_csv_woocatalogo',			            array( 'cCatalogWooCatalog',  'fGetCatalogCSV' ) );
		//add_action( 'wp_ajax_update_json_catalog_extend_woocatalogo',	array( 'cCatalogWooCatalog',  'fUpdateJsonExtendCatalogWooCatalogo' ));

		add_action( 'wp_ajax_insert_product_woocatalogo',				array( 'cProductWooCatalogo', 'fInsertProductWooCatalogo' ) );
		//add_action( 'wp_ajax_multi_insert_product_woocatalogo',			array( 'cProductWooCatalogo', 'fMultiInsertProducttWooCatalogo' ) );
		add_action( 'wp_ajax_delete_product_woocatalogo',				array( 'cProductWooCatalogo', 'fDeleteProductWooCatalogo' ) );
		//add_action( 'wp_ajax_multi_delete_product_woocatalogo',			array( 'cProductWooCatalogo', 'fMultiDeleteProductWooCatalogo' ) );
        add_action( 'wp_ajax_preview_product_woocatalogo',				array( 'cProductWooCatalogo', 'fPreviewProductWooCatalogo' ) );
		//add_action( 'wp_ajax_create_link_product_woocatalogo',			array( 'cProductWooCatalogo', 'fCreateLinkProductWooCatalogo' ) );
		//add_action( 'wp_ajax_insert_product_by_categories_woocatalogo',	array( 'cProductWooCatalogo', 'fInsertProductByCategoryWooCatalogo' ));
		//add_action( 'wp_ajax_unpublish_product_woocatalogo',			array( 'cProductWooCatalogo', 'fUnpublishProductWoCatalogo' ));
		add_action( 'wp_ajax_insert_attr_product_woocatalogo',			array( 'cProductWooCatalogo', 'fInsertAttrProductWooCatalogo' ));
		add_action( 'wp_ajax_show_price_woocatalogo',					array( 'cProductWooCatalogo', 'fPriceShowWooCatalogo' ));
		add_action( 'wp_ajax_show_stock_woocatalogo',					array( 'cProductWooCatalogo', 'fStockShowWooCatalogo' ));

		//procesar por lotes el stock
		add_action('wp_ajax_procesar_lote_productos', array( 'cCatalogWooCatalog', 'procesar_lote_productos' ));



		add_action( 'woocommerce_product_options_general_product_data',	array( 'cProductWooCatalogo', 'agregar_campo_proveedor' ));
		add_action( 'woocommerce_process_product_meta',					array( 'cProductWooCatalogo', 'guardar_valor_proveedor' ));
		add_action( 'woocommerce_after_product_name',					array( 'cProductWooCatalogo', 'mostrar_valor_proveedor' ));
		
		


	}


	//Cargar Resources Vendor
	public static function load_resources() {

		//Cargar Resources Vendor
	    wp_enqueue_script('jquery.dataTables.min',		WOOCATALOGO__PLUGIN_URL . 'admin/vendor/datatables/jquery.dataTables.min.js', array('jquery'), '1.11.3');
	    wp_enqueue_script('dataTables.responsive.min',	WOOCATALOGO__PLUGIN_URL . 'admin/vendor/datatables/dataTables.responsive.min.js', array('jquery'), '2.2.9');
	    wp_enqueue_script('dataTables.select.min',		WOOCATALOGO__PLUGIN_URL . 'admin/vendor/datatables/dataTables.select.min.js', array('jquery'), '1.3.3');
	    wp_enqueue_script('dataTables.buttons.min',		WOOCATALOGO__PLUGIN_URL . 'admin/vendor/datatables/dataTables.buttons.min.js', array('jquery'), '2.1.0');
	    wp_enqueue_script('pdfmake.min.js',				WOOCATALOGO__PLUGIN_URL . 'admin/vendor/datatables/pdfmake.min.js', array('jquery'), '1.0');
	    wp_enqueue_script('vfs_fonts',					WOOCATALOGO__PLUGIN_URL . 'admin/vendor/datatables/vfs_fonts.js', array('jquery'), '1.0');
	    wp_enqueue_script('jszip.min',					WOOCATALOGO__PLUGIN_URL . 'admin/vendor/datatables/jszip.min.js', array('jquery'), '1.0');
	    wp_enqueue_script('buttons.print.min',			WOOCATALOGO__PLUGIN_URL . 'admin/vendor/datatables/buttons.print.min.js', array('jquery'), '1.0');
	    wp_enqueue_script('buttons.html5.min',			WOOCATALOGO__PLUGIN_URL . 'admin/vendor/datatables/buttons.html5.min.js', array('jquery'), '1.0');
		wp_enqueue_style('jquery.dataTables.min',		WOOCATALOGO__PLUGIN_URL . 'admin/vendor/datatables/jquery.dataTables.min.css', array(), '1.11.3', 'all');
	    wp_enqueue_style('responsive.dataTables.min',	WOOCATALOGO__PLUGIN_URL . 'admin/vendor/datatables/responsive.dataTables.min.css', array(), '2.2.9', 'all');
	    wp_enqueue_style('select.dataTables.min',		WOOCATALOGO__PLUGIN_URL . 'admin/vendor/datatables/select.dataTables.min.css', array(), '1.3.3', 'all');
	    wp_enqueue_style('buttons.dataTables.min',		WOOCATALOGO__PLUGIN_URL . 'admin/vendor/datatables/buttons.dataTables.min.css', array(), '2.1.0', 'all');
	    wp_enqueue_style('font-awesome.min',			WOOCATALOGO__PLUGIN_URL . 'admin/vendor/font-awesome/font-awesome.min.css', array(), '4.7.0', 'all');
        
		//Cargar Resources Admin
        wp_enqueue_style('admin-woocatalogo',			WOOCATALOGO__PLUGIN_URL . 'admin/css/admin-woocatalogo.css', array(), '1.0', 'all');
        wp_enqueue_script('admin-woocatalogo',			WOOCATALOGO__PLUGIN_URL . 'admin/js/admin-woocatalogo.js', array('jquery'), '1.4');

		///funcion para actualizar stock
		wp_enqueue_script('script-actualizacion-stock', WOOCATALOGO__PLUGIN_URL . 'admin/js/actualizacion-stock.js', array('jquery'), '1.0', true );



        //Funciones personalizadas
		wp_localize_script('admin-woocatalogo','Global',
			array(
			    'url'    => admin_url( 'admin-ajax.php' ),
			    'nonce'  => wp_create_nonce( 'segu' )
			)
		);


	    ///funcion para actualizar stock
		wp_localize_script('script-actualizacion-stock', 'datosActualizacion',
		    array(
				'ajax_url' => admin_url('admin-ajax.php'),
				'nonce'    => wp_create_nonce('actualizar_stock_nonce')
			)
		);


		wp_localize_script('admin-woocatalogo','aUpdateJsonCatalog',			array('action' => 'update_json_catalog_woocatalogo'));
		wp_localize_script('admin-woocatalogo','aPriceShowWooCatalogo',        array('action' => 'show_price_woocatalogo'));
		wp_localize_script('admin-woocatalogo','aStockShowWooCatalogo',        array('action' => 'show_stock_woocatalogo'));
		wp_localize_script('admin-woocatalogo','aSaveConfigGlobal',            array('action' => 'save_config_woocatalogo'));
		wp_localize_script('admin-woocatalogo','aDeleteConfigGlobal',			array('action' => 'delete_config_woocatalogo'));
		wp_localize_script('admin-woocatalogo','aInsertProductoWooCatalogo',	array('action' => 'insert_product_woocatalogo'));
		wp_localize_script('admin-woocatalogo','aDeleteProductoWooCatalogo',	array('action' => 'delete_product_woocatalogo'));
		wp_localize_script('admin-woocatalogo','aUpdateStockWooCatalogo',		array('action' => 'update_stock_product_woocatalogo'));
		wp_localize_script('admin-woocatalogo','aUpdatPriceCatalogo',			array('action' => 'update_price_product_woocatalogo'));
		wp_localize_script('admin-woocatalogo','aDownLoadCSVWooCatalogo',      array('action' => 'get_csv_woocatalogo'));

		wp_localize_script('admin-woocatalogo','aSaveLicenseWooCatalogo',		array('action' => 'save_license_woocatalogo'));
		wp_localize_script('admin-woocatalogo','aInsertAttrWooCatalogo',		array('action' => 'insert_attr_product_woocatalogo'));
		wp_localize_script('admin-woocatalogo','aPreviewProductWooCatalogo',    array('action' => 'preview_product_woocatalogo'));
		



    }
	
	


}