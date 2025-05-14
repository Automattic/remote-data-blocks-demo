<?php

declare(strict_types=1);

namespace RemoteDataBlocksDemo\Shopify;

use RemoteDataBlocks\Integrations\Shopify\ShopifyDataSource;
use RemoteDataBlocks\Validation\Types;

defined( 'ABSPATH' ) || exit();

class ExampleShopifyDataSource extends ShopifyDataSource
{
    protected const SERVICE_NAME = REMOTE_DATA_BLOCKS_SHOPIFY_SERVICE;
    protected const SERVICE_SCHEMA_VERSION = 1;

    protected static function get_service_config_schema(): array
    {
        return Types::object([
            '__version' => Types::integer(),
            'access_token' => Types::skip_sanitize(Types::string()),
            'display_name' => Types::string(),
            'enable_blocks' => Types::nullable(Types::boolean()),
            'store_name' => Types::string(),
        ]);
    }

    protected static function map_service_config(array $service_config): array
    {
        return [
            'display_name' => $service_config['display_name'],
            'endpoint' => 'https://mock.shop/api/2024-04/graphql.json',
            'image_url' => parent::map_service_config($service_config)['image_url'],
            'request_headers' => [
                'Content-Type' => 'application/json',
            ],
        ];
    }
}
