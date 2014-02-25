<?php

namespace DbUtils\Tests\Saver;

trait RealtimeTestsTrait
{
	private $_tableName = 'test.documents';
	private $_db;
	private $_saver;
	private $_limit = 200;

	private $_allColumns = [
		'id',
		'group_id',
		'name',
		'content',
		'date',
		'bindata',
	];

	abstract protected function _getSaverClass();

	protected function _getXmlBaseName()
	{
		return 'documents-empty.xml';
	}

	public function setUp()
	{
		parent::setUp();

		$this->_tableName = $this->getTableName();
		$this->_db = $this->newAdapter();

		$class = $this->_getSaverClass();
		$this->_saver = new $class(
			$this->_db,
			$this->_tableName);

		$this->_saver->setBatchSize(8);
	}

	public function assertPreConditions()
	{
		$this->assertInstanceOf(
			'\DbUtils\Saver\SaverInterface', $this->_saver);
		$this->assertTableRowCount($this->_tableName, 0);
	}

	/**
	 * Загружаем в таблицу данные и проверяем, что все верно.
	 * Данные генерируются на лету с помощью Faker
	 *
	 * @param string[] $columns какие колонки используем
	 * @param \Closure $modiFy доп-но обрабатываем этой ф-ей
	 */
	protected function _verifyColumns(
		array $columns = null,
		\Closure $modiFy = null)
	{
		$dataSet = [];
		foreach ($this->newProvider($columns) as $row)
		{
			if (isset($modiFy))
			{
				$modiFy($row);
			}
			$this->_saver[] = $dataSet[] = $row;
		}
		$this->_saver->save();

		$this->assertEquals($dataSet,
			$this->_fetchAll($columns));
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

	/**
	 * @group bindata
	 */
	public function testBinDataWithNullBytes()
	{
		$this->_verifyColumns(null,
			function(array &$row)
			{
				$row['bindata'][5] = "\000";
			});
	}

	/**
	 * @group bindata
	 */
	public function testBinDataWithTabsAndNewLinesAndSlashes()
	{
		$this->_verifyColumns(null,
			function (array &$row)
			{
				$row['bindata'][2] = "\t";
				$row['bindata'][5] = "\n";
				$row['bindata'][7] = '\\';
				$row['bindata'][11] = "\r";
			});
	}

	/**
	 * @group nulls1
	 */
	public function testNullValuesInGroupidAndContent()
	{
		$this->_verifyColumns(null,
			function (array &$row)
			{
				$row['group_id'] = $row['content'] = null;
			});
	}

	/**
	 * @group nulls
	 */
	public function testSlashNInContent()
	{
		$this->_verifyColumns(null,
			function (array &$row)
			{
				$row['content'] = '\N';
			});
	}
	/**
	 * @group nulls
	 */
	public function testSlashNInBindata()
	{
		$this->_verifyColumns(null,
			function (array &$row)
			{
				$bin = $row['bindata'];
				$bin = substr($bin, 0, 3) . '\N' .
					substr($bin, 3);
				$row['bindata'] = $bin;
			});
	}

	public function testAllColumns()
	{
		$this->_verifyColumns($this->_allColumns);
	}


	public function testCols1_3()
	{
		$this->_testCols(1,3);
	}

	public function testCols1_2_3()
	{
		$this->_testCols(1,2,3);
	}

	/**
	 * @group oki
	 */
	public function testCols1_3_4()
	{
		$this->_testCols(1,3,4);
	}

	public function testCols1_3_4_6()
	{
		$this->_testCols(1,3,4,6);
	}

	public function testCols1_3_5_6()
	{
		$this->_testCols(1,3,5,6);
	}
}
