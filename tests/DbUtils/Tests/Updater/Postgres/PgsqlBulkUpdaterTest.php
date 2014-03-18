<?php

namespace DbUtils\Tests\Updater\Postgres;

class PgsqlBulkUpdaterTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\Updater\UpdaterTestsTrait;

	protected function _getPdoDriverName()
	{
		return 'pgsql';
	}

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Pgsql\Pgsql';
	}

	protected function _getUpdaterClass()
	{
		return '\DbUtils\Updater\Postgres\BulkUpdater';
	}
}
