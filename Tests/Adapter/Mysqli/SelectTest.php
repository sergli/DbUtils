<?php

class SelectTest extends PHPUnit_Framework_TestCase
{
	private $_select;

	public function setUp()
	{
		$db = (new DbUtils\DiContainer())['mysql'];
		$db->getTable('test.documents')->truncate();
		$db->query('insert into test.documents values ' .
			" (null, 101, 'Title #1', 'Content #1')");

		$this->_select = $db->query('select id, content from test.documents');
	}

	public function testTraversable()
	{
		$this->assertInstanceOf('\Traversable', $this->_select);
	}

	public function testCount()
	{
		$this->assertCount(1, $this->_select);
	}

	public function testGetResource()
	{
		$this->assertInstanceOf('\mysqli_result',
			$this->_select->getResource());
	}

	/**
	 * @expectedException PHPUnit_Framework_Error_Warning
	 * @expectedExceptionMessage Couldn't fetch mysqli_result
	 */
	public function testFree()
	{
		$this->assertTrue($this->_select->free());
		iterator_to_array($this->_select);
	}
}
