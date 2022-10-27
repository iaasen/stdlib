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

	public function __construct($latitude, ?float $longitude = null)
	{
		if(is_object($latitude) || is_array($latitude)) {
			foreach($latitude AS $key => $value) {
				if(in_array($key, ['latitude', 'longitude'])) $this->$key = (float) $value;
				else $this->$key = (string) $value;
			}
		}
		else {
			$this->latitude = (float) $latitude;
			$this->longitude = $longitude;
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