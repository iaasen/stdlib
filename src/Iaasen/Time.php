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
		4 => "dd.MM.YYYY", // 17.10.2018
	];

	const MONTH_NAMES = [
		1 => 'januar',
		2 => 'februar',
		3 => 'mars',
		4 => 'april',
		5 => 'mai',
		6 => 'juni',
		7 => 'juli',
		8 => 'august',
		9 => 'september',
		10 => 'oktober',
		11 => 'november',
		12 => 'desember',
	];

	/**
	 * @param \DateTime $dateTime
	 * @param int|string $format
	 * @return string
	 */
	public static function format(\DateTime $dateTime, $format = 1) : string {
		$dateFormatter = new IntlDateFormatter('nb_NO', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT);
		if(is_string($format)) $dateFormatter->setPattern($format);
		else $dateFormatter->setPattern(self::DATE_FORMATS[$format]);
		return $dateFormatter->format($dateTime);
	}

	public static function getMonthName(int $month, bool $ucFirst = false) : string {
		if($ucFirst) return ucfirst(self::MONTH_NAMES[$month]);
		else return self::MONTH_NAMES[$month];
	}

	public static function getStartOfLastWeek() : DateTime {
		return new DateTime('monday last week');
	}

	public static function getStartOfThisWeek() : DateTime {
		return new DateTime('monday this week');
	}

	public static function getStartOfNextWeek() : DateTime {
		return new DateTime('monday next week');
	}

}