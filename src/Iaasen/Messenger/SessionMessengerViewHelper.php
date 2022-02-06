<?php

namespace Iaasen\Messenger;


class SessionMessengerViewHelper
{
	protected SessionMessenger $sessionMessenger;


	public function __invoke() : self {
		return $this;
	}


	public function render() : string {

		$output = '';
		foreach($this->getSessionMessenger()->getMessagesWithSeverity() AS $message) {
			$output .= '<div class="message ' . $message['severity'] . '">' . $message['message'] . '</div>' . PHP_EOL;
		}
		return $output;
	}


	public function renderPanel() : string {
		$output = '<table class="table table-condensed" style="margin-bottom: 0">';

		foreach($this->getSessionMessenger()->getMessagesWithSeverity() AS $message) {
			$output .= '<tr class="' . $message['severity'] . '"><td>' . $message['message'] . '</tr></td>' . PHP_EOL;
		}

		$output .= '</table>';
		return $output;
	}


	public function count() : int {
		return $this->getSessionMessenger()->count();
	}


	protected function getSessionMessenger() : SessionMessenger {
		if(!isset($this->sessionMessenger) || !$this->sessionMessenger instanceof SessionMessenger) $this->sessionMessenger = new SessionMessenger();
		return $this->sessionMessenger;
	}
}