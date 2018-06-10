<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 09.06.2018
 * Time: 22:51
 */

namespace Iaasen\Controller\Plugin;


use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use Zend\Navigation\Navigation;

class NavigationPlugin extends AbstractPlugin
{
	/** @var Navigation */
	protected $navigation;

	public function __construct(Navigation $navigation)
	{
		$this->navigation = $navigation;
	}

	public  function __invoke() : Navigation
	{
		return $this->navigation;
	}
}