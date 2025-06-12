<?php

/**
 * Plugin Name: Shopify Demo Integration
 * Description: Integrates with example shop using Remote Data Blocks
 * Author: WordPress VIP
 * Author URI: https://remotedatablocks.com/
 * Text Domain: remote-data-blocks-shopify-demo
 * Version: 1.0.0
 */

declare(strict_types=1);

namespace RemoteDataBlocksDemo\Shopify;

use RemoteDataBlocks\Config\Query\GraphqlQuery;
use RemoteDataBlocks\Integrations\Shopify\ShopifyIntegration;
use function add_action;

require_once __DIR__ . '/example-shopify-datasource.php';

/**
 * Register the Shopify data source and blocks.
 */
function register_example_shop(): void {

	$shopify_data_source = ExampleShopifyDataSource::from_array([
		'service_config' => [
			'__version' => 1,
			'access_token' => '',  // Not needed for mock shop
			'display_name' => 'Example Shop',
			'enable_blocks' => true,
			'store_name' => 'mock-store',  // Not used but required by schema
		],
	]);

	
	$queries = ShopifyIntegration::get_queries( $shopify_data_source );

	$pdp_query = GraphqlQuery::from_array( [
		'data_source' => $shopify_data_source,
		'input_schema' => [
			'id' => [
				'type' => 'id',
				'name' => 'Product ID',
			],
		],
		'output_schema' => [
			'is_collection' => false,
			'type' => [
				'description' => [
					'name' => 'Product description',
					'path' => '$.data.product.descriptionHtml',
					'type' => 'string',
				],
				'image_alt_text' => [
					'name' => 'Image Alt Text',
					'path' => '$.data.product.featuredImage.altText',
					'type' => 'image_alt',
				],
				'image_url' => [
					'name' => 'Image URL',
					'path' => '$.data.product.featuredImage.url',
					'type' => 'image_url',
				],
				'price' => [
					'name' => 'Item price',
					'path' => '$.data.product.priceRange.maxVariantPrice.amount',
					'type' => 'currency_in_current_locale',
				],
				'title' => [
					'name' => 'Title',
					'path' => '$.data.product.title',
					'type' => 'title',
				],
				'online_store_url' => [
					'name' => 'Online Store URL',
					'generate' => function ( $data ): string {
						return $data['data']['product']['onlineStoreUrl'] . '?affiliate_id=123456789';
					},
					'type' => 'button_url',
				],
			],
		],
		'graphql_query' => file_get_contents( __DIR__ . '/GetProductById.graphql' ),
	] );    

	register_remote_data_block( [
		'title' => $shopify_data_source->get_display_name() . ' Product Details Page',
		'icon' => 'cart',
		'render_query' => [
			'query' => $pdp_query,
		],
		'selection_queries' => [
			[
				'query' => $queries['shopify_search_products'],
				'type' => 'search',
			],
		],
		'patterns' => [
			[
				'html' => file_get_contents( __DIR__ . '/pattern-pdp.html' ),
				'role' => 'inner_blocks',
				'title' => 'Product Details Page',
			],
		],
		'overrides' => [
			[
				'name' => 'product_id_override',
				'display_name' => __( 'Use product ID from URL', 'rdb-demo' ),
				'help_text' => __( 'For use on the /products/ page', 'rdb-demo' ),
			],
		],
	] );

	add_rewrite_rule( '^products/([0-9]+)/?', 'index.php?pagename=products&demo_product_id=$matches[1]', 'top' );

	add_filter( 'query_vars', function ( array $query_vars ): array {
		$query_vars[] = 'demo_product_id';
		return $query_vars;
	}, 10, 1 );

	add_filter( 'remote_data_blocks_query_input_variables', function ( array $input_variables, array $enabled_overrides ): array {
		if ( true === in_array( 'product_id_override', $enabled_overrides, true ) ) {
			$product_id = get_query_var( 'demo_product_id' );

			if ( ! empty( $product_id ) ) {
				$input_variables['id'] = 'gid://shopify/Product/' . $product_id;
			}
		}

		return $input_variables;
	}, 10, 2 );

	$teaser_query = GraphqlQuery::from_array( [
		'data_source' => $shopify_data_source,
		'input_schema' => [
			'id' => [
				'type' => 'id',
				'name' => 'Product ID',
			],
		],
		'output_schema' => [
			'is_collection' => false,
			'type' => [
				'image_alt_text' => [
					'name' => 'Image Alt Text',
					'path' => '$.data.product.featuredImage.altText',
					'type' => 'image_alt',
				],
				'image_url' => [
					'name' => 'Image URL',
					'path' => '$.data.product.featuredImage.url',
					'type' => 'image_url',
				],
				'title' => [
					'name' => 'Title',
					'path' => '$.data.product.title',
					'type' => 'button_text',
				],
				'details_button_url' => [
					'name' => 'Details URL',
					'generate' => function ( $data ): string {
						$full_id = $data['data']['product']['id'];
						$numeric_id = basename( $full_id );
						return '/products/' . $numeric_id;
					},
					'type' => 'button_url',
				],
			],
		],
		'graphql_query' => file_get_contents( __DIR__ . '/GetProductById.graphql' ),
	] );    

	register_remote_data_block( [
		'title' => $shopify_data_source->get_display_name() . ' Product Teaser',
		'icon' => 'cart',
		'render_query' => [
			'query' => $teaser_query,
		],
		'selection_queries' => [
			[
				'query' => $queries['shopify_search_products'],
				'type' => 'search',
			],
		],
		'patterns' => [
			[
				'html' => file_get_contents( __DIR__ . '/pattern-teaser.html' ),
				'role' => 'inner_blocks',
				'title' => 'Product Teaser',
			],
		],
	] );
}

// Register the integration when WordPress initializes
add_action( 'init', __NAMESPACE__ . '\\register_example_shop' );
