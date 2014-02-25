<?php

namespace DbUtils\Tests\Saver\Mysql\LoadFile;

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
		return '\DbUtils\Adapter\Pdo\Mysql';
	}

	protected function _getPdoDriverName()
	{
		return 'mysql';
	}

	protected function _getSaverClass()
	{
		return '\DbUtils\Saver\Mysql\LoadFileSaver';
	}
}
