<?php
/**
 * User: ingvar
 * Date: 24.09.2016
 * Time: 19.26
 */

namespace Oppned\Log;


use Zend\ServiceManager\ServiceLocatorInterface;

class LoggerFactory
{
	/**
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return Logger
	 */
	public function __invoke($serviceLocator)
	{
		$logTable = $serviceLocator->get(\Oppned\Log\LogTable::class);
		$userTable = $serviceLocator->get(\Acl\Model\UserTable::class);
		return new Logger($logTable, $userTable);
	}

}