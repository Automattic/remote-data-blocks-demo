<?php declare(strict_types = 1);

/**
 * Plugin Name: Car Locator
 * Description: Creates a custom block to be used with Remote Data Blocks to display car locations.
 * Version: 1.0.0
 * Author: WordPress VIP and Cascade AI
 */

namespace RemoteDataBlocks\Example\Airtable\LeafletMap;

function register_leaflet_map_block(): void {

	// Register the Leaflet script and stylesheet. The handles are referenced in
	// `block.json` for use in the block editor and the WordPress frontend.
	wp_register_style( 'leaflet-style', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4' );
	wp_register_script( 'leaflet-script', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true );

	// Register the block using the build artifact.
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', __NAMESPACE__ . '\\register_leaflet_map_block' );
