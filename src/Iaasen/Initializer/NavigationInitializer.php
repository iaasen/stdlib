<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 09.06.2018
 * Time: 22:39
 */

namespace Iaasen\Initializer;


use Interop\Container\ContainerInterface;
use Laminas\Navigation\Navigation;
use Laminas\ServiceManager\Initializer\InitializerInterface;

class NavigationInitializer implements InitializerInterface
{

	/**
	 * Initialize the given instance
	 *
	 * @param  ContainerInterface $container
	 * @param  object $instance
	 * @return void
	 */
	public function __invoke(ContainerInterface $container, $instance)
	{
		if($instance instanceof NavigationAwareInterface) {
			$navigation = $container->get(Navigation::class);
			$instance->setNavigation($navigation);
		}
	}
}