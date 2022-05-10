<?php
/**
 * User: ingvar
 * Date: 10.05.2022
 */

namespace Iaasen\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class LaminasMvcConfigFactory implements FactoryInterface
{
	public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null)
	{
		return new LaminasMvcConfig($container->get('config'));
	}
}