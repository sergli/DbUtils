<?php

namespace DbUtils\Tests\Saver\Mysql;

class LoadFileSaverTest extends \PHPUnit_Framework_TestCase
{
	private $_tableClass = '\DbUtils\Table\MysqlTable';
	private $_adapterClass = '\DbUtils\Adapter\Mysqli\Mysqli';
	private $_saverClass = '\DbUtils\Saver\Mysql\LoadFileSaver';

	use \DbUtils\Tests\Saver\BaseSaverTestsTrait;

	public function testGetFileName()
	{
		$file = $this->createSaver()->getFileName();
		$this->assertFileExists($file);
	}
}