<?php
/**
 * User: ingvar.aasen
 * Date: 18.06.2018
 * Time: 13:29
 */

namespace Iaasen\Factory;


use Interop\Container\ContainerInterface;
use Zend\Form\ElementInterface;

class ReflectionBasedAbstractFactory extends \Zend\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory
{
	public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
	{
		if(is_subclass_of($requestedName, ElementInterface::class)) {
			return $this->createFormElement($container, $requestedName);
		}
		else return parent::__invoke($container, $requestedName, $options);
	}

	/**
	 * Passes Form Elements to the FormElementManager.
	 * This factory does not handle dependencies, you will still need to make Factories for Form elements with dependencies.
	 * @param ContainerInterface $container
	 * @param $requestedName
	 * @return mixed
	 */
	public function createFormElement(ContainerInterface $container, $requestedName) {
		return $container->get('FormElementManager')->get($requestedName);
	}
}