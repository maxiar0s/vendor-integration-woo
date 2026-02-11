<?php
/**
 * Interface for WooCatalogo Providers
 * @link        https://siroe.cl
 * @since       1.0.0
 * 
 * @package     base
 * @subpackage  base/include
 */

if (!defined('ABSPATH')) {
    exit;
}

interface WooCatalogoProviderInterface {
    
    /**
     * Authenticate with the provider API.
     * Should return true on success, false on failure.
     * May store tokens in options/transients.
     */
    public function authenticate();

    /**
     * Get the full catalog of products.
     * Should return an array of normalized product data.
     * 
     * @return array Array of products or false on error.
     */
    public function getCatalog();

    /**
     * Get stock and price for a specific product.
     * 
     * @param string $part_number Manufacturer Part Number
     * @param string $sku Provider SKU (optional)
     * @return array|false ['price' => float, 'stock' => int] or false
     */
    public function getProductStockPrice($part_number, $sku = '');

    /**
     * Get extended product details (descriptions, images, attributes).
     * 
     * @param string $part_number
     * @return object|false Product details object
     */
    public function getProductDetails($part_number);

    /**
     * Get the provider name/slug.
     * 
     * @return string
     */
    public function getProviderSlug();
}
