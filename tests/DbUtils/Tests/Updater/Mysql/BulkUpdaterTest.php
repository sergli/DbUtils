<?php

namespace Dbutils\Tests\Updater\Mysql;

class BulkUpdaterTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\DatabaseTestCaseTrait;

	private $_db;
	private $_tableName;

	private $_allColumns = [
		'id',
		'group_id',
		'name',
		'content',
		'date',
		'bindata',
	];

	protected function _getPdoDriverName()
	{
		return 'mysql';
	}

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Mysqli\Mysqli';
	}

	protected function _getUpdaterClass()
	{
		return '\DbUtils\Updater\Mysql\BulkUpdater';
	}

	public function setUp()
	{
		$this->_db = $this->newAdapter();
		$this->_tableName = $this->getTableName();
	}

	public function newUpdater(array $columns = null)
	{
		$class = $this->_getUpdaterClass();
		$updater = new $class($this->_db,
			$this->_tableName, $columns);

		return $updater;
	}

	public function testCreateUpdaterWithNoColumns()
	{
		$updater = $this->newUpdater();
		$this->assertNull($updater->getColumns());
		$this->assertNull($updater->getUniqueConstraint());
	}


	public function columnsWithPrimaryProvider()
	{
		return [
			[ 'id', 'name' ],
			[ 'id', 'group_id' ],
			[ 'id', 'name', 'content' ],
			[ 'bindata', 'date', 'id' ],
		];
	}

	public function columnsWithUniqueProvider()
	{
		return [
			[ 'name' ],
			[ 'name', 'group_id' ],
			[ 'name', 'group_id', 'content' ],
			[ 'bindata', 'date', 'name' ],
		];
	}

	/**
	 * @dataProvider columnsWithPrimaryProvider
	 */
	public function testCreateWithPrimary()
	{
		$updater = $this->newUpdater(func_get_args());
		$this->assertEquals(['id'],
			$updater->getUniqueConstraint());
	}

	/**
	 * @dataProvider columnsWithUniqueProvider
	 */
	public function testCreateWithUniqueConstraint()
	{
		$updater = $this->newUpdater(func_get_args());
		$this->assertEquals(['name'],
			$updater->getUniqueConstraint());
	}

	public function columnsWithoutUniqueProvider()
	{
		return [
			[ 'group_id' ],
			[ 'content', 'date' ],
			[ 'group_id', 'bindata', 'date' ],
			[ 'bindata', 'content' ],
		];
	}

	/**
	 * @dataProvider columnsWithoutUniqueProvider
	 * @expectedException \DbUtils\Updater\UpdaterException
	 * @expectedExceptionMessage no unique constraint found
	 */
	public function testCreateWithNonUniqueConstraint()
	{
		$this->newUpdater(func_get_args());
	}

	protected function _testCols($col /*, ... */)
	{
		$cols = [];
		$all = array_values($this->_allColumns);

		foreach (func_get_args() as $arg)
		{
			$cols[] = $all[$arg - 1];
		}

		$this->_verifyColumns($cols);
	}

	protected function _verifyColumns(
		array $columns = null,
		\Closure $modiFy = null)
	{
		$updater = $this->newUpdater();
		$dataSet = [];
		foreach ($this->newProvider($columns) as $row)
		{
			if (isset($modiFy))
			{
				$modiFy($row);
			}
			$updater[] = $dataSet[] = $row;
		}
		$updater->update();

		$this->assertEquals($dataSet,
			$this->_fetchAll($columns));
	}
}
