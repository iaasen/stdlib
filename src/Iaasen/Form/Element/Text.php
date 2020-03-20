<?php

namespace Iaasen\Form\Element;

use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\Form\Element\Text AS LaminasText;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\StringLength;

class Text extends LaminasText implements InputProviderInterface {
	
	public function getInputSpecification() {
		return [
			'name' => $this->getName(),
			'required' => true,
			'filters' => [
				new StringTrim(),
				new StripTags(),
			],
			'validators' => [
				new StringLength([
					'encoding' => 'UTF-8',
					'min' => 1,
					'max' => 255
				]),
			]
		];
	}
}