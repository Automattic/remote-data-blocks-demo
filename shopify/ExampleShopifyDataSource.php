<?php

declare(strict_types=1);

namespace ShopifyDemo;

use RemoteDataBlocks\Integrations\Shopify\ShopifyDataSource;
use RemoteDataBlocks\Validation\Types;

class ExampleShopifyDataSource extends ShopifyDataSource
{
    protected const SERVICE_NAME = 'shopify';
    protected const SERVICE_SCHEMA_VERSION = 1;

    protected static function get_service_config_schema(): array
    {
        return Types::object([
            '__version' => Types::integer(),
            'access_token' => Types::skip_sanitize(Types::string()),
            'display_name' => Types::string(),
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
