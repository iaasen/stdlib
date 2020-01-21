<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 09.06.2018
 * Time: 22:39
 */

namespace Iaasen\Initializer;


use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Initializer\InitializerInterface;
use Laminas\View\Renderer\PhpRenderer;

class ViewRendererInitializer implements InitializerInterface
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
		if($instance instanceof ViewRendererAwareInterface) {
			$viewRenderer = $container->get(PhpRenderer::class);
			$instance->setViewRenderer($viewRenderer);
		}
	}
}