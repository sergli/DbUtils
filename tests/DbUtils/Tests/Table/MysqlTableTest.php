<?php

namespace DbUtils\Tests\Table;

class MysqlTableTest extends \PHPUnit_Extensions_Database_TestCase
{
	use TestTableTrait;

	public function setUp()
	{
		parent::setUp();

		$db = new \DbUtils\Adapter\Mysqli\Mysqli(
			(new \DbUtils\DiContainer)['config']['mysql']);

		$this->_table = new \DbUtils\Table\MysqlTable(
			$db, $this->_tableName);
	}

	public function getConnection()
	{
		$config = (new \DbUtils\DiContainer)['config']['mysql'];
		$dsn = 'mysql:dbname=' . $config['dbname'];
		$pdo = new \PDO($dsn,
			$config['user'], $config['password']);

		return $this->createDefaultDbConnection($pdo);
	}

	public function getDataSet()
	{
		return $this->createFlatXMLDataSet(
			__DIR__ . '/../../../_files/documents.xml');
	}

	public function testGetConnection()
	{
		$this->assertInstanceOf(
			'\DbUtils\Adapter\MysqlAdapterInterface',
			$this->_table->getConnection()
		);
	}
}
