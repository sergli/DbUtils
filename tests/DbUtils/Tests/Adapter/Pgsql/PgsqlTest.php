<?php

namespace DbUtils\Tests\Adapter\Pgsql;

class PgsqlTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\Adapter\BaseFeaturesTestsTrait;

	use \DbUtils\Tests\Adapter\FetchMethodsTestsTrait;

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
		$dsn = sprintf('pgsql:host=%s;dbname=%s',
			$config['host'], $config['dbname']);
		$pdo = new \PDO($dsn,
			$config['user'], $config['password']);

		return $this->createDefaultDbConnection($pdo);
	}

	public function getDataSet()
	{
		return $this->createFlatXMLDataSet(
			__DIR__ . '/../../../../_files/documents.xml');
	}

}
