<?php
/**
 * User: ingvar.aasen
 * Date: 18.06.2018
 * Time: 12:54
 */

namespace Iaasen;


use Zend\ModuleManager\Feature\AutoloaderProviderInterface;

class Module implements AutoloaderProviderInterface
{
	public function getAutoloaderConfig() {
		return [
			'Zend\Loader\StandardAutoloader' => [
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