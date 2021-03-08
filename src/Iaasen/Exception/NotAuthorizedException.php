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
	public function __construct(
		string $message = "Forbidden",
		int $code = 403,
		Throwable $previous = null
	) {
		parent::__construct($message, $code, $previous);
	}
}