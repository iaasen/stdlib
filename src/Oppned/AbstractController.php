<?php

namespace Oppned;

use Acl\Model\User;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractActionController;
//use Priceestimator\View\Helper\MenuWidget;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;


abstract class AbstractController extends AbstractActionController {
	protected $redirect;

	/** @var User|null */
	protected $currentUser;
	/** @var  \Zend\Navigation\Navigation */
	protected $navigation;


	public function __construct($currentUser, $navigation)
	{
		$this->currentUser = $currentUser;
		$this->navigation = $navigation;
	}

//	public function onDispatch(\Zend\Mvc\MvcEvent $e) {
//		return parent::onDispatch($e);
//	}

	public function getRedirect($defaultUrl = false) {
		if($this->redirect === null) {
			$redirect = $this->params()->fromQuery('redirect', null);
			if(!$redirect && $this->getRequest()->getHeader('Referer')) {
				$redirect = $this->getRequest()->getHeader('Referer')->uri()->getPath();
			}
			if($redirect === null) $redirect = $defaultUrl;
			$this->redirect = $redirect;
		}
		return $this->redirect;
	}
	
	public function render($template, $data) {
		$viewRender = $this->getServiceLocator()->get('ViewRenderer');
		$view = new ViewModel($data);
		$view->setTemplate($template);
		return $viewRender->render($view);
	}

	public function ajaxFailed($message) {
		$data = array();
		$data['success'] = false;
		$data['messages'] = $message;
		return new JsonModel($data);
	}




}
