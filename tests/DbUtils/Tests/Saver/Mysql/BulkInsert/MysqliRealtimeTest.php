<?php

namespace DbUtils\Tests\Saver\Mysql\BulkInsert;

use \DbUtils\Tests\DatabaseTestCaseTrait as DbTrait;
use \DbUtils\Tests\Saver\RealtimeTestsTrait as RtTrait;

class MysqliRealtimeTest extends
	\PHPUnit_Extensions_Database_TestCase
{
	use DbTrait,
		RtTrait
	{
		RtTrait::_getXmlBaseName insteadof DbTrait;
	}

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Mysqli\Mysqli';
	}

	protected function _getPdoDriverName()
	{
		return 'mysql';
	}

	protected function _getSaverClass()
	{
		return '\DbUtils\Saver\Mysql\BulkInsertSaver';
	}
}
