<?php

namespace DbUtils\Tests\Adapter\Mysqli;

class MysqliTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\DatabaseTestCaseTrait;

	use \DbUtils\Tests\Adapter\AdapterTestsTrait;

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Mysqli\Mysqli';
	}

	protected function _getPdoDriverName()
	{
		return 'mysql';
	}
}
