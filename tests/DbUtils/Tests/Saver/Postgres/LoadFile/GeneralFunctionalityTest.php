<?php

namespace DbUtils\Tests\Saver\Postgres\BulkInsert;

class GeneralFunctionalityTest extends \PHPUnit_Framework_TestCase
{
	use \DbUtils\Tests\Saver\GeneralFunctionalityTestsTrait;

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\PostgresAdapterInterface';
	}

	protected function _getSaverClass()
	{
		return '\DbUtils\Saver\Postgres\LoadFileSaver';
	}
}
