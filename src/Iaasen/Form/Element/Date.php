<?php
/**
 * User: ingvar.aasen
 * Date: 08.02.2017
 * Time: 12:38
 */

namespace Iaasen\Form\Element;


class Date extends \Laminas\Form\Element\Date
{
	public function init() {
		$this->setAttribute('class', 'datePicker');
	}

	public function setValue($value)
	{
		$match = preg_match('/\d{4}-\d{2}-\d{2}/', $value, $matches);
		if($match) return parent::setValue($matches[0]);
		else return parent::setValue($value);
	}
}