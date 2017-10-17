<?php
/**
 * User: ingvar.aasen
 * Date: 29.09.2017
 * Time: 15:24
 */

namespace Iaasen\Console;


use Zend\Console\ColorInterface AS Color;
use Zend\Console\Console;
use Zend\Log\Writer\Stream AS ZendStream;
use Zend\Log\Logger AS ZendLogger;

class Logger
{
	const DEFAULT_PATH = '/var/log/api-workers/';

	protected static $loggerInstance;

	protected static $consoleInstance;

	public static function createInstance($filename = null, $path = null) {
		if(self::$loggerInstance) self::$loggerInstance = null;

		global $argv;
		$filename = $filename ?? ($argv[1]) ? $argv[1] . '.log' : 'other.log';
		$path = $path ?? self::DEFAULT_PATH;
		$logFileName = $path . $filename;

		$stream = @fopen($logFileName, 'a');
		if(!$stream) throw new \DomainException("Unable to open log-file: " . $logFileName);
		$writer = new ZendStream($stream);
		self::$loggerInstance = new ZendLogger();
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

	public static function debug($message) {
		self::log($message, ZendLogger::DEBUG);
	}

	public static function info($message) {
		self::log($message, ZendLogger::INFO);
	}

	public static function notice($message) {
		self::log($message, ZendLogger::NOTICE);
	}

	public static function warn($message) {
		self::log($message, ZendLogger::WARN);
	}

	public static function err($message) {
		self::log($message, ZendLogger::ERR);
	}

	public static function crit($message) {
		self::log($message, ZendLogger::CRIT);
	}

	public static function alert($message) {
		self::log($message, ZendLogger::ALERT);
	}

	public static function emerg($message) {
		self::log($message, ZendLogger::EMERG);
	}

	public static function log($message, $severity) {
		self::getInstance()->log($severity, $message);

		switch($severity) {
			case ZendLogger::NOTICE:
				$color = Color::CYAN;
				break;
			case ZendLogger::WARN:
				$color = Color::LIGHT_YELLOW;
				break;
			case ZendLogger::ERR:
			case ZendLogger::CRIT:
			case ZendLogger::ALERT:
			case ZendLogger::EMERG:
				$color = Color::RED;
				break;
			default:
				$color = Color::NORMAL;
		}

		self::getConsoleInstance()->writeLine($message, $color);
	}

}