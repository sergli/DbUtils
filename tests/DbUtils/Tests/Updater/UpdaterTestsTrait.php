<?php

namespace DbUtils\Tests\Updater;

trait UpdaterTestsTrait
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

	protected function _getXmlBaseName()
	{
		return 'documents-large.xml';
	}

	public function setUp()
	{
		parent::setUp();

		$this->_db = $this->newAdapter();
		$this->_tableName = $this->getTableName();

		$this->_data = $this->_fetchAll(
			$this->_allColumns);
	}

	public function assertPreConditions()
	{
		$this->assertNotEmpty($this->_data);
	}

	/**
	 * Создаёт экземпляр Updater от указанных колонок
	 *
	 * @param array $columns
	 * @return Updater
	 */
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

	/**
	 * @group Oki2
	 */
	public function testUpdateById()
	{
		$cols = [ 'id', 'content', 'bindata' ];
		//	обновим content в первых 30 записях
		$updater = $this->newUpdater($cols);
		$limit = 30;
		$prov = new \DbUtils\Tests\DataProvider($cols);

		for ($j = 1; $j <= $limit; $j++)
		{
			$row = $prov->getRecord();

			if ($j > 5 && $j <= 10)
			{
				$row['bindata'][2] = "\t";
				$row['bindata'][5] = "\n";
				$row['bindata'][7] = '\\';
				$row['bindata'][11] = "\r";
			}

			$this->_data[$j - 1]['content'] = $row['content'];
			$this->_data[$j - 1]['bindata'] = $row['bindata'];

			$updater->add($row);
		}

		$updater->update();

		$this->assertEquals(
			$this->_data,
			$this->_fetchAll()
		);
	}

	public function testUpdateByName()
	{
		$cols = [ 'name', 'content', 'bindata' ];
		$updater = $this->newUpdater($cols);
		$limit = 30;
		$prov = new \DbUtils\Tests\DataProvider($cols);

		for ($j = 1; $j <= $limit; $j++)
		{
			$name = "Name #$j";

			$row = $prov->getRecord();
			$this->_data[$j - 1]['content'] = $row['content'];
			$this->_data[$j - 1]['bindata'] = $row['bindata'];

			$row['name'] = $name;

			$updater->add($row);
		}
		$updater->update();

		$this->assertEquals(
			$this->_data,
			$this->_fetchAll()
		);
	}
}

