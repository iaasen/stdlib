<?php
/**
 * User: ingvar.aasen
 * Date: 29.09.2017
 * Time: 15:24
 */

namespace Iaasen\Console;


use Laminas\Console\ColorInterface AS Color;
use Laminas\Console\Console;
use Laminas\Log\Writer\Stream AS LaminasStream;
use Laminas\Log\Logger AS LaminasLogger;

class Logger
{
	const DEFAULT_PATH = '/var/log/api-workers/';

	protected static $loggerInstance;

	protected static $consoleInstance;

	public static function createInstance($filename = null, $path = null) {
		if(self::$loggerInstance) self::$loggerInstance = null;

		global $argv;
		$filename = $filename ?? (($argv[1]) ? $argv[1] . '.log' : 'other.log');
		$path = $path ?? self::DEFAULT_PATH;
		$logFileName = $path . $filename;

		$stream = @fopen($logFileName, 'a');
		if(!$stream) throw new \DomainException("Unable to open log-file: " . $logFileName);
		$writer = new LaminasStream($stream);
		self::$loggerInstance = new LaminasLogger();
		self::$loggerInstance->addWriter($writer);
		return self::$loggerInstance;
	}

	public static function getInstance() {
		return self::$loggerInstance ?? self::createInstance();
	}

	protected static function getConsoleInstance() {
		if(!self::$consoleInstance) self::$consoleInstance = Console::getInstance();
		return self::$consoleInstance;
	}

	/**
	 * Color: white
	 */
	public static function debug($message) {
		self::log($message, LaminasLogger::DEBUG);
	}

	/**
	 * Color: white
	 */
	public static function info($message, $rewriteLastLine = false) {
		self::log($message, LaminasLogger::INFO, $rewriteLastLine);
	}

	/**
	 * Color: cyan
	 */
	public static function notice($message) {
		self::log($message, LaminasLogger::NOTICE);
	}

	/**
	 * Color: yellow
	 */
	public static function warn($message) {
		self::log($message, LaminasLogger::WARN);
	}

	/**
	 * Color: red
	 */
	public static function err($message) {
		self::log($message, LaminasLogger::ERR);
	}

	/**
	 * Color: red
	 */
	public static function crit($message) {
		self::log($message, LaminasLogger::CRIT);
	}

	/**
	 * Color: red
	 */

	public static function alert($message) {
		self::log($message, LaminasLogger::ALERT);
	}

	/**
	 * Color: red
	 */
	public static function emerg(string $message) : void {
		self::log($message, LaminasLogger::EMERG);
	}

	public static function log($message, $severity, $rewriteLastLine = false) {
		self::getInstance()->log($severity, $message);

		switch($severity) {
			case LaminasLogger::NOTICE:
				$color = Color::CYAN;
				break;
			case LaminasLogger::WARN:
				$color = Color::LIGHT_YELLOW;
				break;
			case LaminasLogger::ERR:
			case LaminasLogger::CRIT:
			case LaminasLogger::ALERT:
			case LaminasLogger::EMERG:
				$color = Color::RED;
				break;
			default:
				$color = Color::NORMAL;
		}
		if($rewriteLastLine) {
			self::getConsoleInstance()->clearLine();
			self::getConsoleInstance()->write($message, $color);
		}
		else self::getConsoleInstance()->writeLine($message, $color);
	}

}