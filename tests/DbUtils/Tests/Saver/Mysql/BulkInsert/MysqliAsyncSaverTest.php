<?php

namespace DbUtils\Tests\Saver\Mysql\BulkInsert;

/**
 * @group async
 */
class MysqliAsyncSaverTest extends
	MysqliRealtimeTest
{
	protected $_limit = 200;

	public function setUp()
	{
		parent::setUp();

		$this->_saver->setOptAsync();
	}

	public function assertPreConditions()
	{
		parent::assertPreConditions();

		$s = $this->_saver;

		$this->assertTrue( boolval(
			$s->getOptions() & $s::OPT_ASYNC));
	}

	public function testSetOptAsync()
	{
		$this->markTestSkipped();
	}
}
