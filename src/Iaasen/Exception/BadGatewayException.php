<?php

namespace Iaasen\Exception;

/**
 * The server was acting as a gateway or proxy and received an invalid response from the upstream server.
 */
class BadGatewayException extends \RuntimeException
{
	public function __construct(
		string $message = "The server was acting as a gateway or proxy and received an invalid response from the upstream server.",
		int $code = 502,
		\Throwable $previous = null
	) {
		parent::__construct($message, $code, $previous);
	}

}