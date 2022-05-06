<?php
/**
 * User: ingvar
 * Date: 05.05.2022
 * Time: 14.32
 */


namespace Iaasen\Service;

use Box\Spout\Common\Type;
use Box\Spout\Reader\CSV\Reader;
use Box\Spout\Reader\ReaderFactory;

class MysqlBulkWriterUsageExampleCsv
{
	protected MysqlBulkWriter $writer;
	protected string $url;

	public function __construct(MysqlBulkWriter $writer, $url = '')
	{
		$this->writer = $writer;
		$this->url = $url;
	}

	public function setUrl(string $url)
	{
		$this->url = $url;
	}

	public function import()
	{

		$this->writer->setTable('table_name');
		$this->writer->setFields([
			'id',
			'field1',
			'field2',
			'field3',
		]);

		/** @var Reader $reader */
		$reader = ReaderFactory::create(Type::CSV); // for CSV files
		$reader->open($this->url);

		echo "Import:                    ";
		$count = 0;
		foreach ($reader->getSheetIterator() as $sheet) {
			foreach ($sheet->getRowIterator() as $row) {
				if ($count > 0) { // Exlude headerline
					$rowData = [
						(int) $row[0], // id
						utf8_encode($row[0]), // field1
						$row[1], // field2
						$row[2], // field3
					];
					$this->writer->insert($rowData);

					if ($count % 1000 === 0) {
						echo "\033[6D" . str_pad($count, 6, ' ', STR_PAD_LEFT);
					}
				} // Prevent headerline to be saved
				$count++;
			}
		}
		$reader->close();
		$this->writer->finalCommit();
		echo "\033[6D" . str_pad($count - 1, 6, ' ', STR_PAD_LEFT);

		$affected = $this->writer->deleteOlderThan('timestamp_updated', 4);
		echo ' (' . $affected . ' old rows deleted)' . PHP_EOL;
	}
}