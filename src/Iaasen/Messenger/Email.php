<?php
/**
 * User: iaase
 * Date: 18.04.2018
 */

namespace Iaasen\Messenger;

use Iaasen\Exception\InvalidArgumentException;
use Iaasen\Model\AbstractEntityV2;

/**
 * @property string $from Sender email address
 * @property array $to Recipient email addresses
 * @property string $subject Email subject
 * @property string $body Plain text email body
 * @property string $body_html HTML email body
 */
class Email extends AbstractEntityV2
{
	protected string $from;
	/** @var string[] */
	protected array $to;
	protected string $subject;
	protected string $body;
	protected string $body_html;

	public function setTo(string|array $value): void {
		if(is_string($value)) $value = [$value];
		foreach($value as $row) {
			if(!filter_var($row, FILTER_VALIDATE_EMAIL)) throw new InvalidArgumentException($row . ' is not a valid email address', 400);
		}
		$this->to = $value;
	}

}
