<?php
/**
 * Nexsys Provider Implementation
 * @link        https://siroe.cl
 * @since       1.0.0
 * 
 * @package     base
 * @subpackage  base/include
 */

if (!defined('ABSPATH')) {
    exit;
}

class WooCatalogoNexsysProvider extends WooCatalogoProviderAbstract
{

    const BASE_URL_TEMPLATE = "https://www.nexsysla.com/:country/wp-json/resellers/v1/";
    const TRANSIENT_TOKEN_KEY = 'woocatalogo_nexsys_token';
    const TOKEN_EXPIRATION = DAY_IN_SECONDS; // 24 hours

    public function getProviderSlug()
    {
        return 'nexsys';
    }

    protected function loadSettings($settings = [])
    {
        if (empty($settings)) {
            $this->user_id = get_option('woocatalogo_nexsys_email', '');
            $this->password = get_option('woocatalogo_nexsys_password', '');
            $this->country = get_option('woocatalogo_nexsys_country', 'cl');
        } else {
            parent::loadSettings($settings);
        }
    }

    private function getBaseUrl()
    {
        return str_replace(':country', $this->country, self::BASE_URL_TEMPLATE);
    }

    public function authenticate()
    {
        if (empty($this->user_id) || empty($this->password))
            return false;

        $token = get_transient(self::TRANSIENT_TOKEN_KEY);
        if ($token) {
            return true;
        }

        return $this->login();
    }

    public function login()
    {
        $url = $this->getBaseUrl() . 'login';

        $body = json_encode([
            'email' => $this->user_id,
            'password' => $this->password
        ]);

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ];

        $response = $this->remoteRequest($url, 'POST', $headers, $body);

        if ($response && isset($response['token'])) {
            set_transient(self::TRANSIENT_TOKEN_KEY, $response['token'], self::TOKEN_EXPIRATION);
            return true;
        }

        return false;
    }

    private function getToken()
    {
        $token = get_transient(self::TRANSIENT_TOKEN_KEY);
        if (!$token) {
            $this->login();
            $token = get_transient(self::TRANSIENT_TOKEN_KEY);
        }
        return $token;
    }

    protected function remoteRequest($url, $method = 'GET', $headers = [], $body = [])
    {
        // Prevent recursion: If this is a login request, do not apply retry logic
        if (strpos($url, 'login') !== false) {
            return parent::remoteRequest($url, $method, $headers, $body);
        }

        $response = parent::remoteRequest($url, $method, $headers, $body);

        // Handle 429 Too Many Requests
        if ($response === false && $this->last_response_code == 429) {
            $this->log("Rate limited (429). Sleeping for 2 seconds and retrying...");
            sleep(2);
            return parent::remoteRequest($url, $method, $headers, $body);
        }

        // Check for 401/403 Forbidden - Token Expired
        if ($response === false && ($this->last_response_code == 401 || $this->last_response_code == 403)) {
            $this->log("Token expired (Code: {$this->last_response_code}). Re-authenticating...");

            // 1. Delete expired token
            delete_transient(self::TRANSIENT_TOKEN_KEY);
            delete_transient('woocatalogo_nexsys_auth_error');

            // 2. Re-authenticate
            if ($this->login()) {
                $this->log("Re-authentication successful. Retrying request...");

                // 3. Update headers with new token
                $newToken = get_transient(self::TRANSIENT_TOKEN_KEY);
                if (isset($headers['Authorization'])) {
                    $headers['Authorization'] = 'Bearer ' . $newToken;
                }

                // 4. Retry request
                return parent::remoteRequest($url, $method, $headers, $body);
            } else {
                $this->log("Re-authentication failed. Please check credentials.");
                set_transient('woocatalogo_nexsys_auth_error', 'Error de autenticación Nexsys. Por favor, revise sus credenciales.', DAY_IN_SECONDS);
            }
        }

        return $response;
    }

    public function getCatalog($page = 1, $per_page = 100)
    {
        $token = $this->getToken();
        if (!$token)
            return [];

        $all_products = [];
        $current_page = 1;
        $max_pages = 50; // Safety limit

        do {
            $url = add_query_arg([
                'per_page' => $per_page,
                'page' => $current_page
            ], $this->getBaseUrl() . 'products');

            $headers = [
                'Authorization' => 'Bearer ' . $token
            ];

            $response = $this->remoteRequest($url, 'GET', $headers);

            if (empty($response) || !is_array($response)) {
                break;
            }

            if (!$response || !isset($response['data']))
                break;

            $products_list = $response['data'];

            $logged = false;
            foreach ($products_list as $product) {
                if (!$logged) {
                    file_put_contents(__DIR__ . '/product_debug_log.txt', print_r($product, true));
                    $logged = true;
                }
                // Use SKU or MPN as common identifier.
                $mpn = isset($product['mpn']) ? $product['mpn'] : (isset($product['sku']) ? $product['sku'] : '');
                if (empty($mpn))
                    continue;

                $all_products[] = [
                    'id' => $product['id'],
                    'sku' => $product['sku'],
                    'part_number' => $mpn,
                    'nombre_producto' => isset($product['name']) ? $product['name'] : (isset($product['title']['rendered']) ? $product['title']['rendered'] : ''),
                    'stock' => isset($product['stock_quantity']) ? $product['stock_quantity'] : (isset($product['inventory']) ? $product['inventory'] : 0),
                    'precio' => isset($product['price']) ? $product['price'] : 0,
                    'moneda' => isset($product['currency']) ? $product['currency'] : 'USD',
                    'categoria' => $this->mapCategory($product),
                    'subcategoria' => $this->mapSubcategory($product),
                    'proveedor' => 'Nexsys',
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql'),

                    // Raw data strictly if needed by specific logic
                    'raw_data' => $product
                ];
            }

            // Check if we should continue
            // If response count < per_page, we're done.
            if (count($products_list) < $per_page) {
                break;
            }

            $current_page++;

        } while ($current_page <= $max_pages);

        return $all_products;
    }

    public function getProductStockPrice($part_number, $sku = '')
    {
        $token = $this->getToken();
        if (!$token)
            return false;

        $searchKey = !empty($sku) ? $sku : $part_number;

        // Changing endpoint from 'product' to 'products' to get full object details
        $url = add_query_arg('sku', $searchKey, $this->getBaseUrl() . 'products');
        $headers = ['Authorization' => 'Bearer ' . $token];

        $response = $this->remoteRequest($url, 'GET', $headers);

        if ($response) {
            // Check for 'data' wrapper as seen in getCatalog
            $data = isset($response['data']) ? $response['data'] : $response;

            // Strict Filtering: Find the exact match in the returned array
            if (is_array($data)) {
                foreach ($data as $item) {
                    $api_sku = isset($item['sku']) ? $item['sku'] : '';
                    $api_mpn = isset($item['mpn']) ? $item['mpn'] : '';

                    // Compare with both SKU and Part Number (ignoring case)
                    if (strcasecmp($api_sku, $searchKey) === 0 || strcasecmp($api_mpn, $searchKey) === 0) {
                        return [
                            'price' => isset($item['price']) ? $item['price'] : 0,
                            'stock' => isset($item['stock_quantity']) ? $item['stock_quantity'] : 0
                        ];
                    }
                }
            } elseif (is_object($data)) {
                // Single object check (less likely if endpoint returns list, but good for safety)
                $api_sku = isset($data['sku']) ? $data['sku'] : '';
                $api_mpn = isset($data['mpn']) ? $data['mpn'] : '';
                if (strcasecmp($api_sku, $searchKey) === 0 || strcasecmp($api_mpn, $searchKey) === 0) {
                    return [
                        'price' => isset($data['price']) ? $data['price'] : 0,
                        'stock' => isset($data['stock_quantity']) ? $data['stock_quantity'] : 0
                    ];
                }
            }
        }

        return ['price' => 0, 'stock' => 0];
    }

    public function getProductDetails($part_number)
    {
        $token = $this->getToken();
        if (!$token)
            return false;

        // Changing endpoint from 'product' to 'products' to get full object details
        $url = add_query_arg('sku', $part_number, $this->getBaseUrl() . 'products');
        $headers = ['Authorization' => 'Bearer ' . $token];

        $response = $this->remoteRequest($url, 'GET', $headers);

        if ($response && isset($response['data']) && is_array($response['data'])) {
            foreach ($response['data'] as $product) {
                $api_sku = isset($product['sku']) ? $product['sku'] : '';
                $api_mpn = isset($product['mpn']) ? $product['mpn'] : '';

                // Strict comparison
                if (strcasecmp($api_sku, $part_number) === 0 || strcasecmp($api_mpn, $part_number) === 0) {
                    return $product;
                }
            }
            // If we iterated through all results and found no match, return false/empty
            return false;
        }

        return $response;
    }

    public function mapCategory($product)
    {
        $category_slug = '';
        if (isset($product['category']) && is_array($product['category']) && !empty($product['category'])) {
            $category_slug = $product['category'][0];
        }

        $mapping = [
            'communications-conference-equipment' => 'Equipos de Conferencia y Comunicación',
            'computers' => 'Computadores',
            'monitors' => 'Monitores',
            'pos' => 'POS',
            'printers-scanners' => 'Impresoras y Escáneres',
            'printing-supplies' => 'Suministros de Impresión',
            'projectors' => 'Proyectores',
        ];

        if (array_key_exists($category_slug, $mapping)) {
            return $mapping[$category_slug];
        }

        // Return original if no mapping found, or "Sin Categoria" if empty
        return !empty($category_slug) ? ucfirst($category_slug) : 'Sin Categoria';
    }

    public function mapSubcategory($product)
    {
        $subcategory_name = '';
        if (isset($product['subcategory']) && is_array($product['subcategory']) && !empty($product['subcategory'])) {
            // Using the first one found
            $subcategory_name = $product['subcategory'][0];
        }

        $mapping = [
            'All-in-One PCs/Workstations' => 'Computadores Todo-en-Uno/Estaciones de Trabajo',
            'Audio Conferencing Systems' => 'Sistemas de Audio Conferencia',
            'Barcode Reader Accessories' => 'Accesorios Lectores Código Barras',
            'Barcode Readers' => 'Lectores de Código de Barras',
            'Cleaning Media' => 'Medios de Limpieza',
            'Computer Components' => 'Componentes de Computador',
            'Computer Monitors' => 'Monitores de Computador',
            'Computers' => 'Computadores',
            'Conference & communications equipment' => 'Equipos de Conferencia y Comunicación',
            'Customer Display' => 'Pantallas de Cliente',
            'Data Projectors' => 'Proyectores de Datos',
            'Headphone Pillows' => 'Almohadillas de Audífonos',
            'Headphone/Headset Accessories' => 'Accesorios de Audífonos',
            'Headphones & Headsets' => 'Audífonos y Auriculares',
            'Ink Cartridges' => 'Cartuchos de Tinta',
            'Inkjet Printers' => 'Impresoras de Inyección',
            'Label Printers' => 'Impresoras de Etiquetas',
            'Laptops' => 'Notebooks',
            'Monitors' => 'Monitores',
            'Multifunction Printers' => 'Impresoras Multifuncionales',
            'Multifunctionals' => 'Multifuncionales',
            'PCs/Workstations' => 'PCs y Estaciones de Trabajo',
            'POS' => 'POS',
            'POS Printers' => 'Impresoras POS',
            'POS System Accessories' => 'Accesorios Sistema POS',
            'POS Systems' => 'Sistemas POS',
            'POS terminals' => 'Terminales POS',
            'Printer Ink Refills' => 'Recargas de Tinta',
            'Printers & Scanners' => 'Impresoras y Escáneres',
            'Printing Supplies' => 'Suministros de Impresión',
            'Scanners' => 'Escáneres',
            'Toner Cartridges' => 'Cartuchos de Tóner',
            'Toner Collectors' => 'Recolectores de Tóner'
        ];

        if (array_key_exists($subcategory_name, $mapping)) {
            return $mapping[$subcategory_name];
        }

        return !empty($subcategory_name) ? $subcategory_name : 'Sin Subcategoria';
    }
}
