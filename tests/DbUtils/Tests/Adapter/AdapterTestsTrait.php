<?php

namespace DbUtils\Tests\Adapter;

trait AdapterTestsTrait
{
	private $_tableName;
	private $_db;

	private $_columns;
	private $_fetchSql;

	public function setUp()
	{
		parent::setUp();

		$this->_tableName = $this->getTableName();

		$this->_db = $this->newAdapter();

		$this->_columns =
		[
			'group_id',
			'name',
			'content'
		];
		$this->_fetchSql = 'SELECT ' .
			implode(', ', $this->_columns) .
			' FROM ' . $this->_tableName;
	}

	public function assertPreConditions()
	{
		$this->assertTableRowCount(
			$this->_tableName, 5);
	}

	public function testSelectContainsOnlyArrays()
	{
		$sql = 'SELECT * FROM ' . $this->_tableName;
		$select = $this->_db->query($sql);
		$this->assertInstanceOf(
			'\DbUtils\Adapter\SelectInterface', $select);
		$this->assertContainsOnly('array', $select);
		$this->assertCount(5, $select);
	}

	public function testInsertQuery()
	{
		$row = $this->newProvider()->current();
		$sql = sprintf(
			"INSERT INTO %s (id, group_id, name, content)
			VALUES (%d, %d, '%s', '%s')",
				$this->_tableName,
				$row['id'],
				$row['group_id'],
				$row['name'],
				$row['content']
		);
		$this->_db->query($sql);
		$this->assertTableRowCount($this->_tableName, 6);
	}

	public function testGetExistingTable()
	{
		$table = $this->_db->getTable($this->_tableName);
		$this->assertInstanceOf(
			'\DbUtils\Table\TableInterface', $table);
		$this->assertStringEndsWith($this->_tableName,
			$table->getFullName());
	}

	public function testTableExists()
	{
		$this->assertTrue(
			$this->_db->tableExists($this->_tableName));

		$this->_db->query('DROP TABLE IF EXISTS tmp');
		$this->assertFalse(
			$this->_db->tableExists('tmp'));

		$this->_db->query('CREATE TEMPORARY TABLE tmp (id int)');
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

	public function quotesProvider()
	{
		return [
			[ "провер'ка" ],
			[ "провер''ка" ],
			[ "провер\\ка" ],
			[ "провер\"ка" ],
			[ "провер\nка" ],
			[ "провер`ка" ],
			[ "провер\"\"ка" ],
//			[ "провер\000ка" ],
			[ "провер\rка" ],
		];
	}

	/**
	 * @dataProvider quotesProvider
	 */
	public function testQuote($str)
	{
		$sql = 'SELECT ' . $this->_db->quote($str);

		$this->assertEquals($str,
			$this->_db->fetchOne($sql));
	}

	public function testFetchAll()
	{
		$arr = $this->_db->fetchAll($this->_fetchSql);
		$this->assertEquals([
			[ 'group_id' => 10, 'name' => 'Name #1', 'content' => 'Content #1' ],
			[ 'group_id' => 20, 'name' => 'Name #2', 'content' => 'Content #2' ],
			[ 'group_id' => 30, 'name' => 'Name #3', 'content' => 'Content #3' ],
			[ 'group_id' => 40, 'name' => 'Name #4', 'content' => 'Content #4' ],
			[ 'group_id' => 50, 'name' => 'Name #5', 'content' => 'Content #5' ],
		], $arr);
	}

	public function testFetchPairs()
	{
		$arr = $this->_db->fetchPairs($this->_fetchSql);
		$this->assertEquals([
			10	=> 'Name #1',
			20	=> 'Name #2',
			30	=> 'Name #3',
			40	=> 'Name #4',
			50	=> 'Name #5',
		], $arr);
	}


	public function testFetchRow()
	{
		$arr = $this->_db->fetchRow($this->_fetchSql);
		$this->assertEquals([
			'group_id' => 10,
			'name' => 'Name #1',
			'content' => 'Content #1'
		], $arr);
	}

	public function testFetchOne()
	{
		$val = $this->_db->fetchOne($this->_fetchSql);
		$this->assertEquals('10', $val);
	}

	public function testFetchColumn1()
	{
		$arr = $this->_db->fetchColumn($this->_fetchSql);
		$expected = [ 10, 20, 30, 40, 50 ];
		$this->assertEquals($expected, $arr);
	}

	public function testFetchColumn3()
	{
		$arr = $this->_db->fetchColumn($this->_fetchSql, 3);
		$expected = [
			'Content #1',
			'Content #2',
			'Content #3',
			'Content #4',
			'Content #5'
		];
		$this->assertEquals($expected, $arr);
	}

	public function testFetchCol1()
	{
		$this->testFetchColumn1();
	}

	public function testFetchCol3()
	{
		$this->testFetchColumn3();
	}
}
