<?php
/**
 * Created by PhpStorm.
 * User: Ingvar
 * Date: 08.11.2015
 * Time: 14.40
 */

namespace Oppned\Factory;


use Laminas\ServiceManager\AbstractFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ModelAbstractFactory implements AbstractFactoryInterface
{
	protected $pattern = '/Priceestimator\\\\(.*?)Model\\\\(.*?)$/';


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
	    if(
	    	preg_match($this->pattern, $requestedName)
	        && !fnmatch('*Factory', $requestedName)
	    ) {
	    	return true;
	    }
	    else return false;
    }

    /**
     * Create service with name
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @param $name
     * @param $requestedName
     * @return mixed
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
//    	if($requestedName == 'Priceestimator\Model\Field') throw new \Exception();
//    	echo $requestedName . ' ';
    	$model = new $requestedName();
	    if(property_exists($model, 'group')) {
	    	$model->group = $serviceLocator->get(\Acl\Service\UserService::class)->getCurrentUser()->current_group;
	    }
	    return $model;
    }
}