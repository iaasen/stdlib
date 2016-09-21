<?php
/**
 * User: Ingvar
 * Date: 30.04.2016
 * Time: 06.01
 */

namespace Oppned\Navigation;


use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MainMenuFactory implements FactoryInterface
{

	/**
	 * Create service
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return mixed
	 */
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$menu = new \Zend\View\Helper\Navigation\Menu();
		$menu->setUlClass('nav navbar-nav');
		$menu->setMaxDepth(0);
		return $menu;
	}
}