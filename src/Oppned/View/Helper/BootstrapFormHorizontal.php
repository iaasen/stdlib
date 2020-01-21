<?php
namespace Oppned\View\Helper;

use Laminas\Form\Element;
use Laminas\Form\Fieldset;
use Laminas\View\Helper\AbstractHelper;

class BootstrapFormHorizontal extends AbstractHelper
{
	/**
	 * @param \Laminas\Form\Form $form
	 * @return mixed
	 */
	public function __invoke($form) {
		/** @var Element $element */
		foreach($form->getElements() AS $element) {
			switch($element->getAttribute('type')) {
				case 'submit':
					$element->setOptions(array('column-size' => 'sm-9 col-sm-offset-3 text-right'));
					break;
				case 'checkbox':
					$element->setOptions(array('column-size' => 'sm-9 col-sm-offset-3'));
					break;
				default:
					$element->setOptions(array('column-size' => 'sm-9', 'label_attributes' => array('class' => 'col-sm-3')));
					break;
			}
		}
		foreach($form->getFieldsets() AS $fieldset) {
			/** @var Fieldset $fieldset */
			foreach($fieldset->getElements() AS $element) {
				switch($element->getAttribute('type')) {
					case 'submit':
						$element->setOptions(array('column-size' => 'sm-9 col-sm-offset-3'));
						break;
					case 'checkbox':
						$element->setOptions(array('column-size' => 'sm-9 col-sm-offset-3'));
						break;
					default:
						$element->setOptions(array('column-size' => 'sm-9', 'label_attributes' => array('class' => 'col-sm-3')));
						break;
				}
			}
			/** @var Fieldset $subFieldset */
			foreach($fieldset->getFieldsets() AS $subFieldset) {
				/** @var Fieldset $subElement */
				foreach($subFieldset->getElements() AS $subElement) {
					switch($subElement->getAttribute('type')) {
						case 'submit':
							$subElement->setOptions(array('column-size' => 'sm-9 col-sm-offset-3'));
							break;
						case 'checkbox':
							$subElement->setOptions(array('column-size' => 'sm-9 col-sm-offset-3'));
							break;
						default:
							$subElement->setOptions(array('column-size' => 'sm-9', 'label_attributes' => array('class' => 'col-sm-3')));
							break;
					}
				}
			}
		}
		return $form;
	}
	
}