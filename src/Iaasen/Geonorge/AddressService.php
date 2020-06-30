<?php
/**
 * User: ingvar.aasen
 * Date: 13.05.2019
 * Time: 15:45
 */

namespace Iaasen\Geonorge;


use Iaasen\GRS80Ellipsoid;
use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Geotools;
use Iaasen\Geonorge\Entity\Address;

/**
 * Documentation:
 * - https://ws.geonorge.no/adresser/v1/
 */
class AddressService
{
	const BASE_URL = 'adresser/v1/';

	/** @var Transport */
	protected $transport;
	/** @var TranscodeService */
	protected $transcodeService;


	public function __construct()
	{
		$this->transport = new Transport(['base_url' => Transport::BASE_URL . self::BASE_URL]);
		$this->transcodeService = new TranscodeService();
	}


	/**
	 * @param string $search
	 * @return Address[]
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function search(string $search) : array {
		$url = 'sok';
		$query = [
			'sok' => $search,
		];
		$data = json_decode($this->transport->sendGet($url, $query));
		$addresses = [];
		foreach($data->adresser AS $row) {
			$address = new Address($row);
			$address->location_utm32 = $this->generateLocationUtm($address);
			$address->id = base64_encode($address->getMatrikkel());
			$addresses[] = $address;
		}
		return $addresses;
	}


	/**
	 * @param string $matrikkel
	 * @return Address
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getByMatrikkel(string $matrikkel) : ?Address {
		preg_match('/(\d+)-(\d+)\/(\d+)(\/(\d+))?/', $matrikkel, $matches);
		$query = [
			'kommunenummer' => $matches[1],
			'gardsnummer' => $matches[2],
			'bruksnummer' => $matches[3],
		];
		if(isset($matches[5])) $query['festenummer'] = $matches[5];

		$url = 'sok';
		$data = json_decode($this->transport->sendGet($url, $query));

		if(count($data->adresser)) {
			$row = array_pop($data->adresser);
			$address = new Address($row);
			//$address->location_utm = $this->transcodeService->transcodeGRS80toUTM32($address->representasjonspunkt->lat, $address->representasjonspunkt->lon); // Using Geonorge
			$address->location_utm = $this->transcodeGRS80ToUTM($address->representasjonspunkt->lat, $address->representasjonspunkt->lon); // Using Geotools
			$address->id = base64_encode($address->getMatrikkel());
			return $address;
		}
		return null;
	}


	protected function transcodeGRS80ToUTM(string $latitude, string $longitude) : array {
		$geotools = new Geotools();
		$GRS80 = new GRS80Ellipsoid();
		$coordinate = new Coordinate([$latitude, $longitude], $GRS80);
		$coordinate = explode(' ', $geotools->convert($coordinate)->toUTM());
		return [
			'utm_zone' => $coordinate[0],
			'utm_north' => $coordinate[1],
			'utm_east' => $coordinate[2],
		];
	}


}