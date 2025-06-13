<?php
/**
 * Registry class to hold the car location query.
 */

declare(strict_types = 1);
class CarLocatorRegistry {
	/** @var \RemoteDataBlocks\Config\Query\HttpQuery|null */
	public static $query = null;
}
