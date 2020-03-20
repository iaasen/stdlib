<?php

namespace Iaasen\Form\Element;

use Laminas\Form\Element\Submit AS ZendSubmit;

class Submit extends ZendSubmit {
	
	public function __construct($name = null, $options = []) {
		$this->label = 'Save'; // TwbBundle requires a label to be set
		parent::__construct($name, $options);
	}
	
}