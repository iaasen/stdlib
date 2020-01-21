<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 08.02.2017
 * Time: 12:38
 */

namespace Oppned\Form\Element;


class DateTimeLocal extends \Laminas\Form\Element\DateTimeLocal
{
	const DATETIME_LOCAL_FORMAT = 'Y-m-d H:i';
	const DATETIME_FORMAT = self::DATETIME_LOCAL_FORMAT;

	public function init() {
		$this->setFormat('Y-m-d H:i');
		//$this->setAttribute('step', 1);
		$this->setAttribute('class', 'dateTimePicker');
	}

	public function getInputSpecification()
	{
		return [
			'name' => $this->getName(),
			'required' => true,
			'filters' => [
				['name' => 'Laminas\Filter\StringTrim'],
			],
		];

	}
}