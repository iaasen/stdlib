<?php
/**
 * User: ingvar.aasen
 * Date: 31.05.2023
 */

namespace Iaasen\Cli;

use Symfony\Component\Console\Style\StyleInterface;

abstract class AbstractWorker implements WorkerInterface
{
	protected array $gearman_config;
	protected StyleInterface $io;
	protected \GearmanWorker $worker;
	protected static bool $run = true;
	protected static bool $reload = false;

	abstract protected function setupService() : void;
	abstract protected function tearDownService() : void;
	abstract protected function work() : void;

	public function __construct(
		array $gearmanConfig,
		StyleInterface $io
	) {
		$this->gearman_config = $gearmanConfig;
		$this->io = $io;
	}


	protected function setupSignalListeners() {
		$className = get_class($this);

		pcntl_signal(SIGHUP, function() use($className) {
			$this->io->text('Received SIGHUP - reloading process' . getmypid());
			$className::$reload = true;
		});
		pcntl_signal(SIGINT, function() use ($className) {
			$this->io->text('Received SIGINT - stopping process ' . getmypid());
			$className::$run = false;
		});
		pcntl_signal(SIGTERM, function() use ($className) {
			$this->io->text('Received SIGTERM - stopping process ' . getmypid());
			$className::$run = false;
		});
	}


	public function __invoke() : void {
		$this->io->title('Start service: ' . substr(get_class($this), strrpos(get_class($this), '\\') + 1));
		$this->setupWorker();
		$this->setupService();
		$this->io->newLine(2);

		while(true) {
			if(!self::$run) {
				$this->io->title('Stop service: ' . substr(get_class($this), strrpos(get_class($this), '\\') + 1));
				$this->teardownService();
				$this->tearDownWorker();
				$this->io->newLine(2);
				exit('Halt');
			}
			if(self::$reload) {
				$this->io->title('Restart service: ' . substr(get_class($this), strrpos(get_class($this), '\\') + 1));				$this->teardownService();
				$this->setupService();
				$this->io->newLine(2);
				self::$reload = false;
			}
			$this->work();
			pcntl_signal_dispatch();
		}
	}


	protected function setupWorker() : void {
		$this->setupSignalListeners();

		// Start worker
		$this->io->writeLn("Setup worker");
		$this->worker = new \GearmanWorker();
		$connectionCount = 0;
		foreach($this->gearman_config['servers'] AS $server) {
			$this->io->writeLn("Connect to server: " . $server['url'] . ':' . $server['port']);
			try {
				$success = $this->worker->addServer($server['url'], $server['port']);
				if($success) {
					$this->io->writeLn('Connected to: ' . $server['url'] . ':' . $server['port']);
					$connectionCount++;
				}
			}
			catch(\GearmanException $e) {
				$this->io->error('Failed to connect to: ' . $server['url'] . ':' . $server['port']);
			}
		}

		if(!$connectionCount) {
			$this->io->error("No servers connected");
			exit('Halt');
		}
	}


	public function tearDownWorker() : void {
	}


	protected function shouldContinue() : bool {
		pcntl_signal_dispatch();
		if(!self::$run || self::$reload) return false;
		else return true;
	}

}