<?php

namespace DbUtils\Tests\Saver\Postgres\PgCopyFrom;

class GeneralFunctionalityTest extends \PHPUnit_Framework_TestCase
{
	use \DbUtils\Tests\Saver\GeneralFunctionalityTestsTrait;

	protected function _getAdapterClass()
	{
		//		return '\DbUtils\Adapter\PostgresAdapterInterface';
		return '\DbUtils\Adapter\Pgsql\Pgsql';
	}

	protected function _getSaverClass()
	{
		return '\DbUtils\Saver\Postgres\PgCopyFromSaver';
	}
}
