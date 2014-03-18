<?php

namespace DbUtils\Tests;

trait DatabaseTestCaseTrait
{
	private $_conn = null;
	private static $_pdo = null;

	abstract protected function _getPdoDriverName();
	abstract protected function _getAdapterClass();

	public function tearDown()
	{
		$this->_db = null;
		$this->_saver = null;
		$this->_table = null;
		$this->_select = null;
	}

	public function getTableName()
	{
		return 'test.documents';
	}

	protected function _getXmlBaseName()
	{
		return 'documents.xml';
	}

	public function newAdapter()
	{
		$class = $this->_getAdapterClass();
		return new $class($this->_getConfig());
	}

	protected function _getConfig()
	{
		$dic = new \DbUtils\DiContainer;
		$driver = $this->_getPdoDriverName();

		return $dic['config'][$driver];
	}

	public function getConnection()
	{
		if ($this->_conn === null)
		{
			if (static::$_pdo === null)
			{
				$driver = $this->_getPdoDriverName();
				$config = $this->_getConfig();

				$dsn = sprintf('%s:host=%s;dbname=%s',
					$driver,
					$config['host'],
					$config['dbname']
				);

				static::$_pdo = new \PDO($dsn,
					$config['user'], $config['password']);
			}

			$this->_conn =
				$this->createDefaultDbConnection(static::$_pdo);
		}

		return $this->_conn;
	}

	public function getDataSet()
	{
		$xmlBaseName = basename($this->_getXmlBaseName());
		$fileName = __DIR__ . '/../../_files/' . $xmlBaseName;
		return $this->createFlatXMLDataSet($fileName);
	}

	public function newProvider(array $cols = null,
		$limit = 200)
	{
		return new \LimitIterator(
			new \DbUtils\Tests\DataProvider($cols),
			0, $limit);
	}

	protected function _fetchAll(array $columns = null)
	{
		$pdo = $this->getDatabaseTester()
			->getConnection()
			->getConnection();

		$columns = $columns
			? implode(', ', $columns)
			: '*';

		$sql = 'SELECT ' . $columns .
			' FROM ' . $this->getTableName() .
			' ORDER BY id ASC';

		$stmt = $pdo->query($sql, \PDO::FETCH_ASSOC);
		$ret = [];
		foreach ($stmt as $row)
		{
			if (isset($row['bindata']))
			{
				$bin = $row['bindata'];
				if (is_resource($bin))
				{
					$row['bindata'] = stream_get_contents($bin);
				}
			}
			$ret[] = $row;
		}

		return $ret;
	}
}
