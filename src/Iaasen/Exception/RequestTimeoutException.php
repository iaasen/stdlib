<?php
/**
 * User: iaase
 * Date: 17.12.2022
 */

namespace Iaasen\Exception;

class RequestTimeoutException extends \RuntimeException
{
	public function __construct(string $message = "", int $code = 408, \Throwable $previous = null)
	{
		if($code == 0) $code = 408;
		parent::__construct($message, $code, $previous);
	}
}