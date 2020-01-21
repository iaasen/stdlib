<?php
/**
 * Created by PhpStorm.
 * User: Ingvar
 * Date: 06.11.2015
 * Time: 03.04
 */

namespace Oppned\Factory;


use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ControllerAbstractFactory implements AbstractFactoryInterface
{

    /**
     * Determine if we can create a service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return fnmatch('*Controller', $requestedName);
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return AbstractActionController
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        $currentUser = $serviceLocator->getServiceLocator()->get(\Acl\Service\UserService::class)->getCurrentUser();
        return new $requestedName($currentUser);
    }
}