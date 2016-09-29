<?php

namespace Oppned\Form\Element;

use Zend\Form\Element\Number AS ZendNumber;
use Zend\InputFilter\InputProviderInterface;

class Number extends ZendNumber implements InputProviderInterface {
	public function getInputSpecification() {
		return array(
			'name' => $this->getName(),
			'required' => false,
			'filters' => array(
				array('name' => 'Int'),
				array(
					'name' => 'Null',
					'options' => array(
						'type' => 'integer',
					),
				),
			),
		);
	}
}