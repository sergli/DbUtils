<?php

namespace DbUtils\Tests\Saver;

trait RealtimeTestsTrait
{
	private $_db;
	private $_tableName;
	private $_saver;
	private $_limit;

	abstract protected function _newPdo(array $config);
	abstract protected function _newAdapter(array $config);
	abstract protected function _newSaver($db, $tableName);

	public function setUp()
	{
		parent::setUp();

		$this->_tableName = 'test.documents';
		$this->_limit = 200;

		$config = (new \DbUtils\DiContainer)['config'];

		$this->_db = $this->_newAdapter($config);

		$this->_saver = $this->_newSaver(
			$this->_db, $this->_tableName);
		$this->_saver->setBatchSize(8);
	}

	protected function _fetchAll(array $columns)
	{
		$pdo = $this->getDatabaseTester()
			->getConnection()
			->getConnection();

		$sql = 'select ' . implode(',', $columns) .
			' from ' . $this->_tableName;

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

	protected function _verifyColumns(array $columns)
	{
		$dataSet = [];
		foreach ($this->newProvider($columns) as $row)
		{
			$this->_saver[] = $dataSet[] = $row;
		}
		$this->_saver->save();

		$this->assertEquals($dataSet,
			$this->_fetchAll($columns));
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

	public function newProvider(array $cols = null)
	{
		$cols = $cols ?:
			[ 'id', 'group_id', 'title',
			'content', 'date', 'bindata' ];

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
		$columns = [ 'id', 'title', 'bindata' ];
		$dataSet = [];

		foreach ($this->newProvider($columns) as $record)
		{
			$record['bindata'][5] = "\000";
			$dataSet[] = $record;
			$this->_saver[] = $record;
		}

		$this->_saver->save();

		$this->assertEquals($dataSet,
			$this->_fetchAll($columns));
	}

	/**
	 * @group bindata
	 */
	public function testBinDataWithTabsAndNewLinesAnsSlashes()
	{
		$columns = [ 'id', 'title', 'bindata' ];
		$dataSet = [];
		foreach ($this->newProvider($columns) as $record)
		{
			$record['bindata'][2] = "\t";
			$record['bindata'][5] = "\n";
			$record['bindata'][7] = '\\';
			$dataSet[] = $record;
			$this->_saver[] = $record;
		}

		$this->_saver->save();

		$this->assertEquals($dataSet,
			$this->_fetchAll($columns));
	}

	public function testColumnsIdGroupidTitleContent()
	{
		$cols = ['id', 'group_id', 'title', 'content'];
		$this->_verifyColumns($cols);
	}

	public function testColumnsIdTitle()
	{
		$this->_verifyColumns([ 'id', 'title' ]);
	}

	public function testColumnsIdTitleDate()
	{
		$this->_verifyColumns([ 'id', 'title', 'date' ]);
	}

	/**
	 * @group bindata
	 */
	public function testColumnsIdTitleContentBindata()
	{
		$this->_verifyColumns([
			'id', 'title', 'content', 'bindata']);
	}

	public function testAllColumns()
	{
		$this->_verifyColumns([
			'id', 'title', 'content',
			'group_id', 'date', 'bindata']
		);
	}
}
