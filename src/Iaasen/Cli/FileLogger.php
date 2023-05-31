<?php
/**
 * User: ingvar.aasen
 * Date: 30.05.2023
 */

namespace Iaasen\Cli;

use Laminas\Log\Logger as LaminasLogger;
use Laminas\Log\Writer\Stream as LaminasStream;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\StyleInterface;

class FileLogger implements StyleInterface {

	protected $logFilename = 'data/worker.log';
	protected \Laminas\Log\Logger $logger;
	protected ?int $progressLength = null;
	protected ?int $progressAdvance = null;

	public function __construct(
		?string $logFilename = null
	) {
		if($logFilename) $this->logFilename = $logFilename;
		$stream = @fopen($this->logFilename, 'a');
		if(!$stream) throw new \DomainException("Unable to open log-file: " . $this->logFilename);
		$writer = new LaminasStream($stream);
		$this->logger = new \Laminas\Log\Logger();
		$this->logger->addWriter($writer);
		$this->text('Log file: ' . $this->logFilename);
	}


	public function title(string $message) {
		$this->log('============================================');
		$this->log($message);
		$this->log('============================================');
	}


	public function section(string $message) {
		$this->log('--------------------------------------------');
		$this->log($message);
		$this->log('--------------------------------------------');
	}


	public function listing(array $elements) {
		foreach($elements AS $element) {
			$this->log('* ' . $element);
		}
	}


	public function text($message) {
		$this->log($message);
	}


	public function success($message) {
		$this->log('[OK] ' . $message);
	}


	public function error($message) {
		$this->log('[ERROR] ' . $message, LaminasLogger::ERR);
	}


	public function warning($message) {
		$this->log('[WARNING] ' . $message, LaminasLogger::WARN);
	}


	public function note($message) {
		$this->log('[NOTE] ' . $message, LaminasLogger::NOTICE);
	}


	public function caution($message) {
		$this->log('[CAUTION] ' . $message, LaminasLogger::WARN);
	}


	public function table(array $headers, array $rows) {
		$maxLength = 0;
		foreach($headers AS $header) if(strlen($header) > $maxLength) $maxLength = strlen($header);
		foreach($rows AS $row) {
			foreach ($row AS $column) if(strlen($column) > $maxLength) $maxLength = strlen($column);
		}

		$boundary = array_fill(0, count($headers), str_repeat('-', $maxLength));
		$rows = array_merge([$headers], [$boundary], $rows, [$boundary]);

		$table = PHP_EOL;
		foreach($rows AS $row) {
			foreach($row AS $column) {
				$table .= '| ' . $column . str_repeat(' ', $maxLength - strlen($column)) . ' ';
			}
			$table .= '|' . PHP_EOL;
		}

		$this->log($table);
	}


	public function ask(string $question, string $default = null, callable $validator = null) {
		$this->log('[ASK] ' . $question . ': ' . $default);
		return $default;
	}


	public function askHidden(string $question, callable $validator = null) {
		$this->log('[ASK] ' . $question . ': (hidden)');
	}


	public function confirm(string $question, bool $default = true) {
		$this->log('[CONFIRM] ' . $question . ': ' . ($default ? 'yes' : 'no'));
		return $default;
	}


	public function choice(string $question, array $choices, $default = null) {
		$this->log('[CHOICE] ' . $question . ': ' . $default);
		return $default;
	}


	public function newLine(int $count = 1) {
		for($i = 0; $i < $count; $i++) { $this->log(''); }
	}


	public function progressStart(int $max = 0) {
		$this->progressLength = $max;
		$this->progressAdvance = 0;
	}


	public function progressAdvance(int $step = 1) {
		$this->progressAdvance += $step;
	}


	public function progressFinish() {
		$this->log('[PROGRESS] '. $this->progressAdvance . '/' . $this->progressLength);
		$this->progressLength = $this->progressAdvance = null;
	}




	///////////////////////
	// SymfonyStyle only //
	///////////////////////

	public function horizontalTable(array $headers, array $rows) {
		$longestHeader = 0;
		foreach($headers AS $header) if(strlen($header) > $longestHeader) $longestHeader = strlen($header);


		$output = PHP_EOL . '--------------------------------------------';
		foreach($headers AS $key => $header) {
			$output .= PHP_EOL . $header . str_repeat(' ', $longestHeader - strlen($header)) . ' | ';
			$rowColumns = [];
			foreach($rows AS $row) {
				$rowColumns[] = $row[$key];
			}
			$output .= implode(', ', $rowColumns);
		}
		$output .= PHP_EOL . '--------------------------------------------' . PHP_EOL;
		$this->log($output);
	}


	public function definitionList(...$list) {
		throw new \Exception('Not implemented');
	}


	public function progressIterate(iterable $iterable, int $max = null): iterable {
		throw new \Exception('Not implemented');
	}


	public function askQuestion(Question $question) {
		throw new \Exception('Not implemented');
	}


	public function writeln($messages, int $type = OutputInterface::OUTPUT_NORMAL) {
		if(is_array($messages)) foreach($messages AS $message) $this->log($message);
		else $this->log($messages);
	}


	public function write($messages, bool $newline = false, int $type = OutputInterface::OUTPUT_NORMAL) {
		$this->writeln($messages, $type);
	}


	/////////////////////////////////////////////////////////////


	protected function log(string $message, int $severity = LaminasLogger::INFO) : void {
		$this->logger->log($severity, $message);
	}


	public function setLogFilename(string $logFilename) : void {
		$this->logFilename = $logFilename;
	}




}