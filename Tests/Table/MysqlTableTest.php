<?php

class MysqlTableTest extends PHPUnit_Framework_TestCase
{
	private $_db;

	public function setUp()
	{
		$dic = new DbUtils\DiContainer;
		$config = $dic['config']['mysql'];

		$this->_db = new DbUtils\Adapter\Mysqli\Mysqli($config);

		$this->_table = $this->_db->getTable('test.documents');
	}

	public function testGetConnection()
	{
		$this->assertInstanceOf('DbUtils\Adapter\AdapterInterface',
			$this->_table->getConnection());
	}

	public function testGetName()
	{
		$this->assertEquals('documents', $this->_table->getName());
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
		$this->assertEquals('PRIMARY', $pk['name']);
		$this->assertEquals([ 0 => 'id' ], $pk['columns']);
	}

	public function testGetUniques()
	{
		$arr = $this->_table->getUniques();
		$this->assertCount(1, $arr);
		$this->assertArrayHasKey('uidx_group_id', $arr);
		$this->assertEquals('group_id', array_pop($arr)['columns'][0]);
	}

	public function testGetConstraints()
	{
		$arr = $this->_table->getConstraints();
		$this->assertCount(2, $arr);
		$this->assertEquals('id', $arr['PRIMARY']['columns'][0]);
		$this->assertEquals('group_id', $arr['uidx_group_id']['columns'][0]);
	}

	public function testGetIndices()
	{
		$arr = $this->_table->getIndices();
		$this->assertCount(3, $arr);
		$this->assertTrue($arr['PRIMARY']['is_primary']);
		$this->assertTrue($arr['uidx_group_id']['is_unique']);
		$this->assertFalse($arr['idx_group_id']['is_unique']);
	}

	public function testGetColumns()
	{
		$this->assertEquals([
			'id'		=> 'int(11)',
			'group_id'	=> 'int(11)',
			'title'		=> 'text',
			'content'	=> 'text',
		], $this->_table->getColumns());
	}

	public function testGetColumnsTwoTimes()
	{
		$this->assertEquals(
			$this->_table->getColumns(),
			$this->_table->getColumns()
		);
	}

	public function testGetRelationId()
	{
		$oid = $this->_table->getRelationId();
		$this->assertNull($oid);
	}

	public function testGetPk()
	{
		$this->assertEquals(
			$this->_table->getPrimaryKey(),
			$this->_table->getPk()
		);
	}

	public function testTruncate()
	{
		$query = 'select * from ' . $this->_table->getFullName();

		$this->_table->truncate();
		$this->assertEmpty($this->_db->fetchAll($query));

		$queryInsert = 'insert into ' . $this->_table->getFullName() .
			" values (null, 101, 'Title #1', 'Content #1')";

		$this->_db->query($queryInsert);

		$this->assertCount(1, $this->_db->fetchAll($query));

		$this->_table->truncate();

		$this->assertEmpty($this->_db->fetchAll($query));
	}
}
