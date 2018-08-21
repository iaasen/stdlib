<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 01.02.2016
 * Time: 12:55
 */

namespace Iaasen\Transport;

use GuzzleHttp\Client AS GuzzleClient;
use Iaasen\Exception\InvalidArgumentException;

/**
 * Class GuzzleHttpTransport
 * @package Iaasen\Transport
 * Properties:
 * 		$base_url - Url of the service without a trailing slash. Endpoint urls should not start with a slash
 * 		$headers - Format: ['name' => 'value']
 * Basic auth: Add to config: ['auth' => ['username', 'password']]
 */
abstract class GuzzleHttpTransport implements HttpTransportInterface
{
	/** @var  string */
	public $base_url;
	/** @var  string[] */
	public $headers = [];
	/**
	 * Format: ['username', 'password', 'type']
	 * Default type is 'basic'
	 * Other types are 'digest', 'ntlm'
	 * @var string[]
	 */
	private $auth;

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
	abstract protected function renewSession() : bool;

	public function __construct(array $config)
	{

		$permittedConfig = ['base_url', 'headers', 'cookies', 'auth'];
		$config = array_intersect_key($config, array_flip($permittedConfig));
		foreach($config AS $key => $value) {
			if($key == 'auth') {
				$authArray = [
					$value['username'],
					$value['password'],
					isset($value['type']) ? $value['type'] : 'basic',
				];
				$this->auth = $authArray;
			}
			else {
				$this->$key = $value;
			}
		}

		$guzzleConfig = [
			'base_uri' => $this->base_url,
			'http_errors' => true, //  Don't throw exceptions for 4xx errors
			'cookies' => $this->cookies,
//			'headers' => $this->headers,
//			'auth' => [$this->username, $this->password],
			'verify' => false
		];
		if($this->auth) $guzzleConfig['auth'] = $this->auth;


		$this->client = new GuzzleClient($guzzleConfig);
	}

	public function setHeaders(array $headers) {
		$this->headers = $headers;
	}

	public function addHeader(string $name, string $value) {
		$this->addHeaders([$name => $value]);
	}

	public function addHeaders(array $headers) {
		$this->headers = array_merge($this->headers, $headers);
	}

	public function deleteHeader(string $key) {
		unset($this->headers[$key]);
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $payload
	 * @param bool $checkSession
	 * @return string
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function send(string $method, string $url, array $payload = [], bool $checkSession = true)
	{
		if($checkSession) $this->checkSession();
		try {
			return $this->internalSend($method, $url, $payload);
		}
		catch(\GuzzleHttp\Exception\GuzzleException $e) {
			if($e->getCode() == 401) { // 401 when access token is not accepted
				if($this->renewSession()) return $this->internalSend($method, $url, $payload);
			}
			throw $e;
		}
	}

	/**
	 * @param string $method
	 * @param string $url
	 * @param array $payload
	 * @return string
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	protected function internalSend(string $method, string $url, array $payload = []) : string {
		$allowedMethods = ['POST', 'GET', 'PUT', 'DELETE'];
		if(!in_array($method, $allowedMethods)) {
			throw new InvalidArgumentException('Only GET, POST, PUT and DELETE allowed');
		}

		$allowedPayload = ['query', 'json', 'form_params', 'body'];
		foreach($payload AS $key => $value) {
			if(!in_array($key, $allowedPayload)) throw new InvalidArgumentException("Payload must be 'query', 'json', 'form_params' or 'body'");
		}
		$payload['headers'] = $this->headers;

		$response = $this->client->request($method, $url, $payload);

		return $response->getBody()->getContents();
	}

	/**
	 * @param string $url
	 * @param array $query
	 * @return string
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function sendGet(string $url, array $query = [])
	{
		return $this->send('GET', $url, ['query' => $query]);
	}

	/**
	 * @deprecated Use sendGet()
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function sendQuery($url, $query)
	{
		return $this->sendGet($url, $query);
	}

	/**
	 * @param string $url
	 * @param $json
	 * @param array $query
	 * @return string
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function sendPostWithJson(string $url, $json, array $query = [])
	{
		return $this->send('POST', $url, [
			'json' => $json,
			'query' => $query,
		]);
	}

	/**
	 * @param string $url
	 * @param $body
	 * @param array $query
	 * @return string
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function sendPostWithBody(string $url, $body, array $query = []) {
		$data = $this->send('POST', $url, [
			'body' => $body,
			'query' => $query,
		]);
		return $data;
	}

	/**
	 * @param string $url
	 * @param $post
	 * @param array $query
	 * @return string
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function sendPostWithFormData(string $url, $post, array $query = [])
	{
		$this->addHeader('Content-Type', 'application/x-www-form-urlencoded');
		$data = $this->send('POST', $url, [
			'form_params' => $post,
			'query' => $query,
		]);
		$this->addHeader('Content-Type', 'application/json');
		return $data;
	}

	/**
	 * @param string $url
	 * @param $json
	 * @param array $query
	 * @return string
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function sendPutWithJson(string $url, $json, array $query = [])
	{
		return $this->send('PUT', $url, [
			'json' => $json,
			'query' => $query,
		]);
	}

	/**
	 * @param string $url
	 * @return string
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function sendDelete(string $url) {
		return $this->send('DELETE', $url);
	}
}