<?php
/**
 * User: ingvar.aasen
 * Date: 27.09.2017
 * Time: 18:15
 */

namespace Iaasen\Console;



abstract class AbstractWorker implements WorkerInterface
{
	/** @var array */
	protected $gearman_config;
	/** @var \Laminas\Console\Adapter\AdapterInterface  */
	protected $console;
	/** @var  \GearmanWorker */
	protected $worker;

	/** @var  bool */
	protected static $run = true;
	/** @var bool  */
	protected static $reload = false;

	abstract protected function setupService();
	abstract protected function tearDownService();
	abstract protected function work();

	public function __construct(array $gearmanConfig)
	{
		$this->gearman_config = $gearmanConfig;
	}

	protected function setupSignalListeners() {
		$className = get_class($this);

		pcntl_signal(SIGHUP, function() use($className) {
			Logger::info('Received SIGHUP - reloading process' . getmypid());
			$className::$reload = true;
		});
		pcntl_signal(SIGINT, function() use ($className) {
			Logger::info('Received SIGINT - stopping process ' . getmypid());
			$className::$run = false;
		});
		pcntl_signal(SIGTERM, function() use ($className) {
			Logger::info('Received SIGTERM - stopping process ' . getmypid());
			$className::$run = false;
		});
	}

	public function __invoke()
	{
		$this->setupWorker();
		$this->setupService();

		while(true) {
			if(!self::$run) {
				$this->teardownService();
				exit('Halt');
			}
			if(self::$reload) {
				$this->teardownService();
				$this->setupService();
				self::$reload = false;
			}
			$this->work();
			pcntl_signal_dispatch();
		}
	}

	protected function setupWorker() {
		$this->setupSignalListeners();

		Logger::info(
			PHP_EOL .
			'============================================' . PHP_EOL .
			'Starting service: ' . substr(get_class($this), strrpos(get_class($this), '\\') + 1) . PHP_EOL .
			'============================================'
		);

		// Start worker
		Logger::info("Setup worker");
		$this->worker = new \GearmanWorker();
		$connectionCount = 0;
		foreach($this->gearman_config['servers'] AS $server) {
			Logger::info("Connect to server: " . $server['url'] . ':' . $server['port']);
			try {
				$success = $this->worker->addServer($server['url'], $server['port']);
				if($success) {
					Logger::info('Connected to: ' . $server['url'] . ':' . $server['port']);
					$connectionCount++;
				}
			}
			catch(\GearmanException $e) {
				Logger::alert('Failed to connect to: ' . $server['url'] . ':' . $server['port']);
			}
		}

		if($connectionCount) {
			Logger::info("Connected");
		}
		else {
			Logger::alert("No servers connected");
			exit('Halt');
		}
	}

	protected function shouldContinue() {
		pcntl_signal_dispatch();
		if(!self::$run || self::$reload) return false;
		else return true;
	}

}