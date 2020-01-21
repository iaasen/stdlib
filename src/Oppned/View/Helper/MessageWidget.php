<?php
namespace Oppned\View\Helper;

use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\View\Helper\AbstractHelper;
use Oppned\Message;

class MessageWidget extends AbstractHelper
{
	/** @var  FlashMessenger */
	protected $flashMessenger;


	public function __invoke() {
		return $this;
	}
		
	public function render($maxLevel = false) {
		$output = '';

		foreach($this->getFlashMessenger()->getMessages() AS $message) {
			$output .= '<div class="message">' . $message . '</div>' . PHP_EOL;
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

		$this->getFlashMessenger()->addMessage("asdf");
		foreach($this->getFlashMessenger()->getMessages() AS $message) {
			$output .= '<tr class=""><td>' . $message . '</tr></td>' . PHP_EOL;
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
		return $this->getFlashMessenger()->count() + Message::count($maxLevel);
	}

	protected function getFlashMessenger() {
		if(!$this->flashMessenger) $this->flashMessenger = new FlashMessenger();
		return $this->flashMessenger;
	}
}
