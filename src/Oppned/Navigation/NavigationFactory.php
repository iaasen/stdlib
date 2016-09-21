<?php
/**
 * User: Ingvar
 * Date: 04.05.2016
 * Time: 00.58
 */

namespace Oppned\Navigation;


use Zend\Navigation\Service\NavigationAbstractServiceFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class NavigationFactory implements FactoryInterface
{

	/**
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return mixed
	 */
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		return $serviceLocator->get('Zend\Navigation\Service\DefaultNavigationFactory');
		//return $serviceLocator->get('Zend\Navigation\Default');
	}
}