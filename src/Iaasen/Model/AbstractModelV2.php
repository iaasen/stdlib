<?php
/**
 * Created by PhpStorm.
 * User: Ingvar
 * Date: 11.11.2015
 * Time: 19.19
 */

namespace Iaasen\Model;

use ArrayObject;
use Exception;
use Iaasen\DateTime;
use Iaasen\Exception\InvalidArgumentException;
use Iaasen\Exception\LogicException;
use phpDocumentor\Reflection\DocBlockFactory;
use phpDocumentor\Reflection\Types\Array_;
use phpDocumentor\Reflection\Types\Object_;
use ReflectionClass;
use ReflectionProperty;

/**
 * Class AbstractModel
 * @package Iaasen\Model
 * @property DateTime $timestamp_created
 * @property DateTime $timestamp_updated
 */
abstract class AbstractModelV2 extends AbstractEntityV2
{
	/** @var DateTime */
	protected $timestamp_created;
	/** @var DateTime */
	protected $timestamp_updated;


	public function __construct(array $data = [])
	{
		$this->timestamp_created = new DateTime();
		$this->timestamp_updated = $this->timestamp_created;
		parent::__construct($data);
	}


	public function __get($name) {
		$getterName = 'get' . ucfirst($name);
		if(method_exists($this, $getterName)) return $this->$getterName();

		$doc = self::$docBlockData[get_class($this)];
		if(isset($doc[$name])) return $this->$name;

		throw new InvalidArgumentException("Property '$name' not found or is private in " . get_class($this));
	}




	public function __clone()
	{
		$this->id = null;
		$this->timestamp_created = new DateTime();
		$this->timestamp_updated = $this->timestamp_created;
	}


	/**
	 * Called when object is created from database by TableGateway
	 * Called when form is validated
	 *
	 * @param array $data
	 * @throws \Exception
	 * @return void
	 */
	public function exchangeArray($data) {
		foreach($data AS $key => $value) {
			$this->__set($key, $value);
		}
	}

	/**
	 * Called by \Laminas\Form::bind()
	 * @return array
	 */
	public function getArrayCopy() {
		$data = [];
		foreach(self::$docBlockData[get_class($this)] AS $name => $doc) {
			$value = $this->__get($name);

			switch($doc['value']) {
				case '\DateTime':
				case 'DateTime':
					if($value) {
						/** @var DateTime $value */
						$data[$name] = $value->format(self::MYSQL_TIME_FORMAT);
					}
					else $data[$name] = null;
					break;
				default:
					$data[$name] = $value;
					break;
			}
		}
		return $data;
	}


//	/**
//	 * Used by \Priceestimator\Model\DbTable to format model data for the database.
//	 * @return array
//	 */
//	public function databaseSaveArray() : array {
//		$data = [];
//		foreach(self::$docBlockData[get_class($this)] AS $name => $doc) {
//			$value = $this->__get($name);
//			switch ($doc['value']) {
//				case 'bool':
//					$data[$name] = ($value) ? 1 : 0;
//					break;
//				case '\DateTime':
//					/** @var \DateTime $value */
//					if($value) $data[$name] = $value->format(self::MYSQL_TIME_FORMAT);
//					else $data[$name] = null;
//					break;
//				case 'string[]':
//				case 'int[]':
//				case '[]':
//				case 'mixed[]':
//				case 'array':
//					if(is_array($value)) $data[$name] = json_encode($value);
//					else $data[$name] = json_encode($value);
//					break;
//				case '\stdClass':
//					$data[$name] = json_encode($value);
//					break;
//				default:
//					$data[$name] = $value;
//					break;
//			}
//		}
//		$data['timestamp_updated'] = date(self::MYSQL_TIME_FORMAT);
//		return $data;
//	}

}