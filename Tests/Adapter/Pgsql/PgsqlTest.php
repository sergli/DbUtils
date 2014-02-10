<?php

xdebug_disable();

//todo testViewExists
//test exceptions while query

class PgsqlTest extends PHPUnit_Framework_TestCase
{

	private $_db;
	private $_sql;

	public function setUp()
	{
		$dic = new DbUtils\DiContainer;

		$config = $dic['config']['postgres'];

		$this->_db = new DbUtils\Adapter\Pgsql\Pgsql($config);
		$this->_sql =
		"SELECT table_name, table_schema, table_type " .
		"FROM information_schema.tables WHERE " .
		"table_schema = 'information_schema' " .
		"order by table_name asc LIMIT 20";
	}

	public function testAdapterImplementsInterfaces()
	{
		$this->assertInstanceOf('DbUtils\Adapter\AdapterInterface', $this->_db);
		$this->assertInstanceOf('DbUtils\Adapter\PostgresAdapterInterface', $this->_db);
		$this->assertInstanceOf('DbUtils\Adapter\AsyncExecInterface', $this->_db);
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
		$this->_db->getTable('documents')->truncate();
		$sql = "insert into documents values (default, 1, 'title', 'content')";
		$r = $this->_db->query($sql);
		$this->assertGreaterThan(0, pg_affected_rows($r->getResource()));
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

		$this->assertInternalType('resource', $select->getResource());

		$this->assertCount(20, $select);

		foreach ($select as $row)
		{
			$this->assertInternalType('array', $row);
			$this->assertCount(3, $row);
			$this->assertArrayHasKey('table_name', $row);
			$this->assertArrayHasKey('table_schema', $row);
			$this->assertArrayHasKey('table_type', $row);
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
		$this->assertEquals('administrable_role_authorizations', $arr);
	}

	public function testFetchPairs()
	{
		$arr = $this->_db->fetchPairs($this->_sql);
		$this->assertCount(20, $arr);
		$this->assertArrayHasKey('applicable_roles', $arr);
		$this->assertArrayHasKey('attributes', $arr);
		$this->assertEquals('information_schema', $arr['collations']);
	}

	public function testFetchColumn1()
	{
		$arr = $this->_db->fetchColumn($this->_sql, 1);
		$this->assertCount(20, $arr);
		$this->assertContainsOnly('string', $arr);
		$this->assertContains('applicable_roles', $arr);

	}

	public function testFetchColumn3()
	{
		$arr = $this->_db->fetchColumn($this->_sql, 3);
		$this->assertCount(20, $arr);
		$this->assertContains('VIEW', $arr);
	}

	public function testQuote()
	{
		$str = "prover'ka";
		$this->assertEquals("E'prover''ka'",
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
		$this->assertInstanceOf('DbUtils\Table\PostgresTable', $table);
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
