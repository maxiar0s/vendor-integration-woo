<?php
// Script to extract categories from Nexsys API
// Usage: wp eval-file extract_categories.php OR copy to your WP root and run with `php extract_categories.php`
// This script assumes you have WP loaded or run with wp-cli

$products = [];
if (class_exists('VendorIntegrationNexsysProvider')) {
    $provider = new VendorIntegrationNexsysProvider();
    $page = 1;
    $max_pages = 5; // Limit to 5 pages for quick check, increase if needed

    do {
        echo "Fetching page $page...\n";
        $p = $provider->getCatalog($page, 100);
        if (empty($p))
            break;

        foreach ($p as $prod) {
            $products[] = $prod;
        }
        $page++;
    } while ($page <= $max_pages);
} else {
    echo "This script must be run within a WordPress environment where VendorIntegrationNexsysProvider is available or included.\n";
    exit;
}

$cats = [];
$subcats = [];

foreach ($products as $prod) {
    if (isset($prod['raw_data'])) {
        $raw = $prod['raw_data'];

        if (isset($raw['category'])) {
            if (is_array($raw['category'])) {
                foreach ($raw['category'] as $c)
                    $cats[] = $c;
            } else {
                $cats[] = $raw['category'];
            }
        }

        if (isset($raw['subcategory'])) {
            if (is_array($raw['subcategory'])) {
                foreach ($raw['subcategory'] as $s)
                    $subcats[] = $s;
            } else {
                $subcats[] = $raw['subcategory'];
            }
        }
    }
}

$cats = array_unique($cats);
sort($cats);

$subcats = array_unique($subcats);
sort($subcats);

echo "\n--- CATEGORIES ---\n";
foreach ($cats as $c) {
    echo "- " . $c . "\n";
}

echo "\n--- SUBCATEGORIES ---\n";
foreach ($subcats as $s) {
    echo "- " . $s . "\n";
}
