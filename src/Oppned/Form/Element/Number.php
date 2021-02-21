<?php

namespace Oppned\Form\Element;

use Laminas\Form\Element\Number AS LaminasNumber;
use Laminas\InputFilter\InputProviderInterface;

class Number extends LaminasNumber implements InputProviderInterface {
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