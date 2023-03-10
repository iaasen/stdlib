<?php
/**
 * User: ingvar.aasen
 * Date: 10.03.2023
 */

namespace Iaasen\Form\Element;

use Laminas\Form\Element\Text;
use Laminas\Stdlib\ArrayUtils;

/**
 * Add "jquery-datetimepicker" to package.json
 *
 * Add to the html-page:
 * <link href="(some folder)jquery.datetimepicker.min.css" media="screen" rel="stylesheet" type="text/css">
 * <script src="(some folder)/jquery.datetimepicker.full.min.js"></script>
 *
 * Add this script:
 * $(document).ready(function() {
 *   $.datetimepicker.setLocale('no');
 *   $('.dateTimePicker').datetimepicker({
 *     format: 'Y-m-d H:i',
 *     dayOfWeekStart: 1
 *   });
 * });
 */
class DateTimePicker extends Text {

	const DEFAULT_OPTIONS = [
		'format' => 'Y-m-d H:i',
	];


	public function __construct($name = null, iterable $options = []) {
		parent::__construct($name, $options);
		$this->setAttribute('class', 'dateTimePicker');
	}


	public function setValue($value) {
		if(is_null($value)) return parent::setValue($value);
		return parent::setValue((new \DateTime($value))->format($this->getOption('format')));
	}


	public function setOptions(iterable $options) {
		if ($options instanceof \Traversable) {
			$options = ArrayUtils::iteratorToArray($options);
		}
		return parent::setOptions(array_merge(self::DEFAULT_OPTIONS, $options));
	}
}