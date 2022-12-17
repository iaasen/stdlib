<?php

namespace Oppned\Log;

use Iaasen\Model\AbstractModel;

/**
 * Class Log
 * @package Oppned\Log
 * @property int $id
 * @property string $user
 * @property string $group
 * @property int $priority
 * @property string $message
 * @property bool $viewed
 */
class Log extends AbstractModel {
	/** @var  int */
	protected $id;
	/** @var  string */
	protected $user;
	/** @var  string  */
	protected $group;
	/** @var  int */
	protected $severity;
	/** @var  string */
	protected $message;
	/** @var  bool */
	protected $viewed;

	const EMERG  = 0;
	const ALERT  = 1;
	const CRIT   = 2;
	const ERR    = 3;
	const WARN   = 4;
	const NOTICE = 5;
	const INFO   = 6;
	const DEBUG  = 7;

	public static $priorities = array(
		0 => 'EMERG',
		1 => 'ALERT',
		2 => 'CRIT',
		3 => 'ERR',
		4 => 'WARN',
		5 => 'NOTICE',
		6 => 'INFO',
		7 => 'DEBUG'
	);


	public function __set($name, $value) {
		switch($name) {
			case 'severity':
			case 'priority':
				if(is_numeric($value)) {
					$this->severity = $value;
				}
				else {
					$this->severity = array_search($value, self::$priorities);
				}
				break;
			default:
				parent::__set($name, $value);
				break;
		}
	}
	
	public function __get($name) {
		switch($name) {
			case 'priority':
				return $this->severity;
			case 'severityName':
			case 'priorityName':
				return self::$priorities[$this->severity];
				break;
			default:
				return parent::__get($name);
				break;
		}
	}

	public function __clone() {
		$this->id = null;
	}

	public function toArray() {
		$data = array();
		foreach ( $this->data as $key => $value ) {
			$data[$key] = $value;
		}
		return $data;
	}

}
