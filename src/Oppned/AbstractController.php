<?php

namespace Oppned;

use Acl\Model\User;
use Priceestimator\Service\AbstractTable;
use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\Mvc\Controller\AbstractActionController;
use Priceestimator\View\Helper\MenuWidget;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;


abstract class AbstractController extends AbstractActionController {
	protected $tables = array();
	protected $redirect;
	protected $currentUser;

	public function __construct(User $currentUser)
	{
		$this->currentUser = $currentUser;
	}

	public function onDispatch(\Zend\Mvc\MvcEvent $e) {
		MenuWidget::mainMenu($this->getServiceLocator()->get('Acl\AuthService')->hasIdentity());
		return parent::onDispatch($e);
	}

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

//	/**
//	 * @param string $table
//	 * @return DbTable
//	 */
//	public function getTable($table) {
//		return $this->getServiceLocator()->get(ucfirst($table) . 'Table');
//	}

//	/**
//	 * @param string $form
//	 * @return Form
//	 */
//	public function getForm($form) {
//		return $this->getServiceLocator()
//			->get('FormElementManager')
//			->get('Priceestimator\Form\\' . ucfirst($form) . 'Form');
//	}
//
//	/**
//	 * @param string $fieldset
//	 * @return Fieldset
//	 */
//	public function getFieldset($fieldset) {
//		return $this->getServiceLocator()
//			->get('FormElementManager')
//			->get('Priceestimator\Form\\' . ucfirst($fieldset) . 'Fieldset');
//	}
}
