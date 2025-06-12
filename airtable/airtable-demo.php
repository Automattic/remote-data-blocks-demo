<?php

declare(strict_types=1);

/**
 * Plugin Name: Airtable RDB Integration
 * Description: Creates a custom block to be used with Remote Data Blocks to display Airtable records
 * Author: WPVIP
 * Author URI: https://remotedatablocks.com/
 * Text Domain: remote-data-blocks
 * Version: 1.0.0
 * Requires Plugins: remote-data-blocks
 */

namespace RemoteDataBlocksDemo\Airtable;

use RemoteDataBlocks\Integrations\Airtable\AirtableDataSource;
use RemoteDataBlocks\Config\Query\HttpQuery;

function register_airtable(): void {    
	// Register the stylesheet so it's known to WordPress
	wp_register_style(
		'rdb-kexp-cover-style',
		plugin_dir_url( __FILE__ ) . 'pattern-cover.css',
		array(),
		filemtime( plugin_dir_path( __FILE__ ) . 'pattern-cover.css' )
	);

	$access_token = base64_decode( 'cGF0YXRRMVZzb3M5dWRsVjQuYmZjZmU3ZThjYmQ0OGMyOTIxMzFmMmIxMzgyOWFiY2ViMTkyMGZjOGY4NGM3YjAwZWZhZDlkMDdmNDIzMjI1OQ==' );
	$base_id = 'appsURUQQ9rdXTiHd';
	$table_id = 'tblAJRx2nL9dymoS3';

	$data_source = AirtableDataSource::from_array( [
		'service_config' => [
			'__version' => 1,
			'access_token' => $access_token,
			'base' => [
				'id' => $base_id,
				'name' => 'KEXP Top 100',
			],
			'enable_blocks' => false,
			'display_name' => 'KEXP Top 100',
			'tables' => [
				[
					'id' => $table_id,
					'name' => '2024',
					'output_query_mappings' => [
						[
							'key' => 'Rank',
							'name' => 'Rank',
							'path' => '$.fields["Rank"]',
							'type' => 'number',
						],
						[
							'key' => 'Artist',
							'name' => 'Artist',
							'path' => '$.fields["Artist"]',
							'type' => 'string',
						],
						[
							'key' => 'Album',
							'name' => 'Album',
							'path' => '$.fields["Album"]',
							'type' => 'string',
						],
						[
							'key' => 'Album Art URL',
							'name' => 'Album Art URL',
							'path' => '$.fields["Album Art URL"]',
							'type' => 'image_url',
						],
						[
							'key' => 'Spotify Artist URL',
							'name' => 'Spotify Artist URL',
							'path' => '$.fields["Spotify Artist URL"]',
							'type' => 'button_url',
						],
					],
				],
			],
		],
	] );

	$get_query = HttpQuery::from_array( [
		'data_source' => $data_source,
		'endpoint' => function ( array $input_variables ) use ( $data_source ): string {
			return $data_source->get_endpoint() . '/tblAJRx2nL9dymoS3/' . $input_variables['record_id'];
		},
		'input_schema' => [
			'record_id' => [
				'name' => 'Record ID',
				'type' => 'id',
			],
		],
		'output_schema' => [
			'is_collection' => false,
			'type' => [
				'record_id' => [
					'name' => 'Record ID',
					'path' => '$["id"]',
					'type' => 'id',
				],
				'Rank' => [
					'name' => 'Rank',
					'path' => '$.fields["Rank"]',
					'type' => 'number',
				],
				' Artist' => [
					'name' => ' Artist',
					'path' => '$.fields["Artist"]',
					'type' => 'string',
				],
				'Album' => [
					'name' => 'Album',
					'path' => '$.fields["Album"]',
					'type' => 'string',
				],
				'Album Art URL' => [
					'name' => 'Album Art URL',
					'path' => '$.fields["Album Art URL"]',
					'type' => 'image_url',
				],
				'Spotify Artist URL' => [
					'name' => 'Spotify Artist URL',
					'path' => '$.fields["Spotify Artist URL"]',
					'type' => 'button_url',
				],
			],
		],
	] );

	$list_query = HttpQuery::from_array( [
		'data_source' => $data_source,
		'endpoint' => $data_source->get_endpoint() . '/tblAJRx2nL9dymoS3?view=viwa0BdzTWkcXWyUp',
		'input_schema' => [],
		'output_schema' => [
			'is_collection' => true,
			'path' => '$.records[*]',
			'type' => [
				'record_id' => [
					'name' => 'Record ID',
					'path' => '$["id"]',
					'type' => 'id',
				],
				'Rank' => [
					'name' => 'Rank',
					'path' => '$.fields["Rank"]',
					'type' => 'number',
				],
				' Artist' => [
					'name' => ' Artist',
					'path' => '$.fields["Artist"]',
					'type' => 'string',
				],
				'Album' => [
					'name' => 'Album',
					'path' => '$.fields["Album"]',
					'type' => 'string',
				],
			],
		],
	] );

	register_remote_data_block( [
		'title' => sprintf( '%s/%s', $data_source->get_display_name(), '2024' ),
		'render_query' => [
			'query' => $get_query,
		],
		'patterns' => [
			[
				'title' => 'KEXP Top 100 Cover',
				'html' => file_get_contents( __DIR__ . '/pattern-cover.html' ),
			],
			[
				'title' => 'KEXP Top 100 Spotify Link',
				'html' => file_get_contents( __DIR__ . '/pattern-spotify-link.html' ),
			],
		],
		'selection_queries' => [
			[
				'query' => $list_query,
				'type' => 'list',
			],
		],
	] );

	register_remote_data_block( [
		'title' => sprintf( '%s/%s Loop', $data_source->get_display_name(), '2024' ),
		'render_query' => [
			'query' => $list_query,
		],
		'patterns' => [
			[
				'title' => 'KEXP Top 100 Listing',
				'html' => file_get_contents( __DIR__ . '/pattern-list.html' ),
			],
		],
	] );
}

add_action( 'init', __NAMESPACE__ . '\\register_airtable' );

/**
 * Enqueues the shared stylesheet for the KEXP blocks (frontend and editor).
 */
function enqueue_kexp_shared_block_assets(): void {
	// Enqueue the style that was registered in register_airtable().
	wp_enqueue_style( 'rdb-kexp-cover-style' );
}

add_action( 'enqueue_block_assets', __NAMESPACE__ . '\enqueue_kexp_shared_block_assets' );
