<?php
/**
 * User: ingvar
 * Date: 02.07.2020
 */

namespace Iaasen\Geonorge\Entity;


class LocationUtm
{
	public float $utm_north;
	public float$utm_east;
	public string $utm_zone = '32N';


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
	}
}