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
use Laminas\Db\Adapter\Adapter as DbAdapter;
use PhpOffice\PhpSpreadsheet\Reader\DefaultReadFilter;
use Swiss\Service\Sharepoint\ProjectReadFilter;

class MysqlBulkWriterUsageExampleExcel
{
	protected string $path = 'path';
	protected string $filenameProjects = 'filename.xlsx';
	protected MysqlBulkWriter $writer;
	protected DbAdapter $dbAdapter;

	public function __construct(DbAdapter $dbAdapter)
	{
		$this->dbAdapter = $dbAdapter;
		$this->writer = new MysqlBulkWriter($this->dbAdapter);
		$this->writer->setTable('table_name');
		$this->writer->setFields([
			'id',
			'field1',
			'field2',
			'field3',
		]);
	}

	public function runImport() {
		$inputFilename = $this->path . '/' . $this->filenameProjects;
		$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		$reader->setReadDataOnly(true);
		$reader->setReadFilter(new DefaultReadFilter());

		$spreadsheet = $reader->load($inputFilename);
		$sheet = $spreadsheet->getSheet($spreadsheet->getFirstSheetIndex());
		$rows = $sheet->getRowIterator();
		foreach($rows AS $key => $row) {
			if($key == 1) continue; // Headers on first row

			$e = [];
			foreach($row->getCellIterator() AS $col) {
				$e[] = $col->getValue();
			}

			if(strlen($e[6])) { // Make extra field
				// Convert from Excel date serial number. Days after 01.01.1900
				$expiry = new \DateTime();
				$expiry->setTimestamp(($e[6] - 25569) * 86400);
				$expiry = $expiry->format('Y-m-d');
			}
			else {
				$expiry = null;
			}

			$this->writer->insert([
				(int) $e[0], // id
				$e[1], // field1
				$e[2], // field2
				$expiry, // field3
			]);

		}
		$this->writer->finalCommit();
	}}