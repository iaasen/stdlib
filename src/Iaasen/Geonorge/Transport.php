<?php
/**
 * User: ingvar.aasen
 * Date: 03.08.2018
 * Time: 11:33
 */

namespace Iaasen\Geonorge;


use Iaasen\Transport\GuzzleHttpTransport;

class Transport extends GuzzleHttpTransport
{
	const BASE_URL = 'https://ws.geonorge.no/';


	/**
	 * Is run before each request
	 * @return void
	 */
	protected function checkSession()
	{
	}

	/**
	 * Is run if error code is given from server
	 * Request is attempted a second time if function returns true
	 * If function returns false the API error will be forwarded
	 * @return bool
	 */
	protected function renewSession(): bool
	{
		return false;
	}
}