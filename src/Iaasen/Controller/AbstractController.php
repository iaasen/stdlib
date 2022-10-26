<?php
/**
 * User: iaase
 * Date: 05.05.2018
 */

namespace Iaasen\Controller;


use Iaasen\Controller\Plugin\NavigationPlugin;
use Iaasen\Initializer\NavigationAwareInterface;
use Iaasen\Initializer\ViewRendererAwareInterface;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Navigation\Navigation;
use Laminas\Uri\Uri;
use Laminas\View\Model\JsonModel;
use Laminas\View\Model\ViewModel;
use Laminas\View\Renderer\PhpRenderer;

/**
 * @method NavigationPlugin navigation()
 */
class AbstractController extends AbstractActionController implements NavigationAwareInterface, ViewRendererAwareInterface
{
	protected ?string $redirect = null;
	protected PhpRenderer $viewRenderer;
	protected Navigation $navigation;


	public function getRedirect(string $defaultUrl = '/') : string {
        $redirect = $this->params()->fromQuery('redirect_uri');
        if(!$redirect) $redirect = $defaultUrl;
        $this->redirect = $redirect;
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