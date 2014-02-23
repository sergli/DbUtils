<?php

namespace DbUtils\Tests\Saver\Mysql\BulkInsert;

use \DbUtils\Adapter\Pdo\Mysql as Adapter;
use \DbUtils\Saver\Mysql\BulkInsertSaver as Saver;

class PdoRealtimeTest extends
	\PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\Saver\RealtimeTestsTrait;

	protected function _newPdo(array $config)
	{
		$config = $config['mysql'];
		$pdo = new \PDO('mysql:dbname=' .
			$config['dbname'],
			$config['user'], $config['password']);
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