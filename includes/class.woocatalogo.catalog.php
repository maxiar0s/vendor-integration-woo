<?php
if (!defined('ABSPATH')) exit;
/**
 * Clases para el catalogo - WooCatalogo
 * @link        https://siroe.cl
 * @since       1.0.0
 * 
 * @package     base
 * @subpackage  base/include
 */

class cCatalogWooCatalog {


    public static function fGetCatalogCSV($nonce) {

        // Verificar nonce para seguridad desde $_GET
        $nonce = isset($_GET['nonce']) ? sanitize_text_field($_GET['nonce']) : '';
        if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
            wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
        }
    
        // Delegate CSV generation and download to the API class
        (new cWooCatalogoApiRequest())->fGenerateCatalogCSV();
    }


    public static function fUpdateJsonCatalog($nonce = null, $is_cron = false) {

        if (!$is_cron) {
            $nonce = sanitize_text_field( $_POST['nonce'] );
            if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
                wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
            }
            if (!current_user_can('manage_options')) {
                wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
            }
        }

        $awooArray = [];
        $oCatalogWooCatalogo = (new cWooCatalogoApiRequest())->fGetCatalogWooCatalogo();


        // Verificar que la propiedad "data" existe y es un array
        if (isset($oCatalogWooCatalogo->data) && is_array($oCatalogWooCatalogo->data)) {
            foreach ($oCatalogWooCatalogo->data as $producto) {

                // Buscar el producto por partnumber o SKU
                $existingProductId = !empty($producto->part_number) 
                ? wc_get_product_id_by_sku($producto->part_number) 
                : null;

                if (empty($existingProductId) && !empty($producto->sku)) {
                    $existingProductId = wc_get_product_id_by_sku($producto->sku);
                }


                $sRes = ($existingProductId) ? "SI" : "NO" ;
                $awooArray[] = [
                    'woo'           => $sRes,
                    'id'            => $producto->id, 
                    'sku'           => $producto->sku, 
                    'mpn'           => $producto->part_number,
                    'nombre'        => $producto->nombre_producto,
                    'precio'        => $producto->precio,
                    'stock'         => $producto->stock,
                    'categoria'     => $producto->categoria,
                    'subcategoria'  => $producto->subcategoria,
                    'proveedor'     => $producto->proveedor,
                    'creado'        => $producto->created_at,
                    'actualizado'   => $producto->updated_at,
                    'acciones'      => "

                        <button type=\"button\" title=\"Ver Precio\" onclick=\"fPriceShowWooCatalogo('{$producto->part_number}')\"><span class=\"dashicons dashicons-money-alt\"></span></button>
                        
                        <button type=\"button\" title=\"Ver Stock\" onclick=\"fStockShowWooCatalogo('{$producto->part_number}')\"><span class=\"dashicons dashicons-products\"></span></button>

                        <button type=\"button\" title=\"Insertar Producto\" onclick=\"fInsertProductWooCatalogo('{$producto->part_number}','{$producto->proveedor}' )\"><span class=\"dashicons dashicons-insert\"></span></button>

                        <button type=\"button\" title=\"Eliminar Producto\" onclick=\"fDeleteProductWooCatalogo('{$producto->part_number}')\"><span class=\"dashicons dashicons-remove\"></span></button>

                        <button type=\"button\" title=\"Actualizar Ficha Técnica\" onclick=\"fUpdateAtrrWooCatalogo('{$producto->part_number}')\"><span class=\"dashicons dashicons-database-add\"></span></button>

                        <button type=\"button\" title=\"Vista Previa\" onclick=\"fPreviewProductWooCatalogo('{$producto->part_number}')\"><span class=\"dashicons dashicons-share-alt2\"></span></button>

                    "

                    
                ];
            }
        } else {
            echo "Usted no tiene acceso a nuestro servicio, por favor verifique su licencia con contacto@josecortesia.cl";
        }

        $jCatalog= json_encode(array('data' => $awooArray));
        file_put_contents(WOOCATALOGO__PLUGIN_DIR.'/admin/dataWooCatalogo/dataWooCatalogo.json', $jCatalog);
        echo "Actualización del catálogo completa";
        wp_die();
    }


    
    public static function fUpdatePriceWooCatalogo($nonce = null, $is_cron = false) {

        if (!$is_cron) {
            $nonce  = sanitize_text_field( $_POST['nonce'] );
            if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
                wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
            }
            if (!current_user_can('manage_options')) {
                wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
            }
        }

        $oTagsDB = (new cWooCatalogoApiRequest())->fGetConfigValuesWooCatalogo();
        
        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1, // Obtener todos los productos
        );
        
        $products_query = new WP_Query($args);
        
        if ($products_query->have_posts()) {
            while ($products_query->have_posts()) {

                $products_query->the_post();
        
                // Obtener el objeto WC_Product
                $product = wc_get_product(get_the_ID());
        
                // Datos del producto
                $part_number = $product->get_sku();
                $product_id = wc_get_product_id_by_sku($part_number);
                $proveedor = get_post_meta($product_id, '_proveedor', true);
                $sku_proveedor = get_post_meta($product_id, '_sku_proveedor', true);

                if ($product_id && $proveedor) {

                    $product = wc_get_product($product_id);
                    $oGetPriceWooCatalogo = (new cWooCatalogoApiRequest())->fGetProductPriceStock($part_number, $sku_proveedor, $proveedor);

                    //proteger el precio
                    $current_price = 9000000;
                    // Obtener el nuevo precio desde el webservice
                    $new_price = isset($oGetPriceWooCatalogo->data[0]->precio) ? $oGetPriceWooCatalogo->data[0]->precio : null;
                    
                    // Comprobar si el nuevo precio es válido (no nulo y no igual a cero)
                    $checkprecio = (!is_null($new_price) && $new_price > 0) ? $new_price : $current_price;
                    $price = floatval(sanitize_text_field($checkprecio));

                    $etiquetas = get_the_terms ( $product_id, 'product_tag' );
                    $etiquetas_precio = isset($etiquetas[0]) ? $etiquetas[0]->name : '';

                    foreach ($oTagsDB  as $bd_res) {

                        if ($bd_res['etiquetas_precio'] == $etiquetas_precio) {

                            if (empty($oTagsDB)) {

                                $dolar    = 99000000;
                                $fmult = 999999999;
                                $comision = 999999;
                                

                            }else{

                                $dolar    = floatval($bd_res['dolar']);
                                $fmult = floatval($bd_res['fmult']);
                                $comision = floatval($bd_res['comision']);

                            }

                            $priceVenta = round(($price*$dolar)*$fmult*$comision,0,PHP_ROUND_HALF_UP);
                            $product->set_regular_price($priceVenta);
                            $product->save(); 
              
                        }
    
                    }

                } else {
                    continue;
                }
            }
            echo "Actualización de precios completa";
            
            wp_reset_postdata(); // Restaurar los datos originales del loop de WordPress
        } else {
            echo "No se encontraron productos publicados";
        }
        
        die(); 
    }

    public static function fUpdateStockWooCatalogo($nonce = null, $is_cron = false) {

        if (!$is_cron) {
            $nonce = sanitize_text_field( $_POST['nonce'] );
            if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
                wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
            }
            if (!current_user_can('manage_options')) {
                wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
            }
        }

        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => -1, // Obtener todos los productos
        );
        
        $products_query = new WP_Query($args);
        
        if ($products_query->have_posts()) {
            while ($products_query->have_posts()) {
                $products_query->the_post();
        
                // Obtener el objeto WC_Product
                $product = wc_get_product(get_the_ID());
        
                // Acceder al SKU del producto
                $part_number = $product->get_sku();
                $product_id = wc_get_product_id_by_sku($part_number);
                $proveedor = get_post_meta($product_id, '_proveedor', true);
                $sku_proveedor = get_post_meta($product_id, '_sku_proveedor', true);

                if ($product_id && $proveedor) {
                    $product = wc_get_product($product_id);
                    $oGetStockWooCatalogo = (new cWooCatalogoApiRequest())->fGetProductPriceStock($part_number, $sku_proveedor, $proveedor);

                    if (isset($oGetStockWooCatalogo->data[0]->stock) && $oGetStockWooCatalogo->data[0]->stock != 0) {
                        $stock = $oGetStockWooCatalogo->data[0]->stock;
                        $product->set_stock_status('instock');
                        $product->set_stock_quantity($stock);
                        $product->save();

                    }else{
                        $stock = 0;
                        $product->set_stock_status('outofstock');
                        $product->set_stock_quantity($stock);
                        $product->save();
                    }
                } else {
                    continue;
                }
            }
            echo "Actualización de stock completa";
            wp_reset_postdata(); // Restaurar los datos originales del loop de WordPress
        } else {
            echo "No se encontraron productos publicados";
        }
        
    
        die();

    }

    public static function procesar_lote_productos() {
        check_ajax_referer('actualizar_stock_nonce', 'nonce');
    
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permisos insuficientes.');
        }
    
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $tamano_lote = isset($_POST['tamano_lote']) ? intval($_POST['tamano_lote']) : 50;
    
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => $tamano_lote,
            'offset'         => $offset,
            'post_status'    => 'publish',
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_proveedor',
                    'value'   => '',
                    'compare' => '!=',
                ),
                array(
                    'key'     => '_sku_proveedor',
                    'value'   => '',
                    'compare' => '!=',
                ),
            ),
        );
        
    
        $query = new WP_Query($args);
        $productos = $query->posts;
        $total_productos = $query->found_posts;
        $actualizados = 0;
        $oTagsDB = (new cWooCatalogoApiRequest())->fGetConfigValuesWooCatalogo();
        $new_price = 0; // Initialize

        foreach ($productos as $producto) {
            
            $product = wc_get_product($producto->ID);
            if (!$product) {
                // Manejar el caso donde el producto no se encuentra
                continue;
            }else{
                $part_number = $product->get_sku();
                $sku_proveedor = get_post_meta($producto->ID, '_sku_proveedor', true);
                $proveedor = get_post_meta($producto->ID, '_proveedor', true);
                
                if ($sku_proveedor && $proveedor) {
                    $datos_api = (new cWooCatalogoApiRequest())->obtener_datos_producto_api($part_number, $sku_proveedor, $proveedor);
                    
                    // Convert object return to array for array access if needed, or use object access
                    // The returned data from obter_datos_producto_api is object->data array->objects
                    
                    if (!empty($datos_api->data)) {
                        $producto_api = $datos_api->data[0]; // Access as object
                        
                        // Handle Stock
                        if (isset($producto_api->stock)) {
                            if ($producto_api->stock != 0) {
                                $stock = $producto_api->stock;
                                $product->set_stock_status('instock');
                                $product->set_stock_quantity($stock);
                            }else{
                                $stock = 0;
                                $product->set_stock_status('outofstock');
                                $product->set_stock_quantity($stock);
                            }
                            $product->save();
                        }
                        
                        // Handle Price
                        if (isset($producto_api->precio)) {
                            $current_price = 9000000;
                            $new_price = $producto_api->precio;
                            $checkprecio = (!is_null($new_price) && $new_price > 0) ? $new_price : $current_price;
                            $price = floatval(sanitize_text_field($checkprecio));

                            $etiquetas = get_the_terms( $producto->ID, 'product_tag' );
                            $etiquetas_precio = isset($etiquetas[0]) ? $etiquetas[0]->name : '';

                            foreach ($oTagsDB  as $bd_res) {
                                if ($bd_res['etiquetas_precio'] == $etiquetas_precio) {
                                    $dolar    = floatval($bd_res['dolar']);
                                    $fmult = floatval($bd_res['fmult']);
                                    $comision = floatval($bd_res['comision']);

                                    $priceVenta = round(($price*$dolar)*$fmult*$comision,0,PHP_ROUND_HALF_UP);
                                    $product->set_regular_price($priceVenta);
                                    $product->save(); 
                                }
                            }
                        }
                        
                        $actualizados++;
                    }
                }
            }
        }
    
        wp_send_json_success(array(
            'total'       => $total_productos,
            'actualizados' => $actualizados,
            'datos_api' => isset($datos_api) ? $datos_api : [],
            'datos_precio'=> $new_price,
            'etiqueta_producto' => isset($etiquetas_precio) ? $etiquetas_precio : '',
            'etiqueta_bd'      => isset($bd_res['etiquetas_precio']) ? $bd_res['etiquetas_precio'] : '',
        ));
    }

/*
    public static function fUpdateJsonExtendCatalogWooCatalogo($nonce) {

        $nonce = sanitize_text_field( $_POST['nonce'] );
        
        if (!wp_verify_nonce($nonce, 'segu')) {
            die ("Ajaaaa, estas de noob!");
        }
    }
*/
    




    
}