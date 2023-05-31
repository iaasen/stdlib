<?php
/**
 * User: ingvar.aasen
 * Date: 30.05.2023
 */

namespace Iaasen\Cli;

use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

class CliOutput extends SymfonyStyle {

	public function __construct(
		ArgvInput $input,
		ConsoleOutput $output
	) {
		parent::__construct($input, $output);
	}

}