<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 08.03.2016
 * Time: 15:27
 */

namespace Oppned;


class AbstractEntity
{
	public function __construct($data = [])
	{
		if(count($data)) $this->exchangeArray($data);
	}

	public function exchangeArray($data) {
		foreach($data AS $key => $value) {
			if(isset($this->hydrate) && isset($this->hydrate[$key])) {
				$hydrateKey = $this->hydrate[$key]['name'];
				if(isset($this->hydrate[$key]['type'])) {
					switch($this->hydrate[$key]['type']) {
						case 'jsonDecode':
							$value = json_decode($value, true);
							break;
						case 'int':
							$value = (int) $value;
							break;
					}
				}
				if(is_null($this->$hydrateKey) || !is_null($value)) { // Make sure default values are not overwritten with null
					$this->$hydrateKey = $value;
				}
			}
			elseif(property_exists($this, $key)) {
				if(is_null($this->$key) || !is_null($value)) { // Make sure default values are not overwritten with null
					$this->$key = $value;
				}
			}
			else {
				$this->$key = $value;
			}
		}
	}

	public function getArrayCopy() {
		return (array) $this;
	}

}