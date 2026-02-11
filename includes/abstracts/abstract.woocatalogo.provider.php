<?php
/**
 * Abstract Class for WooCatalogo Providers
 * @link        https://siroe.cl
 * @since       1.0.0
 * 
 * @package     base
 * @subpackage  base/include
 */

if (!defined('ABSPATH')) {
    exit;
}

abstract class WooCatalogoProviderAbstract implements WooCatalogoProviderInterface {

    protected $api_key;
    protected $user_id;
    protected $password;
    protected $country;
    protected $slug;

    public function __construct($settings = []) {
        $this->slug = $this->getProviderSlug();
        $this->loadSettings($settings);
    }

    protected function loadSettings($settings) {
        // Load settings from provided array or database
        $this->api_key = isset($settings['api_key']) ? $settings['api_key'] : '';
        $this->user_id = isset($settings['user_id']) ? $settings['user_id'] : '';
        $this->password = isset($settings['password']) ? $settings['password'] : '';
        $this->country = isset($settings['country']) ? $settings['country'] : 'cl'; // Default Chile
    }

    /* Helper function to standardize log messages */
    protected function log($message) {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            error_log("[WooCatalogo - {$this->slug}] " . print_r($message, true));
        }
    }

    /* Helper for cURL requests */
    protected function remoteRequest($url, $method = 'GET', $headers = [], $body = []) {
        $args = [
            'method'    => $method,
            'headers'   => $headers,
            'timeout'   => 45,
            'sslverify' => false, // Consider enabling for production if certs are valid
        ];

        if (!empty($body)) {
            $args['body'] = $body;
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            $this->log("Request Error: " . $response->get_error_message());
            return false;
        }

        $code = wp_remote_retrieve_response_code($response);
        if ($code >= 400) {
            $this->log("HTTP Error {$code}: " . wp_remote_retrieve_body($response));
            return false;
        }

        return json_decode(wp_remote_retrieve_body($response), true);
    }
}
