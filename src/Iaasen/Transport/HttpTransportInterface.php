<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 07.11.2016
 * Time: 09:22
 */

namespace Iaasen\Transport;


interface HttpTransportInterface
{
	public function send(string $method, string $url, array $payload = [], bool $checkSession = true);
	public function sendGet(string $url, array $query = []);
	public function sendPostWithJson(string $url, $json, array $query = []);
	public function sendPostWithBody(string $url, $body, array $query = []);
	public function sendPostWithFormData(string $url, $post, array $query = []);
	public function sendPutWithJson(string $url, $json, array $query = []);
	public function sendDelete(string $url);

	public function setHeaders(array $headers);
	public function addHeaders(array $headers);
	public function addHeader(string $key, string $header);
	public function deleteHeader(string $key);
}