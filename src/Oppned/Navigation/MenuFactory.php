<?php
/**
 * User: Ingvar
 * Date: 30.04.2016
 * Time: 06.01
 */

namespace Oppned\Navigation;


use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Class MenuFactory
 * @package Oppned\Navigation
 * @deprecated Use \Iaasen\Navigation\SubMenuNavigation
 */
class MenuFactory implements FactoryInterface
{

	/**
	 * Create service
	 *
	 * @param ServiceLocatorInterface $serviceLocator
	 * @return mixed
	 */
	public function createService(ServiceLocatorInterface $serviceLocator)
	{
		$menu = new \Laminas\View\Helper\Navigation\Menu();
		$menu->setUlClass('nav nav-pills nav-stacked');
		$menu->setOnlyActiveBranch(true);
		$menu->setRenderParents(false);
		$menu->setMinDepth(1);
		$menu->setMaxDepth(2);
		//$menu->setPartial(['layout/menuPartial', 'settings']);
		return $menu;
	}
}