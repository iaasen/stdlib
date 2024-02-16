<?php
/**
 * User: ingvar.aasen
 * Date: 09.11.2023
 */

namespace Iaasen\Cli;

use Iaasen\WithOptions;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AbstractCommand extends Command {
	protected SymfonyStyle $io;
	
	
	public function initialize(InputInterface $input, OutputInterface $output) : void {
		date_default_timezone_set('Europe/Oslo');
		ini_set('intl.default_locale', 'nb-NO');
		mb_internal_encoding("UTF-8");

		$this->io = new SymfonyStyle($input, $output);
		parent::initialize($input, $output);
	}


	public function iterate(array $objects) : void {
		$getTheRest = false;
		$current = 0;
		$count = count($objects);

		foreach($objects AS $object) {
			$current++;
			dump($object);
			$this->io->text("<info>Entry $current/$count</info>");
			if(!$getTheRest && $current < $count) {
				$answer = $this->io->ask("Get next ('a' to get all, 'q' to break)");
				if($answer == 'a') $getTheRest = true;
				if($answer == 'q') break;
			}
		}
	}
	
	
	public static function extractWith($withString) : array {
				return WithOptions::extractWith($withString);
	}


	protected function printOptions(int $limit, array $with, array $withOptions, array $searchFields) : void {
		$this->io->horizontalTable(
			[
				'Limit (-l)',
				'With (-w)',
				'Available with options',
				'Available search fields',
			],
			[
				[
					$limit,
					json_encode($with),
					json_encode($withOptions),
					implode(', ', $searchFields),
				],
			]
		);
	}


	protected function getOutputType(InputInterface $input) : string {
		$output = $input->getOption('output');
		if(in_array($output, ['json', 'dump', 'table'])) return $output;
		if($input->getOption('dump') !== false) return 'dump';
		if($input->getOption('json') !== false) return 'json';
		if($input->getOption('table') !== false) return 'table';
		return 'table';
	}


	protected function configureIterableDatabaseLookup() : void {
		$this->addArgument('id', InputArgument::OPTIONAL);
		$this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit number of results to return');
		$this->addOption('with', 'w', InputOption::VALUE_OPTIONAL, 'Connected objects to include in the response');
		$this->addOption('table', 't', InputOption::VALUE_OPTIONAL, 'Return table', false);
		$this->addOption('dump', 'd', InputOption::VALUE_OPTIONAL, 'Dump the objects', false);
		$this->addOption('json', 'j', InputOption::VALUE_OPTIONAL, 'Return as Json', false);
		$this->addOption('output', 'o', InputOption::VALUE_OPTIONAL, 'Output: table, dump or json', false);
		$this->addOption('random', 'r', InputOption::VALUE_OPTIONAL, 'Collect random objects for debugging', false);
	}
	
}
