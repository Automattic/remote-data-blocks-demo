<?php declare(strict_types = 1);

/**
 * Plugin Name: Car Locator
 * Description: Creates a custom block to be used with Remote Data Blocks to display car locations.
 * Version: 1.0.0
 * Author: WordPress VIP and Cascade AI
 */

namespace RemoteDataBlocks\Demo\CarLocator;	

use RemoteDataBlocks\Config\DataSource\HttpDataSource;
use RemoteDataBlocks\Config\Query\HttpQuery;

/**
 * Registry class to hold the car location query.
 */
class CarLocatorRegistry {
	/** @var \RemoteDataBlocks\Config\Query\HttpQuery|null */
	public static $query = null;
}

function register_leaflet_map_block(): void {

	// Register the Leaflet script and stylesheet. The handles are referenced in
	// `block.json` for use in the block editor and the WordPress frontend.
	wp_register_style( 'leaflet-style', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', [], '1.9.4' );
	wp_register_script( 'leaflet-script', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', [], '1.9.4', true );

	// Register the block using the build artifact.
	register_block_type( __DIR__ . '/build' );

	
	$car_location_data_source = HttpDataSource::from_array( [
		'display_name' => 'Car Locations',
		'endpoint' =>'https://dummyjson.com/c/8b75-8395-46b9-9633',
		'request_headers' => [
			'Content-Type' => 'application/json',
		],
	] );

	if ( !$car_location_data_source instanceof HttpDataSource ) {
		return;
	}

	$car_location_query = HttpQuery::from_array([
		'data_source' => $car_location_data_source,
		'output_schema' => [
			'is_collection' => true,
			'type' => [
				'id' => [
					'name' => 'ID',
					'path' => '$.id',
					'type' => 'integer',
				],
				'latitude' => [
					'name' => 'Latitude',
					'path' => '$.coordinates.latitude',
					'type' => 'number',
				],
				'longitude' => [
					'name' => 'Longitude',
					'path' => '$.coordinates.longitude',
					'type' => 'number',
				],
				'vehicle_type' => [
					'name' => 'Vehicle Type',
					'path' => '$.vehicle_type',
					'type' => 'string',
				],
				'charge_percentage' => [
					'name' => 'Charge Percentage',
					'path' => '$.charge_percentage',
					'type' => 'number',
				],
				'status' => [
					'name' => 'Status',
					'path' => '$.status',
					'type' => 'string',
				],
			],
		],
	]);

	CarLocatorRegistry::$query = $car_location_query;

}
add_action( 'init', __NAMESPACE__ . '\\register_leaflet_map_block' );
