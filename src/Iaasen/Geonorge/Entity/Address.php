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
	// Identities
	public string $id;
	public int $adressekode;
	public string $objtype;
	public DateTime $oppdateringsdato;

	// The address
	public string $adressenavn;
	public int $nummer;
	public string $bokstav;
	public string $postnummer;
	public string $poststed;

	// Convenience compliations
	public string $adressetekst;
	public string $adressetekstutenadressetilleggsnavn;
	public string $adressetillegsnavn;

	// Location
	public ?\stdClass $representasjonspunkt;
	public bool $stedfestingverifisert;
	public ?LocationUtm $location_utm; // Locally generated
	public ?LocationLatLong $location_lat_long; // Locally generated

	// Matrikkel
	public string $kommunenavn;
	public string $kommunenummer;
	public int $gardsnummer;
	public int $bruksnummer;
	public int $festenummer;
	public ?int $undernummer = null;
	/** @var string[] */
	public array $bruksenhetsnummer;


	public function __construct($data = []) {
		parent::__construct($data);
		// Don't overwrite the integer addressId from Matrikkel
		if(!isset($this->id)) $this->id = $this->generateUniqueId();
	}


	public function setRepresentasjonspunkt($representasjonspunkt) : void {
		if(is_object($representasjonspunkt) && $representasjonspunkt->epsg == 'EPSG:4258') {
			$representasjonspunkt->lat = round($representasjonspunkt->lat, 6);
			$representasjonspunkt->lon = round($representasjonspunkt->lon, 6);
		}
		elseif(is_array($representasjonspunkt) && $representasjonspunkt['epsg'] == 'EPSG:4258') {
			$representasjonspunkt['lat'] = round($representasjonspunkt['lat'], 6);
			$representasjonspunkt['lon'] = round($representasjonspunkt['lon'], 6);
		}
		parent::setObjectInternal('\stdClass', 'representasjonspunkt', $representasjonspunkt);
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