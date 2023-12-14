<?php
/**
 * User: ingvar.aasen
 * Date: 30.05.2023
 */

namespace Iaasen\Cli;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\StyleInterface;

class Logger implements StyleInterface {
	/** @var StyleInterface[] */
	protected array $loggers;

	public function __construct(StyleInterface ...$loggers) {
		$this->loggers = $loggers;
	}

	public function title(string $message) {
		foreach($this->loggers AS $logger) { $logger->title($message); }
	}

	public function section(string $message) {
		foreach($this->loggers AS $logger) { $logger->section($message); }
	}

	public function listing(array $elements) {
		foreach($this->loggers AS $logger) { $logger->listing($elements); }
	}

	public function text($message) {
		foreach($this->loggers AS $logger) { $logger->text($message); }
	}

	public function success($message) {
		foreach($this->loggers AS $logger) { $logger->success($message); }
	}

	public function error($message) {
		foreach($this->loggers AS $logger) { $logger->error($message); }
	}

	public function warning($message) {
		foreach($this->loggers AS $logger) { $logger->warning($message); }
	}

	public function note($message) {
		foreach($this->loggers AS $logger) { $logger->note($message); }
	}

	public function caution($message) {
		foreach($this->loggers AS $logger) { $logger->caution($message); }
	}

	public function table(array $headers, array $rows) {
		foreach($this->loggers AS $logger) { $logger->table($headers, $rows); }
	}

	public function ask(string $question, string $default = null, callable $validator = null) : mixed {
		foreach($this->loggers AS $logger) {
			$default = $logger->ask($question, $default, $validator);
		}
		return $default;
	}

	public function askHidden(string $question, callable $validator = null) : mixed {
		$answer = reset($this->loggers)->askHidden($question, $validator);
		foreach (array_slice($this->loggers, 1) AS $logger) {
			$logger->askHidden($question, $validator);
		}
		return $answer;
	}

	public function confirm(string $question, bool $default = true) : bool {
		foreach($this->loggers AS $logger) {
			$default = $logger->confirm($question, $default);
		}
		return $default;
	}

	public function choice(string $question, array $choices, $default = null) : mixed {
		foreach($this->loggers AS $logger) { $default = $logger->choice($question, $choices, $default); }
		return $default;
	}

	public function newLine(int $count = 1) {
		foreach($this->loggers AS $logger) { $logger->newLine($count); }
	}

	public function progressStart(int $max = 0) {
		foreach($this->loggers AS $logger) { $logger->progressStart($max); }
	}

	public function progressAdvance(int $step = 1) {
		foreach($this->loggers AS $logger) { $logger->progressAdvance($step); }
	}

	public function progressFinish() {
		foreach($this->loggers AS $logger) { $logger->progressFinish(); }
	}




	///////////////////////
	// SymfonyStyle only //
	///////////////////////

	public function horizontalTable(array $headers, array $rows) {
		foreach($this->loggers AS $logger) { $logger->horizontalTable($headers, $rows); }
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
		foreach($this->loggers AS $logger) { $logger->writeln($messages); }
	}


	public function write($messages, bool $newline = false, int $type = OutputInterface::OUTPUT_NORMAL) {
		foreach($this->loggers AS $logger) { $logger->write($messages); }
	}


	public function divider() : void {
		foreach($this->loggers AS $logger) { $logger->writeln('--------------------------------------------'); }
	}

}