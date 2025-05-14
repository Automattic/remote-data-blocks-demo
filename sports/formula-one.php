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
		'service_config' => [
			'__version' => 1,
            'display_name' => 'Formula One',
		    'endpoint' => 'https://v1.formula-1.api-sports.io',
			'request_headers' => [
				'x-rapidapi-host' => 'v1.formula-1.api-sports.io',
				'x-rapidapi-key' => 'ea3c1812a737d3f72e95db1faef79e92',
				'Content-Type' => 'application/json',
			],
		],
	]);

    $driver_rankings_query = HttpQuery::from_array([
        'data_source' => $f1_data_source,
        'display_name' => 'Driver Rankings Season',
        'endpoint' => function (array $input_variables) use ($f1_data_source): string {
            return $f1_data_source->get_endpoint() . '/rankings/drivers';
        },
        'input_schema' => [
            'season' => [
                'type'        => 'integer',
                'name'        => 'Year (e.g. 2025)',
            ],
        ],
        'output_schema' => [
            'is_collection' => true,
            'type'          => [
                'position'      => ['name' => 'Position', 'path' => '$.response[*].position', 'type' => 'integer'],
                'driver_name'   => ['name' => 'Driver Name', 'path' => '$.response[*].driver.name', 'type' => 'string'],
                'driver_image'  => ['name' => 'Driver Image', 'path' => '$.response[*].driver.image', 'type' => 'image_url'],
                'team_name'     => ['name' => 'Team Name', 'path' => '$.response[*].team.name', 'type' => 'string'],
                'team_logo'     => ['name' => 'Team Logo', 'path' => '$.response[*].team.logo', 'type' => 'image_url'],
                'points'        => ['name' => 'Points', 'path' => '$.response[*].points', 'type' => 'integer'],
                'wins'          => ['name' => 'Wins', 'path' => '$.response[*].wins', 'type' => 'integer'],
            ],
        ],
    ]);

    $f1_seasons_list_query = HttpQuery::from_array([
        'data_source'   => $f1_data_source,
        'display_name'  => 'F1 Seasons List',
        'endpoint'      => function () use ($f1_data_source): string {
            return $f1_data_source->get_endpoint() . '/seasons';
        },
        'output_schema' => [
            'is_collection' => true,
            'path'          => '$.response[*]', // API returns an array of integers for seasons
            'type'          => [
                'season' => ['name' => 'Season Value', 'path' => '$', 'type' => 'integer'], // Value for render_query input
                'name'   => ['name' => 'Season Display', 'path' => '$', 'type' => 'string'],  // For display in dropdown
            ],
        ],
    ]);

    // Register the Formula One Driver Rankings Block
    register_remote_data_block([
        'title'           => __( 'Formula One Driver Rankings', 'remote-data-blocks-demo' ),
        'render_query'    => [
            'query' => $driver_rankings_query,
        ],
        'selection_queries' => [
            [
                'display_name' => __( 'Select Season', 'remote-data-blocks-demo' ),
                'query'        => $f1_seasons_list_query,
                'type'         => 'list',
            ],
        ],
    ]);
/*
    // 2. Driver Information by ID Query
    $driver_by_id_query = HttpQuery::from_array([
        'data_source' => $f1_data_source,
        'display_name' => 'Driver Information',
        'endpoint' => function (array $input_variables) use ($f1_data_source): string {
            return $f1_data_source->get_endpoint() . '/drivers'; 
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
                'nationality'          => ['name' => 'Nationality', 'path' => '$.response[0].nationality', 'type' => 'string'],
                'birthdate'            => ['name' => 'Birthdate', 'path' => '$.response[0].birthdate', 'type' => 'string'],
                'world_championships'  => ['name' => 'World Championships', 'path' => '$.response[0].world_championships', 'type' => 'integer'],
                'current_team_name'    => ['name' => 'Current Team', 'path' => '$.response[0].teams[0].team.name', 'type' => 'string'],
                'current_team_logo'    => ['name' => 'Current Team Logo', 'path' => '$.response[0].teams[0].team.logo', 'type' => 'image_url'],
            ],
        ],
    ]);


    // 3. Team Information by ID Query
    $team_by_id_query = HttpQuery::from_array([
        'data_source' => $f1_data_source,
        'display_name' => 'Team Information',
        'endpoint' => function (array $input_variables) use ($f1_data_source): string {
            return $f1_data_source->get_endpoint() . '/teams';
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
                'base'                => ['name' => 'Base Location', 'path' => '$.response[0].base', 'type' => 'string'],
                'world_championships' => ['name' => 'World Championships', 'path' => '$.response[0].world_championships', 'type' => 'integer'],
                'director'            => ['name' => 'Director', 'path' => '$.response[0].director', 'type' => 'string'],
                'engine_supplier'     => ['name' => 'Engine Supplier', 'path' => '$.response[0].engine', 'type' => 'string'],
                'chassis'             => ['name' => 'Chassis', 'path' => '$.response[0].chassis', 'type' => 'string'],
            ],
        ],
    ]);
    
*/
}

add_action( 'init', __NAMESPACE__ . '\\formula_one_rdb_init' );
