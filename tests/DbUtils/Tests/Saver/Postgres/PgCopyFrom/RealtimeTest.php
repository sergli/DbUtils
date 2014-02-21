<?php

namespace DbUtils\Tests\Saver\Postgres\PgCopyFrom;

use \DbUtils\Adapter\Pgsql\Pgsql as Adapter;
use \DbUtils\Saver\Postgres\PgCopyFromSaver as Saver;

class RealtimeTest extends
	\PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\Saver\RealtimeTestsTrait;

	protected function _newPdo(array $config)
	{
		$config = $config['postgres'];
		$pdo = new \PDO('pgsql:dbname=' .
			$config['dbname'],
			$config['user'], $config['password']);
		$pdo->query('SET client_encoding TO UTF8');

		return $pdo;
	}

	protected function _newAdapter(array $config)
	{
		return new Adapter($config['postgres']);
	}

	protected function _newSaver($db, $tableName)
	{
		return new Saver($db, $tableName);
	}
}
