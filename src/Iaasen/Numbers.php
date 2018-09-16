<?php
/**
 * Created by PhpStorm.
 * User: ingvar.aasen
 * Date: 29.06.2017
 * Time: 12:02
 */

namespace Iaasen;


class Numbers
{
	public static function secondsToHumanReadable($seconds) {
		if ($seconds == 0) return '0 s';
		else {
			$s = $seconds % 60;
			$m = floor(($seconds % 3600) / 60);
			$h = floor(($seconds % 86400) / 3600);
			$d = floor($seconds / 86400);
			//$d = floor(($up%2592000)/86400);
			//$M = floor($up/2592000);
			$txt = '';
			//if($M) $txt = $M . 'mnd';
			if ($d) $txt .= ' ' . $d . 'd';
			if ($h) $txt .= ' ' . $h . 't';
			if ($m) $txt .= ' ' . $m . 'm';
			if ($s) $txt .= ' ' . $s . 's';
		}
		return ltrim($txt, ' ,');
	}

	public static $defaultFormat = [
		0, // Decimals
		',', // Decimal point
		' ', // Thousands separator
	];

	/**
	 * @param int $number
	 * @return string
	 */
	public static function format(float $number) : string {
		return number_format($number, self::$defaultFormat[0], self::$defaultFormat[1],self::$defaultFormat[2]);
	}
}