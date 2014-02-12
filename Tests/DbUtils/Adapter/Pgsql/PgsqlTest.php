<?php

namespace DbUtils\Adapter\Pgsql;

class PgsqlTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Adapter\BaseFeaturesTestsTrait;

	use \DbUtils\Adapter\FetchMethodsTestsTrait;

	private $_db;

	private $_tableName = 'test.documents';

	public function setUp()
	{
		parent::setUp();

		$this->_db = new \DbUtils\Adapter\Pgsql\Pgsql(
			(new \DbUtils\DiContainer)['config']['postgres']);
	}

	public function getConnection()
	{
		$config = (new \DbUtils\DiContainer)['config']['postgres'];
		$dsn = 'pgsql:dbname=' . $config['dbname'];
		$pdo = new \PDO($dsn,
			$config['user'], $config['password']);

		return $this->createDefaultDbConnection($pdo);
	}

	public function getDataSet()
	{
		return $this->createFlatXMLDataSet(
			__DIR__ . '/../../../_files/documents.xml');
	}

	public function testQuote()
	{
		$str = "prover'ka";
		$this->assertEquals("E'prover''ka'",
			$this->_db->quote($str));
	}
}
