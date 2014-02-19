<?php

namespace DbUtils\Tests\Adapter;

trait BaseFeaturesTestsTrait
{
	public function testSelectQuery()
	{
		$sql = 'select * from ' . $this->_tableName;
		$select = $this->_db->query($sql);
		$this->assertInstanceOf(
			'\DbUtils\Adapter\SelectInterface', $select);
		$this->assertContainsOnly('array', $select);
		$this->assertCount(5, $select);
	}

	public function testInsertQuery()
	{
		$this->assertTableRowCount($this->_tableName, 5);
		$sql = 'insert into ' . $this->_tableName .
			' (id, group_id, title, content) ' .
			" values (101, 2, 'Title #101', 'Content #101')";
		$this->_db->query($sql);
		$this->assertTableRowCount($this->_tableName, 6);
	}

	public function testGetExistingTable()
	{
		$table = $this->_db->getTable($this->_tableName);
		$this->assertInstanceOf('\DbUtils\Table\TableInterface', $table);
		$this->assertStringEndsWith($this->_tableName, $table->getFullName());
	}

	public function testTableExists()
	{
		$this->assertTrue(
			$this->_db->tableExists($this->_tableName));

		$this->_db->query('drop table if exists tmp');
		$this->assertFalse(
			$this->_db->tableExists('tmp'));

		$this->_db->query('create temporary table tmp (id int)');
		$this->assertTrue(
			$this->_db->tableExists('tmp'));
	}

	/**
	 * @expectedException \DbUtils\Table\TableNotExistsException
	 */
	public function testGetNonExistingTable()
	{
		$this->_db->getTable('non_existing_table');
	}
}
