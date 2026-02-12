<?php
if (!defined('ABSPATH'))
    exit;
/**
 * Clases de Consulta a Api- WooCatalogo
 * @link        https://siroe.cl
 * @since       1.0.1
 * 
 * @package     base
 * @subpackage  base/include
 */


class cVendorIntegrationApiRequest
{

    private static function normalizeProviderName($provider_name)
    {
        $normalized = strtolower(trim((string) $provider_name));
        return preg_replace('/[^a-z0-9]/', '', $normalized);
    }

    private static function isProviderCompatible($provider_name)
    {
        if ($provider_name === null || $provider_name === '') {
            return true;
        }

        $provider = self::get_provider_instance();
        $current_slug = self::normalizeProviderName($provider->getProviderSlug());
        $incoming = self::normalizeProviderName($provider_name);

        if ($incoming === '') {
            return true;
        }

        return $incoming === $current_slug;
    }

    /**
     * Get the Nexsys provider instance.
     * 
     * @return VendorIntegrationNexsysProvider
     */
    public static function get_provider_instance()
    {
        return new VendorIntegrationNexsysProvider();
    }

    /**
     * Generates CSV catalog from Nexsys provider.
     */
    public static function fGenerateCatalogCSV()
    {
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

    private static function fDownloadCSV($data, $filename)
    {
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
    public static function fGetProductPriceStock($part_number, $sku, $proveedor)
    {
        if (!self::isProviderCompatible($proveedor)) {
            return (object) ['data' => []];
        }

        $provider = self::get_provider_instance();

        // Standard Interface call
        $data = $provider->getProductStockPrice($part_number, $sku);

        // Wrap to match legacy expectation: ->data[0]->precio
        if ($data) {
            $formatted = (object) [
                'precio' => $data['price'],
                'stock' => $data['stock'],
                'moneda' => isset($data['currency']) ? $data['currency'] : 'USD',
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
    public static function fGetCatalogWooCatalogo()
    {
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

    public static function fGetCatalogExtendWooCatalogo($part_number)
    {
        $provider = self::get_provider_instance();
        $details = $provider->getProductDetails($part_number);
        $provider_slug = ucfirst($provider->getProviderSlug()); // e.g., Nexsys

        if ($details) {
            $items = [];
            $raw_items = [];

            if (is_array($details) && isset($details['data'])) {
                $raw_items = $details['data'];
            } elseif (is_array($details)) { // Single item or list without 'data' wrapper
                $raw_items = isset($details[0]) ? $details : [$details];
            } else {
                $raw_items = [$details];
            }

            foreach ($raw_items as $d) {
                $obj = (object) $d;

                // Normalization Logic
                if (!isset($obj->part_number)) {
                    // Try to find a suitable candidate
                    $obj->part_number = isset($obj->mpn) ? $obj->mpn : (isset($obj->sku) ? $obj->sku : '');
                }

                if (!isset($obj->proveedor)) {
                    $obj->proveedor = $provider_slug;
                }

                // Map currency
                if (!isset($obj->moneda)) {
                    $obj->moneda = isset($obj->currency) ? $obj->currency : 'USD';
                }

                // Map generic fields if missing
                if (!isset($obj->nombre_producto)) {
                    // Check 'name' or 'title'
                    if (isset($obj->name))
                        $obj->nombre_producto = $obj->name;
                    elseif (isset($obj->title) && is_object($obj->title))
                        $obj->nombre_producto = $obj->title->rendered;
                    elseif (isset($obj->title))
                        $obj->nombre_producto = $obj->title;
                }

                if (!isset($obj->precio)) {
                    $obj->precio = isset($obj->price) ? $obj->price : 0;
                    // Handle potential string formatting if needed, though sanitize usually handles it elsewhere
                }

                // Ensure other fields expected by product.php exist
                // product.php uses: descripcion, htmlContent, caracteristicas
                if (!isset($obj->descripcion))
                    $obj->descripcion = isset($obj->short_description) ? $obj->short_description : '';

                // If htmlContent is missing, construct it from description or content
                if (!isset($obj->htmlContent)) {
                    $obj->htmlContent = isset($obj->content->rendered) ? $obj->content->rendered : (isset($obj->content) ? $obj->content : '');
                    if (empty($obj->htmlContent))
                        $obj->htmlContent = $obj->descripcion;
                }

                if (!isset($obj->caracteristicas)) {
                    // If features are returned as 'attributes' or similar
                    $obj->caracteristicas = isset($obj->attributes) ? $obj->attributes : [];
                }

                // Map categories using the provider's logic
                $obj->categoria = $provider->mapCategory($d);
                $obj->subcategoria = $provider->mapSubcategory($d);

                // Map 'image' to 'imagen' for consistency
                if (!isset($obj->imagen)) {
                    $obj->imagen = isset($obj->image) ? $obj->image : '';
                }

                $items[] = $obj;
            }

            return (object) ['data' => $items];
        }

        return (object) ['data' => []];
    }

    // Config Helpers
    public static function fGetConfigValuesWooCatalogo()
    {
        global $wpdb;
        $query = "SELECT * FROM {$wpdb->prefix}vendor_integration";
        $result = $wpdb->get_results($query, ARRAY_A);
        if (empty($result)) {
            $result = array();
        }
        return $result;
    }

    // Compatibility aliases
    public static function obtaining_data_product_api($part_number, $sku, $proveedor)
    {
        return self::fGetProductPriceStock($part_number, $sku, $proveedor);
    }

    public static function obtener_datos_producto_api($part_number, $sku, $proveedor)
    {
        return self::fGetProductPriceStock($part_number, $sku, $proveedor);
    }
}
