<?php

namespace DbUtils\Tests\Table;

trait TableTestsTrait
{
	private $_db;
	private $_tableName = 'test.documents';
	private $_table;

	abstract protected function _newPdo(array $config);
	abstract protected function _newAdapter(array $config);
	abstract protected function _newTable($db, $tableName);

	public function setUp()
	{
		parent::setUp();

		$config = (new \DbUtils\DiContainer)['config'];

		$this->_db = $this->_newAdapter($config);
		$this->_table = $this->_newTable(
			$this->_db, $this->_tableName);
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->_db = null;
		$this->_table = null;
	}

	public function getConnection()
	{
		$config = (new \DbUtils\DiContainer)['config'];

		$pdo = $this->_newPdo($config);

		return $this->createDefaultDbConnection($pdo);
	}

	public function testGetConnection()
	{
		$this->assertInstanceOf(
			'\DbUtils\Adapter\AdapterInterface',
			$this->_table->getConnection());
	}

	public function getDataSet()
	{
		$xml = __DIR__ . '/../../../_files/documents.xml';
		return $this->createFlatXmlDataSet($xml);
	}


	public function testGetName()
	{
		$this->assertEquals('documents',
			$this->_table->getName());
	}

	public function testGetSchema()
	{
		$this->assertEquals('test', $this->_table->getSchema());
	}

	public function testGetFullName()
	{
		$this->assertEquals('test.documents',
			$this->_table->getFullName());
	}

	public function testGetPrimaryKey()
	{
		$pk = $this->_table->getPrimaryKey();
		$this->assertEquals('id', $pk['columns'][0]);
		$this->assertEquals('PRIMARY KEY', $pk['type']);
	}

	public function testGetUniques()
	{
		$arr = $this->_table->getUniques();
		$this->assertCount(1, $arr);
		$this->assertEquals('name',
			$arr['uidx_name']['columns'][0]);
	}

	public function testGetConstraints()
	{
		$arr = $this->_table->getConstraints();
		$this->assertCount(2, $arr);
		$this->assertArrayHasKey('uidx_name', $arr);
		//	primary key
		$this->assertEquals('id', array_shift($arr)['columns'][0]);
	}

	public function testGetIndices()
	{
		$arr = $this->_table->getIndices();
		$this->assertCount(3, $arr);
		$this->assertArrayHasKey('idx_group_id_name', $arr);
		$this->assertArrayHasKey('uidx_name', $arr);
	}

	public function testGetColumns()
	{
		$arr = $this->_table->getColumns();
		$columns = [
			'id',
			'group_id',
			'name',
			'content',
			'date',
			'bindata'
		];
		$this->assertEquals($columns, array_keys($arr));


		$this->assertContains('int', $arr['id']);
		$this->assertContains('int', $arr['group_id']);
		$this->assertContains('var', $arr['name']);
		$this->assertContains('text', $arr['content']);
	}

	public function testTruncate()
	{
		//	сначала было 5 записей
		$this->assertTableRowCount($this->_tableName, 5);
		//	обнуляем
		$this->_table->truncate();
		//	стало 0 записей
		$this->assertTableRowCount($this->_tableName, 0);
		//	добавляем запись-пустышку
		//	todo проверить что, не сбилась sequence
	}

	public function testGetPk()
	{
		$this->testGetPrimaryKey();
	}
}
