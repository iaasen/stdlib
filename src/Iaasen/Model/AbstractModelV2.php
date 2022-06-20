<?php
/**
 * User: Ingvar
 * Date: 11.11.2015
 */

namespace Iaasen\Model;

use Iaasen\DateTime;

/**
 * @property DateTime $timestamp_created
 * @property DateTime $timestamp_updated
 */
abstract class AbstractModelV2 extends AbstractEntityV2
{
	protected ?int $id = null;
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