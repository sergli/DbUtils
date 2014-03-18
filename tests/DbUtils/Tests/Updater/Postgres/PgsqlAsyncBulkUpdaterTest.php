<?php

namespace DbUtils\Tests\Updater\Postgres;

/**
 * @group async
 */
class PgsqlAsyncBulkUpdaterTest extends PgsqlBulkUpdaterTest
{
	public function newUpdater(array $columns = null)
	{
		$updater = parent::newUpdater($columns);
		$updater->setOptAsync();

		return $updater;
	}
}
