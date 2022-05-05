<?php
/**
 * Moved from Swiss because the use case is wider than a single project
 * User: ingvar.aasen
 * Date: 12.04.2019
 */

namespace Iaasen\Service;


use Iaasen\DateTime;
use Laminas\Db\Adapter\Adapter;

class MysqlBulkWriter
{
	protected string $table;
	protected array $fields;
	protected Adapter $dbAdapter;
	protected int $rowCount = 0;
	protected string $query = '';

	public function __construct(Adapter $dbAdapter)
	{
		$this->dbAdapter = $dbAdapter;
	}

	public function setTable(string $table) : void {
		$this->table = $table;
	}

	public function setFields(array $fields) : void {
		$this->fields = $fields;
	}

	public function insert(array $row) {
		$this->queryBegin();
		$this->query .= '(';
		for($i = 0; $i < count($row); $i++) {
			if(is_null($row[$i])) $this->query .= 'NULL';
			elseif(is_bool($row[$i])) $this->query .= ($row[$i]) ? 1 : 0;
			elseif(is_int($row[$i])) $this->query .= $row[$i];
			else $this->query .= $this->dbAdapter->getPlatform()->quoteValue($row[$i]);

			if($i < count($row)-1) $this->query .= ', ';
		}
		$this->query .= '),' . PHP_EOL;
		$this->rowCount++;
		$this->queryCommit();
	}

	protected function queryBegin() : void {
		if($this->rowCount == 0) {
			$this->query = "REPLACE INTO `{$this->table}` (";
			for($i = 0; $i < count($this->fields); $i++) {
				$this->query .= '`' . $this->fields[$i] . '`';
				if($i < count($this->fields)-1) $this->query .= ', ';
			}
			$this->query .= ') VALUES' . PHP_EOL;
		}
	}

	protected function queryCommit($now = false) : void {
		if($this->rowCount == 0) return;

		if($now || $this->rowCount >= 100) {
			$this->query = substr($this->query, 0, -2);
			$this->query .= ';' . PHP_EOL;
			$this->dbAdapter->query($this->query)->execute();
			$this->query = '';
			$this->rowCount = 0;
		}
	}

	public function finalCommit() {
		$this->queryCommit(true);
	}

	/**
	 * @param string $field
	 * @param int $hours Delete rows with timestamp more than $hours hours before $offset
	 * @param DateTime|null $offset Offset defaults to now
	 * @return int Affected rows
	 * @throws \Exception
	 * Deletes older than $hours hours before $offset
	 */
	public function deleteOlderThan(string $field, int $hours, ?DateTime $offset = null) : int {
		if(!$offset) $offset = new DateTime();
		$interval = new \DateInterval('PT' . $hours . 'H');
		$offset->sub($interval);

		$query = "DELETE FROM `$this->table` WHERE `$field` < '{$offset->formatMysql()}'";
		return $this->dbAdapter->query($query)->execute()->getAffectedRows();
	}
}