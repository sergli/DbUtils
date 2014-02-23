<?php

namespace DbUtils\Tests\Saver\Mysql\BulkInsert;

use \DbUtils\Adapter\Mysqli\Mysqli as Adapter;
use \DbUtils\Saver\Mysql\BulkInsertSaver as Saver;

class MysqliRealtimeTest extends
	\PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\Saver\RealtimeTestsTrait;

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

	protected function _newSaver($db, $tableName)
	{
		return new Saver($db, $tableName);
	}
}
