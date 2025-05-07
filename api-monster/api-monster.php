<?php

declare(strict_types=1);

/**
 * Plugin Name: 5e SRD Monsters RDB Example
 * Description: Creates a custom block to be used with Remote Data Blocks in order to retrieve artwork from the 5e SRD.
 * Author: WPVIP
 * Author URI: https://remotedatablocks.com/
 * Text Domain: remote-data-blocks
 * Version: 1.0.0
 * Requires Plugins: remote-data-blocks
 */

namespace RemoteDataBlocks\Example\SRDMonsters;

use RemoteDataBlocks\Config\DataSource\HttpDataSource;
use RemoteDataBlocks\Config\Query\HttpQuery;

function register_srd_block(): void
{
	$srd_data_source = HttpDataSource::from_array([
		'service_config' => [
			'__version' => 1,
			'display_name' => '5e SRD Monsters',
			'endpoint' => 'https://www.dnd5eapi.co/api/monsters',
			'request_headers' => [
				'Content-Type' => 'application/json',
			],
		],
	]);

	$get_monster_query = HttpQuery::from_array([
		'data_source' => $srd_data_source,
		'endpoint' => function (array $input_variables) use ($srd_data_source): string {
			return sprintf('%s/%s', $srd_data_source->get_endpoint(), $input_variables['index'] ?? '');
		},
		'input_schema' => [
			'index' => [
				'name' => 'Index',
				'type' => 'id',
			],
		],
		'output_schema' => [
			'is_collection' => false,
			'path' => '$',
			'type' => [
				'name' => [
					'name' => 'Name',
					'type' => 'string',
				],
				'size' => [
					'name' => 'Size',
					'type' => 'string',
				],
				'image' => [
					'name' => 'Image URL',
					'generate' => function ($data): string {
						return 'https://www.dnd5eapi.co' . $data['image'];
					},
					'type' => 'image_url',
				],
			],
		],
	]);

	$list_monsters_query = HttpQuery::from_array([
		'data_source' => $srd_data_source,
		'output_schema' => [
			'is_collection' => true,
			'path' => '$.results[*]',
			'type' => [
				'index' => [
					'name' => 'Index',
					'type' => 'id',
				],
				'name' => [
					'name' => 'Name',
					'type' => 'string',
				],
			],
		],
	]);

	register_remote_data_block([
		'display_name' => 'API Monster',
		'data_source' => $srd_data_source,
		'render_query' => [
			'query' => $get_monster_query,
		],
		'selection_queries' => [
			[
				'query' => $list_monsters_query,
				'type' => 'list',
			],
		],
	]);
}
add_action('init', __NAMESPACE__ . '\\register_srd_block');
