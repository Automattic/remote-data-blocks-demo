<?php declare(strict_types = 1);

namespace RemoteDataBlocksDemo\CarLocator;

// Retrieve the query from the registry.
$car_location_query = CarLocatorRegistry::$query;

// Ensure the query is available before trying to use it.
if ( ! $car_location_query instanceof \RemoteDataBlocks\Config\Query\HttpQuery ) {
	?>
<div
	<?php echo get_block_wrapper_attributes(); ?> >
	<p>Car location data is currently unavailable.</p>	
	</div>
<?php
	return;
}

$response = $car_location_query->execute( [] );
$coordinates = [];

if ( ! is_wp_error( $response ) ) {
	$coordinates = array_map( function ( $value ) {
		$result = $value['result'];
		return [
			'id' => $result['id']['value'],
			'x' => $result['latitude']['value'],
			'y' => $result['longitude']['value'],
			'type' => $result['vehicle_type']['value'],
			'charge' => $result['charge_percentage']['value'],
			'status' => $result['status']['value'],
		];
	}, $response['results'] );
}

?>
<div
	<?php echo get_block_wrapper_attributes(); ?>
	data-map-coordinates="<?php echo( esc_attr( wp_json_encode( $coordinates ) ) ); ?>"
	style="height: 600px;width: 100%;max-width: none;min-width:600px"
>
</div>
