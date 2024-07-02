<?php
/**
 * User: ingvar.aasen
 * Date: 01.02.2016
 */

namespace Iaasen\Transport;

use GuzzleHttp\Client AS GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Iaasen\Exception\BadGatewayException;
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
	public string $base_url;
	/** @var string[] */
	public array $headers = [];
	/**
	 * Format: ['username', 'password', 'type']
	 * Default type is 'basic'
	 * Other types are 'digest', 'ntlm'
	 * @var string[]
	 */
	private ?array $auth = null;

	protected GuzzleClient $client;
	protected ?bool $cookies = null;
	protected array $historyContainer = [];
	protected bool $debug = false;

	protected int $connect_timeout = 30;

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

		$permittedConfig = ['base_url', 'headers', 'cookies', 'auth', 'connect_timeout'];
		$config = array_intersect_key($config, array_flip($permittedConfig));
		foreach($config AS $key => $value) {
			if($key == 'auth') {
				$authArray = [
					$value['username'],
					$value['password'],
					$value['type'] ?? 'basic',
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
			'verify' => false,
			'timeout' => $this->connect_timeout,
		];
		if($this->auth) $guzzleConfig['auth'] = $this->auth;

		// Log requests to $this->historyContainer
		if($this->debug) {
			$guzzleConfig['handler'] = $this->createHistoryMiddleware();
		}

		$this->client = new GuzzleClient($guzzleConfig);
	}

	protected function createHistoryMiddleware() {
		$history = Middleware::history($this->historyContainer);
		$stack = HandlerStack::create();
		$stack->push($history);
		return $stack;
	}

	/**
	 * Output history to screen
	 */
	public function viewHistory() {
		echo 'Count: ' . count($this->historyContainer) . '<br><br>';
		/** @var array $transaction */
		foreach ($this->historyContainer as $transaction) {
			/** @var Request $request */
			$request = $transaction['request'];
			/** @var Response $response */
			$response = $transaction['response'];
			echo '---------------------------------------------------' . '<br>';
			echo $request->getMethod() . ' ' . $request->getUri() . '<br>';
			echo 'Response: ' . $response->getStatusCode() . ' ' . $response->getReasonPhrase() . '<br>';
			if(strlen($request->getBody())) {
				echo 'Body: <pre>' . $request->getBody() . '</pre><br>';
			}
			echo '<br><br>';
		}
	}

	public function setHeaders(array $headers) {
		$this->headers = $headers;
	}

	public function addHeader(string $key, string $header) {
		$this->addHeaders([$key => $header]);
	}

	public function addHeaders(array $headers) {
		$this->headers = array_merge($this->headers, $headers);
	}

	public function deleteHeader(string $key) {
		unset($this->headers[$key]);
	}


	public function setConnectTimeout(int $seconds) : void {
		$this->connect_timeout = $seconds;
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
		catch (BadResponseException $e) {
			if($e->getCode() == 401) { // 401 when access token is not accepted
				if($this->renewSession()) return $this->internalSend($method, $url, $payload);
			}
			elseif($e->getCode() == 500) {
				throw new BadGatewayException($e->getResponse()->getStatusCode() . ' - ' . $e->getResponse()->getReasonPhrase());
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
		$allowedMethods = ['GET', 'POST', 'PATCH', 'PUT', 'DELETE'];
		if(!in_array($method, $allowedMethods)) {
			throw new InvalidArgumentException('Only GET, POST, PATCH, PUT and DELETE allowed');
		}

		$allowedPayload = ['query', 'json', 'form_params', 'body'];
		foreach($payload AS $key => $value) {
			if(!in_array($key, $allowedPayload)) throw new InvalidArgumentException("Payload must be 'query', 'json', 'form_params' or 'body'");
		}
		$payload['headers'] = $this->headers;
		$payload['connect_timeout'] = $this->connect_timeout;

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
	 * @param $post
	 * @param array $query
	 * @return string
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function sendPatchWithFormData(string $url, $post, array $query = [])
	{
		$this->addHeader('Content-Type', 'application/x-www-form-urlencoded');
		$data = $this->send('PATCH', $url, [
			'form_params' => $post,
			'query' => $query,
		]);
		return $data;
	}

	public function sendPatchWithJson(string $url, $json, array $query = []) {
		$this->addHeader('Content-Type', 'application/json');
		$json = $this->send('PATCH', $url, [
			'json' => $json,
			'query' => $query,
		]);
		return $json;
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
	public function sendDelete(string $url, array $query = []) {
			return $this->send('DELETE', $url, [
			'query' => $query,
		]);
	}
}