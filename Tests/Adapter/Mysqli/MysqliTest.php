<?php

xdebug_disable();

//	todo testViewExists

class MysqliTest extends PHPUnit_Framework_TestCase
{

	private $_db;
	private $_sql;

	public function setUp()
	{
		$dic = new DbUtils\DiContainer;

		$config = $dic['config']['mysql'];

		$this->_db = new DbUtils\Adapter\Mysqli\Mysqli($config);
		$this->_sql =
		"SELECT table_name, engine, update_time " .
		"FROM information_schema.tables WHERE " .
		"table_schema = 'information_schema' " .
		"order by table_name asc LIMIT 20";

	}

	public function testAdapterImplementsInterfaces()
	{
		$this->assertInstanceOf('DbUtils\Adapter\AdapterInterface', $this->_db);
		$this->assertInstanceOf('DbUtils\Adapter\MysqlAdapterInterface', $this->_db);
		$this->assertInstanceOf('DbUtils\Adapter\AsyncExecInterface', $this->_db);
	}

	public function testAdapterExtendsMysqli()
	{
		$this->assertInstanceOf('\Mysqli', $this->_db);
	}

	public function testPrimitiveQuery()
	{
		$select = $this->_db->query('SELECT 2 * 2 AS four');
		$this->assertInstanceOf('DbUtils\Adapter\SelectInterface', $select);
		$this->assertCount(1, $select);
		foreach ($select as $row)
		{
			$this->assertArrayHasKey('four', $row);
			$this->assertEquals(4, $row['four']);
			break;
		}
	}

	public function testInsertQuery()
	{
		$sql = "insert into test.documents values (null, 1, 'title', 'content')";
		$r = $this->_db->query($sql);
		$this->assertNotInstanceOf('DbUtils\Adapter\SelectInterface', $r);

		$this->assertTrue($r);
		$this->assertGreaterThan(0, $this->_db->affected_rows);
	}

	public function testEmptySelect()
	{
		$sql =  'select * from information_schema.tables where 1 > 2';
		$select = $this->_db->query($sql);
		$this->assertInstanceOf('DbUtils\Adapter\SelectInterface', $select);
		$this->assertCount(0, $select);
	}


	public function testSelect()
	{
		$select = $this->_db->query($this->_sql);

		$this->assertInstanceOf('\mysqli_result', $select->getResource());

		$this->assertCount(20, $select);

		foreach ($select as $row)
		{
			$this->assertInternalType('array', $row);
			$this->assertCount(3, $row);
			$this->assertArrayHasKey('table_name', $row);
			$this->assertArrayHasKey('engine', $row);
			$this->assertArrayHasKey('update_time', $row);
		}
	}

	public function testFetchAll()
	{
		$arr = $this->_db->fetchAll($this->_sql);
		$this->assertCount(20, $arr);
		$this->assertContainsOnly('array', $arr);
	}

	public function testFetchRow()
	{
		$arr = $this->_db->fetchRow($this->_sql);
		$this->assertCount(3, $arr);
	}

	public function testFetchOne()
	{
		$arr = $this->_db->fetchOne($this->_sql);
		$this->assertInternalType('string', $arr);
		$this->assertEquals('CHARACTER_SETS', $arr);
	}

	public function testFetchPairs()
	{
		$arr = $this->_db->fetchPairs($this->_sql);
		$this->assertCount(20, $arr);
		$this->assertArrayHasKey('ENGINES', $arr);
		$this->assertArrayHasKey('COLLATIONS', $arr);
		$this->assertEquals('MEMORY', $arr['COLLATIONS']);
	}

	public function testFetchColumn1()
	{
		$arr = $this->_db->fetchColumn($this->_sql, 1);
		$this->assertCount(20, $arr);
		$this->assertContainsOnly('string', $arr);
		$this->assertContains('ENGINES', $arr);

	}

	public function testFetchColumn2()
	{
		$arr = $this->_db->fetchColumn($this->_sql, 2);
		$this->assertCount(20, $arr);
		$this->assertContains('MEMORY', $arr);
	}

	public function testQuote()
	{
		$str = "prover'ka";
		$this->assertEquals("'prover\\'ka'",
			$this->_db->quote($str));
	}

	public function testTableExists()
	{
		$tableName = 'information_schema.tables';

		$this->assertTrue($this->_db->tableExists($tableName));

		return $this->_db;
	}


	 public function testTableNotExists()
	 {
		$tables = ['information_schema.tabl', 'test.tabl'];

		foreach ( $tables as $tableName )
		{
			$this->assertFalse($this->_db->tableExists($tableName));
		}

		return $this->_db;
	}

	/**
	 * @depends testTableExists
	 */
	 public function testGetExistingTable($db)
	 {
		$tableName = 'information_schema.tables';
		$table = $db->getTable($tableName);
		$this->assertInstanceOf('DbUtils\Table\TableInterface', $table);
		$this->assertInstanceOf('DbUtils\Table\MysqlTable', $table);
	 }

	/**
	 * @depends testTableExists
	 * @expectedException DbUtils\Table\TableNotExistsException
	 */
	public function testGetNonExistingTable1($db)
	{
		$tableName = 'information_schema.tabl';
	 	$db->getTable($tableName);
	}

	/**
	 * @depends testTableExists
	 * @expectedException DbUtils\Table\TableNotExistsException
	 */
	public function testGetNonExistingTable2($db)
	{
		$tableName = 'test.tabl';
	 	$db->getTable($tableName);
	}
}
