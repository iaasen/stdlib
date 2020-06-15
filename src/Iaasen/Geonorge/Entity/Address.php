<?php
/**
 * User: ingvar
 * Date: 03.04.2020
 * Time: 11.01
 */

namespace Iaasen\Geonorge\Entity;


use Iaasen\DateTime;
use Iaasen\Model\AbstractEntity;

/**
 * Based on Geonorge data format
 * https://kartkatalog.geonorge.no/metadata/adresse-rest-api/44eeffdc-6069-4000-a49b-2d6bfc59ac61
 * Class Address
 * @package Iaasen\Geonorge\Entity
 */
class Address extends AbstractEntity
{
	/** @var string */
	public $id;
	/** @var int */
	public $adressekode;
	/** @var string */
	public $adressenavn;
	/** @var string */
	public $adressetekst;
	/** @var string */
	public $adressetekstutenadressetilleggsnavn;
	/** @var string */
	public $adressetillegsnavn;
	/** @var string */
	public $bokstav;
	/** @var string[] */
	public $bruksenhetsnummer;
	/** @var int */
	public $bruksnummer;
	/** @var int */
	public $festenummer;
	/** @var int */
	public $gardsnummer;
	/** @var string */
	public $kommunenavn;
	/** @var string */
	public $kommunenummer;
	/** @var int */
	public $nummer;
	/** @var string */
	public $objtype;
	/** @var DateTime */
	public $oppdateringsdato;
	/** @var string */
	public $postnummer;
	/** @var string */
	public $poststed;
	/** @var \stdClass */
	public $representasjonspunkt;
	/** @var bool */
	public $stedfestingverifisert;
	/** @var int */
	public $undernummer;

	// Locally generated data, not from Geonorge
	/** @var string[] */
	public $location_utm32;


	public function getMatrikkel() : string {
		$matrikkel = $this->kommunenummer . '-' . $this->gardsnummer . '/' . $this->bruksnummer;
		if($this->festenummer) $matrikkel .= '/' . $this->festenummer;
		return $matrikkel;
	}


	public function getFullAddress() : string {
		return $this->adressetekst . ', ' . $this->postnummer . ' ' . $this->poststed;
	}


	public function getMapUrlGoogle() : string {
		return 'https://www.google.no/maps/search/' . $this->adressetekst . ', ' . $this->poststed;
		// https://www.google.com/maps/place/64°00'40.4"N+11°29'14.8"E
	}


	public function getMapUrl1881() : string {
		return 'https://kart.1881.no/?query=' . $this->adressetekst . ', ' . $this->poststed;
	}


	public function getMapUrlKartserver() : string {
		return 'https://www.kartserver.no/?google_address=' . $this->adressetekst . ', ' . $this->poststed;
	}

}