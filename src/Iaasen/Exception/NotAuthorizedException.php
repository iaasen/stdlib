<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 22.04.2018
 * Time: 18:00
 */

namespace Iaasen\Exception;


use Throwable;

class NotAuthorizedException extends \DomainException
{
	public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
	{
		if($code === 0) $code = 403;
		parent::__construct($message, $code, $previous);
	}
}