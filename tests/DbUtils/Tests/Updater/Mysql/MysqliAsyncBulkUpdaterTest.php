<?php

namespace DbUtils\Tests\Updater\Mysql;

/**
 * @group async
 */
class MysqliAsyncBulkUpdaterTest extends MysqliBulkUpdaterTest
{
	public function newUpdater(array $columns = null)
	{
		$updater = parent::newUpdater($columns);
		$updater->setOptAsync();

		return $updater;
	}
}
