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
	protected DateTime $timestamp_created;
	protected DateTime $timestamp_updated;

	/**
	 * @param array|\stdClass $data
	 */
	public function __construct($data = [])
	{
		$this->timestamp_created = new DateTime();
		$this->timestamp_updated = $this->timestamp_created;
		parent::__construct($data);
	}


	public function __clone()
	{
		$this->id = null;
		$this->timestamp_created = new DateTime();
		$this->timestamp_updated = $this->timestamp_created;
	}


	public function databaseSaveArray(): array
	{
		$data = parent::databaseSaveArray();
		$data['timestamp_updated'] = date(self::MYSQL_TIME_FORMAT);
		return $data;
	}

}