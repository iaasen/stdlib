<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 22.04.2018
 * Time: 18:00
 */

namespace Iaasen\Exception;


use Throwable;

class NotFoundException extends \DomainException
{
	public function __construct(
		string $message = "The requested resource could not be found",
		int $code = 404,
		Throwable $previous = null
	) {
		parent::__construct($message, $code, $previous);
	}
}