<?php
/**
 * User: ingvar.aasen
 * Date: 13.05.2019
 * Time: 17:14
 */

namespace Iaasen\Geonorge;



use Iaasen\Geonorge\Entity\LocationLatLong;
use Iaasen\Geonorge\Entity\LocationUtm;

/**
 * Source:
 * https://ws.geonorge.no/transApi/ <- the old one
 * https://ws.geonorge.no/transformering/v1/  <- the new one
 */
class TranscodeService
{
	const BASE_URL = 'https://ws.geonorge.no/transformering/v1/';

	const LATITUDE_BANDS = [
		'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X' // W is default for Norway
	];

	// See https://www.kartverket.no/Kart/transformere-koordinater/
	const UTM_ZONES = [
		31 => 25831,
		32 => 25832, // Default for Norway
		33 => 25833,
	];

	const COORDINATE_SYSTEMS = [
		4258 => 'ETRS89 Geografisk 2D', // Almost same as WGS84
		4326 => 'WGS84 Geografisk 2D', // Almost same as GRS80
		25831 => 'ETRS89 UTM 31 2D',
		25832 => 'ETRS89 UTM 32 2D',
		25833 => 'ETRS89 UTM 33 2D',
	];

	protected Transport $transport;

	public function __construct()
	{
		$this->transport = new Transport(['base_url' => self::BASE_URL]);
	}


	public function transcodeLocationLatLongTotm(LocationLatLong $locationLatLong) : LocationUtm {
		return $this->transcodeLatLongToUTM($locationLatLong->latitude, $locationLatLong->longitude);
	}


	public function transcodeLocationUtmToLatLong(LocationUtm $locationUtm) : LocationLatLong {
		return $this->transcodeUTMtoLatLong($locationUtm->utm_north, $locationUtm->utm_east, $locationUtm->utm_zone);
	}


	public function transcodeLatLongToUTM(float $latitude, float $longitude, int $zone = 32) : LocationUtm {
		$url = 'transformer';
		$query = [
			'x' => $longitude,
			'y' => $latitude,
			'fra' => 4258, // Check pagesource on this page: https://www.kartverket.no/Kart/transformere-koordinater/
			'til' => self::UTM_ZONES[$zone],
		];
		$data = json_decode($this->transport->sendGet($url, $query));
		$latitudeBand = self::LATITUDE_BANDS[(int) (($latitude + 80) / 8)];
		return new LocationUtm($data->y, $data->x, $zone . $latitudeBand);
	}


	/**
	 * @deprecated use transcodeLatLongToUTM32()
	 */
	public function transcodeGRS80toUTM32(float $latitude, float $longitude, int $zone = 32) : LocationUtm {
		return $this->transcodeLatLongToUTM($latitude, $longitude, $zone);
	}


	/**
	 * WGS84 - used by GPS-satellites
	 * ETRS89 - used by Europe
	 * GRS80 - was used by GPS-satellites before
	 * All three are almost the same
	 * @param float $utmNorth
	 * @param float $utmEast
	 * @param string $utmZone
	 * @return LocationLatLong // Using ETRS89
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function transcodeUTMtoLatLong(float $utmNorth, float $utmEast, string $utmZone = '32W') : LocationLatLong {
		$utmZoneInt = (int) substr($utmZone, 0, 2);
		$url = 'transformer';
		$query = [
			'x' => $utmEast,
			'y' => $utmNorth,
			'fra' => self::UTM_ZONES[$utmZoneInt], // Check $self::getProjections()
			'til' => 4326,
		];
		$data = json_decode($this->transport->sendGet($url, $query));
		return new LocationLatLong($data->y, $data->x);
	}


	public function getProjections() : array {
		$url = 'projeksjoner';
		return json_decode($this->transport->sendGet($url));
	}

}