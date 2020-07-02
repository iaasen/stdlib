<?php
/**
 * User: ingvar
 * Date: 02.07.2020
 * Time: 13.06
 */

namespace Iaasen\Geonorge\Entity;


class LocationUtm
{
	/** @var string */
	public $utm_zone = '32N';
	/** @var int */
	public $utm_north;
	/** @var int */
	public $utm_east;


	public function __construct($utm_north = null, ?int $utm_east = null, ?string $utm_zone = '32N')
	{
		if(is_object($utm_north) || is_array($utm_north)) {
			foreach($utm_north AS $key => $value) {
				$this->$key = $value;
			}
		}
		else {
			$this->utm_zone = $utm_zone;
			$this->utm_north = $utm_north;
			$this->utm_east = $utm_east;
		}
	}
}