<?php

namespace DbUtils\Tests\Table\Mysql;


class MysqliTableTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\DatabaseTestCaseTrait;

	use \DbUtils\Tests\Table\TableTestsTrait;

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Mysqli\Mysqli';
	}

	protected function _getTableClass()
	{
		return '\DbUtils\Table\MysqlTable';
	}

	protected function _getPdoDriverName()
	{
		return 'mysql';
	}

	public function testGetConnection()
	{
		$this->assertInstanceOf(
			'\DbUtils\Adapter\MysqlAdapterInterface',
			$this->_table->getConnection()
		);
	}
}
