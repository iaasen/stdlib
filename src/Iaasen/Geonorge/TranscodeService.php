<?php
/**
 * User: ingvar.aasen
 * Date: 13.05.2019
 * Time: 17:14
 */

namespace Iaasen\Geonorge;



use Iaasen\Geonorge\Entity\LocationUtm;

/**
 * Source:
 * https://ws.geonorge.no/transApi/
 * @package Nteb\ApiServer\Geonorge\Service
 */
class TranscodeService
{
	const BASE_URL = 'https://ws.geonorge.no/transApi/';

	public static $latitudeBands = [
		'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X'
	];

	// See https://www.kartverket.no/Kart/transformere-koordinater/
	public static $zones = [
		31 => 21,
		32 => 22,
		33 => 23,
		34 => 24,
		35 => 25,
		36 => 26,
	];

	/** @var Transport */
	protected $transport;

	public function __construct()
	{
		$this->transport = new Transport(['base_url' => self::BASE_URL]);
	}

	public function transcodeGRS80toUTM32(float $latitude, float $longitude, int $zone = 32) : LocationUtm {
		$url = self::BASE_URL;
		$query = [
			'ost' => $longitude,
			'nord' => $latitude,
			'fra' => 84, // Check pagesource on this page: https://www.kartverket.no/Kart/transformere-koordinater/
			'til' => self::$zones[$zone],
		];
		$data = json_decode($this->transport->sendGet($url, $query));
		$latitudeBand = self::$latitudeBands[($latitude + 80) / 8];
		return new LocationUtm($data->nord, $data->ost, $zone . $latitudeBand);
	}

}