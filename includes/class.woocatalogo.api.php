<?php
/**
 * Clases de Consulta a Api- WooCatalogo
 * @link        https://siroe.cl
 * @since       1.0.1
 * 
 * @package     base
 * @subpackage  base/include
 */


class cWooCatalogoApiRequest {

    /**
     * Get the Nexsys provider instance.
     * 
     * @return WooCatalogoNexsysProvider
     */
    public static function get_provider_instance() {
        return new WooCatalogoNexsysProvider();
    }

    /**
     * Generates CSV catalog from Nexsys provider.
     */
    public static function fGenerateCatalogCSV() {
        $all_products = [];
        
        try {
            $provider = self::get_provider_instance();
            $catalogData = $provider->getCatalog();
            if ($catalogData && is_array($catalogData)) {
               $all_products = $catalogData; 
            }
        } catch (Exception $e) {
            error_log("Error fetching catalog from Nexsys: " . $e->getMessage());
        }

        // Generate CSV content
        $csvData = [];
        $csvData[] = ['Part Number', 'Nombre Producto', 'Categoria', 'Proveedor', 'Precio', 'Stock', 'SKU'];

        foreach ($all_products as $prod) {
             $csvData[] = [
                isset($prod['part_number']) ? $prod['part_number'] : '',
                isset($prod['nombre_producto']) ? $prod['nombre_producto'] : '',
                isset($prod['categoria']) ? $prod['categoria'] : '',
                isset($prod['proveedor']) ? $prod['proveedor'] : '',
                isset($prod['precio']) ? $prod['precio'] : '0',
                isset($prod['stock']) ? $prod['stock'] : '0',
                isset($prod['sku']) ? $prod['sku'] : ''
            ];
        }

        self::fDownloadCSV($csvData, 'catalogo_consolidado.csv');
    }
    
    private static function fDownloadCSV($data, $filename) {
        if (!headers_sent()) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $filename . '";');
        }
        
        $output = fopen('php://output', 'w');
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        exit;
    }
    
    /**
     * Fetch product price and stock from Nexsys provider.
     */
    public static function fGetProductPriceStock($part_number, $sku, $proveedor) {
        $provider = self::get_provider_instance();
        
        // Standard Interface call
        $data = $provider->getProductStockPrice($part_number, $sku);
        
        // Wrap to match legacy expectation: ->data[0]->precio
        if ($data) {
             $formatted = (object) [
                 'precio' => $data['price'],
                 'stock' => $data['stock'],
                 'precioMasBajo' => $data['price'],
                 'stockMasBajo' => $data['stock']
             ];
             return (object) ['data' => [$formatted]];
        }
        
        return (object) ['data' => []];
    }
       
    /**
     * Get Catalog for JSON update.
     */
    public static function fGetCatalogWooCatalogo() {
        $merged_data = [];

        try {
            $provider = self::get_provider_instance();
            $data = $provider->getCatalog();
            if ($data && is_array($data)) {
                foreach ($data as $item) {
                     // Cast to object for legacy code compatibility ($item->param)
                     $merged_data[] = (object) $item;
                }
            }
        } catch (Exception $e) {
             error_log("Error in fGetCatalogWooCatalogo: " . $e->getMessage());
        }

        return (object) ['data' => $merged_data];
    }

    public static function fGetCatalogExtendWooCatalogo($part_number) {
        $provider = self::get_provider_instance();
        $details = $provider->getProductDetails($part_number);
        
        if ($details) {
            // If details is already strict format
             if (is_array($details) && isset($details['data'])) {
                $items = [];
                foreach ($details['data'] as $d) $items[] = (object)$d;
                return (object) ['data' => $items];
             }

             // If details is single product array/object
             $item = is_array($details) ? (object)$details : $details;
             return (object) ['data' => [$item]];
        }
        
        return (object) ['data' => []];
    }

    // Config Helpers
    public static function fGetConfigValuesWooCatalogo (){
        global $wpdb;
        $query = "SELECT * FROM {$wpdb->prefix}woocatalogo";
        $result = $wpdb->get_results($query, ARRAY_A);
        if (empty($result)) {
            $result = array();
        }
        return $result;
    }
    
    // Compatibility aliases
    public static function obtaining_data_product_api($part_number, $sku, $proveedor) {
         return self::fGetProductPriceStock($part_number, $sku, $proveedor);
    }

    public static function obtener_datos_producto_api($part_number, $sku, $proveedor) {
        return self::fGetProductPriceStock($part_number, $sku, $proveedor);
   }
}
