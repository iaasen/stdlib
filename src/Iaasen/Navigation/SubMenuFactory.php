<?php
/**
 * User: Ingvar
 * Date: 30.04.2016
 * Time: 06.01
 */

namespace Iaasen\Navigation;


use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceManager;

class SubMenuFactory implements FactoryInterface
{
	/**
	 * @param ServiceManager $container
	 * @param string $requestedName
	 * @param array|null $options
	 * @return \Laminas\View\Helper\Navigation\Menu|object
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
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