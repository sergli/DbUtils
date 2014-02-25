<?php

namespace DbUtils\Tests\Saver\Postgres\LoadFile;

use \DbUtils\Tests\DatabaseTestCaseTrait as DbTrait;
use \DbUtils\Tests\Saver\RealtimeTestsTrait as RtTrait;

class PdoRealtimeTest extends
	\PHPUnit_Extensions_Database_TestCase
{
	use DbTrait,
		RtTrait
	{
		RtTrait::_getXmlBaseName insteadof DbTrait;
	}

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Pdo\Pgsql';
	}

	protected function _getPdoDriverName()
	{
		return 'pgsql';
	}

	protected function _getSaverClass()
	{
		return '\DbUtils\Saver\Postgres\LoadFileSaver';
	}
}
