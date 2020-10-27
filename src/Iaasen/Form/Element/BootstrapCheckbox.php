<?php
/**
 * User: ingvar
 * Date: 20.10.2020
 * Time: 13.09
 */

namespace Iaasen\Form\Element;



class BootstrapCheckbox extends \Laminas\Form\Element\Checkbox
{
	protected $attributes = [
		'type' => 'checkbox',
		'data-on-text' => 'On',
		'data-off-text' => 'Off',
		'data-on-color' => 'success',
		'data-off-color' => 'danger',
		'data-size' => 'small',
	];



}