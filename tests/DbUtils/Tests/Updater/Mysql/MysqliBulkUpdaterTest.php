<?php

namespace DbUtils\Tests\Updater\Mysql;

class MysqliBulkUpdaterTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\Updater\UpdaterTestsTrait;

	protected function _getPdoDriverName()
	{
		return 'mysql';
	}

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Mysqli\Mysqli';
	}

	protected function _getUpdaterClass()
	{
		return '\DbUtils\Updater\Mysql\BulkUpdater';
	}
}
