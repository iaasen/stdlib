<?php
namespace Oppned\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Oppned\Message;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class MessageWidget extends AbstractHelper implements ServiceLocatorAwareInterface
{
	protected $serviceLocator;
	
	public function __invoke() {
		return $this;
	}
		
	public function render($maxLevel = false) {
		$output = '';
		if($this->getServiceLocator()->get('flashmessenger')->getPluginFlashMessenger()->count()) {
			$output .= $this->getServiceLocator()->get('flashmessenger')->render();
		}
		if(Message::count($maxLevel)) {
			$messages = Message::returnArray($maxLevel);
			foreach($messages AS $m) {
				$output .= '<div class="message ' . $m['css'] . '">' . $m['message'] . '</div>' . PHP_EOL;
			}
		}
		return $output;
	}
	
	public function renderPanel($maxLevel = false) {
		$css = array(
			1 => 'success',
			2 => 'warning',
			3 => 'danger'
		);
		
		$output = '<table class="table table-condensed" style="margin-bottom: 0px">';
		if($this->getServiceLocator()->get('flashmessenger')->getPluginFlashMessenger()->count()) {
			$output .= $this->getServiceLocator()->get('flashmessenger')->render();
		}
		if(Message::count($maxLevel)) {
			$messages = Message::returnArray($maxLevel);
			foreach($messages AS $m) {
				$output .= '<tr class="' . $css[$m['level']] . '"><td>' . $m['message'] . '</tr></td>' . PHP_EOL;
			}
		}
		$output .= '</table>';
		return $output;
	}
	
	public function count($maxLevel = false) {
		return $this->getServiceLocator()->get('flashmessenger')->getPluginFlashMessenger()->count() + Message::count($maxLevel);
	}
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}
	
	public function getServiceLocator() {
		return $this->serviceLocator;
	}
}
