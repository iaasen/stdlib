<?php
namespace Iaasen\Service;

use Iaasen\Model\AbstractEntityV2;
use Iaasen\Model\AbstractModel;
use Iaasen\Exception\NotFoundException;
use Iaasen\Model\AbstractModelV2;
use Iaasen\Model\ModelInterface;
use Iaasen\Model\ModelInterfaceV2;
use Laminas\Db\Adapter\Adapter;
use Laminas\Db\Sql\AbstractPreparableSql;
use Laminas\Db\Sql\Where;
use Laminas\Db\TableGateway\TableGateway;
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Sql;
use Laminas\Db\TableGateway\TableGatewayInterface;


abstract class AbstractTable
{
	/** @var  mixed */
	protected $currentUser;
	/** @var  TableGateway  */
	protected $primaryGateway;

	protected bool $allowInsertWithId = false;
	const MYSQL_TIMESTAMP_FORMAT = 'Y-m-d H:i:s';

	public function __construct(
		$currentUser,
		TableGatewayInterface $primaryGateway,
		array $additionalDependencies = []
	) {
		$this->currentUser = $currentUser;
		$this->primaryGateway = $primaryGateway;
		foreach ($additionalDependencies AS $key => $value) {
			if (!property_exists($this, $key)) {
				throw new \LogicException($key . ' is not defined in ' . get_class($this));
			}
			$this->$key = $value;
		}
	}


	/**
	 * Allow to insert rows into the database with 'id' set, bypassing the auto numbering scheme
	 */
	public function setAllowInsertWithId(bool $allow) : void {
		$this->allowInsertWithId = $allow;
	}


	public function getAllowInsertWithId() : bool {
		return $this->allowInsertWithId;
	}


	/**
	 * @param array $where
	 * @param array $order
	 * @return ModelInterface[]|ModelInterfaceV2[]
	 */
	protected function fetchAll($where = [], $order = []) {
		$rowSet = $this->primaryGateway->select(
				function(Select $select) use ($where, $order) {
					$select->where($where);
					$select->order($order);
				}
		);

		$objects = [];
		for($i = 0; $i < $rowSet->count(); $i++) {
			$objects[] = $rowSet->current();
			$rowSet->next();
		}

		return $objects;
	}


	/**
	 *
	 * @param int $id
	 * @return ModelInterface|ModelInterfaceV2|null
	 * @throws NotFoundException
	 */
	protected function find($id) {
		$id  = (int) $id;
		$rowSet = $this->primaryGateway->select(['id' => $id]);
		$row = $rowSet->current();
		if (!$row) {
			throw new NotFoundException("Row '$id' not found in " . get_class($this));
		}
		return $row;
	}


	/**
	 * @param AbstractModel|AbstractEntityV2 $model
	 * @return int
	 */
	protected function save($model) {
		$data = $model->databaseSaveArray();
		unset($data['id']);
		if(isset($data['timestamp_updated'])) $data['timestamp_updated'] = date(self::MYSQL_TIMESTAMP_FORMAT, time());
		
		$id = (int) $model->id;
		if ($id == 0) {
			// New row with autoincrement
			$this->primaryGateway->insert($data);
			$id = (int) $this->primaryGateway->getLastInsertValue();
		} else {
			try {
				// Update existing row
				$this->find($id); // Throws exception if not found
				unset($data['timestamp_created']);
				$this->primaryGateway->update($data, ['id' => $id]);
			}
			catch(NotFoundException $e) {
				// New row with id set, overrides autoincrement
				if($this->allowInsertWithId) {
					$data['id'] = $model->id;
					$this->primaryGateway->insert($data);
				}
				else throw $e;
			}
		}
		$model->id = $id;
		return $id;
	}


	/**
	 * @param int $id
	 * @return bool
	 */
	protected function delete($id) {
		if(is_object($id)) {
			/** @var mixed $id */
			$id = $id->id;
		}
		$result = $this->primaryGateway->delete(['id' => $id]);
		return (bool) $result;
	}

	
	/**
	 * Send query directly to adapter return rows
	 * 
	 * @param AbstractPreparableSql $select
	 * @param bool $outputSqlString
	 * @return \Laminas\Db\Adapter\Driver\ResultInterface
	 */
	protected function query($select, $outputSqlString = false) {
		if($outputSqlString) echo $this->getSqlString($select);
		$sql = new Sql($this->primaryGateway->getAdapter());
		$statement = $sql->prepareStatementForSqlObject($select);
		return $statement->execute();
	}

	protected function getSqlString(AbstractPreparableSql $sql) : string {
		return $sql->getSqlString($this->primaryGateway->getAdapter()->getPlatform());
	}

	protected function getSelectPrototype() : Select {
		return $this->primaryGateway->getSql()->select();
	}

	protected function selectToObjects(Select $select) : array {
		return $this->convertRowSetToArray($this->primaryGateway->selectWith($select));
	}

	protected function whereToObjects(Where $where) : array {
		return $this->convertRowSetToArray($this->primaryGateway->select($where));
	}

	protected function selectToArrays(Select $select) : array {
		return $this->convertRowSetToArray($this->query($select));
	}
	
	public function getObjectPrototype() {
		return $this->primaryGateway->getResultSetPrototype()->getArrayObjectPrototype();
	}

	public function getTableName() {
		return $this->primaryGateway->getTable();
	}

	public function convertRowSetToArray($rowSet) : array {
		$objects = [];
		foreach($rowSet as $row) {
			$objects[] = $row;
		}
		return $objects;
	}

	public function getAdapter() {
		return $this->primaryGateway->getAdapter();
	}

	public function begin() {
		/** @var Adapter $adapter */
		$adapter = $this->primaryGateway->getAdapter();
		$adapter->query("START TRANSACTION;", Adapter::QUERY_MODE_EXECUTE);

	}

	public function commit() {
		/** @var Adapter $adapter */
		$adapter = $this->primaryGateway->getAdapter();
		$adapter->query("COMMIT;", Adapter::QUERY_MODE_EXECUTE);
	}


	/**
	 * Returns an array where they key is the the key from the objects and the value is an array of all objects with that key.
	 * Useful for collecting dependencies from the database
	 * @param mixed[] $objects
	 * @param string $key
	 * @return mixed[]
     * @deprecated Use ObjectKeyMatrix class
	 * @see ObjectKeyMatrix
	 */
	protected function getObjectKeyMatrix(array $objects, string $key = 'id') : array {
		return ObjectKeyMatrix::getObjectKeyMatrix($objects, $key);
	}


    /**
     * @deprecated Use ObjectKeyMatrix class
	 * @see ObjectKeyMatrix
     */
	protected function getArrayKeyMatrix(array $array, string $key = 'id') : array {
		return ObjectKeyMatrix::getArrayKeyMatrix($array, $key);
	}


	/**
	 * @param mixed[] $objectKeyMatrix The object matrix given by getObjectKeyMatrix()
	 * @param string $objectFunction What function to call on parent object
	 * @param mixed[] $childObjects The child objects to populate from
	 * @param string $childKey The child object attribute used to match against the object matrix
     * @deprecated Use ObjectKeyMatrix class
	 * @see ObjectKeyMatrix
	 */
	protected function populateObjectKeyMatrixWithFunctionCall(array $objectKeyMatrix, string $objectFunction, array $childObjects, string $childKey) : void {
		ObjectKeyMatrix::populateObjectKeyMatrixWithFunctionCall($objectKeyMatrix, $objectFunction, $childObjects, $childKey);
	}

}
