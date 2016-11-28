<?php
/**
 * User: ingvar
 * Date: 24.09.2016
 * Time: 21.51
 */

namespace Oppned\Log;


use Zend\ServiceManager\ServiceLocatorInterface;

class LogTableFactory
{
	/**
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return \Oppned\Log\LogTable
	 */
	public function __invoke($serviceLocator)
	{
		$tableGateway = $serviceLocator->get('Oppned\Log\LogTableGateway');
		$currentUser = $serviceLocator->get(\Acl\Service\UserTable::class)->getCurrentUser();
		return new \Oppned\Log\LogTable($currentUser, $tableGateway);
	}

}