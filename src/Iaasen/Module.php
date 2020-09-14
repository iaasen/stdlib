<?php
/**
 * User: ingvar.aasen
 * Date: 18.06.2018
 * Time: 12:54
 */

namespace Iaasen;


use Laminas\Loader\StandardAutoloader;
use Laminas\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface
{
	public function getAutoloaderConfig() {
		return [
			StandardAutoloader::class => [
				'namespaces' => [
					__NAMESPACE__ => __DIR__ . '/../src/' . __NAMESPACE__
				]
			]
		];
	}

	public function getConfig() {
		return include __DIR__ . '/../../config/module.config.php';
	}


}