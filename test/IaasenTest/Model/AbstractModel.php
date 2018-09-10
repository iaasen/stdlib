<?php
/**
 * User: ingvar.aasen
 * Date: 10.09.2018
 * Time: 13:08
 */

namespace IaasenTest\Model;


use Iaasen\Entity\DateTime;
use PHPUnit\Framework\TestCase;

class AbstractModel extends TestCase
{
	protected $testData = [
		'bool' => true,
		'int' => 10,
		'float' => 10.2,
		'string' => 'abc',
		'stringArray' => '["abc","bcd"]',
		'dateTime' => '2018-08-15 15:10:00',
	];

	protected $time = '2018-08-15 15:10:00';


	public function setUp()
	{
		date_default_timezone_set('Europe/Oslo');
		parent::setUp();
	}

	/**
	 * @throws \Exception
	 */
	public function testSettingAndGettingVariables() {
		$object = new AbstractModelImplementation();

		$object->exchangeArray($this->testData);

		$this->assertSame(true, $object->bool);
		$this->assertSame(10, $object->int);
		$this->assertSame(10.2, $object->float);
		$this->assertSame('abc', $object->string);
		$this->assertSame(['abc', 'bcd'], $object->stringArray);
		$object->stringArray = ['abc', 'bcd'];
		$this->assertSame(['abc', 'bcd'], $object->stringArray);

	}

	public function testSettingAndGettingDates() {
		$object = new AbstractModelImplementation();
		$object->dateTime = null;
		$this->assertSame(null, $object->dateTime);
		$object->dateTime = '';
		$this->assertSame(null, $object->dateTime);
		$object->dateTime = $this->time;
		$this->assertEquals(new DateTime($this->time), $object->dateTime);
		$object->dateTime = new DateTime($this->time);
		$this->assertEquals(new DateTime($this->time), $object->dateTime);
		$object->dateTime = new \DateTime($this->time);
		$this->assertEquals(new DateTime($this->time), $object->dateTime);
	}

	public function testIsset() {
		$object = new AbstractModelImplementation();
		$this->assertSame(false, isset($object->string));
		$object->string = "";
		$this->assertSame(true, isset($object->string));
		$object->string = "abc";
		$this->assertSame(true, isset($object->string));
	}

//	public function testUnset() {
//		$object = new AbstractModelImplementation();
//		$object->string = 'abc';
//		unset($object->string);
//		$this->assertSame('abc', $object->string);
//		unset($object->string);
//		$this->assertSame(null, $object->string);
//	}

	public function testGetArrayCopy() {
		$object = new AbstractModelImplementation();
		$object->exchangeArray($this->testData);
		$object->timestamp_created = $this->time;
		$object->timestamp_updated = $this->time;

		$testData = $this->testData;
		$testData['stringArray'] = ['abc', 'bcd'];
		$testData['timestamp_created'] = $this->time;
		$testData['timestamp_updated'] = $this->time;

		$this->assertSame($testData, $object->getArrayCopy());
	}

	public function testDatabaseSaveArray() {
		$object = new AbstractModelImplementation();
		$object->exchangeArray($this->testData);
		$object->timestamp_created = $this->time;
		$object->timestamp_updated = $this->time;

		$data = $object->databaseSaveArray();
		$this->assertSame(1, $data['bool']);
		$this->assertSame(10, $data['int']);
		$this->assertSame(10.2, $data['float']);
		$this->assertSame('abc', $data['string']);
		$this->assertSame('["abc","bcd"]', $data['stringArray']);
		$this->assertSame($this->time, $data['dateTime']);
		$this->assertSame($this->time, $data['timestamp_created']);
		$this->assertNotEquals($this->time, $data['timestamp_updated']);
		$this->assertTrue(is_string($data['timestamp_updated']));
	}


}