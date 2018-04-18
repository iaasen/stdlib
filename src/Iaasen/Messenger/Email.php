<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 18.04.2018
 * Time: 21:06
 */

namespace Iaasen\Messenger;


use Iaasen\Model\AbstractEntity;

class Email extends AbstractEntity
{
	/** @var  string */
	public $from;
	/** @var  string[] */
	public $to;
	/** @var  string */
	public $subject;
	/** @var  string */
	public $body;
	/** @var  string */
	public $body_html;
//	/** @var  array */
//	public $attachments;

	public function setTo($value) {
		if(is_string($value)) $value = [$value];
		foreach($value as $row) {
			if(!filter_var($row, FILTER_VALIDATE_EMAIL)) throw new \InvalidArgumentException($row . ' is not a valid email address', 400);
		}
		$this->to = $value;
	}
}