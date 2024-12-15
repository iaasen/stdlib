<?php
/**
 * User: ingvar.aasen
 * Date: 29.06.2017
 */

namespace Iaasen;

class Numbers {

	/**
	 * Makes the class usable as a Laminas view helper
	 */
	public function __invoke(): self {
		return $this;
	}


	public static function secondsToHumanReadable(int $seconds): string {
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


	public static function bytesToHumanReadable(int $size, int $precision = 2): string {
		$units = ['B','kB','MB','GB','TB','PB','EB','ZB','YB'];
		$step = 1024;
		$i = 0;
		while (($size / $step) > 0.9) {
			$size = $size / $step;
			$i++;
		}
		return round($size, $precision). ' ' . $units[$i];
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
