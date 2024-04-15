<?PHP
namespace Oppned\Log;


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

	/** @var \Oppned\Log\Log[] */
	public array $logs = [];


	public function __construct(
		private LogTable $logTable,
		private UserService $userService
	) {}

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
	public function groupLog(string|int $priority, string $message, ?string $group = null) : void {
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
		
	public function userLog(int|string $priority,string $message, ?string $user = null) : void {
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
	public function getGroupLogs(string $filter = 'important', int $limit = 5, ?string $group = null) : array {
		if(!$group) $group = $this->userService->getCurrentUser()->current_group;
		return $this->logTable->getGroupLogs($filter, $limit, $group);
	}
	
	public function save() : void {
		foreach($this->logs AS $log) {
			$this->logTable->save($log);
		}
	}
}
