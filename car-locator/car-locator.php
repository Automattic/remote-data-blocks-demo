<?php
/**
 * Plugin Name: Car Locator
 * Description: Provides a REST API endpoint for car location data.
 * Version: 1.0
 * Author: Cascade AI
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Register the REST API route.
 */
add_action( 'rest_api_init', 'car_locator_register_routes' );

/**
 * Registers the custom REST API routes for the Car Locator plugin.
 */
function car_locator_register_routes() {
    register_rest_route(
        'car-locator/v1',
        '/vehicles',
        array(
            'methods'  => WP_REST_Server::READABLE,
            'callback' => 'car_locator_get_vehicles',
            'permission_callback' => '__return_true', // Allow public access
        )
    );
}

/**
 * Callback function to get vehicle data.
 *
 * @param WP_REST_Request $request The REST API request object.
 * @return WP_REST_Response|WP_Error The REST API response or error object.
 */
function car_locator_get_vehicles( WP_REST_Request $request ) {
    $data_file = plugin_dir_path( __FILE__ ) . 'mock-vehicle-data.json';

    if ( ! file_exists( $data_file ) ) {
        return new WP_Error( 'file_not_found', 'Mock data file not found.', array( 'status' => 404 ) );
    }

    $json_data = file_get_contents( $data_file );
    if ( false === $json_data ) {
        return new WP_Error( 'file_read_error', 'Could not read mock data file.', array( 'status' => 500 ) );
    }

    $vehicles = json_decode( $json_data, true );
    if ( null === $vehicles ) {
        return new WP_Error( 'json_decode_error', 'Error decoding JSON data.', array( 'status' => 500 ) );
    }

    return new WP_REST_Response( $vehicles, 200 );
}
