<?php

namespace DbUtils\Saver\Mysql;

class BulkInsertSaverTest extends \PHPUnit_Framework_TestCase
{
	private $_tableClass = '\DbUtils\Table\MysqlTable';
	private $_adapterClass = '\DbUtils\Adapter\Mysqli\Mysqli';
	private $_saverClass = '\DbUtils\Saver\Mysql\BulkInsertSaver';

	use \DbUtils\Saver\BaseSaverTestsTrait;
}
