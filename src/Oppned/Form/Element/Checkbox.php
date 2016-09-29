<?php

namespace Oppned\Form\Element;

use Zend\Form\Element\Checkbox AS ZendCheckbox;
use Zend\InputFilter\InputProviderInterface;

class Checkbox extends ZendCheckbox implements InputProviderInterface {
	
	public function getInputSpecification() {
		return array(
			'name' => $this->getName(),
			'required' => true,
			'allow_empty' => true,
			'filters' => array(
				array('name' => 'Boolean')
			),
		);		
	}
}