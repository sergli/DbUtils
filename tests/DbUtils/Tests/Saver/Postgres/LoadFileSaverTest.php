<?php

namespace DbUtils\Tests\Saver\Postgres;

class LoadFileSaverTest extends \PHPUnit_Framework_TestCase
{
	private $_tableClass = '\DbUtils\Table\PostgresTable';
	private $_adapterClass = '\DbUtils\Adapter\Pgsql\Pgsql';
	private $_saverClass = '\DbUtils\Saver\Postgres\LoadFileSaver';

	use \DbUtils\Tests\Saver\BaseSaverTestsTrait;

	public function testGetFileName()
	{
		$file = $this->createSaver()->getFileName();
		$this->assertFileExists($file);
	}
}
