<?php

namespace DbUtils\Tests\Adapter\Mysqli;

class MysqliTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\Adapter\BaseFeaturesTestsTrait;

	use \DbUtils\Tests\Adapter\FetchMethodsTestsTrait;

	private $_db;

	private $_tableName = 'documents';

	public function setUp()
	{
		parent::setUp();

		$this->_db = new \DbUtils\Adapter\Mysqli\Mysqli(
			(new \DbUtils\DiContainer)['config']['mysql']);
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
			__DIR__ . '/../../../../_files/documents.xml');
	}
}
