<?php

namespace DbUtils\Tests\Table;

class PostgresTableTest extends \PHPUnit_Extensions_Database_TestCase
{
	use TestTableTrait;

	public function setUp()
	{
		parent::setUp();

		$db = new \DbUtils\Adapter\Pgsql\Pgsql(
			(new \DbUtils\DiContainer)['config']['postgres']);

		$this->_table = new \DbUtils\Table\PostgresTable(
			$db, $this->_tableName);
	}

	public function getConnection()
	{
		$config = (new \DbUtils\DiContainer)['config']['postgres'];
		$dsn = sprintf('pgsql:host=%s;dbname=%s',
			$config['host'], $config['dbname']);
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
			'\DbUtils\Adapter\PostgresAdapterInterface',
			$this->_table->getConnection()
		);
	}
}
