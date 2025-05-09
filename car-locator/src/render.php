<?php declare(strict_types = 1);

namespace RemoteDataBlocks\Example\Airtable\LeafletMap;


$coordinates = [["x" => 46.188567, "y" => -123.830123, "name" => "Car 1"]];


?>
<div
	<?php echo get_block_wrapper_attributes(); ?>
	data-map-coordinates="<?php echo( esc_attr( wp_json_encode( $coordinates ) ) ); ?>"
	style="height: 600px;width: 100%;max-width: none"
>
</div>
