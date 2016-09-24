<?php
/**
 * Created by PhpStorm.
 * User: Ingvar
 * Date: 08.11.2015
 * Time: 13.20
 */

namespace Oppned\Factory;


use Priceestimator\Service\AbstractTable;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class TableAbstractFactory implements AbstractFactoryInterface
{
	protected $pattern = '/Priceestimator\\\\(.*?)Service\\\\(.*?)Table$/';

	/**
	 * Determine if we can create a service with name
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 * @param $name
	 * @param $requestedName
	 * @return bool
	 */
	public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
	{
		if(preg_match($this->pattern, $requestedName)) return true;
		else return false;
	}

	/**
	 * Create service with name
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 * @param $name
	 * @param $requestedName
	 * @return AbstractTable
	 */
	public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
	{
		preg_match($this->pattern, $requestedName, $matches);
		$currentUser = $serviceLocator->get('UserTable')->getCurrentUser();
		$tableGateway = $serviceLocator->get($requestedName . 'Gateway');
		return new $requestedName($currentUser, $tableGateway);
	}
}