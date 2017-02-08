<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 08.02.2017
 * Time: 12:38
 */

namespace Oppned\Form\Element;


class Date extends \Zend\Form\Element\DateTime
{
	public function init() {
		$this->setOption('format', 'Y-m-d');
		$this->setAttribute('step', 1);
		$this->setAttribute('class', 'datePicker');
	}

}