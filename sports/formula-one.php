<?php
/**
 * Plugin Name: Formula One RDB Integration
 * Description: Demonstrates Remote Data Blocks with Formula One data.
 * Version: 1.0
 * Author: WordPress VIP
 * Requires Plugins: remote-data-blocks
 */

declare(strict_types=1);

namespace RemoteDataBlocksDemo\FormulaOne;

use RemoteDataBlocks\Config\DataSource\HttpDataSource;
use RemoteDataBlocks\Config\Query\HttpQuery;
use RemoteDataBlocks\Blocks\register_remote_data_block;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}


/**
 * Initializes the Formula One RDB integration and registers queries/blocks.
 * Attached to 'init' action.
 */
function formula_one_rdb_init(): void {

	$f1_data_source = HttpDataSource::from_array([
		'display_name' => 'Formula One',
		'endpoint' => 'https://v1.formula-1.api-sports.io',
		'request_headers' => [
			'x-rapidapi-host' => 'v1.formula-1.api-sports.io',
			'x-rapidapi-key' => base64_decode('ZWEzYzE4MTJhNzM3ZDNmNzJlOTVkYjFmYWVmNzllOTI='),
		],
	]);

    $driver_rankings_query = HttpQuery::from_array([
        'data_source' => $f1_data_source,
        'display_name' => 'Driver Rankings Season',
        'endpoint' => function (array $input_variables) use ($f1_data_source): string {
            
            $endpoint = $f1_data_source->get_endpoint() . '/rankings/drivers';

            if ( ! empty( $input_variables['season'] ) ) {
                $endpoint .= '?season=' . $input_variables['season'];
            }

            return $endpoint;
        },
        'input_schema' => [
            'season' => [
                'type'        => 'integer',
                'name'        => 'Year (2021-2023)',
            ],
        ],
        'output_schema' => [
            'is_collection' => true,
            'path'          => '$.response[*]',
            'type'          => [
                'position'      => ['name' => 'Position', 'path' => '$.position', 'type' => 'integer'],
                'driver_name'   => ['name' => 'Driver Name', 'path' => '$.driver.name', 'type' => 'button_text'],
                'driver_image'  => ['name' => 'Driver Image', 'path' => '$.driver.image', 'type' => 'image_url'],
                'driver_image_alt' => [
                    'name' => 'Driver Image Alt Text',
                    'type' => 'image_alt',
                    'generate' => function( $item_data ) {
                        $driver_name = $item_data['driver']['name'] ?? 'N/A';
                        return sprintf( 'Photo of %s', $driver_name );
                    },
                ],
                 'driver_name_linked' => [
                    'name' => 'Driver Link',
                    'type' => 'html',
                    'generate' => function( $item_data ) {
                        $driver_name = $item_data['driver']['name'] ?? 'N/A';
                        $driver_id = $item_data['driver']['id'] ?? null;
                        $url = $driver_id ? "/drivers/{$driver_id}" : '#';
                        return sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $driver_name ) );
                    },
                ],
                'team_name'     => ['name' => 'Team Name', 'path' => '$.team.name', 'type' => 'button_text'],
                'team_name_linked' => [
                    'name' => 'Team Link',
                    'type' => 'html',
                    'generate' => function( $item_data ) {
                        $team_name = $item_data['team']['name'] ?? 'N/A';
                        $team_id = $item_data['team']['id'] ?? null;
                        $url = $team_id ? "/teams/{$team_id}" : '#';
                        return sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $team_name ) );
                    },
                ],
                'points'        => ['name' => 'Points', 'path' => '$.points', 'type' => 'integer'],
            ],
        ],
    ]);

    // Register the Formula One Driver Rankings Block
    register_remote_data_block([
        'title'           => __( 'Formula One Driver Rankings', 'remote-data-blocks-demo' ),
        'render_query'    => [
            'query' => $driver_rankings_query,
        ],
        'patterns' => [
            [
                'html' => file_get_contents( __DIR__ . '/pattern-listing.html' ),
                'role' => 'inner_blocks',
                'title' => 'Driver Listing',
            ],
        ],
    ]);

    
    $driver_by_id_query = HttpQuery::from_array([
        'data_source' => $f1_data_source,
        'display_name' => 'Driver Information',
        'endpoint' => function (array $input_variables) use ($f1_data_source): string {
            $endpoint = $f1_data_source->get_endpoint() . '/drivers';
            if ( ! empty( $input_variables['id'] ) ) {
                $endpoint .= '?id=' . rawurlencode($input_variables['id']);
            }
            return $endpoint;
        },
        'input_schema' => [
            'id' => [
                'type'        => 'integer',
                'name'        => 'Driver ID',
                'required'    => true,
                'description' => 'The unique ID of the driver.',
            ],
        ],
        'output_schema' => [
            'is_collection' => false,
            'type'          => [
                'name'                 => ['name' => 'Name', 'path' => '$.response[0].name', 'type' => 'string'],
                'image'                => ['name' => 'Image', 'path' => '$.response[0].image', 'type' => 'image_url'],
                'driver_image_alt' => [
                    'name' => 'Driver Image Alt Text',
                    'type' => 'image_alt',
                    'generate' => function( $item_data ) {
                        $driver_name = $item_data['response'][0]['name'] ?? 'N/A';
                        return sprintf( 'Photo of %s', $driver_name );
                    },
                ],
                'nationality'          => ['name' => 'Nationality', 'path' => '$.response[0].nationality', 'type' => 'string'],
                'birthdate'            => ['name' => 'Birthdate', 'path' => '$.response[0].birthdate', 'type' => 'string'],
                'birthplace'           => ['name' => 'Birthplace', 'path' => '$.response[0].birthplace', 'type' => 'string'],
                'world_championships'  => ['name' => 'World Championships', 'path' => '$.response[0].world_championships', 'type' => 'integer'],
                'podiums'              => ['name' => 'Podiums', 'path' => '$.response[0].podiums', 'type' => 'integer'],
                'career_points'        => ['name' => 'Career Points', 'path' => '$.response[0].career_points', 'type' => 'integer'],
                'current_team_name'    => ['name' => 'Current Team', 'path' => '$.response[0].teams[0].team.name', 'type' => 'string'],
                'current_team_logo'    => ['name' => 'Current Team Logo', 'path' => '$.response[0].teams[0].team.logo', 'type' => 'image_url'],
                'current_team_logo_alt'=> ['name' => 'Team Logo Alt Text', 'path' => '$.response[0].teams[0].team.name', 'type' => 'image_alt'],
                'team_name_linked' => [
                    'name' => 'Team Link',
                    'type' => 'html',
                    'generate' => function( $item_data ) {
                        $team_name = $item_data['response'][0]['teams'][0]['team']['name'] ?? 'N/A';
                        $team_id = $item_data['response'][0]['teams'][0]['team']['id'] ?? null;
                        $url = $team_id ? "/teams/{$team_id}" : '#';
                        return sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $team_name ) );
                    },
                ],
            ],
        ],
    ]);
    

    // New: Driver Search Query
    $driver_search_query = HttpQuery::from_array([
        'data_source'   => $f1_data_source,
        'display_name'  => 'Driver Search Results',
        'endpoint'      => function (array $input_variables) use ($f1_data_source): string {
            $endpoint = $f1_data_source->get_endpoint() . '/drivers';
            if ( ! empty( $input_variables['search'] ) ) {
                $endpoint .= '?search=' . rawurlencode( $input_variables['search'] );
            }
            return $endpoint;
        },
        'input_schema'  => [
            'search' => [
                'type'        => 'ui:search_input',
                'name'        => 'Search Term',
                'required'    => true,
                'description' => 'The search term for drivers (e.g., Ham).',
            ],
        ],
        'output_schema' => [
            'is_collection'   => true,
            'path' => '$.response[*]', // Path to the array of drivers
            'type'            => [
                'id'                  => ['name' => 'Driver ID', 'path' => '$.id', 'type' => 'integer'],
                'driver_name'         => ['name' => 'Name', 'path' => '$.name', 'type' => 'string'],
                'driver_image'        => ['name' => 'Image', 'path' => '$.image', 'type' => 'image_url'],
                'team_name'           => ['name' => 'Team Name', 'path' => '$.teams[0].team.name', 'type' => 'string'],
            ],
        ],
        'pagination_schema' => [
            'total_items' => [
                'name' => 'Total items',
                'path' => '$.results',
                'type' => 'integer',
            ],
        ],
    ]);

    // Register the Driver Profile block (Search + Display Details)
    register_remote_data_block([
        'title'             => __( 'Formula One Driver Profile', 'remote-data-blocks-demo' ),
        'icon'              => 'dashicons-id-alt', // Icon for displaying details by ID
        'instructions'      => __( 'Use the search to find a driver. Their details will be displayed once selected.', 'remote-data-blocks-demo' ),
        'render_query'      => [
            'query' => $driver_by_id_query,
        ],
        'selection_queries' => [
            [
                'query' => $driver_search_query,
                'type'  => 'search',
            ],
        ],
        'patterns' => [
                [
                    'html' => file_get_contents( __DIR__ . '/pattern-driver.html' ),
                    'role' => 'inner_blocks',
                    'title' => 'Driver Profile',
                ],
            ],
        'overrides' => [
            [
                'name' => 'driver_id_override',
                'display_name' => __( 'Use driver ID from URL', 'rdb-demo' ),
                'help_text' => __( 'For use on the /drivers/ page', 'rdb-demo' ),
            ],
        ],
    ]);

    add_rewrite_rule( '^drivers/([0-9]+)/?', 'index.php?pagename=drivers&demo_driver_id=$matches[1]', 'top' );

    add_filter( 'query_vars', function ( array $query_vars ): array {
        $query_vars[] = 'demo_driver_id';
        return $query_vars;
    }, 10, 1 );

    add_filter( 'remote_data_blocks_query_input_variables', function ( array $input_variables, array $enabled_overrides ): array {
        if ( true === in_array( 'driver_id_override', $enabled_overrides, true ) ) {
            $driver_id = get_query_var( 'demo_driver_id' );

            if ( ! empty( $driver_id ) ) {
                $input_variables['id'] = $driver_id;
            }
        }

        return $input_variables;
    }, 10, 2 );


    // 3. Team Information by ID Query
    $team_by_id_query = HttpQuery::from_array([
        'data_source' => $f1_data_source,
        'display_name' => 'Team Information',
        'endpoint' => function (array $input_variables) use ($f1_data_source): string {
            $endpoint = $f1_data_source->get_endpoint() . '/teams';
            if ( ! empty( $input_variables['id'] ) ) {
                $endpoint .= '?id=' . rawurlencode( $input_variables['id'] );
            }
            return $endpoint;
        },
        'input_schema' => [
            'id' => [
                'type'        => 'integer',
                'name'        => 'Team ID',
                'required'    => true,
                'description' => 'The unique ID of the team.',
            ],
        ],
        'output_schema' => [
            'is_collection' => false,
            'type'          => [
                'name'                => ['name' => 'Name', 'path' => '$.response[0].name', 'type' => 'string'],
                'logo'                => ['name' => 'Logo', 'path' => '$.response[0].logo', 'type' => 'image_url'],
                'logo_alt'            => ['name' => 'Team Logo Alt Text', 'path' => '$.response[0].name', 'type' => 'image_alt'],
                'base'                => ['name' => 'Location', 'path' => '$.response[0].base', 'type' => 'string'],
                'first_team_entry'    => ['name' => 'First Year in F1', 'path' => '$.response[0].first_team_entry', 'type' => 'integer'],
                'world_championships' => ['name' => 'World Championships', 'path' => '$.response[0].world_championships', 'type' => 'integer'],
                'president'           => ['name' => 'President', 'path' => '$.response[0].president', 'type' => 'string'],
                'director'            => ['name' => 'Director', 'path' => '$.response[0].director', 'type' => 'string'],
                'technical_manager'   => ['name' => 'Technical Manager', 'path' => '$.response[0].technical_manager', 'type' => 'string'],
                'engine_supplier'     => ['name' => 'Engine Supplier', 'path' => '$.response[0].engine', 'type' => 'string'],
                'chassis'             => ['name' => 'Chassis', 'path' => '$.response[0].chassis', 'type' => 'string'],
                'tyres'               => ['name' => 'Tyres', 'path' => '$.response[0].tyres', 'type' => 'string'],
            ],
        ],
    ]);

    // 4. Team Search Query
    $team_search_query = HttpQuery::from_array([
        'data_source'   => $f1_data_source,
        'display_name'  => 'Team Search Results',
        'endpoint'      => function (array $input_variables) use ($f1_data_source): string {
            $endpoint = $f1_data_source->get_endpoint() . '/teams';
            if ( ! empty( $input_variables['search'] ) ) {
                $endpoint .= '?search=' . rawurlencode( $input_variables['search'] );
            }
            return $endpoint;
        },
        'input_schema'  => [
            'search' => [
                'type'        => 'ui:search_input',
                'name'        => 'Search Term for Teams',
                'required'    => true,
                'description' => 'The search term for teams (e.g., Red Bull).',
            ],
        ],
        'output_schema' => [
            'is_collection'   => true,
            'path' => '$.response[*]',
            'type'            => [
                'id'      => ['name' => 'Team ID', 'path' => '$.id', 'type' => 'integer'],
                'name'    => ['name' => 'Name', 'path' => '$.name', 'type' => 'string'],
                'logo'    => ['name' => 'Logo', 'path' => '$.logo', 'type' => 'image_url'],
            ],
        ],
        'pagination_schema' => [
            'total_items' => [
                'name' => 'Total items',
                'path' => '$.results',
                'type' => 'integer',
            ],
        ],
    ]);

    // Register the Team Profile block
    register_remote_data_block([
        'title'        => __( 'Formula One Team Profile', 'remote-data-blocks-demo' ),
        'icon'         => 'dashicons-groups',
        'instructions' => __( 'Search for a Formula One team and display their profile information.', 'remote-data-blocks-demo' ),
        'render_query' => [
            'query' => $team_by_id_query,
        ],
        'selection_queries' => [
            [
                'query' => $team_search_query,
                'type'  => 'search',
            ],
        ],
        'patterns' => [
                [
                    'html' => file_get_contents( __DIR__ . '/pattern-team.html' ),
                    'role' => 'inner_blocks',
                    'title' => 'Team Profile',
                ],
            ],
        'overrides'    => [
            [
                'name'         => 'team_id_override',
                'display_name' => __( 'Use team ID from URL', 'remote-data-blocks-demo' ),
                'help_text'    => __( 'For use on the /teams/ page to show a specific team.', 'remote-data-blocks-demo' ),
            ],
        ],
    ]);

    // Rewrite rules and filters for Teams
    add_rewrite_rule( '^teams/([0-9]+)/?', 'index.php?pagename=teams&demo_team_id=$matches[1]', 'top' );

    add_filter( 'query_vars', function ( array $query_vars ): array {
        $query_vars[] = 'demo_team_id'; // Add demo_team_id
        return $query_vars;
    }, 10, 1 );

    add_filter( 'remote_data_blocks_query_input_variables', function ( array $input_variables, array $enabled_overrides ): array {
        // Handle Driver ID override (existing logic, ensure it's still needed or merged)
        if ( true === in_array( 'driver_id_override', $enabled_overrides, true ) ) {
            $driver_id = get_query_var( 'demo_driver_id' );
            if ( ! empty( $driver_id ) ) {
                $input_variables['id'] = $driver_id;
            }
        }

        // Handle Team ID override (new logic)
        if ( true === in_array( 'team_id_override', $enabled_overrides, true ) ) {
            $team_id = get_query_var( 'demo_team_id' );
            if ( ! empty( $team_id ) ) {
                // Important: Ensure 'id' is the correct input field name for team_by_id_query
                $input_variables['id'] = $team_id; 
            }
        }

        return $input_variables;
    }, 10, 2 ); // Ensure this priority doesn't conflict if there was an old filter

}

add_action( 'init', __NAMESPACE__ . '\\formula_one_rdb_init' );
