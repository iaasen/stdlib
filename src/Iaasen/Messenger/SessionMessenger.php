<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 26.04.2018
 * Time: 21:50
 */

namespace Iaasen\Messenger;
use Zend\Mvc\Controller\Plugin\PluginInterface;
use Zend\Session\Container;
use Zend\Session\ManagerInterface;
use Zend\Stdlib\DispatchableInterface;

/**
 * Making the FlashMessenger work across multiple instances by not deleting the session data on construct.
 * Increasing default hops to 2
 */
class SessionMessenger implements PluginInterface
{
	const NAMESPACE_DEFAULT = 'default';

	const SEVERITY_DEFAULT = 'default';
	const SEVERITY_SUCCESS = 'success';
	const SEVERITY_WARNING = 'warning';
	const SEVERITY_ERROR = 'error';
	const SEVERITY_INFO = 'default';

	protected static $container;

	/** @var ManagerInterface */
	protected $session;
	/** @var string  */
	protected $namespace = self::NAMESPACE_DEFAULT;
	/**
	 * @var null|DispatchableInterface
	 */
	protected $controller;


	/**
	 * @param string $message
	 * @param string $severity
	 * @param int $hops
	 * @return SessionMessenger
	 */
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

	/**
	 * @return \SplQueue
	 */
	protected function getQueue() : \SplQueue {
		$container = $this->getContainer();
		if(!isset($container->{$this->namespace})) $container->{$this->namespace} = new \SplQueue();
		return $container->{$this->namespace};
	}

	/**
	 * @param string $namespace
	 */
	public function setNamespace(string $namespace = self::NAMESPACE_DEFAULT) : void {
		$this->namespace = $namespace;
	}

	/**
	 * Retrieve the session manager
	 * If none composed, lazy-loads a SessionManager instance
	 * @return ManagerInterface
	 */
	protected function getSessionManager() : ManagerInterface
	{
		if (!$this->session instanceof ManagerInterface) {
			$this->session = Container::getDefaultManager();
		}
		return $this->session;
	}

	/**
	 * Get session container for session messages
	 * @return Container
	 */
	protected function getContainer() : Container
	{
		if (self::$container instanceof Container) {
			return self::$container;
		}
		$manager = $this->getSessionManager();
		self::$container = new Container('SessionMessenger', $manager);
		return self::$container;
	}

	/**
	 * Set the current controller instance
	 *
	 * @param  DispatchableInterface $controller
	 * @return void
	 */
	public function setController(DispatchableInterface $controller)
	{
		$this->controller = $controller;
	}

	/**
	 * Get the current controller instance
	 *
	 * @return null|DispatchableInterface
	 */
	public function getController()
	{
		return $this->controller;
	}
}
