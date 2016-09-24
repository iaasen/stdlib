<?php
namespace Oppned;

use Acl\Model\User;
use Priceestimator\Model\ModelInterface;
use Zend\Db\Sql\AbstractPreparableSql;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;



abstract class AbstractTable
{
	/** @var  User */
	protected $currentUser;
	/** @var TableGateway  */
	protected $primaryGateway;

	public function __construct(User $currentUser, TableGateway $primaryGateway, array $additionalDependencies = [])
	{
		$this->currentUser = $currentUser;
		$this->primaryGateway = $primaryGateway;
		foreach ($additionalDependencies AS $key => $value) {
			if (!property_exists($this, $key)) {
				throw new \LogicException($key . ' is not defined in ' . get_class($this));
			}
			$this->$key = $value;
		}
	}
	
	protected function fetchAll($where = null, $order = array())
	{
		$rowSet = $this->primaryGateway->select(
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
		$rowset = $this->primaryGateway->select(array('id' => $id));
		$row = $rowset->current();
		if (!$row) {
			//throw new \Exception("Could not find row $id");
			throw new \Exception("Row '$id' not found", 104);
		}
		return $row;
	}

	/**
	 * @param ModelInterface $model
	 * @return int
	 * @throws \Exception
	 */
	protected function save($model) {
		$data = $model->databaseSaveArray();
		unset($data['id']);
		if(isset($data['timestamp_updated'])) $data['timestamp_updated'] = date("Y-m-d H:i:s", time());
		
		$id = (int) $model->id;
		if ($id == 0) {
			$this->primaryGateway->insert($data);
			$id = $this->primaryGateway->getLastInsertValue();
		} else {
			if ($this->find($id)) {
				unset($data['timestamp_created']);
				$this->primaryGateway->update($data, array('id' => $id));
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
		$result = $this->primaryGateway->delete(array('id' => $id));
		return (bool) $result;
	}
	
	/**
	 * Send query directly to adapter return rows
	 * 
	 * @param AbstractPreparableSql $select
	 * @param bool $outputSqlString
	 * @return \Zend\Db\Adapter\Driver\ResultInterface
	 */
	protected function query($select, $outputSqlString = false) {
		if($outputSqlString) echo $select->getSqlString($this->primaryGateway->getAdapter()->getPlatform());
		$sql = new Sql($this->primaryGateway->getAdapter());
		$statement = $sql->prepareStatementForSqlObject($select);
		return $statement->execute();
	}
	
	public function getObjectPrototype() {
		return $this->primaryGateway->getResultSetPrototype()->getArrayObjectPrototype();
	}



}
