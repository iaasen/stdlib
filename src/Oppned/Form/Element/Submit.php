<?php

namespace Oppned\Form\Element;

use Zend\Form\Element\Submit AS ZendSubmit;

class Submit extends ZendSubmit {
	
	public function __construct($name = null, $options = []) {
		$this->label = 'Save';
		//$this->attributes['id'] = 'submitbutton';
		return parent::__construct($name, $options);
	}
	
}