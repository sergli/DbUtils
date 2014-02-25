<?php

namespace DbUtils\Tests\Adapter;

trait SelectTestsTrait
{
	private $_select;

	abstract public function testGetResource();

	public function setUp()
	{
		parent::setUp();

		$db = $this->newAdapter();

		$sql = 'SELECT * FROM ' . $this->getTableName();

		$this->_select = $db->query($sql);
	}

	public function assertPreConditions()
	{
		$this->assertInstanceOf(
			'\DbUtils\Adapter\SelectInterface',
			$this->_select
		);
		$this->assertInstanceOf('\Traversable',
			$this->_select);
	}

	public function testTraverse()
	{
		$this->assertContainsOnly('array', $this->_select);
	}

	public function testCount()
	{
		$this->assertCount(5, $this->_select);
	}
}
