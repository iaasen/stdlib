<?php

namespace Oppned;

use Acl\Model\User;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\Mvc\Controller\AbstractActionController;
//use Priceestimator\View\Helper\MenuWidget;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;


abstract class AbstractController extends AbstractActionController {
	protected $redirect;

	/** @var User|null */
	protected $currentUser;
	/** @var  \Laminas\Navigation\Navigation */
	protected $navigation;


	public function __construct($currentUser, $navigation)
	{
		$this->currentUser = $currentUser;
		$this->navigation = $navigation;
	}

//	public function onDispatch(\Laminas\Mvc\MvcEvent $e) {
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
