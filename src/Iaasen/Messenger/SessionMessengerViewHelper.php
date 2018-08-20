<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 26.04.2018
 * Time: 21:53
 */

namespace Iaasen\Messenger;


class SessionMessengerViewHelper
{
	/** @var  SessionMessenger */
	protected $sessionMessenger;

	public function __invoke() {
		return $this;
	}

	public function render() {

		$output = '';
		foreach($this->getSessionMessenger()->getMessagesWithSeverity() AS $message) {
			$output .= '<div class="message ' . $message['severity'] . '">' . $message['message'] . '</div>' . PHP_EOL;
		}
		return $output;
	}

	public function renderPanel() {
		$output = '<table class="table table-condensed" style="margin-bottom: 0">';

		foreach($this->getSessionMessenger()->getMessagesWithSeverity() AS $message) {
			$output .= '<tr class="' . $message['severity'] . '"><td>' . $message['message'] . '</tr></td>' . PHP_EOL;
		}

		$output .= '</table>';
		return $output;
	}

	public function count() {
		return $this->getSessionMessenger()->count();
	}

	protected function getSessionMessenger() {
		if(!$this->sessionMessenger) $this->sessionMessenger = new SessionMessenger();
		return $this->sessionMessenger;
	}
}