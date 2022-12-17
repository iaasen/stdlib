<?php
/**
 * User: ingvar
 * Date: 24.10.2022
 */

namespace Iaasen\Geonorge\Entity;


use Laminas\Stdlib\ArraySerializableInterface;

class LocationLatLong implements ArraySerializableInterface
{
	public float $latitude;
	public float $longitude;
	public int $epsg = 4258;

	const EPSG = [
		4258 => 'ETRS89 Geografisk 2D', // Almost same as WGS84
		4326 => 'WGS84 Geografisk 2D', // Almost same as GRS80
	];

	public function __construct($latitude, ?float $longitude = null, int $epsg = 4258)
	{
		if(is_object($latitude) || is_array($latitude)) {
			foreach($latitude AS $key => $value) {
				if(in_array($key, ['latitude', 'longitude', 'epsg'])) $this->$key = (float) $value;
				else $this->$key = (string) $value;
			}
		}
		else {
			$this->latitude = (float) $latitude;
			$this->longitude = $longitude;
			$this->epsg = $epsg;
		}
	}

	public function exchangeArray(array $array) {
		if(isset($array['latitude'])) $this->latitude = (float) $array['latitude'];
		if(isset($array['longitude'])) $this->longitude = (float) $array['longitude'];
	}

	public function getArrayCopy() {
		return (array) $this;
	}
}