<?php
namespace Oppned\Log;

use Oppned\AbstractTable;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Select;

class LogTable extends AbstractTable
{
	public function find($id) {
		if($this->accessToView($id)) {
			return parent::find($id);
		}
		else return false;	
	}
	
	public function save($model) {
		if($this->accessToEdit($model)) {
			return parent::save($model);
		}
		return false;
	}
	
	public function delete($id) {
		if($this->accessToEdit($id)) {
			return parent::delete($id);
		}
		else return false;
	}
	
	
	
	/**
	 * 
	 * @param string $filter Valid options: 'important', 'read', 'unread', 'all'
	 * @param string $group
	 */
	public function getGroupLogs($filter = 'important', $limit = 10, $group = null) {
		if(!$group) $group = $this->currentUser->current_group;
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

//		$query = [];
//		$query['group'] = $group;
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
		
		$logs = array();
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
	
	protected function accessToView($id, $idType = 'log') {
		return true;
	}
	protected function accessToEdit($mixed) {
		return true;
	}
	/*
	protected function accessToView($id, $idType = 'log') {
		$user = $this->getCurrentUser();
		
		if($idType == 'log') {
			$select = new Select();
			$select->from($this->tableGateway->getTable()); // 'messagelog'
			$select->columns(array('group'));
			$select->where->equalTo('id', $id);
			$row = $this->query($select)->current();
			$group = $row['group'];
			if(isset($user->access[$group]) && $user->access[$group]['access_level'] > 0) {
				Message::create(6, "Giving user '{$user->username}' access to view quotation $id of group '$group'");
				return true;
			}
			else {
				Message::create(3, "Declining user '{$user->username}' access to view quotation '$id' of group '$group'");
				return false;
			}
		}
		elseif($idType == 'group') {
			
			if(isset($user->access[$id]) && $user->access[$id]['access_level'] > 0) {
				Message::create(6, "Giving user '{$user->username}' access to view quotations of group '$id'");
				return true;
			}
			else {
				Message::create(3, "Declining user '{$user->username}' access to view quotations of group '$id'");
				return false;
			}
		}
	}
	
	/**
	 * 
	 * @param unknown $mixed Quotation object or quotations_id
	 * @return boolean
	 * /
	protected function accessToEdit($mixed) {
		$user = $this->getCurrentUser();
		
		if($mixed instanceof Quotation) {
			if(isset($id->id)) $id = $id->id;
			else { // New quotation, no id yet
				if(isset($user->access[$mixed->group])) {
					if($mixed->owner == $user->username && $user->access[$mixed->group]['access_level'] > 1) {
						Message::create(6, "Giving user '{$user->username}' access to create quotations for group '{$mixed->group}', owned by '{$mixed->owner}'");
						return true;
					}
					elseif($user->access[$mixed->group]['access_level'] > 2) {
						Message::create(6, "Giving user '{$user->username}' access to create quotations for group '{$mixed->group}', owned by '{$mixed->owner}'");
						return true;
					}
				}
				Message::create(3, "Declining user '{$user->username}' access to create quotations for group '{$mixed->group}', owned by '{$mixed->owner}'");
				return false;
			}
		}
		else $id = $mixed;
		
		// Id is given, check the database
		$select = new Select();
		$select->from($this->tableGateway->getTable()); // 'quotations'
		$select->columns(array('group', 'owner'));
		$select->where->equalTo('id', $id);
		$row = $this->query($select)->current();
		
		if(isset($user->access[$row['group']])) {
			if($row['owner'] == $user->username && $user->access[$row['group']]['access_level'] > 1) {
				Message::create(6, "Giving user '{$user->username}' access to edit quotation $id of group '{$row['group']}', owned by '{$row['owner']}'");
				return true;
			}
			elseif($user->access[$row['group']]['access_level'] > 2) {
				Message::create(6, "Giving user '{$user->username}' access to edit quotation $id of group '{$row['group']}', owned by '{$row['owner']}'");
				return true;
			}
		}
		Message::create(3, "Declining user '{$user->username}' access to edit quotation $id of group '{$row['group']}', owned by '{$row['owner']}'");
		return false;
	}
	*/
}