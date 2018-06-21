<?php
/**
 * User: ingvar.aasen
 * Date: 21.06.2018
 * Time: 16:00
 */

namespace Iaasen\Exception;


class InvalidArgumentException extends \InvalidArgumentException
{
	public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
	{
		if($code === 0) $code = 400;
		parent::__construct($message, $code, $previous);
	}

}