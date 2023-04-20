<?php
/**
 * User: ingvar
 * Date: 02.07.2020
 */

namespace Iaasen\Geonorge\Entity;


use Iaasen\Exception\InvalidArgumentException;
use Laminas\Stdlib\ArraySerializableInterface;

class LocationUtm implements ArraySerializableInterface
{
	public string $id; // Unique id
	public float $utm_north;
	public float $utm_east;
	public string $utm_zone;
	public int $epsg;

	/**
	 * WGS84 : UTM 01N - 60N : EPSG:32601 - EPSG:32660
	 * WGS84 : UTM 01S - 60S : EPSG:32501 - EPSG:32560
	 * ETRS89: UTM 28N - 38N : EPSG:25828 - EPSG:25838
	 */
	const EPSG = [
		25831 => 'ETRS89 / UTM zone 31N',
		25832 => 'ETRS89 / UTM zone 32N',
		25833 => 'ETRS89 / UTM zone 33N',
		32631 => 'WGS 84 / UTM zone 31N',
		32632 => 'WGS 84 / UTM zone 32N',
		32633 => 'WGS 84 / UTM zone 33N',
	];


	/**
	 * Construct has two options:
	 * - Regenerate object from API response, using only the first parameter
	 * - Create new object using all fields. The first one being a float
	 * @param \stdClass|array|float $utm_north
	 * @param float|null $utm_east
	 * @param string|null $utm_zone
	 */
	public function __construct($utm_north, ?float $utm_east = null, ?string $utm_zone = '32N')
	{
		if(is_object($utm_north) || is_array($utm_north)) {
			foreach($utm_north AS $key => $value) {
				if(in_array($key, ['utm_north', 'utm_east'])) $this->$key = (float) $value;
				elseif($key == 'utm_zone') $this->setUtmZone($value);
			}
		}
		else {
			$this->utm_north = (float) $utm_north;
			$this->utm_east = $utm_east;
			$this->setUtmZone($utm_zone);
		}
		$this->generateUniqueId();
	}


	public function exchangeArray(array $array) {
		if(isset($array['utm_north'])) $this->utm_north = (float) $array['utm_north'];
		if(isset($array['utm_east'])) $this->utm_east = (float) $array['utm_east'];
		if(isset($array['utm_zone'])) $this->utm_zone = (string) $array['utm_zone'];
	}


	public function getArrayCopy() {
		return (array) $this;
	}


	public function setUtmZone(string $zone) : void {
		$this->utm_zone = $zone;
		$this->epsg = self::getEpsgFromUtmZone($zone);
	}


	// Example: 32N
	public static function getEpsgFromUtmZone(string $zone) : ?int {
		$zonePart1 = (int) $zone;
		$zonePart2 = (string) substr($zone, -1, 1);
		if($zone < 1 || $zone > 60) throw new InvalidArgumentException('Unknown UTM zone ' . $zone);
		if(strcasecmp($zonePart2,'S') == 0) return 325 . $zonePart1;
		if($zone >= 28 && $zone <= 37) return 258 . $zonePart1;
		return 326 . $zonePart1;
	}


	public function generateUniqueId() : string {
		$base64fields = [
			'utm',
			round($this->utm_north),
			round($this->utm_east),
			$this->epsg,
		];
		$this->id = base64_encode(implode('-', $base64fields));
		return $this->id;
	}
}