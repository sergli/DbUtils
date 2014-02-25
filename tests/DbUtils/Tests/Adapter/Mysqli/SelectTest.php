<?php

namespace DbUtils\Tests\Adapter\Mysqli;

class SelectTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\DatabaseTestCaseTrait;

	use \DbUtils\Tests\Adapter\SelectTestsTrait;

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Mysqli\Mysqli';
	}

	protected function _getPdoDriverName()
	{
		return 'mysql';
	}

	public function testGetResource()
	{
		$this->assertInstanceOf('\mysqli_result',
			$this->_select->getResource());
	}
}
