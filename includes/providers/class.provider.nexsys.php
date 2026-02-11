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

class WooCatalogoNexsysProvider extends WooCatalogoProviderAbstract {

    const BASE_URL_TEMPLATE = "https://www.nexsysla.com/:country/wp-json/resellers/v1/";
    const OPTION_TOKEN_KEY = 'woocatalogo_nexsys_token';

    public function getProviderSlug() {
        return 'nexsys';
    }

    protected function loadSettings($settings = []) {
        if (empty($settings)) {
            $this->user_id = get_option('woocatalogo_nexsys_email', '');
            $this->password = get_option('woocatalogo_nexsys_password', '');
            $this->country = get_option('woocatalogo_nexsys_country', 'cl');
        } else {
            parent::loadSettings($settings);
        }
    }

    private function getBaseUrl() {
        return str_replace(':country', $this->country, self::BASE_URL_TEMPLATE);
    }

    public function authenticate() {
        if (empty($this->user_id) || empty($this->password)) return false;

        $token = get_option(self::OPTION_TOKEN_KEY);
        // Basic validation: if token exists, assume valid. 
        // In production, we'd check expiration or handle 401.
        if ($token) {
            return true;
        }

        return $this->login();
    }

    public function login() {
        $url = $this->getBaseUrl() . 'login';
        
        $body = json_encode([
            'email' => $this->user_id, 
            'password' => $this->password
        ]);

        $response = $this->remoteRequest($url, 'POST', ['Content-Type' => 'application/json'], $body);

        if ($response && isset($response['token'])) {
            update_option(self::OPTION_TOKEN_KEY, $response['token']);
            return true;
        }

        return false;
    }

    private function getToken() {
        $token = get_option(self::OPTION_TOKEN_KEY);
        if (!$token) {
            $this->login();
            $token = get_option(self::OPTION_TOKEN_KEY);
        }
        return $token;
    }

    public function getCatalog($page = 1, $per_page = 100) {
        $token = $this->getToken();
        if (!$token) return [];

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

            foreach ($response as $product) {
                // Use SKU or MPN as common identifier.
                
                $mpn = isset($product['mpn']) ? $product['mpn'] : (isset($product['sku']) ? $product['sku'] : '');
                if (empty($mpn)) continue;

                $all_products[] = [
                    'id'            => $product['id'],
                    'sku'           => $product['sku'],
                    'part_number'   => $mpn,
                    'nombre_producto' => isset($product['name']) ? $product['name'] : (isset($product['title']['rendered']) ? $product['title']['rendered'] : ''),
                    'stock'         => isset($product['stock_quantity']) ? $product['stock_quantity'] : 0,
                    'precio'        => isset($product['price']) ? $product['price'] : 0,
                    'categoria'     => 'Sin Categoria', // Nexsys categories mapping needed
                    'proveedor'     => 'Nexsys', 
                    'created_at'    => current_time('mysql'),
                    'updated_at'    => current_time('mysql'),
                         
                    // Raw data strictly if needed by specific logic
                    'raw_data'      => $product
                ];
            }
            
            // Check if we should continue
            // If response count < per_page, we're done.
            if (count($response) < $per_page) {
                break;
            }
            
            $current_page++;

        } while ($current_page <= $max_pages);

        return $all_products; 
    }

    public function getProductStockPrice($part_number, $sku = '') {
        $token = $this->getToken();
        if (!$token) return false;
        
        $searchKey = !empty($sku) ? $sku : $part_number;

        $url = add_query_arg('sku', $searchKey, $this->getBaseUrl() . 'product'); // Assuming 'product' endpoint per previous findings
        $headers = ['Authorization' => 'Bearer ' . $token];

        $response = $this->remoteRequest($url, 'GET', $headers);

        if ($response) {
            // Nexsys returns object for single product? Or array?
            // Postman usually implies API returns object for singular endpoint.
            // If array check first item.
            $item = is_array($response) && isset($response[0]) ? $response[0] : $response;

            return [
                'price' => isset($item['price']) ? $item['price'] : 0,
                'stock' => isset($item['stock_quantity']) ? $item['stock_quantity'] : 0
            ];
        }

        return ['price' => 0, 'stock' => 0];
    }

    public function getProductDetails($part_number) {
        $token = $this->getToken();
        if (!$token) return false;
        
        $url = add_query_arg('sku', $part_number, $this->getBaseUrl() . 'product');
        $headers = ['Authorization' => 'Bearer ' . $token];
        
        return $this->remoteRequest($url, 'GET', $headers);
    }
}
