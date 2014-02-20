<?php

namespace DbUtils\Tests\Saver\Mysql\LoadFile;

class VerifyOutcomeTest extends \PHPUnit_Extensions_Database_TestCase
{
	private $_db;
	private $_tableName;
	private $_saver;
	private $_limit;


	public function getConnection()
	{
		$config = (new \DbUtils\DiContainer)['config']['mysql'];
		$pdo = new \PDO("mysql:dbname={$config['dbname']}",
			$config['user'], $config['password']);

		return $this->createDefaultDbConnection($pdo);
	}

	public function getDataSet()
	{
		$xml = __DIR__ .
			'/../../../../../_files/documents-empty.xml';
		return $this->createFlatXmlDataSet($xml);
	}


	private function _fetchAll(array $columns)
	{
		$sql = 'select ' . implode(',', $columns) .
			' from ' . $this->_tableName;

		return $this->_db->fetchAll($sql);
	}

	private function _getProvider(array $columns, $limit = 20)
	{
		return new \LimitIterator(
			new \DbUtils\Tests\DataProvider($columns),
			0,
			$limit
		);
	}


	public function setUp()
	{
		parent::setUp();


		$this->_tableName = 'test.documents';
		$this->_limit = 20;

		$this->_db = new \DbUtils\Adapter\Mysqli\Mysqli(
			(new \DbUtils\DiContainer)['config']['mysql']);

		$this->_saver = new \DbUtils\Saver\Mysql\LoadFileSaver(
			$this->_db, $this->_tableName);

		$this->_saver->setBatchSize(8);

	}

	public function testBinDataWithNullBytes()
	{
		$columns = [ 'id', 'title', 'bindata' ];
		$dataSet = [];
		foreach ($this->_getProvider($columns) as $record)
		{
			$record['bindata'][5] = "\000";
			$dataSet[] = $record;
			$this->_saver[] = $record;
		}

		$this->_saver->save();

		$this->assertEquals($dataSet,
			$this->_fetchAll($columns));
	}

	public function testBinDataWithTabsAndNewLinesAnsSlashes()
	{
		$columns = [ 'id', 'title', 'bindata' ];
		$dataSet = [];
		foreach ($this->_getProvider($columns) as $record)
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


	private function _verifyOutcome(array $columns)
	{
		$this->_saver->reset();
		$dataSet = [];
		foreach ($this->_getProvider($columns) as $record)
		{
			$dataSet[] = $record;
			$this->_saver[] = $record;
		}
		$this->_saver->save();

		$this->assertEquals($dataSet,
			$this->_fetchAll($columns));
	}

	public function testColumnsIdGroupidTitleContent()
	{
		$this->_verifyOutcome([
			'id',
			'group_id',
			'title',
			'content'
		]);
	}

	public function testColumnsIdTitle()
	{
		$this->_verifyOutcome([ 'id', 'title' ]);
	}

	public function testColumnsIdTitleDate()
	{
		$this->_verifyOutcome([ 'id', 'title', 'date' ]);
	}

	public function testColumnsIdTitleContentBindata()
	{
		$this->_verifyOutcome([
			'id', 'title', 'content', 'bindata']);
	}

	public function testAllColumns()
	{
		$this->_verifyOutcome([
			'id', 'title', 'content', 'group_id', 'date', 'bindata']);
	}
}
