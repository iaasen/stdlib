<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 08.02.2017
 * Time: 12:38
 */

namespace Oppned\Form\Element;


class DateTimeLocal extends \Zend\Form\Element\DateTimeLocal
{
	public function init() {
		//$this->setOption('format', 'Y-m-d H:i');
		//$this->setAttribute('step', 1);
		$this->setAttribute('class', 'dateTimePicker');
	}
}