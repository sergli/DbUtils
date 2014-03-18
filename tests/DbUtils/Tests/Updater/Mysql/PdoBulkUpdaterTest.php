<?php

namespace Dbutils\Tests\Updater\Mysql;

class PdoBulkUpdaterTest extends \PHPUnit_Extensions_Database_TestCase
{
	use \DbUtils\Tests\Updater\UpdaterTestsTrait;

	protected function _getPdoDriverName()
	{
		return 'mysql';
	}

	protected function _getAdapterClass()
	{
		return '\DbUtils\Adapter\Pdo\Mysql';
	}

	protected function _getUpdaterClass()
	{
		return '\DbUtils\Updater\Mysql\BulkUpdater';
	}
}
