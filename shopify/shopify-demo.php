<?php

/**
 * Plugin Name: Shopify Demo Integration
 * Description: Integrates with example shop using Remote Data Blocks
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 */

declare(strict_types=1);

namespace ShopifyDemo;

use RemoteDataBlocks\Integrations\Shopify\ShopifyIntegration;
use function add_action;

require_once __DIR__ . '/ExampleShopifyDataSource.php';

/**
 * Register the Shopify data source and blocks.
 */
function register_example_shop(): void
{
    $shopify_data_source = ExampleShopifyDataSource::from_array([
        'service' => 'shopify',
        'service_config' => [
            '__version' => 1,
            'access_token' => '',  // Not needed for mock shop
            'display_name' => 'Example Shop',
            'store_name' => 'mock',  // Not used but required by schema
        ],
    ]);

    ShopifyIntegration::register_blocks_for_shopify_data_source($shopify_data_source);
}

// Register the integration when WordPress initializes
add_action('init', __NAMESPACE__ . '\\register_example_shop');
