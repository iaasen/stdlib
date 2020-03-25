<?php

namespace Iaasen\Form\Element;

use Laminas\Filter\Boolean;
use Laminas\Form\Element\Checkbox AS ZendCheckbox;
use Laminas\InputFilter\InputProviderInterface;

class Checkbox extends ZendCheckbox implements InputProviderInterface {
	
	public function getInputSpecification() {
		return [
			'name' => $this->getName(),
			'required' => true,
			'allow_empty' => true,
			'filters' => [
				['name' => Boolean::class]
			],
		];
	}
}