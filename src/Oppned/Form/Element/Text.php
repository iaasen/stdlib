<?php

namespace Oppned\Form\Element;

use Zend\Form\Element\Text AS ZendText;
use Zend\InputFilter\InputProviderInterface;

class Text extends ZendText implements InputProviderInterface {
	
	public function getInputSpecification() {
		return array(
			'name' => $this->getName(),
			'required' => true,
			'filters' => array(
				array('name' => 'Zend\Filter\StringTrim'),
				array('name' => 'Zend\Filter\StripTags'),
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