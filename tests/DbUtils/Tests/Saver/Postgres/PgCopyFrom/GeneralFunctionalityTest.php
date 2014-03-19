<?php

namespace DbUtils\Tests\Saver\Postgres\PgCopyFrom;

class GeneralFunctionalityTest extends \PHPUnit_Framework_TestCase
{
	use \DbUtils\Tests\Saver\GeneralFunctionalityTestsTrait;

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Pgsql\Pgsql';
	}

	protected function _getSaverClass()
	{
		return '\DbUtils\Saver\Postgres\PgCopyFromSaver';
	}

	public function testCreateSaverWithNoColumns1()
	{
		$this->markTestSkipped();
	}

	public function testCreateSaverWithNoColumns2()
	{
		$this->markTestSkipped();
	}

	public function testCreateSaverWithConcreteColumns()
	{
		$this->markTestSkipped();
	}

	/**
	 * @expectedException \DbUtils\Saver\SaverException
	 * @expectedExceptionMessage partial set of columns
	 */
	public function testCreateSaverWithNotFullSetOfColumns()
	{
		$saver = $this->newSaver(['id','name']);
	}

	public function testColumnsReordering()
	{
		$saver = $this->newSaver(['name', 'group_id',
			'id', 'content', 'name', 'bindata', 'date' ]);
		$this->assertEquals(
			[ 'id', 'group_id', 'name', 'content',
				'date', 'bindata' ], $saver->getColumns());
	}
}
