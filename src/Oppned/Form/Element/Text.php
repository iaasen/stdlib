<?php

namespace Oppned\Form\Element;

use Laminas\Form\Element\Text AS LaminasText;
use Laminas\InputFilter\InputProviderInterface;

class Text extends LaminasText implements InputProviderInterface {
	
	public function getInputSpecification() {
		return array(
			'name' => $this->getName(),
			'required' => true,
			'filters' => array(
				array('name' => 'Laminas\Filter\StringTrim'),
				array('name' => 'Laminas\Filter\StripTags'),
			),
			'validators' => array(
				array(
					'name' => 'StringLength',
					'options' => array(
						'encoding' => 'UTF-8',
						'min' => 1,
						'max' => 255
					)
				)
			)
		);		
	}
}