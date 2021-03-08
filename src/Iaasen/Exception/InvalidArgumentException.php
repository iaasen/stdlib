<?php
/**
 * User: ingvar.aasen
 * Date: 21.06.2018
 * Time: 16:00
 */

namespace Iaasen\Exception;


class InvalidArgumentException extends \InvalidArgumentException
{
	public function __construct(
		string $message = "Bad request",
		int $code = 400,
		\Throwable $previous = null
	) {
		parent::__construct($message, $code, $previous);
	}

}