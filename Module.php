<?php

/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/General for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */
namespace Oppned;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\ResultSet\ResultSet;

class Module implements AutoloaderProviderInterface {

	public function getAutoloaderConfig() {
		return [
//			'Zend\Loader\ClassMapAutoloader' => array (
//				__DIR__ . '/autoload_classmap.php'
//			),
			'Zend\Loader\StandardAutoloader' => [
				'namespaces' => [
					__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__
				]
			]
		];
	}

	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}
	
	public function getServiceConfig() {
		return [
			'factories' => [
//				'Logger' => function ($sm) {
//					$logTable = $sm->get('LogTable');
//					$userTable = $sm->get('UserTable');
//					return new Logger($logTable, $userTable);
//				},
//				'LogTable' => function ($sm) {
//					$tableGateway = $sm->get('LogTableGateway');
//					$table = new \General\LogTable($tableGateway);
//					return $table;
//				},
//				'LogTableGateway' => function ($sm) {
//					$dbAdapter = $sm->get('Db\Boligkalk');
//					$resultSetPrototype = new ResultSet();
//					$resultSetPrototype->setArrayObjectPrototype(new \Oppned\Log());
//					return new TableGateway('Log', $dbAdapter, null, $resultSetPrototype);
//				},
			]
		];
	}
}
