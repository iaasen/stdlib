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
	/** @var \Zend\Console\Adapter\AdapterInterface  */
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

	public function __construct($gearmanConfig)
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
				Logger::info('Halt');
				exit(0);
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
			'Starting service: ' . (new \ReflectionClass($this))->getShortName() . PHP_EOL .
			'============================================'
		);

		// Start worker
		Logger::info("Setup worker");
		$this->worker = new \GearmanWorker();
		foreach($this->gearman_config['servers'] AS $server) {
			Logger::info("Connect to server: " . $server['url'] . ':' . $server['port']);
			$this->worker->addServer($server['url'], $server['port']);
			Logger::info("Connected");
		}
	}

}