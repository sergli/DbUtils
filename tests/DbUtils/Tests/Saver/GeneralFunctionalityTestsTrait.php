<?php

namespace DbUtils\Tests\Saver;

trait GeneralFunctionalityTestsTrait
{
	private $_tableName;

	private $_exceptionMsg =
		'Ошидалось исключение \OutOfBoundsException';

	abstract protected function _getAdapterClass();
	abstract protected function _getSaverClass();

	public function setUp()
	{
		$this->_tableName = 'test.documents';

		$table = $this->getMock(
			'\DbUtils\Table\TableInterface');
		$table->expects($this->any())
			->method('getColumns')
			->will($this->returnValue([
					'id' 		=> 'int(11)',
					'group_id'	=> 'int(11)',
					'name'		=> 'varchar(100)',
					'content'	=> 'text',
					'date'		=> 'timestamp'
				]));
		$table->expects($this->any())
			->method('getFullName')
			->will($this->returnValue(
				$this->_tableName));


		$db = $this->getMock($this->_getAdapterClass());

		$db->expects($this->any())
			->method('getTable')
			->will($this->returnValue($table));
		$db->expects($this->any())
			->method('quote')
			->will($this->returnArgument(0));

		$this->_db = $db;
	}

	/**
	 * Возвращает почти настоящий сейвер.
	 * Замокан только метод _save()
	 *
	 * @param array $columns
	 * @param array $methods
	 * @return \DbUtils\Saver\SaverInterface
	 */
	public function newSaver(array $columns = null,
		array $methods = [ '_save' ]
	)
	{

		$saver = $this->getMock(
			$this->_getSaverClass(),
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

	public function newProvider(array $cols = null)
	{
		$cols = $cols ?:
			[ 'id', 'group_id', 'name',
			'content', 'date', ];

		return new \DbUtils\Tests\DataProvider($cols);
	}



	/**
	 * @group grain
	 */
	public function testCreateSaverWithNoColumns1()
	{
		$saver = $this->newSaver();
		$this->assertNull($saver->getColumns());

		$cols = [ 'id', 'name' ];
		$row = $this->newProvider($cols)->getRecord();

		$saver->add($row);

		$this->assertEquals(
			[ 'id', 'name' ],
			$saver->getColumns()
		);

	}

	public function testCreateSaverWithNoColumns2()
	{
		$saver = $this->newSaver();
		$this->assertNull($saver->getColumns());

		$row = $this->newProvider()->current();

		$saver->add($row);

		$this->assertEquals([
			'id',
			'group_id',
			'name',
			'content',
			'date',
		], $saver->getColumns());
	}

	public function testCreateSaverWithConcreteColumns()
	{
		$columns = [
			'group_id',
			'name',
			'content'
		];
		$saver = $this->newSaver($columns);

		$this->assertInstanceOf(
			'\DbUtils\Saver\SaverInterface', $saver);
		$this->assertEquals($columns, $saver->getColumns());

		$row = $this->newProvider($columns)->current();

		$saver->add($row);
		$this->assertEquals(1, $saver->getSize());
	}

	public function testCreateSaverWithRepeatedColumns()
	{
		$columns = [ 'group_id', 'id', 'id', 'name' ];
		$saver = $this->newSaver($columns);
		$this->assertEquals(
			[ 'id', 'group_id', 'name' ],
			$saver->getColumns()
		);
	}

	/**
	 * @expectedException \DbUtils\Saver\SaverException
	 */
	public function testCreateSaverWithNonExistingColumns()
	{
		$columns = [ 'id', 'not_exist', ];
		$saver = $this->newSaver($columns);
	}



	public function testAdd()
	{
		$saver = $this->newSaver();
		$saver->setBatchSize(5000);

		$prov = $this->newProvider();

		$row = $prov->getRecord();
		$saver->add($row);
		$this->assertCount(1, $saver);
		for ($i = 2; $i <= 100; $i++)
		{
			$saver->add($prov->getRecord($i));
		}
		$this->assertCount(100, $saver);
	}

	/**
	 * @expectedException \DbUtils\Saver\SaverException
	 */
	public function testAddIncorrectRow1()
	{
		$saver = $this->newSaver();
		$saver->add([ 1, 2 ]);
	}
	/**
	 * @expectedException \DbUtils\Saver\SaverException
	 */
	public function testAddIncorrectRow2()
	{
		$saver = $this->newSaver();
		$saver->add([
			'id'		=> 1,
			'group_id'	=> 1,
			'incorrect_col'	=> 'Content #1',
		]);
	}

	public function testSetLogger()
	{
		$saver = $this->newSaver();
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
		$saver = $this->newSaver();
		$this->assertCount(0, $saver);

		foreach (new \LimitIterator(
			$this->newProvider(), 0, 8) as $row)
		{
			$saver->add($row);
		}
		$this->assertCount(8, $saver);
		$saver->reset();
		$this->assertCount(0, $saver);
	}

	public function testGetSize()
	{
		$saver = $this->newSaver();
		$saver->setBatchSize(100);

		$prov = $this->newProvider();

		$this->assertCount(0, $saver);
		$this->assertEquals(0, $this->getSize());

		for ($i = 1; $i <= 90; $i++)
		{
			$saver->add($prov->getRecord());
		}
		$this->assertCount(90, $saver);
		$this->assertEquals(90, $saver->getSize());

		for ($i = 91; $i <= 110; $i++)
		{
			$saver->add($prov->getRecord());
		}

		$this->assertCount(10, $saver);
		$this->assertEquals(10, $saver->getSize());
	}

	public function testSetBatchSize()
	{
		$saver = $this->newSaver();
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
		$this->newSaver()->setBatchSize(-100);
	}

	/**
	 * @expectedException \OutOfRangeException
	 */
	public function testSetTooBigBatchSize()
	{
		$this->newSaver()->setBatchSize(10000000);
	}

	public function testCallSaveImpliesReset()
	{
		$saver = $this->newSaver();

		foreach (new \LimitIterator(
			$this->newProvider(), 0, 8) as $row)
		{
			$saver->add($row);
		}

		$this->assertCount(8, $saver);

		$saver->save();

		$this->assertCount(0, $saver);
	}

	public function testCallAddImpliesSave()
	{
		$saver = $this->newSaver();

		$saver->expects($this->exactly(9))
			->method('_save');

		foreach (new \LimitIterator(
			$this->newProvider(), 0, 87) as $row)
		{
			$saver->add($row);
		}
		$this->assertCount(7, $saver);

		$saver->save();
	}

	public function testCallDestructImpliesSave()
	{
		$saver = $this->newSaver();
		$saver->expects($this->once())->method('_save');
		$saver->add($this->newProvider()->current());
		$saver->__destruct();
	}

	/**
	 * Проверяем, что $saver[]= вызывает add()
	 * @group ok
	 */
	public function testPushRow()
	{
		$saver = $this->newSaver(null, [ '_add' ]);
		$prov = $this->newProvider();

		$rows = [];
		for ($i = 0; $i < 42; $i++)
		{
			$rows[$i] = $prov->getRecord();
			$saver->expects($this->at($i))
				->method('_add')
				->with($this->isType('array'));
		}

		foreach ($rows as $i => $row)
		{
			$saver[] = $row;
		}
	}

	public function testOffsetGet()
	{
		$saver = $this->newSaver(null, [ '_add' ]);
		$prov = $this->newProvider();

		for ($i = 0; $i < 17; $i++)
		{
			$saver[] = $prov->getRecord();
		}

		for ($i = 0; $i <= 20; $i++)
		{
			try
			{
				$saver[$i];
			}
			catch (\OutOfBoundsException $e)
			{
				continue;
			}
			catch (\Exception $e)
			{
				$this->fail($this->_exceptionMsg);
			}
			$this->fail($this->_exceptionMsg);
		}
	}

	public function testAddRowInTheMiddle()
	{
		$saver = $this->newSaver(null, [ '_add' ]);
		$prov = $this->newProvider();

		for ($i = 0; $i < 17; $i++)
		{
			$saver[] = $prov->getRecord();
		}

		$this->assertCount(7, $saver);

		$saver->expects($this->never())
			->method('_add');

		for ($i = -1; $i < 7; $i++)
		{
			try
			{
				$saver[$i] = $prov->getRecord();
			}
			catch (\OutOfBoundsException $e)
			{
				continue;
			}
			catch (\Exception $e)
			{
				$this->fail($this->_exceptionMsg);
			}
			$this->fail($this->_exceptionMsg);
		}
	}

	/**
	 * @group ok
	 */
	public function testAddRowInTheEnd()
	{
		$saver = $this->newSaver(null, [ '_add' ]);
		$prov = $this->newProvider();

		for ($i = 0; $i < 17; $i++)
		{
			$saver[] = $prov->getRecord();
		}

		$row = $prov->current();

		$saver->expects($this->once())
			->method('_add')
			->with($this->isType('array'));

		$saver[7] = $row;
		$this->assertCount(8, $saver);
	}

	public function testOffsetExists()
	{
		$saver = $this->newSaver(null, [ '_add' ]);
		$prov = $this->newProvider();

		for ($i = 0; $i < 17; $i++)
		{
			$saver[] = $prov->getRecord();
		}

		for ($i = -2; $i <= 20; $i++)
		{
			try
			{
				isset($saver[$i]);
			}
			catch (\OutOfBoundsException $e)
			{
				continue;
			}
			catch (\Exception $e)
			{
				$this->fail($this->_exceptionMsg);
			}
			$this->fail($this->_exceptionMsg);
		}
	}
}
