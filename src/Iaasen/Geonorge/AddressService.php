<?php
/**
 * User: ingvar.aasen
 * Date: 13.05.2019
 * Time: 15:45
 */

namespace Iaasen\Geonorge;


use Iaasen\Exception\InvalidArgumentException;
use Iaasen\Geonorge\Entity\LocationUtm;
use Iaasen\GRS80Ellipsoid;
use League\Geotools\Coordinate\Coordinate;
use League\Geotools\Geotools;
use Iaasen\Geonorge\Entity\Address;

/**
 * Documentation:
 * - https://ws.geonorge.no/adresser/v1/
 * - https://www.kartverket.no/Kart/transformere-koordinater/
 */
class AddressService
{
	const BASE_URL = 'adresser/v1/';
	const DEFAULT_TRANSCODE_SERVICE = 'geonorge'; // Options: geotools, geonorge
	// A third possible option is PhpCoord. See \Partnernett\Model\LatLng for example
	// $latLong = new LatLng($telematorAddress->latitude, $telematorAddress->longitude, 0, RefEll::wgs84());
	// I have commented earlier that geotools misses target by 1-3 meters and PhpCoord get it right. I don't remember when I discovered that.

	/** @var Transport */
	protected $transport;
	/** @var TranscodeService */
	protected $geonorgeTranscodeService;
	/** @var string  */
	public $transcodeServiceToUse = self::DEFAULT_TRANSCODE_SERVICE;


	public function __construct()
	{
		$this->transport = new Transport(['base_url' => Transport::BASE_URL . self::BASE_URL]);
		$this->geonorgeTranscodeService = new TranscodeService();
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
			$addresses[] = $this->createObject($row);
		}
		return $addresses;
	}


	public function getById(string $id) : ?Address {
		$fields = explode('-', base64_decode($id));
		if(count($fields) != 6) return null;

		$fieldNames = [
			0 => 'kommunenummer',
			1 => 'gardsnummer',
			2 => 'bruksnummer',
			3 => 'festenummer',
			4 => 'nummer',
			5 => 'bokstav',
		];
		$query = [];
		foreach($fieldNames AS $key => $fieldName) {
			if(isset($fields[$key]) && strlen($fields[$key])) $query[$fieldName] = $fields[$key];
		}

		$url = 'sok';
		$data = json_decode($this->transport->sendGet($url, $query));
		if(count($data->adresser)) return $this->createObject(array_pop($data->adresser));
		else return null;
	}


	/**
	 * @param string $matrikkel
	 * @return Address[]
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function getByMatrikkel(string $matrikkel) : array {
		preg_match('/(\d+)-(\d+)\/(\d+)(\/(\d+))?/', $matrikkel, $matches);
		$query = [
			'kommunenummer' => $matches[1],
			'gardsnummer' => $matches[2],
			'bruksnummer' => $matches[3],
		];
		if(isset($matches[5])) $query['festenummer'] = $matches[5];

		$url = 'sok';
		$data = json_decode($this->transport->sendGet($url, $query));

		$addresses = [];
		foreach($data->adresser AS $row) {
			$addresses[] = $this->createObject($row);
		}
		return $addresses;
	}


	protected function transcodeGRS80ToUTM(string $latitude, string $longitude) : LocationUtm {
		if($this->transcodeServiceToUse == 'geotools') return $this->transcodeGRS80ToUtmUsingGeotools($latitude, $longitude);
		elseif($this->transcodeServiceToUse == 'geonorge') return $this->geonorgeTranscodeService->transcodeGRS80toUTM32($latitude, $longitude);
		else throw new InvalidArgumentException('Unknown transcode service: ' . $this->transcodeServiceToUse);
	}


	protected function transcodeGRS80ToUtmUsingGeotools(string $latitude, string $longitude) : LocationUtm {
		$geotools = new Geotools();
		$GRS80 = new GRS80Ellipsoid();
		$coordinate = new Coordinate([$latitude, $longitude], $GRS80);
		$coordinate = explode(' ', $geotools->convert($coordinate)->toUTM());
		return new LocationUtm($coordinate[1], $coordinate[2], $coordinate[0]);
	}


	protected function createObject($data) : Address {
		$address = new Address($data);
		$address->location_utm = $this->transcodeGRS80ToUTM($address->representasjonspunkt->lat, $address->representasjonspunkt->lon);
		$address->generateUniqueId();
		return $address;
	}

}