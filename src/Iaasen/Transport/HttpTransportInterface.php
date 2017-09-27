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
	public function sendGet($url, $query = []);
	public function sendPostWithJson($url, $json, $query = []);
	public function sendPostWithFormData($url, $post, $query = []);
	public function sendPutWithJson($url, $json, $query = []);
	public function sendDelete($url);

	public function setHeaders($headers);
	public function addHeaders($headers);
	public function deleteHeader($key);
}