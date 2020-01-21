<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 05.05.2018
 * Time: 21:25
 */

namespace Iaasen\Controller;


use Interop\Container\ContainerInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Factory\AbstractFactoryInterface;
use Laminas\View\Renderer\PhpRenderer;

class ControllerAbstractFactory implements AbstractFactoryInterface
{

	/**
	 * Can the factory create an instance for the service?
	 *
	 * @param  ContainerInterface $container
	 * @param  string $requestedName
	 * @return bool
	 */
	public function canCreate(ContainerInterface $container, $requestedName)
	{
		return class_exists($requestedName) && fnmatch('*Controller', $requestedName);
	}

	/**
	 * Create an object
	 *
	 * @param  ContainerInterface $container
	 * @param  string $requestedName
	 * @param  null|array $options
	 * @return object
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		$controller = new $requestedName();
		return $this->populateController($controller, $container);
	}

	protected function populateController(AbstractActionController $controller, ContainerInterface  $container) {
		if(method_exists($controller, 'setNavigation')) {
			$controller->setNavigation($container->get(Navigation::class));
		}
		if(method_exists($controller, 'setViewRenderer')) {
			$controller->setViewRenderer($container->get(PhpRenderer::class));
		}
		return $controller;
	}

}