<?php

namespace Oppned\Form\Element;

use Laminas\Form\Element\Number AS LaminasNumber;
use Laminas\InputFilter\InputProviderInterface;

class Decimal extends LaminasNumber implements InputProviderInterface {
	
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