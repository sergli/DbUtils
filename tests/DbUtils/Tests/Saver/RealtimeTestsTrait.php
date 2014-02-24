<?php

namespace DbUtils\Tests\Saver;

trait RealtimeTestsTrait
{
	private $_db;
	private $_tableName = 'test.documents';
	private $_saver;
	private $_limit = 200;

	abstract protected function _newPdo(array $config);
	abstract protected function _newAdapter(array $config);
	abstract protected function _newSaver($db, $tableName);

	public function setUp()
	{
		parent::setUp();

		$config = (new \DbUtils\DiContainer)['config'];

		$this->_db = $this->_newAdapter($config);

		$this->_saver = $this->_newSaver(
			$this->_db, $this->_tableName);
		$this->_saver->setBatchSize(8);
	}

	public function tearDown()
	{
		parent::tearDown();
		$this->_db = null;
		$this->_saver = null;
	}

	public function getConnection()
	{
		$config = (new \DbUtils\DiContainer)['config'];

		$pdo = $this->_newPdo($config);

		return $this->createDefaultDbConnection($pdo);
	}

	public function getDataSet()
	{
		$xml = __DIR__ . '/../../../_files/documents-empty.xml';
		return $this->createFlatXmlDataSet($xml);
	}
	protected function _fetchAll(array $columns)
	{
		$pdo = $this->getDatabaseTester()
			->getConnection()
			->getConnection();

		$sql = 'select ' . implode(',', $columns) .
			' from ' . $this->_tableName  .
			' order by id asc';

		$stmt = $pdo->query($sql, \PDO::FETCH_ASSOC);
		$ret = [];
		foreach ($stmt as $row)
		{
			if (isset($row['bindata'])
				&& is_resource($row['bindata']))
			{
				$row['bindata'] = stream_get_contents($row['bindata']);
			}
			$ret[] = $row;
		}

		return $ret;
	}

	protected function _verifyColumns(
		array $columns = null, \Closure $modiFy = null)
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


	public function newProvider(array &$cols = null)
	{
		$cols = $cols ?: [
	//		'id',
			'group_id',
			'name',
			'content',
			'date',
			'bindata'
		];

		return new \LimitIterator(
			new \DbUtils\Tests\DataProvider($cols),
			0, $this->_limit
		);
	}


	/**
	 * @group bindata
	 */
	public function testBinDataWithNullBytes()
	{
		$columns = [ 'name', 'bindata' ];
		$this->_verifyColumns($columns,
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
		$columns = [ 'name', 'bindata' ];
		$this->_verifyColumns($columns,
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
		$columns = [ 'group_id', 'name', 'content' ];
		$this->_verifyColumns($columns,
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
		$columns = [ 'name', 'content' ];
		$this->_verifyColumns($columns,
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
		$columns = [ 'name', 'content', 'bindata' ];
		$this->_verifyColumns($columns,
			function (array &$row)
			{
				$bin = $row['bindata'];
				$bin = substr($bin, 0, 3) . '\N' .
					substr($bin, 3);
				$row['bindata'] = $bin;
			});
	}

	public function testColumnsGroupidNameContent()
	{
		$cols = [ 'group_id', 'name', 'content' ];
		$this->_verifyColumns($cols);
	}

	/**
	 * @group ok
	 */
	public function testColumnsName()
	{
		$cols = [ 'name', ];
		$this->_verifyColumns($cols);
	}

	/**
	 * @group date
	 */
	public function testColumnsNameDate()
	{
		$cols = [ 'name', 'date' ];
		$this->_verifyColumns($cols);
	}

	/**
	 * @group bindata
	 */
	public function testColumnsNameContentBindata()
	{
		$cols = [ 'name', 'content', 'bindata' ];
		$this->_verifyColumns($cols);
	}

	/**
	 * @group allcols
	 */
	public function testAllColumns()
	{
		$cols = [
			'name',
			'content',
			'group_id',
			'date',
			'bindata',
		];
		$this->_verifyColumns($cols);
	}
}
