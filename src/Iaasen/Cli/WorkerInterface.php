<?php
/**
 * User: ingvar.aasen
 * Date: 31.05.2023
 */

namespace Iaasen\Cli;

interface WorkerInterface
{
	function __invoke() : void;
}