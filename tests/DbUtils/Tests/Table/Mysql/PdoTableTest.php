<?php

namespace DbUtils\Tests\Table;

use \DbUtils\Adapter\Pdo\Mysql as Adapter;
use \DbUtils\Table\MysqlTable as Table;

class MysqlTableTest extends
	\PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\Table\TableTestsTrait;

	protected function _newPdo(array $config)
	{
		$config = $config['mysql'];
		$dsn = sprintf('mysql:host=%s;dbname=%s',
			$config['host'], $config['dbname']);
		$pdo = new \PDO($dsn,
			$config['user'],
			$config['password']);
		$pdo->query('SET NAMES utf8');

		return $pdo;
	}

	protected function _newAdapter(array $config)
	{
		return new Adapter($config['mysql']);
	}

	protected function _newTable($db, $tableName)
	{
		return new Table($db, $tableName);
	}

	public function testGetConnection()
	{
		$this->assertInstanceOf(
			'\DbUtils\Adapter\MysqlAdapterInterface',
			$this->_table->getConnection()
		);
	}
}
