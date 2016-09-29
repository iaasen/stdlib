<?php

namespace Oppned\Form\Element;

use Zend\Form\Element\Number AS ZendNumber;
use Zend\InputFilter\InputProviderInterface;

class Decimal extends ZendNumber implements InputProviderInterface {
	
	public function getInputSpecification() {
		return array(
			'name' => $this->getName(),
			'required' => true,
			'filters' => array(
			),
			'validators' => array(
				array('name' => 'Float'),
			)
		);		
	}
}