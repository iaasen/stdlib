<?php
/**
 * User: ingvar
 * Date: 24.09.2016
 * Time: 21.54
 */

namespace Oppned\Log;


use Laminas\Db\ResultSet\ResultSet;
use Laminas\ServiceManager\ServiceLocatorInterface;

class LogTableGatewayFactory
{
	/**
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return LogTableGateway
	 */
	public function __invoke($serviceLocator)
	{
		$dbAdapter = $serviceLocator->get('Db\Boligkalk');
		$resultSetPrototype = new ResultSet();
		$resultSetPrototype->setArrayObjectPrototype(new \Oppned\Log\Log());
		return new LogTableGateway('Log', $dbAdapter, null, $resultSetPrototype);

	}

}