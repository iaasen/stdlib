<?php
namespace Iaasen\Messenger;

use Laminas\Mvc\Controller\Plugin\PluginInterface;
use Laminas\Session\Container;
use Laminas\Session\ManagerInterface;
use Laminas\Stdlib\DispatchableInterface;

/**
 * Replacement of FlashMessenger. Works across multiple instances by not deleting the session data on construct.
 * Increasing default hops to 2
 */
class SessionMessenger implements PluginInterface
{
	const NAMESPACE_DEFAULT = 'session_messenger';

	const SEVERITY_DEFAULT = 'default';
	const SEVERITY_SUCCESS = 'ok success';
	const SEVERITY_WARNING = 'warning';
	const SEVERITY_ERROR = 'error';
	const SEVERITY_INFO = 'default';

	protected static Container $container; // Session container
    protected static ManagerInterface $session; // Session manager

	protected string $namespace = self::NAMESPACE_DEFAULT;
	protected ?DispatchableInterface $controller;


	public function addMessage(string $message, string $severity = self::SEVERITY_DEFAULT, int $hops = 2) : self {
		$messageArray = [
			'severity' => $severity,
			'message' => $message,
		];
		$this->getContainer()->setExpirationHops($hops);
		$this->getQueue()->enqueue($messageArray);
		return $this;
	}


	public function addInfoMessage(string $message) : self {
		return $this->addMessage($message, self::SEVERITY_INFO);
	}

	public function addSuccessMessage(string $message) : self {
		return $this->addMessage($message, self::SEVERITY_SUCCESS);
	}

	public function addWarningMessage(string $message) : self {
		return $this->addMessage($message, self::SEVERITY_WARNING);
	}

	public function addErrorMessage(string $message) : self {
		return $this->addMessage($message, self::SEVERITY_ERROR);
	}


	/**
	 * @param string $severity
	 * @return string[]
	 */
	public function getMessages(string $severity = self::SEVERITY_DEFAULT) : array {
		$queue = $this->getQueue();
		$count = $queue->count();

		$messages = [];
		for($i = 0; $i < $count; $i++) {
			$current = $queue->dequeue();
			if($current['severity'] === $severity) $messages[] = $current['message'];
			else $queue->enqueue($current);
		}
		return $messages;
	}


	public function getInfoMessages() : array {
		return $this->getMessages(self::SEVERITY_INFO);
	}

	public function getSuccessMessages() : array {
		return $this->getMessages(self::SEVERITY_SUCCESS);
	}

	public function getWarningMessages() : array {
		return $this->getMessages(self::SEVERITY_WARNING);
	}

	public function getErrorMessages() : array {
		return $this->getMessages(self::SEVERITY_ERROR);
	}

	/**
	 * @return array[]
	 */
	public function getMessagesWithSeverity() : array {
		$queue = $this->getQueue();
		$count = $queue->count();

		$messages = [];
		for($i = 0; $i < $count; $i++) {
			$messages[] = $queue->dequeue();
		}
		return $messages;
	}


	public function count() : int {
		return $this->getQueue()->count();
	}


	protected function getQueue() : \SplQueue {
		$container = $this->getContainer();
		if(!isset($container->{$this->namespace})) $container->{$this->namespace} = new \SplQueue();
		return $container->{$this->namespace};
	}


	public function setNamespace(string $namespace = self::NAMESPACE_DEFAULT) : void {
		$this->namespace = $namespace;
	}


	/**
	 * Retrieve the session manager
	 * If none composed, lazy-loads a SessionManager instance
	 */
	protected function getSessionManager() : ManagerInterface
	{
		if (!isset($this->session) || !$this->session instanceof ManagerInterface) {
			self::$session = Container::getDefaultManager();
		}
		return self::$session;
	}


	/**
	 * Get session container for session messages
	 */
	protected function getContainer() : Container
	{
		if(!isset(self::$container) || !self::$container instanceof Container) {
            $manager = $this->getSessionManager();
            self::$container = new Container(self::NAMESPACE_DEFAULT, $manager);
		}
		return self::$container;
	}


	/**
	 * Set the current controller instance
	 */
	public function setController(DispatchableInterface $controller) : void
	{
		$this->controller = $controller;
	}


	/**
	 * Get the current controller instance
	 */
	public function getController() : ?DispatchableInterface
	{
		return $this->controller;
	}

	public function clearContainer() : void {
		self::$container = null;
	}
}
