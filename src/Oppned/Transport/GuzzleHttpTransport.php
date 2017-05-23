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
	/** @var  bool */
	protected $cookies;

	/**
	 * Is run before each request
	 * @return void
	 */
	abstract protected function checkSession();

	/**
	 * Is run if error code is given from server
	 * Request is attempted a second time if function returns true
	 * If function returns false the API error will be forwarded
	 * @return bool
	 */
	abstract protected function renewSession();

	public function __construct($config)
	{

		$permittedConfig = ['base_url', 'headers', 'cookies'];
		$config = array_intersect_key($config, array_flip($permittedConfig));
		foreach($config AS $key => $value) {
			$this->$key = $value;
		}

		$guzzleConfig = [
			'base_uri' => $this->base_url,
			'http_errors' => true, //  Don't throw exceptions for 4xx errors
			'cookies' => $this->cookies,
//			'headers' => $this->headers,
//			'auth' => [$this->username, $this->password],
			'verify' => false
		];

		$this->client = new GuzzleClient($guzzleConfig);
	}

	public function setHeaders($headers) {
		$this->headers = $headers;
	}

	public function addHeader($name, $value) {
		$this->addHeaders([$name => $value]);
	}

	public function addHeaders($headers) {
		$this->headers = array_merge($this->headers, $headers);
	}

	public function deleteHeader($key) {
		unset($this->headers[$key]);
	}

	public function send($method, $url, $payload = [], $checkSession = true)
	{
		if($checkSession) $this->checkSession();
		try {
			return $this->internalSend($method, $url, $payload);
		}
		catch(\Exception $e) {
			if($e->getCode() == 401) { // 401 when access token is not accepted
				if($this->renewSession()) return $this->internalSend($method, $url, $payload);
			}
			throw $e;
		}
	}

	protected function internalSend($method, $url, $payload = []) {
		$allowedMethods = ['POST', 'GET', 'PUT', 'DELETE'];
		if(!in_array($method, $allowedMethods)) {
			throw new \Exception('Only GET, POST, PUT and DELETE allowed');
		}

		$allowedPayload = ['query', 'json', 'form_params'];
		foreach($payload AS $key => $value) {
			if(!in_array($key, $allowedPayload)) throw new \Exception("Payload must be 'query', 'json' or 'form_params'");
		}
		$payload['headers'] = $this->headers;

		$response = $this->client->request($method, $url, $payload);
		return $response->getBody()->getContents();
	}

	public function sendGet($url, $query = [])
	{
		return $this->send('GET', $url, ['query' => $query]);
	}

	/**
	 * @deprecated Use sendGet()
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
		$this->addHeader('Content-Type', 'application/x-www-form-urlencoded');
		$data = $this->send('POST', $url, [
			'form_params' => $post,
			'query' => $query,
		]);
		$this->addHeader('Content-Type', 'application/json');
		return $data;
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