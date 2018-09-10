<?php
/**
 * User: ingvar.aasen
 * Date: 10.09.2018
 * Time: 15:14
 */

namespace Iaasen\Exception;


class LogicException extends \LogicException
{
	public function __construct(string $message = "", int $code = 0, \Throwable $previous = null)
	{
		if($code === 0) $code = 500;
		parent::__construct($message, $code, $previous);
	}

}