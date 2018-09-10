<?php
/**
 * User: ingvar.aasen
 * Date: 04.09.2018
 * Time: 13:27
 */

namespace Iaasen\Debug;


use Iaasen\Exception\InvalidArgumentException;

class Timer
{
	protected static $timestamp;

	public static function setStart() : void {
		self::$timestamp = microtime(true);
	}

	/**
	 * @param bool $output Set true to print time to screen/output
	 * @return float
	 */
	public static function getElapsed() : float {
		if(!self::$timestamp) throw new InvalidArgumentException('Counter is not started using setStart()');
		$elapsed = microtime(true) - self::$timestamp;
		self::setStart();
		return $elapsed;
	}

	public static function printElapsed(?string $tag = '') : void {
		$elapsed = self::getElapsed();
		if($tag) echo $tag . ' - ' . date('c') . ' - ' . $elapsed . '<br>' . PHP_EOL;
	}
}