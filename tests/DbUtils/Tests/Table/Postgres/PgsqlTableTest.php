<?php

namespace DbUtils\Tests\Table\Postgres;


class PgsqlTableTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\DatabaseTestCaseTrait;

	use \DbUtils\Tests\Table\TableTestsTrait;

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Pgsql\Pgsql';
	}

	protected function _getTableClass()
	{
		return '\DbUtils\Table\PostgresTable';
	}

	protected function _getPdoDriverName()
	{
		return 'pgsql';
	}

	public function testGetConnection()
	{
		$this->assertInstanceOf(
			'\DbUtils\Adapter\PostgresAdapterInterface',
			$this->_table->getConnection()
		);
	}
}
