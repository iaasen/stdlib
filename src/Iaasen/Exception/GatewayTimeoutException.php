<?php

namespace Iaasen\Exception;


class GatewayTimeoutException extends \RuntimeException
{
	public function __construct(string $message = "", int $code = 504, \Throwable $previous = null)
	{
		parent::__construct($message, $code, $previous);
	}

}