<?php

namespace DbUtils\Tests\Adapter\Pdo;

class PdoMysqlTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\Adapter\BaseFeaturesTestsTrait;

	use \DbUtils\Tests\Adapter\FetchMethodsTestsTrait;

	private $_db;

	private $_tableName = 'test.documents';

	public function setUp()
	{
		parent::setUp();

		$this->_db = new \DbUtils\Adapter\Pdo\Mysql(
			(new \DbUtils\DiContainer)['config']['mysql']);
	}

	public function getConnection()
	{
		$config = (new \DbUtils\DiContainer)['config']['mysql'];
		$dsn = sprintf('mysql:host=%s;dbname=%s',
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
