<?php

namespace DbUtils\Tests\Saver\Postgres\PgCopyFrom;

class GeneralFunctionalityTest extends \PHPUnit_Framework_TestCase
{
	use \DbUtils\Tests\Saver\GeneralFunctionalityTestsTrait;


	protected function _getAdapterClass()
	{
		//		return '\DbUtils\Adapter\PostgresAdapterInterface';
		return '\DbUtils\Adapter\Pgsql\Pgsql';
	}

	protected function _getSaverClass()
	{
		return '\DbUtils\Saver\Postgres\PgCopyFromSaver';
	}

	/**
	 * @expectedException \DbUtils\Saver\SaverException
	 * @expectedExceptionMessage Необходимо указать полный набор колонок
	 */
	public function testCreateSaverWithNotFullSetOfColumns()
	{
		$columns = [ 'id', 'name' ];
		$saver = $this->newSaver($columns);
	}

	/**
	 * @expectedException \DbUtils\Saver\SaverException
	 * @expectedExceptionMessage Необходимо указать полный набор колонок
	 */
	public function testCreateSaverWithRepeatedColumns()
	{
		$columns = [ 'group_id', 'id', 'id', 'name' ];
		$saver = $this->newSaver($columns);
		$this->assertEquals(
			[ 'group_id', 'id', 'name' ],
			$saver->getColumns()
		);
	}

	public function testCreateSaverWithConcreteColumns()
	{
	}

	public function testCreateSaverWithNoColumns1()
	{
	}
}
