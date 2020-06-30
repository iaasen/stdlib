<?php
/**
 * User: ingvar.aasen
 * Date: 13.05.2019
 * Time: 17:14
 */

namespace Iaasen\Geonorge;



/**
 * Source:
 * https://ws.geonorge.no/transApi/
 * @package Nteb\ApiServer\Geonorge\Service
 */
class TranscodeService
{
	const BASE_URL = 'https://ws.geonorge.no/transApi/';

	/** @var Transport */
	protected $transport;

	public function __construct()
	{
		$this->transport = new Transport(['base_url' => self::BASE_URL]);
	}

	public function transcodeGRS80toUTM32(string $latitude, string $longitude) {
		$url = self::BASE_URL;
		$query = [
			'ost' => $longitude,
			'nord' => $latitude,
			'fra' => 84, // Can't find the documentation for these
			'til' => 22, // Can't find the documentation for these
		];
		$data = json_decode($this->transport->sendGet($url, $query));

		return [
			'utm_zone' => '32N',
			'utm_north' => $data->ost,
			'utm_east' => $data->nord,
		];
	}

}