<?php

namespace Iaasen\Exception;


/**
 * The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.
 */
class GatewayTimeoutException extends \RuntimeException
{
	public function __construct(
		string $message = "The server was acting as a gateway or proxy and did not receive a timely response from the upstream server.",
		int $code = 504,
		\Throwable $previous = null
	) {
		parent::__construct($message, $code, $previous);
	}

}