<?php

namespace DbUtils\Tests\Saver\Postgres;

class PgCopyFromSaverTest extends \PHPUnit_Framework_TestCase
{
	private $_tableClass = '\DbUtils\Table\PostgresTable';
	private $_adapterClass = '\DbUtils\Adapter\Pgsql\Pgsql';
	private $_saverClass = '\DbUtils\Saver\Postgres\PgCopyFromSaver';

	use \DbUtils\Tests\Saver\BaseSaverTestsTrait;
}