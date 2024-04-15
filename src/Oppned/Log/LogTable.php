<?php
namespace Oppned\Log;

use Iaasen\Service\AbstractTable;
use Laminas\Db\Sql\Expression;
use Laminas\Db\Sql\Select;

class LogTable extends AbstractTable {

	/**
	 * @param int $id
	 * @return false|Log
	 */
	public function find($id) {
		if($this->accessToView($id)) {
			/** @var Log $log */
			$log = parent::find($id);
			return $log;
		}
		else return false;	
	}


	/**
	 * @param Log $model
	 * @return false|int
	 */
	public function save($model) {
		if($this->accessToEdit($model)) {
			return parent::save($model);
		}
		return false;
	}


	/**
	 * @param int $id
	 * @return bool
	 */
	public function delete($id) {
		if($this->accessToEdit($id)) {
			return parent::delete($id);
		}
		else return false;
	}
	
	
	
	/**
	 * @param string $filter Valid options: 'important', 'read', 'unread', 'all'
	 */
	public function getGroupLogs(string $filter = 'important', int $limit = 10, string $group = null) {
		if(!$group) return false;
		if(!$this->accessToView($group, 'group')) return false;;

		// Count unread logs and make sure they are all listed
		if($filter == 'important') {
			$select = new Select();
			$select->columns(['count' => new Expression('COUNT(*)')]);
			$select->from($this->primaryGateway->getTable());
			$select->where->equalTo('group', $group);
			$select->where->equalTo('viewed', 0);
			$rows = $this->query($select);
			if($rows->current()['count'] > $limit) $limit = (int) $rows->current()['count'];
		}

		$select = new Select();
		$select->from($this->primaryGateway->getTable());
		$select->where->equalTo('group', $group);
		$select->limit($limit);
		
		switch($filter) {
			case 'important':
			default:
				$select->order(['viewed', 'timestamp_created DESC']);
				break;
			case 'read':
				$select->where->equalTo('viewed', 1);
				$select->order('timestamp_created DESC');
				break;
			case 'unread':
				$select->where->equalTo('viewed', 0);
				$select->order('timestamp_created DESC');
				break;
			case 'all':
				$select->order('timestamp_created DESC');
				break;
		}
		$rows = $this->primaryGateway->selectWith($select);
		
		$logs = [];
		foreach($rows AS $row) {
			$logs[] = $row;
			if(!$row->viewed) {
				$row->viewed = true;
				$this->save($row);
				$row->viewed = false;
			}
		}
		return $logs;
	}

	
	protected function accessToView(int|string $id, string $idType = 'log') : bool {
		return true;
	}


	protected function accessToEdit(int|Log $mixed) : bool {
		return true;
	}

}
