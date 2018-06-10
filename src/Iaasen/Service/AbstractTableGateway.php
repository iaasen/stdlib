<?php
/**
 * Created by PhpStorm.
 * User: iaase
 * Date: 10.06.2018
 * Time: 00:33
 */

namespace Iaasen\Service;


use Iaasen\Model\ModelInterface;
use Zend\Db\Adapter\AdapterInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class AbstractTableGateway extends TableGateway
{
	/**
	 * Extend this function with concrete implementations of Adapter and Model.
	 * The ReflectionBasedAbstractFactory will then be able to make it.
	 * @param string $tableName
	 * @param AdapterInterface $adapter
	 * @param $prototype
	 */
	public function __construct(
		string $tableName,
		AdapterInterface $adapter,
		$prototype = null
	)
	{
		$resultSetPrototype = new ResultSet();
		if($prototype) $resultSetPrototype->setArrayObjectPrototype($prototype);
		parent::__construct($tableName, $adapter, null, $resultSetPrototype);
	}
}