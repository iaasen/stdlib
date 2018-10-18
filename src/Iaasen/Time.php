<?php
/**
 * User: ingvar.aasen
 * Date: 11.10.2018
 * Time: 12:50
 */

namespace Iaasen;



use IntlDateFormatter;

class Time
{
	const DATE_FORMATS = [
		1 => "EEEE dd.MM.YYYY HH':'mm", // onsdag 17.10.2018 15:01
		2 => "dd.MM.YYYY HH':'mm EEEE", // 17.10.2018 15:01 onsdag
		3 => "dd.MM.YYYY HH':'mm", // 17.10.2018 15:01
	];

	public static function format(\DateTime $dateTime, int $format = 1) : string {
		$dateFormatter = new IntlDateFormatter('nb_NO', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
		$dateFormatter->setPattern(self::DATE_FORMATS[$format]);
		return $dateFormatter->format($dateTime);
	}
}