<?php
/**
 * User: ingvar
 * Date: 24.09.2016
 * Time: 21.54
 */

namespace Oppned\Log;


use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\ServiceManager\ServiceLocatorInterface;

class LogTableGatewayFactory
{
	/**
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return TableGateway
	 */
	public function __invoke($serviceLocator)
	{
		$dbAdapter = $serviceLocator->get('Db\Boligkalk');
		$resultSetPrototype = new ResultSet();
		$resultSetPrototype->setArrayObjectPrototype(new \Oppned\Log\Log());
		return new TableGateway('Log', $dbAdapter, null, $resultSetPrototype);

	}

}