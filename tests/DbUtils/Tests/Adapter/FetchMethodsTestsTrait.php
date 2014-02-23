<?php

namespace DbUtils\Tests\Adapter;

trait FetchMethodsTestsTrait
{
	private $_fetchSql = '
	select
		id,
		group_id,
		name,
		content
	from
		test.documents';

	public function testFetchAll()
	{
		$arr = $this->_db->fetchAll($this->_fetchSql);
		$this->assertEquals([
			[ 'id' => 1, 'group_id' => 1, 'name' => 'Name #1', 'content' => 'Content #1' ],
			[ 'id' => 2, 'group_id' => 1, 'name' => 'Name #2', 'content' => 'Content #2' ],
			[ 'id' => 3, 'group_id' => 1, 'name' => 'Name #3', 'content' => 'Content #3' ],
			[ 'id' => 4, 'group_id' => 1, 'name' => 'Name #4', 'content' => 'Content #4' ],
			[ 'id' => 5, 'group_id' => 1, 'name' => 'Name #5', 'content' => 'Content #5' ],
		], $arr);
	}

	public function testFetchPairs()
	{
		$arr = $this->_db->fetchPairs($this->_fetchSql);
		$this->assertEquals([
			1 => 1, 2 => 1, 3 => 1, 4 => 1, 5 => 1
		], $arr);
	}


	public function testFetchRow()
	{
		$arr = $this->_db->fetchRow($this->_fetchSql);
		$this->assertEquals(
			[ 'id' => 1, 'group_id' => 1, 'name' => 'Name #1', 'content' => 'Content #1' ],
			$arr
		);
	}

	public function testFetchOne()
	{
		$val = $this->_db->fetchOne($this->_fetchSql);
		$this->assertEquals('1', $val);
	}

	public function testFetchColumn1()
	{
		$arr = $this->_db->fetchColumn($this->_fetchSql);
		$expected = [ 1, 2, 3, 4, 5 ];
		$this->assertEquals($expected, $arr);
	}

	public function testFetchColumn3()
	{
		$arr = $this->_db->fetchColumn($this->_fetchSql, 3);
		$expected = [ 'Name #1', 'Name #2', 'Name #3', 'Name #4', 'Name #5' ];
		$this->assertEquals($expected, $arr);
	}

	public function testFetchCol1()
	{
		$this->testFetchColumn1();
	}

	public function testFetchCol3()
	{
		$this->testFetchColumn3();
	}
}
