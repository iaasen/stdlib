<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 07.11.2016
 * Time: 09:22
 */

namespace Oppned\Transport;


interface HttpTransportInterface
{

	/**
	 * @param string $method GET, POST, PUT, DELETE
	 * @param string $url
	 * @param array $payload
	 * @return mixed
	 */
	public function send($method, $url, $payload = []);

	public function sendGet($url, $query = []);
	public function sendPostWithJson($url, $json, $query = []);
	public function sendPostWithFormData($url, $post, $query = []);
	public function sendPutWithJson($url, $json, $query = []);
	public function sendDelete($url);

	public function setHeaders($headers);
	public function addHeaders($headers);
	public function deleteHeader($key);
}