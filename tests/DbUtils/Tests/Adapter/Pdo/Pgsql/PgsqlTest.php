<?php

namespace DbUtils\Tests\Adapter\Pdo\Pgsql;

class PgsqlTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\DatabaseTestCaseTrait;

	use \DbUtils\Tests\Adapter\AdapterTestsTrait;

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Pdo\Pgsql';
	}

	protected function _getPdoDriverName()
	{
		return 'pgsql';
	}
}
