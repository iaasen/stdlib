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
use Laminas\Http\Header\Referer;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Navigation\Navigation;
use Laminas\Uri\Uri;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

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
			$redirect = $this->params()->fromQuery('redirect', $this->params()->fromQuery('redirect_uri'));
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

	public function appendToQuery(string $key, string $value, ?Uri $uri = null) : Uri {
		if(!$uri) {
			/** @var \Laminas\Http\PhpEnvironment\Request $request */
			$request = $this->getRequest();
			$uri = $request->getUri();
		}
		return $uri->setQuery(array_merge($uri->getQueryAsArray(), [$key => $value]));
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
			if($page instanceof \Laminas\Navigation\Page\Mvc && strpos($page->getRoute(), $search) !== false) {
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