<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 05.05.2018
 * Time: 21:37
 */

namespace Iaasen\Controller;


use Iaasen\Initializer\NavigationAwareInterface;
use Iaasen\Initializer\ViewRendererAwareInterface;
use Zend\Http\Header\Referer;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Navigation\Navigation;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;

class AbstractController extends AbstractActionController implements NavigationAwareInterface, ViewRendererAwareInterface
{
	/** @var string */
	protected $redirect;
	/** @var PhpRenderer */
	protected $viewRenderer;
	/** @var  Navigation */
	protected $navigation;

	public function getRedirect($defaultUrl = false) {
		if($this->redirect === null) {
			$redirect = $this->params()->fromQuery('redirect');
			/** @var Request $request */
			$request = $this->getRequest();
			if(!$redirect && $request->getHeader('Referer')) {
				/** @var Referer $header */
				$header = $request->getHeader('Referer');
				//$redirect = $header->uri()->getPath();
				$redirect = $header->uri()->__toString();
			}
			if($redirect === null) $redirect = $defaultUrl;
			$this->redirect = $redirect;
		}
		return $this->redirect;
	}

	public function render($template, $data) {
		$view = new ViewModel($data);
		$view->setTemplate($template);
		return $this->viewRenderer->render($view);
	}

	public function ajaxFailed($message) {
		$data = array();
		$data['success'] = false;
		$data['messages'] = $message;
		return new JsonModel($data);
	}

	/**
	 * @param string $search
	 * @param array|null $attributes
	 * @param bool|null $visible
	 * @param array|null $pages
	 */
	protected function setAttributesForNavigationPagesRecursive(string $search, ?array $attributes = null, ?bool $visible = null, ?array $pages = null) {
		if($pages === null) $pages = $this->navigation->getPages();
		foreach($pages AS $page) {
			if($page instanceof \Zend\Navigation\Page\Mvc && strpos($page->getRoute(), $search) !== false) {
				if($attributes !== null) $page->setParams($attributes);
				if($visible !== null) $page->setVisible($visible);
			}
			$this->setAttributesForNavigationPagesRecursive($search, $attributes, $visible, $page->getPages());
		}
	}

	protected function setVisibilityForNavigationPagesRecursive(string $search, ?bool $visible = null, ?array $pages = null) {
		$this->setAttributesForNavigationPagesRecursive($search, null, $visible, $pages);
	}

	public function setViewRenderer(PhpRenderer $viewRenderer) {
		$this->viewRenderer = $viewRenderer;
	}

	public function setNavigation(Navigation $navigation) {
		$this->navigation = $navigation;
	}
}