<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 01.02.2016
 * Time: 12:55
 */

namespace Oppned\Transport;

use GuzzleHttp\Client AS GuzzleClient;

abstract class GuzzleHttpTransport implements HttpTransportInterface
{
	/** @var  string */
	public $base_url;
	/** @var  string[] */
	public $headers = [];

	/** @var GuzzleClient  */
	protected $client;

	abstract protected function checkSession();

	public function __construct($config)
	{
		$permittedConfig = ['base_url', 'headers'];
		$config = array_intersect_key($config, array_flip($permittedConfig));
		foreach($config AS $key => $value) {
			$this->$key = $value;
		}

		$this->client = new GuzzleClient([
			'base_uri' => $this->base_url,
//			'headers' => $this->headers,
//			'auth' => [$this->username, $this->password],
			'verify' => false,
		]);
	}

	public function setHeaders($headers) {
		$this->headers = $headers;
	}

	public function addHeaders($headers) {
		$this->headers = array_merge($this->headers, $headers);
	}

	public function deleteHeader($key) {
		unset($this->headers[$key]);
	}

	protected function send($method, $url, $payload = [], $checkSession = true)
	{
		if($checkSession) $this->checkSession();

		$allowedMethods = ['GET', 'POST', 'PUT', 'DELETE'];
		if(!in_array($method, $allowedMethods)) throw new \Exception('Only GET, POST and DELETE allowed');

		$allowedPayload = ['query', 'json', 'form_params'];
		foreach($payload AS $key => $value) {
			if(!in_array($key, $allowedPayload)) throw new \Exception("Payload must be 'query' or 'json'");
		}

		$payload['headers'] = $this->headers;

		$response = $this->client->request($method, $url, $payload);
		if($response->getStatusCode() >= 400) throw new \Exception($response->getReasonPhrase(), $response->getStatusCode());

		return $response->getBody()->getContents();
	}

	public function sendGet($url, $query = [])
	{
		return $this->send('GET', $url, ['query' => $query]);
	}

	/**
	 * @deprecated Use sendGet()
	 * @param string $url
	 * @param string[] $query
	 * @return \stdClass
	 */
	public function sendQuery($url, $query)
	{
		return $this->sendGet($url, $query);
	}

	public function sendPostWithJson($url, $json, $query = [])
	{
		return $this->send('POST', $url, [
			'json' => $json,
			'query' => $query,
		]);
	}

	public function sendPostWithFormData($url, $post, $query = [])
	{
		return $this->send('POST', $url, [
			'form_params' => $post,
			'query' => $query,
		]);
	}

	public function sendPutWithJson($url, $json, $query = [])
	{
		return $this->send('PUT', $url, [
			'json' => $json,
			'query' => $query,
		]);
	}

	public function sendDelete($url) {
		return $this->send('DELETE', $url);
	}
}