<?php

namespace Oppned\Form\Element;

use Laminas\Form\Element\Hidden AS ZendHidden;
use Laminas\InputFilter\InputProviderInterface;

class Primary extends ZendHidden implements InputProviderInterface {

	public function getInputSpecification() {
		return [
			'name' => $this->getName(),
			'required' => true,
			'filters' => [
				[
					'name' => 'Int'
				],
				[
					'name' => 'Null',
					'options' => [
						'type' => 'integer',
					],
				],
			],
		];
	}
}