<?php
/**
 * User: ingvar
 * Date: 24.10.2022
 */

namespace Iaasen\Geonorge\Entity;


class LocationLatLong
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
}