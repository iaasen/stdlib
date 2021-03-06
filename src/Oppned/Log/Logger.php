<?PHP
namespace Oppned\Log;

//use Laminas\Session\Container;
//use Laminas\Stdlib\SplQueue;

use Acl\Service\UserService;

class Logger {
	const EMERG  = 0;
	const ALERT  = 1;
	const CRIT   = 2;
	const ERR    = 3;
	const WARN   = 4;
	const NOTICE = 5;
	const INFO   = 6;
	const DEBUG  = 7;

	/** @var LogTable  */
	private $logTable;
	/** @var \Acl\Service\UserService  */
	private $userService;
	/** @var Log[] */
	public $logs = [];


	public function __construct(LogTable $logTable, UserService $userService)
	{
		$this->logTable = $logTable;
		$this->userService = $userService;
	}

	/**
	 *
	 * Push a new message into the stack.
	 * Type suggestion:
	 * 0 - EMERG
	 * 1 - ALERT
	 * 2 - CRIT
	 * 3 - ERR
	 * 4 - WARN
	 * 5 - NOTICE
	 * 6 - INFO
	 * 7 - DEBUG
	 *
	 * @param string|integer $priority Priority id or name
	 * @param string $message
	 * @param string|null $group
	 */
	public function groupLog($priority, $message, $group = null) {
		$log = new Log();
		$log->priority = $priority;
		$log->message = $message;
		if($group) $log->group = $group;
		else {
			$user = $this->userService->getCurrentUser();
			$log->group = $user->current_group;
		}
		$this->logs[] = $log;
	}
		
	public function userLog($priority, $message, $user = null) {
		$log = new Log();
		$log->priority = $priority;
		$log->message = $message;
		if($user) $log->user = $user;
		else {
			$user = $this->userService->getCurrentUser();
			$log->user = $user->username;
		}
		$this->logs[] = $log;
	}

	/**
	 *
	 * @param string $filter Choices: important, unread, read
	 * @param int $limit
	 * @param string|null $group
	 * @return array
	 */
	public function getGroupLogs($filter = 'important', $limit = 5, $group = null) {
		if(!$group) $group = $this->userService->getCurrentUser()->current_group;
		return $this->logTable->getGroupLogs($filter, $limit, $group);
	}
	
	public function save() {
		foreach($this->logs AS $log) {
			$this->logTable->save($log);
		}
	}
}
