<?php
if (!defined('ABSPATH')) exit;

/**
 * Clases para borrar productos - WooCatalogo
 * @link        https://siroe.cl
 * @since       1.0.0
 * 
 * @package     base
 * @subpackage  base/include
 */


class cProductWooCatalogo {

    public static function fDeleteProductWooCatalogo($nonce) {

        $nonce  = sanitize_text_field( $_POST['nonce'] );
        if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
            wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
        }
        global  $wpdb;
        $part_number   = sanitize_text_field( $_POST['part_number'] );

        $queryIDPost = $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value = %s", $part_number));

        if ($queryIDPost == null) {
            echo "Este producto no esta en Woocommerce"; 
        }else{
            wp_delete_post(intval($queryIDPost), true);
            echo "Eliminado de Woocommerce";
        }
        wp_die();
    }


    
    public static function fInsertAttrProductWooCatalogo($nonce) {

        $nonce  = sanitize_text_field( $_POST['nonce'] );
        
        if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
            wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
        }

        
        $part_number   =  sanitize_text_field( $_POST['part_number'] );
        $oUpdateProductWooCatalogo = (new cWooCatalogoApiRequest())->fGetCatalogExtendWooCatalogo($part_number);
        
        // Verificamos si existe la clave 'data' en el objeto
        if (isset($oUpdateProductWooCatalogo->data) && is_array($oUpdateProductWooCatalogo->data)) {
            // Recorremos el array de productos dentro de 'data'
            foreach ($oUpdateProductWooCatalogo->data as $producto) {
                //.aqui tengo que preguntar, si existe el partnumber, paso, sino existe el partnumber lo busco por el sku.

                // Buscar el producto por partnumber o SKU
                $existingProductId = !empty($producto->part_number) 
                    ? wc_get_product_id_by_sku($producto->part_number) 
                    : null;

                if (empty($existingProductId) && !empty($producto->sku)) {
                    $existingProductId = wc_get_product_id_by_sku($producto->sku);
                }
                

                if ($existingProductId) {

                    $product = wc_get_product($existingProductId); 

                    // Si existe en icecat
                    if (!empty($producto->caracteristicas)) {
                        echo "Con Atributos - ";
                    } else {
                        echo "Sin Atributos - ";
                    }

                    // Common update logic using the helper method
                    self::updateContentAndAttributes($product, $producto);
                    echo "Producto Actualizado";
                    
                } else {
                    
                    echo "Este producto NO está publicado en su tienda";
                    continue;

                }//IF del si el producto no existe
            }

        } else {
            echo "No se encontraron datos de productos.";
        }

        wp_die();
    }


    public static function fInsertProductWooCatalogo($nonce) {
        
        $nonce  = sanitize_text_field( $_POST['nonce'] );
        if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
            wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
        }
        $part_number   =  sanitize_text_field( $_POST['part_number'] );
        $proveedor   =  sanitize_text_field( $_POST['proveedor'] );

        $oCreateProductWooCatalogo = (new cWooCatalogoApiRequest())->fGetCatalogExtendWooCatalogo($part_number);
        
        $msg = ""; 

        // Get global settings
        $config_woocatalogo = (new cWooCatalogoApiRequest())->fGetConfigValuesWooCatalogo();
        $dolar = 1; $comision = 1; $ganancia = 1;

        if ($config_woocatalogo) {
            $dolar = !empty($config_woocatalogo[0]['dolar']) ? floatval($config_woocatalogo[0]['dolar']) : 1;
            $comision = !empty($config_woocatalogo[0]['comision']) ? floatval($config_woocatalogo[0]['comision']) : 1;
            $ganancia = !empty($config_woocatalogo[0]['fmult']) ? floatval($config_woocatalogo[0]['fmult']) : 1;
        } 

        // Verificamos si existe la clave 'data' en el objeto
        if (isset($oCreateProductWooCatalogo->data) && is_array($oCreateProductWooCatalogo->data)) {
            $found_provider = false;
            $items_count = count($oCreateProductWooCatalogo->data);
            error_log("Debug WooCatalogo: API returned {$items_count} items for PartNumber: {$part_number}");

            // Recorremos el array de productos dentro de 'data'
            foreach ($oCreateProductWooCatalogo->data as $index => $producto) {
                
                $prod_pn = isset($producto->part_number) ? $producto->part_number : 'N/A';
                $prod_prov = isset($producto->proveedor) ? $producto->proveedor : 'N/A';
                
                error_log("Debug WooCatalogo Item [{$index}]: SKU: {$prod_pn} | Provider (API): '{$prod_prov}' vs Requested: '{$proveedor}'");
                
                // Inspect the structure of the first item to debug missing properties
                if ($index === 0) {
                     error_log("Debug WooCatalogo Item [0] Full Structure: " . print_r($producto, true));
                }

                // Check for missing or empty required fields
                if (empty($producto->part_number) || empty($producto->proveedor)) {
                    error_log("Debug WooCatalogo: Skipping item [{$index}] due to empty part_number or proveedor");
                    continue;
                }

                // Only process the requested provider
                // Using loose comparison after trim to handle potential invisible characters or type mismatches
                if (strcasecmp(trim($producto->proveedor), trim($proveedor)) === 0) {
                    $found_provider = true;
                    error_log("Debug WooCatalogo: Match found for provider '{$proveedor}'");
                    
                    $existingProductId = wc_get_product_id_by_sku($producto->part_number);

                    if ($existingProductId > 0) {
                        $msg = "Este producto ya está publicado en su tienda";
                        error_log("Debug WooCatalogo: Product already exists (ID: {$existingProductId})");
                    } else {
                        error_log("Debug WooCatalogo: Attempting to create product...");
                        
                        $product = new WC_Product_Simple();
                        // Info Basica del producto 
                        $product->set_name(isset($producto->nombre_producto) ? $producto->nombre_producto : 'Sin Nombre');
                        $product->set_sku($producto->part_number);

                        $price = isset($producto->precio) && is_numeric($producto->precio) ? floatval($producto->precio) : 0;
                        $moneda = isset($producto->moneda) ? $producto->moneda : 'USD';
                        
                        // If currency is CLP, do not apply Dolar conversion (factor = 1)
                        $tipo_cambio = ($moneda === 'CLP') ? 1 : $dolar;

                        // Calculate final price: Price * Type_Exchange * Margin * Commission
                        if ($price > 0) {
                            $final_price = ceil($price * $tipo_cambio * $ganancia * $comision);
                        } else {
                            $final_price = 99999999;
                        }

                        $product->set_regular_price($final_price);
                        $product->set_manage_stock(true);
                        $product->set_stock_status('instock');
                        $product->set_stock_quantity(1);
                        
                        $product->save(); // Save first to get ID
                        
                        // Common update logic
                        self::updateContentAndAttributes($product, $producto);
                        
                        $msg = "Producto creado correctamente";
                        error_log("Debug WooCatalogo: Product created successfully (ID: " . $product->get_id() . ")");
                    }
                    break; // Stop loop after finding the match
                }
            }
            
            if (!$found_provider && empty($msg)) {
                $msg = "No se encontró el producto para el proveedor especificado: " . $proveedor;
                error_log("Debug WooCatalogo: No match found for provider '{$proveedor}' in API response.");
            }

        } else {
            $msg = "No se encontraron datos de productos en la API (data empty). Part Number: " . $part_number;
            error_log("Debug WooCatalogo: API returned no data for PartNumber: {$part_number}");
        }
        
        echo $msg;
        wp_die();
    }

    /**
     * Helper method to update product content, attributes, meta, and images from API data.
     */
    private static function updateContentAndAttributes($product, $producto) {
        // Description and Dimensions
        if (!empty($producto->caracteristicas)) {
            $product->set_short_description($producto->descripcion);
            $product->set_description($producto->htmlContent);

            // Parsing Dimensions
            $width = 0; $length = 0; $height = 0; $weight = 0;
            foreach ($producto->caracteristicas as $caracteristica) {
                foreach ($caracteristica->propiedades as $grupo_propiedades) {
                    if ($grupo_propiedades->grupo == 'Empaquetado' || $grupo_propiedades->grupo == 'Peso y dimensiones') {
                        foreach ($grupo_propiedades->caracteristicas_grupo as $empaquetado) {
                            switch ($empaquetado->nombre) { 
                                case 'Ancho del paquete':
                                case 'Ancho':
                                    $width = cProductWooCatalogo::convertToCm($empaquetado->presentacion);
                                    break;
                                case 'Profundidad del paquete':
                                case 'Profundidad':
                                    $length = cProductWooCatalogo::convertToCm($empaquetado->presentacion);
                                    break;
                                case 'Altura del paquete':
                                case 'Altura':
                                    $height = cProductWooCatalogo::convertToCm($empaquetado->presentacion);
                                    break;
                                case 'Peso del paquete':
                                case 'Peso':
                                    $weight = cProductWooCatalogo::convertToKg($empaquetado->presentacion);
                                    break;
                            }
                        }
                    }
                }
            }
            $product->set_weight($weight);
            $product->set_height($height);
            $product->set_width($width);
            $product->set_length($length);
        } else {
            $product->set_short_description($producto->descripcion);
            $product->set_description($producto->descripcion);
        }

        // Attributes (Marca)
        $attributes = array();
        $atributos = array(
            array(
                'name' => 'Marca',
                'options' => array($producto->marca),
                'position' => 1,
                'visible' => true,
                'variation' => true
            )
        );
        foreach ($atributos as $atributo) {
            $attribute = new WC_Product_Attribute();
            $attribute->set_name($atributo['name']);
            $attribute->set_options($atributo['options']);
            $attribute->set_position($atributo['position']);
            $attribute->set_visible($atributo['visible']);
            $attribute->set_variation($atributo['variation']);
            $attributes[] = $attribute;
        }
        $product->set_attributes($attributes);
        $product->save();

        // Meta Data
        update_post_meta($product->get_id(), '_proveedor', $producto->proveedor);
        update_post_meta($product->get_id(), '_sku_proveedor', $producto->sku);

        // Terms
        wp_set_object_terms($product->get_id(), 'Bodega Externa', 'product_cat', true);
        wp_set_object_terms($product->get_id(), 'Bodega Externa', 'product_tag', true);

        // Remove Uncategorized
        $id_category_sin = get_term_by('name', 'Sin categorizar', 'product_cat');
        $id_category_un = get_term_by('name', 'Uncategorized', 'product_cat');
        $idcategory_sin = isset($id_category_sin->term_id) ? $id_category_sin->term_id : 0;
        $idcategory_un = isset($id_category_un->term_id) ? $id_category_un->term_id : 0;
        
        if($idcategory_sin) wp_remove_object_terms($product->get_id(), $idcategory_sin, 'product_cat');
        if($idcategory_un) wp_remove_object_terms($product->get_id(), $idcategory_un, 'product_cat');

        // Images
        if (!empty($producto->caracteristicas)) {
            $aImagenesData = null;
            $aGalleryData = null;
            foreach ($producto->caracteristicas as $caracteristica) {
                $aImagenesData = $caracteristica->imagenes;
                $aGalleryData = $caracteristica->galeria;
            }
            if ($aImagenesData && $aGalleryData) {
                cProductWooCatalogo::asignarImagenesProducto($product->get_id(), $aImagenesData, $aGalleryData);
            }
        }
        
        // Handle root-level image (fallback or primary if no complex features)
        if (!empty($producto->imagen)) {
             error_log("Debug WooCatalogo: Found root image for product ID " . $product->get_id() . ": " . $producto->imagen);
             $image_id = cProductWooCatalogo::descargarSubirImagen($producto->imagen, $product->get_id());
             if ($image_id) {
                 set_post_thumbnail($product->get_id(), $image_id);
                 error_log("Debug WooCatalogo: Set post thumbnail ID: " . $image_id);
             }
        }
    }

    public static function asignarImagenesProducto($product_new_id, $aImagenesData, $aGalleryData) {
        // Verificar si hay variantes en el producto
        if (empty($aImagenesData) || empty($aGalleryData)) {
            return false;
        }
    
        // Obtener la URL de la imagen principal del producto
        $main_image_url = $aImagenesData->grande; // Usando la URL 'grande' como imagen principal
    
        // Descargar la imagen principal y guardarla en la biblioteca multimedia de WordPress
        $main_image_id = cProductWooCatalogo::descargarSubirImagen($main_image_url, $product_new_id);
    
        // Asignar la imagen principal al producto
        set_post_thumbnail($product_new_id, $main_image_id);
    
        // Obtener las URLs de las imágenes de la galería del producto
        $gallery_image_urls = array();
        foreach ($aGalleryData as $image_data) {
            $gallery_image_urls[] = $image_data->grande; // Usando la URL 'grande' de cada imagen de la galería
        }
    
        // Descargar y subir las imágenes de la galería a la biblioteca multimedia de WordPress
        $gallery_image_ids = array();
        foreach ($gallery_image_urls as $gallery_image_url) {
            $gallery_image_id = cProductWooCatalogo::descargarSubirImagen($gallery_image_url, $product_new_id);
            $gallery_image_ids[] = $gallery_image_id;
        }
    
        // Asignar las imágenes de la galería al producto
        $product = wc_get_product($product_new_id);
        $product->set_gallery_image_ids($gallery_image_ids);
        $product->save();
    
        return true;
    }
    
    public static function descargarSubirImagen($image_url, $post_id) {
        // URL de respaldo si la original falla
        $fallback_url = 'https://picsum.photos/512/512';
        
        // Verificar si la URL de la imagen está vacía
        if (empty($image_url)) {
            error_log("Advertencia: URL de la imagen está vacía, se usará la URL de respaldo.");
            $image_url = $fallback_url;
        }
    
        // Obtener el directorio de subida de WordPress
        $upload_dir = wp_upload_dir();
        $image_name = basename(cProductWooCatalogo::generar_nombre_aleatorio().".jpg");
        $image_path = $upload_dir['path'] . '/' . $image_name;
    
        // Descargar la imagen desde la URL usando wp_remote_get
        $response = wp_remote_get($image_url, array('timeout' => 30, 'sslverify' => true));
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            // Si la URL falla, usar la URL de respaldo (picsum.photos)
            error_log("Error: No se pudo descargar la imagen desde la URL: $image_url. Usando URL de respaldo.");
            $response = wp_remote_get($fallback_url, array('timeout' => 30, 'sslverify' => true));
    
            // Si la URL de respaldo también falla, registrar el error y detener
            if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                error_log("Error crítico: No se pudo descargar la imagen de la URL de respaldo: $fallback_url.");
                return false; // O algún valor que indique el fallo
            }
        }
        $image_content = wp_remote_retrieve_body($response);
    
        // Guardar la imagen descargada en el directorio de uploads
        file_put_contents($image_path, $image_content);
    
        // Obtener el tipo de archivo de la imagen
        $wp_filetype = wp_check_filetype($image_name, null);
    
        // Configurar los datos del archivo adjunto
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($image_name),
            'post_content' => '',
            'post_status' => 'inherit'
        );
    
        // Subir el archivo adjunto a la biblioteca multimedia de WordPress
        $attachment_id = wp_insert_attachment($attachment, $image_path, $post_id);
    
        // Generar los metadatos del archivo adjunto
        $attach_data = wp_generate_attachment_metadata($attachment_id, $image_path);
    
        // Asignar los metadatos al archivo adjunto
        wp_update_attachment_metadata($attachment_id, $attach_data);
    
        return $attachment_id;
    }
    
    
    public static function generar_nombre_aleatorio() {
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $caracteres_longitud = strlen($caracteres);
        $nombre_aleatorio = '';
        $longitud = 10;
    
        for ($i = 0; $i < $longitud; $i++) {
            $indice_caracter = rand(0, $caracteres_longitud - 1);
            $nombre_aleatorio .= $caracteres[$indice_caracter];
        }
    
        return $nombre_aleatorio;
    }

    public static function convertToCm($dimension) {
        // Detectar si la unidad es mm y convertir a cm
        if (strpos($dimension, 'mm') !== false) {
            $dimension = str_replace('mm', '', $dimension);
            $dimension = floatval($dimension) / 10; // Convertir mm a cm
        }

        return floatval($dimension);
    }

    public static function convertToKg($peso) {
        // Detectar si la unidad es g y convertir a kg
        if (strpos($peso, 'g') !== false) {
            $peso = str_replace('g', '', $peso);
            $peso = floatval($peso) / 1000; // Convertir g a kg
        }

        return floatval($peso);
    }
    public static function fPriceShowWooCatalogo($nonce) {

        $nonce = sanitize_text_field( $_POST['nonce'] );
        if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
            wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
        }
        
        $part_number   =  sanitize_text_field( $_POST['part_number'] );
        // Updated to use fGetProductPriceStock. Assumes default provider context or searches all if provider unknown.
        // Frontend likely needs to send provider if we want specificity.
        // For now, passing empty strings for sku/provider to method which should be handled.
        
        $oPriceWooCatalogo = (new cWooCatalogoApiRequest())->fGetProductPriceStock($part_number, '', ''); 
        
        echo json_encode($oPriceWooCatalogo);
        wp_die();
    }
    public static function fStockShowWooCatalogo($nonce) {

        $nonce = sanitize_text_field( $_POST['nonce'] );
        if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
            wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
        }

        $part_number   =  sanitize_text_field( $_POST['part_number'] );
        // Updated to use fGetProductPriceStock
        $oStockWooCatalogo = (new cWooCatalogoApiRequest())->fGetProductPriceStock($part_number, '', '');
        
        echo json_encode($oStockWooCatalogo);
        wp_die();
    }
    /*Previsualizacion de Productos*/
    public static function fPreviewProductWooCatalogo(){

        $nonce = sanitize_text_field( $_POST['nonce'] );
        if (!wp_verify_nonce($nonce, 'woocatalogo_admin')) {
            wp_die(__('Security check failed.', 'vendor-integration-woo'), 403);
        }
        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized access.', 'vendor-integration-woo'), 403);
        }

        $part_number   =  sanitize_text_field( $_POST['part_number'] );
        global  $wpdb;

        $queryIDPost = $wpdb->get_var( $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_sku' AND meta_value = %s", $part_number));

        if ($queryIDPost == null) {
            echo "Este producto no esta en Woocommerce";
        }else{
            echo get_permalink($queryIDPost);
        }
        wp_die();
    }
    /*
    public static function fMultiInsertProducttWooCatalogo($nonce) {
    
        $nonce = sanitize_text_field( $_POST['nonce'] );
        if (!wp_verify_nonce($nonce, 'segu')) {
            die ("Ajaaaa, estas de noob!");
        }

    }

    public static function fInsertProductByCategoryWooCatalogo($nonce) {

        $nonce  = sanitize_text_field( $_POST['nonce'] );
        if (!wp_verify_nonce($nonce, 'segu')) {
            die ("Ajaaaa, estas de noob!");
        }

    }

    public static function fCreateLinkProductWooCatalogo($nonce) {

        $nonce = sanitize_text_field( $_POST['nonce'] );
        if (!wp_verify_nonce($nonce, 'segu')) {
            die ("Ajaaaa, estas de noob!");
        }
    }

    public static function fUnpublishProductWoCatalogo($nonce) {

        $nonce = sanitize_text_field( $_POST['nonce'] );

        if (!wp_verify_nonce($nonce, 'segu')) {
            die ("Ajaaaa, estas de noob!");
        }

    }
    public static function fMultiDeleteProductWooCatalogo($nonce) {

        $nonce = sanitize_text_field( $_POST['nonce'] );
        if (!wp_verify_nonce($nonce, 'segu')) {
            die ("Ajaaaa, estas de noob!");
        }
    }

*/

    // Agregar campo personalizado al metabox de datos generales del producto
    public static function agregar_campo_proveedor() {
        global $woocommerce, $post;
    
        echo '<div class="options_group">';
    
        // Campo de entrada para el proveedor
        woocommerce_wp_text_input(
            array(
                'id' => '_proveedor',
                'label' => __('Proveedor', 'woocommerce'),
                'placeholder' => __('Ingrese el proveedor', 'woocommerce'),
                'desc_tip' => 'true',
                'description' => __('Ingrese el nombre del proveedor para este producto. No lo cambie sino está seguro.', 'woocommerce')
            )
        );
    
        echo '</div>';
        echo '<div class="options_group">';
    
        // Campo de entrada para el proveedor
        woocommerce_wp_text_input(
            array(
                'id' => '_sku_proveedor',
                'label' => __('Sku Proveedor', 'woocommerce'),
                'placeholder' => __('Ingrese el SKU del proveedor', 'woocommerce'),
                'desc_tip' => 'true',
                'description' => __('Ingrese el sku del proveedor para este producto. No lo cambie sino está seguro.', 'woocommerce')
            )
        );
    
        echo '</div>';
    }

    // Guardar valor del proveedor al guardar el producto
    public static function guardar_valor_proveedor($post_id) {
        $producto = wc_get_product($post_id);
    
        $proveedor = isset($_POST['_proveedor']) ? sanitize_text_field($_POST['_proveedor']) : '';
        $producto->update_meta_data('_proveedor', $proveedor);
        $sku_proveedor = isset($_POST['_sku_proveedor']) ? sanitize_text_field($_POST['_sku_proveedor']) : '';
        $producto->update_meta_data('_sku_proveedor', $sku_proveedor);
        $producto->save();
    }
    
    // Mostrar valor del proveedor en el frontend del administrador de productos
    public static function mostrar_valor_proveedor() {
        global $post;
    
        $producto = wc_get_product($post->ID);
        $proveedor = $producto->get_meta('_proveedor');
        $sku_proveedor = $producto->get_meta('_sku_proveedor');
    
        if ($proveedor) {
            echo '<div class="product-proveedor">';
            echo '<strong>' . __('Proveedor:', 'woocommerce') . '</strong> ' . esc_html($proveedor);
            echo '</div>';
        }
        if ($sku_proveedor) {
            echo '<div class="sku-product-proveedor">';
            echo '<strong>' . __('Sku Proveedor:', 'woocommerce') . '</strong> ' . esc_html($sku_proveedor);
            echo '</div>';
        }
    }
}
