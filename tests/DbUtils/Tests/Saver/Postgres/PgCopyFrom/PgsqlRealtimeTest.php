<?php

namespace DbUtils\Tests\Saver\Postgres\PgCopyFrom;

use \DbUtils\Tests\DatabaseTestCaseTrait as DbTrait;
use \DbUtils\Tests\Saver\RealtimeTestsTrait as RtTrait;

class PgsqlRealtimeTest extends
	\PHPUnit_Extensions_Database_TestCase
{
	use DbTrait,
		RtTrait
	{
		RtTrait::_getXmlBaseName insteadof DbTrait;
	}

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Pgsql\Pgsql';
	}

	protected function _getPdoDriverName()
	{
		return 'pgsql';
	}

	protected function _getSaverClass()
	{
		return '\DbUtils\Saver\Postgres\PgCopyFromSaver';
	}

	public function testCols1_3()
	{
		$this->markTestSkipped();
	}

	public function testCols1_2_3()
	{
		$this->markTestSkipped();
	}

	public function testCols1_3_4()
	{
		$this->markTestSkipped();
	}

	/**
	 * @expectedException \DbUtils\Saver\SaverException
	 * @expectedExceptionMessage partial set
	 */
	public function testCols1_3_4_6()
	{
		$this->_testCols(1,3,4,6);
	}

	public function testCols1_3_5_6()
	{
		$this->markTestSkipped();
	}
}
