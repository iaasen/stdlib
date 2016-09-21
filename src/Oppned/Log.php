<?php

namespace Oppned;

class Log {
	protected $data = array(
		'id' => null,
		'user' => null,
		'group' => null,
		'priority' => null,
		'message' => null,
		'viewed' => false,
		'timestamp_created' => null
	);
	
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
	

	public function __construct($data = null) {
		if (! is_null($data)) {
			foreach ( $data as $name => $value ) {
				$this->{$name} = $value;
			}
		}
		$this->timestamp_created = time();
	}

	/**
	 * Called when object is created from database by TableGateway
	 * Called when form is validated.
	 *
	 * @param array $data
	 *        	The fields from the database
	 */
	public function exchangeArray($data) {
		foreach ( $data as $key => $value ) {
			try {
				switch ($key) {
					case 'timestamp_created' :
						$this->$key = strtotime($value);
						break;
					default :
						$this->$key = $value;
						break;
				}
			} catch ( \Exception $e ) {
			}
		}
	}

	/**
	 * Called by $form->bind()
	 *
	 * @return array $data Arraycopy of the datafields
	 */
	public function getArrayCopy() {
		$_data = array();
		foreach ( $this->data as $key => $value ) {
			if (strlen($value))
				switch ($key) {
					case 'timestamp_created' :
						$_data[$key] = date("d.m.Y", $value);
						break;
					default :
						$_data[$key] = $value;
						break;
				}
			else {
				$_data[$key] = null;
			}
		}
		return $_data;
	}

	/**
	 * Used by DbTable to format modeldata for the database.
	 *
	 * @return array $_data
	 */
	public function databaseSaveArray() {
		$_data = array();
		foreach ($this->data as $key => $value ) {
			switch ($key) {
				case 'viewed' :
					$_data[$key] = ($value) ? 1 : 0;
					break;
				case 'timestamp_created' :
					$_data[$key] = date("Y-m-d H:i:s", $value);
					break;
				default :
					$_data[$key] = (strlen($value)) ? $value : null;
					break;
			}
		}
		return $_data;
	}

	public function __toString() {
		$_data = get_class($this) . "\n";
		$_data .= print_r($this->data, true);
		return $_data;
	}

	public function __set($name, $value) {
		switch($name) {
			case 'priority':
				if(is_numeric($value)) {
					$this->data[$name] = $value;
				}
				else {
					foreach(self::$priorities AS $key => $level) {
						if($level == strtoupper($value)) $this->data[$name] = $key;
					}
				}
				break;
			case 'viewed':
				$this->data[$name] = ($value) ? true : false;
			default:
				if (array_key_exists($name, $this->data)) {
					$this->data[$name] = $value;
					// throw new Exception("You cannot set '$name' on " . get_class($this));
				}
				break;
		}
		return false;
	}
	
	public function __get($name) {
		switch($name) {
			case 'priorityName':
				return self::$priorities[$this->data[$name]];
				break;
			default:
				if (array_key_exists($name, $this->data)) {
					return $this->data[$name];
				}
				break;
		}
		return false;
	}

	public function __sleep() {
		return array_keys((array) $this);
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
