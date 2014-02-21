<?php

namespace DbUtils\Tests\Saver;

trait BaseSaverTestsTrait
{
	use ArrayAccessSaverTestsTrait;

	private $_tableName = 'test.documents';
	private $_saver;

	public function genRecord($i, array $columns = null)
	{
		if (!$columns)
		{
			$columns = [ 'id' , 'group_id', 'title', 'content' ];
		}

		$record = [];

		if (in_array('id', $columns))
		{
			$record['id'] = $i;
		}
		if (in_array('group_id', $columns))
		{
			$record['group_id'] = (int) ($i / 100);
		}
		if (in_array('title', $columns))
		{
			$record['title'] = "Title #$i";
		}
		if (in_array('content', $columns))
		{
			$record['content'] = "Content #$i";
		}

		return $record;
	}

	public function setUp()
	{
		//	Мок для таблицы
		$table = $this->getMock(
			$this->_tableClass,
			[ ], [ ], '', false);

		$table->expects($this->any())
			->method('getColumns')
			->will($this->returnValue([
				'id' 		=> 'int(11)',
				'group_id'	=> 'int(11)',
				'title'		=> 'varchar(100)',
				'content'	=> 'text'
			]));
		$table->expects($this->any())
			->method('getFullName')
			->will($this->returnValue($this->_tableName));

		//	Мок для адаптера
		$db = $this->getMock(
			$this->_adapterClass,
			[ ], [ ], '', false);

		$db->expects($this->any())
			->method('getTable')
			->will($this->returnValue($table));

		$db->expects($this->any())
			->method('quote')
			->will($this->returnCallback(
				function ($val) { return "'$val'"; })
			);

		$this->_db = $db;
	}

	public function createSaver(array $columns = null,
		array $methods = [ '_save' ])
	{
		$saver = $this->getMock(
			$this->_saverClass,
			$methods,
			[
				$this->_db,
				$this->_tableName,
				$columns
			]
		);
		$saver->setBatchSize(10);

		return $saver;
	}


	public function testCreateSaverWithNoColumns1()
	{
		$saver = $this->createSaver();
		$this->assertNull($saver->getColumns());

		$row = $this->genRecord(10, [ 'id', 'title' ]);

		$saver->add($row);

		$this->assertEquals(
			[ 'id', 'title' ], $saver->getColumns());
	}

	public function testCreateSaverWithNoColumns2()
	{
		$saver = $this->createSaver();
		$this->assertNull($saver->getColumns());

		$row = $this->genRecord(1);

		$saver->add($row);

		$this->assertEquals([
				'id',
				'group_id',
				'title',
				'content'
			], $saver->getColumns()
		);
	}

	public function testCreateSaverWithConcreteColumns()
	{
		$columns = [
			'group_id',
			'title',
			'content'
		];
		$saver = $this->createSaver($columns);

		$this->assertInstanceOf(
			'\DbUtils\Saver\SaverInterface', $saver);
		$this->assertEquals($columns, $saver->getColumns());

		$row = $this->genRecord(20, $columns);

		$saver->add($row);
		$this->assertEquals(1, $saver->getSize());
	}

	public function testCreateSaverWithRepeatedColumns()
	{
		$columns = [
			'group_id',
			'id',
			'id',
			'title'
		];
		$saver = $this->createSaver($columns);
		$this->assertEquals(
			[ 'group_id', 'id', 'title' ], $saver->getColumns());
	}

	/**
	 * @expectedException \DbUtils\Saver\SaverException
	 */
	public function testCreateSaverWithNonExistingColumns()
	{
		$columns = [
			'id',
			'not_exist',
		];
		$saver = $this->createSaver($columns);
	}



	public function testAdd()
	{
		$saver = $this->createSaver();
		$saver->setBatchSize(5000);
		$row = $this->genRecord(1);
		$saver->add($row);
		$this->assertCount(1, $saver);
		for ($i = 2; $i <= 100; $i++)
		{
			$saver->add($this->genRecord($i));
		}
		$this->assertCount(100, $saver);
	}

	/**
	 * @expectedException \DbUtils\Saver\SaverException
	 */
	public function testAddIncorrectRow1()
	{
		$saver = $this->createSaver();
		$saver->add([ 1, 2 ]);
	}
	/**
	 * @expectedException \DbUtils\Saver\SaverException
	 */
	public function testAddIncorrectRow2()
	{
		$saver = $this->createSaver();
		$saver->add([
			'id'		=> 1,
			'group_id'	=> 1,
			'incorrect_col'	=> 'Content #1',
		]);
	}

	public function testSetLogger()
	{
		$saver = $this->createSaver();
		$logger = $saver->getLogger();
		$this->assertInstanceOf(
			'\Monolog\Logger', $logger);

		unset($logger);

		$logger = new \Monolog\Logger('TestLogger', [
			new \Monolog\Handler\NullHandler ]);


		$saver->setLogger($logger);

		$this->assertSame($logger, $saver->getLogger());
	}

	public function testReset()
	{
		$saver = $this->createSaver();
		$this->assertCount(0, $saver);

		for ($i = 1; $i <= 8; $i++)
		{
			$saver->add($this->genRecord($i));
		}
		$this->assertCount(8, $saver);
		$saver->reset();
		$this->assertCount(0, $saver);
	}

	public function testGetSize()
	{
		$saver = $this->createSaver();
		$this->assertCount(0, $saver);
		$this->assertEquals(0, $this->getSize());

		$saver->setBatchSize(100);
		for ($i = 1; $i <= 90; $i++)
		{
			$saver->add($this->genRecord($i));
		}
		$this->assertCount(90, $saver);
		$this->assertEquals(90, $saver->getSize());

		for ($i = 91; $i <= 110; $i++)
		{
			$saver->add($this->genRecord($i));
		}

		$this->assertCount(10, $saver);
		$this->assertEquals(10, $saver->getSize());
	}

	public function testSetBatchSize()
	{
		$saver = $this->createSaver();
		$saver->setBatchSize(1);
		$this->assertEquals(1, $saver->getBatchSize());
		$saver->setBatchSize(1000);
		$this->assertEquals(1000, $saver->getBatchSize());
		$saver->setBatchSize('lalala');
		$this->assertEquals(0, $saver->getBatchSize());
	}

	/**
	 * @expectedException \OutOfRangeException
	 */
	public function testSetNegativeBatchSize()
	{
		$this->createSaver()->setBatchSize(-100);
	}

	/**
	 * @expectedException \OutOfRangeException
	 */
	public function testSetTooBigBatchSize()
	{
		$this->createSaver()->setBatchSize(10000000);
	}

	public function testSaveImpliesReset()
	{
		$saver = $this->createSaver();

		for ($i = 1; $i <= 8; $i++)
		{
			$saver->add($this->genRecord($i));
		}

		$this->assertCount(8, $saver);

		$saver->save();

		$this->assertCount(0, $saver);
	}

	public function testAddImpliesSave()
	{
		$saver = $this->createSaver();

		$saver->expects($this->exactly(9))->method('_save');

		for ($i = 1; $i <= 87; $i++)
		{
			$saver->add($this->genRecord($i));
		}
		$this->assertCount(7, $saver);

		$saver->save();
	}

	public function testDestructImpliesSave()
	{
		$saver = $this->createSaver();
		$saver->expects($this->once())->method('_save');
		$saver->add($this->genRecord(1));
		$saver->__destruct();
	}
}
