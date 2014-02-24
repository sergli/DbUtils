<?php

namespace DbUtils\Tests\Table\Postgres;

use \DbUtils\Adapter\Pgsql\Pgsql as Adapter;
use \DbUtils\Table\PostgresTable as Table;

class PgsqlTableTest extends
	\PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\Table\TableTestsTrait;

	protected function _newPdo(array $config)
	{
		$config = $config['postgres'];
		$dsn = sprintf('pgsql:host=%s;dbname=%s',
			$config['host'], $config['dbname']);
		$pdo = new \PDO($dsn,
			$config['user'],
			$config['password']);
		$pdo->query('SET client_encoding TO utf8');

		return $pdo;
	}

	protected function _newAdapter(array $config)
	{
		return new Adapter($config['postgres']);
	}

	protected function _newTable($db, $tableName)
	{
		return new Table($db, $tableName);
	}

	public function testGetConnection()
	{
		$this->assertInstanceOf(
			'\DbUtils\Adapter\PostgresAdapterInterface',
			$this->_table->getConnection()
		);
	}
}
