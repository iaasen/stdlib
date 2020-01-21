<?php
/**
 * User: ingvar
 * Date: 21.09.2016
 * Time: 11.32
 */

namespace Oppned\View\Helper;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;
use Laminas\ServiceManager\Factory\FactoryInterface;

class MessageWidgetFactory implements FactoryInterface
{
	/**
	 * Create an object
	 *
	 * @param  ContainerInterface $container
	 * @param  string $requestedName
	 * @param  null|array $options
	 * @return object
	 * @throws ServiceNotFoundException if unable to resolve the service.
	 * @throws ServiceNotCreatedException if an exception is raised when
	 *     creating a service.
	 * @throws ContainerException if any other error occurs
	 */
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		return new MessageWidget();
	}
}