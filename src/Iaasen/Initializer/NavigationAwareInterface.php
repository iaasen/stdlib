<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 09.06.2018
 * Time: 22:39
 */

namespace Iaasen\Initializer;


use Zend\Navigation\Navigation;

interface NavigationAwareInterface
{
	public function setNavigation(Navigation $navigation);
}