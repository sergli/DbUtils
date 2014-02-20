<?php

namespace DbUtils\Tests\Adapter\Pdo;

class PdoMysqlSelectTest extends \PHPUnit_Framework_TestCase
{
	private $_select;

	public function setUp()
	{
		$db = (new \DbUtils\DiContainer)['db.pdo_mysql'];
		$sql = "select * from information_schema.tables
		where table_schema='information_schema' limit 20";
		$this->_select = $db->query($sql);
	}

	public function testGetResource()
	{
		$this->assertInstanceOf(
			'\DbUtils\Adapter\SelectInterface', $this->_select);
		$this->assertInstanceOf('\PDOStatement',
			$this->_select->getResource());
	}

	public function testTraverse()
	{
		$this->assertContainsOnly('array', $this->_select);
	}

	public function testCount()
	{
		$this->assertCount(20,
			iterator_to_array($this->_select));
		$this->assertCount(20, $this->_select);
	}
}