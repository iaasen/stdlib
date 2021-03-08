<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 22.04.2018
 * Time: 18:00
 */

namespace Iaasen\Exception;


use Throwable;

class NotAuthenticatedException extends \DomainException
{
	public function __construct(
		string $message = "Authentication is required and has failed or has not yet been provided",
		int $code = 401,
		Throwable $previous = null
	) {
		parent::__construct($message, $code, $previous);
	}
}