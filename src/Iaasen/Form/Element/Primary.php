<?php

namespace Iaasen\Form\Element;

use Laminas\Filter\ToInt;
use Laminas\Filter\ToNull;
use Laminas\Form\Element\Hidden;
use Laminas\InputFilter\InputProviderInterface;

class Primary extends Hidden implements InputProviderInterface {

	public function getInputSpecification() {
		return [
			'name' => $this->getName(),
			'required' => true,
			'filters' => [
				['name' => ToInt::class],
				[
					'name' => ToNull::class,
					'options' => [
						'type' => 'integer',
					],
				],
			],
		];
	}
}