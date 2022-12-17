<?php
/**
 * User: ingvar
 * Date: 03.04.2020
 */

namespace Iaasen\Geonorge\Entity;


use Iaasen\DateTime;
use Iaasen\Model\AbstractEntityV2;

/**
 * Based on Geonorge data format
 * https://kartkatalog.geonorge.no/metadata/adresse-rest-api/44eeffdc-6069-4000-a49b-2d6bfc59ac61
 */
class Address extends AbstractEntityV2
{
	public string $id;
	public int $adressekode;
	public string $adressenavn;
	public string $adressetekst;
	public string $adressetekstutenadressetilleggsnavn;
	public string $adressetillegsnavn;
	public string $bokstav;
	/** @var string[] */
	public array $bruksenhetsnummer;
	public int $bruksnummer;
	public int $festenummer;
	public int $gardsnummer;
	public string $kommunenavn;
	public string $kommunenummer;
	public int $nummer;
	public string $objtype;
	public DateTime $oppdateringsdato;
	public string $postnummer;
	public string $poststed;
	public \stdClass $representasjonspunkt;
	public bool $stedfestingverifisert;
	public ?int $undernummer = null;

	// Locally generated data, not from Geonorge
	public LocationUtm $location_utm;
	public LocationLatLong $location_lat_long;


	public function __construct($data = []) {
		parent::__construct($data);
		$this->id = $this->generateUniqueId();
	}


	public function getMatrikkel() : string {
		$matrikkel = $this->kommunenummer . '-' . $this->gardsnummer . '/' . $this->bruksnummer;
		if($this->festenummer) $matrikkel .= '/' . $this->festenummer;
		return $matrikkel;
	}


	public function getFullAddress() : string {
		return $this->adressetekst . ', ' . $this->postnummer . ' ' . $this->poststed;
	}


	public function getMapUrlGoogle() : string {
		return 'https://www.google.no/maps/search/' . str_replace([' '], ['+'], $this->adressetekst) . ',+' . str_replace([' '], ['+'], $this->poststed);
		// https://www.google.com/maps/place/64°00'40.4"N+11°29'14.8"E
	}


	public function getMapUrl1881() : string {
		return 'https://kart.1881.no/?query=' . str_replace([' '], ['+'], $this->adressetekst) . ',+' . str_replace([' '], ['+'], $this->poststed);
	}


	public function getMapUrlKartserver() : string {
		return 'https://www.kartserver.no/?google_address=' . str_replace([' '], ['+'], $this->adressetekst) . ',+' . str_replace([' '], ['+'], $this->poststed);
	}


	public function generateUniqueId() : string {
		$this->id = base64_encode(implode('-', [
			$this->kommunenummer,
			$this->gardsnummer,
			$this->bruksnummer,
			$this->festenummer,
			$this->nummer,
			$this->bokstav,
		]));
		return $this->id;
	}

}