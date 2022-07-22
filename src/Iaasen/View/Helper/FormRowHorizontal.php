<?php


namespace Iaasen\View\Helper;


use Laminas\Form\Element;
use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\Form\View\Helper\FormElement;
use Laminas\Form\View\Helper\FormElementErrors;

class FormRowHorizontal extends AbstractHelper
{
	public $rowClass = 'form-group row';
	public $labelClass = 'col-sm-3 control-label col-form-label';
	public $elementDivClass = 'col-sm-9';
	public $inputClass = 'form-control';
	public $submitDivClass = 'col-sm-9 col-sm-offset-3 text-right';
	public $submitClass = 'btn btn-default';
	public $checkboxDivClass = 'col-sm-9 col-sm-offset-3';

	/** @var FormElement */
	protected $elementHelper;

	/** @var FormElementErrors */
	protected $elementErrorsHelper;


	public function __invoke(?Element $element = null)
	{
		if(!$element) return $this;

		$errorHelper = $this->getElementErrorsHelper();
		$elementHelper = $this->getElementHelper();

		switch($element->getAttribute('type')) {
			case 'hidden':
				return $errorHelper($element) . $elementHelper($element);

			case 'submit':
				if(strpos($element->getAttribute('class'), 'btn-primary') !== null) $element->setOption('variant', 'primary');
				if(!$element->getAttribute('class')) $element->setAttribute('class', $this->submitClass);
				$element->setValue($element->getLabel());
				return <<<EOT
					<div class="$this->rowClass">
						<div class="{$this->submitDivClass}">
							{$errorHelper($element)}
							{$elementHelper($element)}
						</div>
					</div>
EOT;

			case 'checkbox':
				$element->setAttribute('class', '');
				$checked = $element->getValue() ? ' checked="checked"' : '';
				return <<<EOT
					<div class="$this->rowClass">
						<div class="{$this->checkboxDivClass}">
							<div class="checkbox">
								{$errorHelper($element)}
								<input type="hidden" name="{$element->getName()}" value="0">
								<label>
									<input id="{$element->getName()}" type="checkbox" name="{$element->getName()}" value="1"{$checked}>
									{$element->getLabel()}
								</label>
							</div>
						</div>
					</div>
EOT;

			default:
				$element->setAttribute('class', implode(' ', [$this->inputClass, $element->getAttribute('class')]));
				return <<<EOT
					<div class="$this->rowClass">
						<label class="$this->labelClass" for="{$element->getName()}">{$element->getLabel()}</label>
						<div class="$this->elementDivClass">
							{$errorHelper($element)}
							{$elementHelper($element)}
						</div>
					</div>
EOT;

		}
	}



    /**
     * Retrieve the FormElement helper
     *
     * @return FormElement
     */
    protected function getElementHelper()
    {
        if ($this->elementHelper) {
            return $this->elementHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->elementHelper = $this->view->plugin('form_element');
        }

        if (! $this->elementHelper instanceof FormElement) {
            $this->elementHelper = new FormElement();
        }

        return $this->elementHelper;
    }

    /**
     * Retrieve the FormElementErrors helper
     *
     * @return FormElementErrors
     */
    protected function getElementErrorsHelper()
    {
        if ($this->elementErrorsHelper) {
            return $this->elementErrorsHelper;
        }

        if (method_exists($this->view, 'plugin')) {
            $this->elementErrorsHelper = $this->view->plugin('form_element_errors');
        }

        if (! $this->elementErrorsHelper instanceof FormElementErrors) {
            $this->elementErrorsHelper = new FormElementErrors();
        }

        return $this->elementErrorsHelper;
    }
}