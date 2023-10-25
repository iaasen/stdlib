<?php
/**
 * User: ingvar
 * Date: 24.10.2022
 */

namespace Iaasen\Geonorge\Entity;


use Iaasen\Exception\InvalidArgumentException;
use Laminas\Stdlib\ArraySerializableInterface;

class LocationLatLong implements ArraySerializableInterface
{
	public string $id; // Unique id
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
			$this->latitude = round((float) $latitude, 6);
			$this->longitude = round($longitude, 6);
			$this->epsg = $epsg;
		}
		$this->generateUniqueId();
	}

	public function exchangeArray(array $array) {
		if(isset($array['latitude'])) $this->latitude = (float) $array['latitude'];
		if(isset($array['longitude'])) $this->longitude = (float) $array['longitude'];
	}

	public function getArrayCopy() {
		return (array) $this;
	}


	public function generateUniqueId() : string {
		$base64fields = [
			'latlong',
			round($this->latitude, 6),
			round($this->longitude, 5),
			$this->epsg,
		];
		$this->id = base64_encode(implode('-', $base64fields));
		return $this->id;
	}


	public static function getLocationFromUniqueId(string $id) : ?LocationLatLong {
		$fields = explode('-', base64_decode($id));
		if(count($fields) !== 4) throw new InvalidArgumentException('Invalid id');
		if($fields[0] !== 'latlong') throw new InvalidArgumentException('Invalid id');
		return new self($fields[1], $fields[2], $fields[3]);
	}

}