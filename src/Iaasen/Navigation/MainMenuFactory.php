<?php
/**
 * User: Ingvar
 * Date: 30.04.2016
 * Time: 06.01
 */

namespace Iaasen\Navigation;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Helper\Navigation\Menu;

class MainMenuFactory implements FactoryInterface
{

	/**
	 *
	 * @param  ServiceManager $container
	 * @param  string $requestedName
	 * @param  null|array $options
	 * @return Menu|object
	 * @throws ServiceNotFoundException if unable to resolve the service.
	 * @throws ServiceNotCreatedException if an exception is raised when
	 *     creating a service.
	 * @throws ContainerException if any other error occurs
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$menu = new \Laminas\View\Helper\Navigation\Menu();
		$menu->setUlClass('nav navbar-nav');
		$menu->setMaxDepth(0);
		return $menu;
	}
}