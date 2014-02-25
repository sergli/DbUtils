<?php

namespace DbUtils\Tests\Adapter\Pdo\Pgsql;

class SelectTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\DatabaseTestCaseTrait;

	use \DbUtils\Tests\Adapter\SelectTestsTrait;

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Pdo\Pgsql';
	}

	protected function _getPdoDriverName()
	{
		return 'pgsql';
	}

	public function testGetResource()
	{
		$this->assertInstanceOf('\PDOStatement',
			$this->_select->getResource());
	}
}
