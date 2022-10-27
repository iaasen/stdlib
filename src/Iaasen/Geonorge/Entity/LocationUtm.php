<?php
/**
 * User: ingvar
 * Date: 02.07.2020
 */

namespace Iaasen\Geonorge\Entity;


use Laminas\Stdlib\ArraySerializableInterface;

class LocationUtm implements ArraySerializableInterface
{
	public float $utm_north;
	public float $utm_east;
	public string $utm_zone = '32N';
	public int $epsg = 25832;

	const EPSG = [
		25831 => 'ETRS89 UTM 31 2D',
		25832 => 'ETRS89 UTM 32 2D',
		25833 => 'ETRS89 UTM 33 2D',
	];
	const ZONE_TO_EPSG = [
		31 => 25831,
		32 => 25832,
		33 => 25833,
	];


	public function __construct($utm_north, ?float $utm_east = null, ?string $utm_zone = '32N')
	{
		if(is_object($utm_north) || is_array($utm_north)) {
			foreach($utm_north AS $key => $value) {
				if(in_array($key, ['utm_north', 'utm_east'])) $this->$key = (float) $value;
				else $this->$key = (string) $value;
			}
		}
		else {
			$this->utm_north = (float) $utm_north;
			$this->utm_east = $utm_east;
			$this->utm_zone = $utm_zone;
		}
		$this->epsg = self::ZONE_TO_EPSG[(int) $utm_zone];
	}


	public function exchangeArray(array $array) {
		if(isset($array['utm_north'])) $this->utm_north = (float) $array['utm_north'];
		if(isset($array['utm_east'])) $this->utm_east = (float) $array['utm_east'];
		if(isset($array['utm_zone'])) $this->utm_zone = (string) $array['utm_zone'];
	}


	public function getArrayCopy() {
		return (array) $this;
	}
}