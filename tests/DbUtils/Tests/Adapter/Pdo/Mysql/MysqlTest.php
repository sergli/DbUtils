<?php

namespace DbUtils\Tests\Adapter\Pdo\Mysql;

class MysqlTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\DatabaseTestCaseTrait;

	use \DbUtils\Tests\Adapter\AdapterTestsTrait;

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Pdo\Mysql';
	}

	protected function _getPdoDriverName()
	{
		return 'mysql';
	}
}
