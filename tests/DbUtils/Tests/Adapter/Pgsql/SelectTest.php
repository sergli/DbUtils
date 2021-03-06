<?php

namespace DbUtils\Tests\Adapter\Pgsql;

class SelectTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\DatabaseTestCaseTrait;

	use \DbUtils\Tests\Adapter\SelectTestsTrait;

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Pgsql\Pgsql';
	}

	protected function _getPdoDriverName()
	{
		return 'pgsql';
	}

	public function testGetResource()
	{
		$this->assertInternalType('resource',
			$this->_select->getResource());
	}
}
