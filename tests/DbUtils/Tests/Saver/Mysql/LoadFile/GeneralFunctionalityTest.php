<?php

namespace DbUtils\Tests\Saver\Mysql\LoadFile;

class GeneralFunctionalityTest extends \PHPUnit_Framework_TestCase
{
	use \DbUtils\Tests\Saver\GeneralFunctionalityTestsTrait;

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\MysqlAdapterInterface';
	}

	protected function _getSaverClass()
	{
		return '\DbUtils\Saver\Mysql\LoadFileSaver';
	}
}
