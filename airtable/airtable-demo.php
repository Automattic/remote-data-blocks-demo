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

namespace RemoteDataBlocks\Example\Airtable\AcademyAward;

use RemoteDataBlocks\Integrations\Airtable\AirtableDataSource;
use RemoteDataBlocks\Integrations\Airtable\AirtableIntegration;

function register_airtable_academy_award_block(): void
{

    $access_token = base64_decode('cGF0YXRRMVZzb3M5dWRsVjQuYmZjZmU3ZThjYmQ0OGMyOTIxMzFmMmIxMzgyOWFiY2ViMTkyMGZjOGY4NGM3YjAwZWZhZDlkMDdmNDIzMjI1OQ==');
    $base_id = 'appHWR6MHe90igNWN'; // Airtable base ID
    $table_id = 'tblGtk5XwzXMG70Ci'; // Airtable table ID

    // Define the data source
    $airtable_data_source = AirtableDataSource::from_array([
        'service_config' => [
            '__version' => 1,
            'access_token' => $access_token,
            'base' => [
                'id' => $base_id,
                'name' => 'Sample Data',
            ],
            'display_name' => 'Academy Award for Sound',
            'tables' => [
                [
                    'id' => $table_id,
                    'name' => 'Academy Award for Sound',
                    'output_query_mappings' => [
                        [
                            'key' => 'record_id',
                            'name' => 'ID',
                            'path' => '$.id',
                            'type' => 'id',
                        ],
                        [
                            'key' => 'year',
                            'name' => 'Year',
                            'path' => '$.fields.award_year',
                            'type' => 'number',
                        ],
                        [
                            'key' => 'name',
                            'name' => 'Name',
                            'path' => '$.fields.award_name',
                            'type' => 'string',
                        ],
                        [
                            'key' => 'recipient',
                            'name' => 'Recipient',
                            'path' => '$.fields.award_recipients',
                            'type' => 'string',
                        ],
                        [
                            'key' => 'film',
                            'name' => 'Film',
                            'path' => '$.fields.film_name',
                            'type' => 'string',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    AirtableIntegration::register_blocks_for_airtable_data_source($airtable_data_source);
    AirtableIntegration::register_loop_blocks_for_airtable_data_source($airtable_data_source);
}

add_action('init', __NAMESPACE__ . '\\register_airtable_academy_award_block');
