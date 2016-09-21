<?php
namespace Oppned\Log;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorInterface;



abstract class DbTable implements ServiceLocatorAwareInterface
{
	protected $tableGateway;
	protected $serviceLocator;
	protected $tables;
	
	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}
	
	protected function fetchAll($where = null, $order = array())
	{
		$rowSet = $this->tableGateway->select(
				function(Select $select) use ($where, $order) {
					$select->where($where);
					$select->order($order);
				}
		);
		
		$objects = array();
		for($i = 0; $i < $rowSet->count(); $i++) {
			$objects[] = $rowSet->current();
			$rowSet->next();
		}
		return $objects;
	}
	
	protected function find($id)
	{
		$id  = (int) $id;
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();
		if (!$row) {
			throw new \Exception("Could not find row $id");
		}
		return $row;
	}
	
	protected function save($model) {
		$data = $model->databaseSaveArray();
		unset($data['id']);
		if(isset($data['timestamp_updated'])) $data['timestamp_updated'] = date("Y-m-d H:i:s", time());
		
		$id = (int) $model->id;
		if ($id == 0) {
			$this->tableGateway->insert($data);
			$id = $this->tableGateway->getLastInsertValue();
		} else {
			if ($this->find($id)) {
				unset($data['timestamp_created']);
				$this->tableGateway->update($data, array('id' => $id));
			} else {
				throw new \Exception('Given id does not exist');
			}
		}
		return $id;
	}
		
	protected function delete($id)
	{
		if(is_object($id)) {
			$id = $id->id;
		}
		$result = $this->tableGateway->delete(array('id' => $id));
		return (bool) $result;
	}
	
	public function getCurrentUser() {
		return $this->getServiceLocator()->get('UserTable')->getCurrentUser();
	}
	
	/**
	 * Send query directly to adapter return rows
	 * 
	 * @param Select $select
	 * @param bool $outputSqlString
	 * @return \Zend\Db\Adapter\Driver\ResultInterface
	 */
	protected function query($select, $outputSqlString = false) {
		if($outputSqlString) echo $select->getSqlString($this->tableGateway->getAdapter()->getPlatform());
		$sql = new Sql($this->tableGateway->getAdapter());
		$statement = $sql->prepareStatementForSqlObject($select);
		return $statement->execute();
	}
	

	
	public function getTable($table) {
		if (!isset($this->tables[$table])) {
			$sm = $this->getServiceLocator();
			$table = ucfirst ($table);
			$this->tables[$table] = $sm->get ($table . 'Table');
		}
		return $this->tables [$table];
	}
	
	public function setServiceLocator(ServiceLocatorInterface $serviceLocator) {
		$this->serviceLocator = $serviceLocator;
	}
	
	public function getServiceLocator() {
		return $this->serviceLocator;
	}



}
