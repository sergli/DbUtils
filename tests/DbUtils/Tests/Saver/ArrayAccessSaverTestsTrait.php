<?php

namespace DbUtils\Tests\Saver;

trait ArrayAccessSaverTestsTrait
{
	private $_exceptionMsg =
		'Ошидалось исключение \OutOfBoundsException';

	private function _getFilledSaver($count = 17)
	{
		$saver = $this->createSaver(null, [ '_add' ]);

		for ($i = 1; $i <= $count; $i++)
		{
			$saver[] = $this->genRecord($i);
		}

		return $saver;
	}

	/**
	 * Проверяем, что $saver[]= вызывает add()
	 */
	public function testPushRow()
	{
		$saver = $this->createSaver(null, [ '_add' ]);

		$rows = [];
		for ($i = 0; $i < 42; $i++)
		{
			$rows[$i] = $this->genRecord($i);
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
		$saver = $this->_getFilledSaver(17);

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
		$saver = $this->_getFilledSaver(17);

		$this->assertCount(7, $saver);

		$saver->expects($this->never())->method('_add');

		for ($i = -1; $i < 7; $i++)
		{
			try
			{
				$saver[$i] = $this->genRecord($i);
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

	public function testAddRowInTheEnd()
	{
		$saver = $this->_getFilledSaver(17);
		$this->assertCount(7, $saver);

		$saver->expects($this->once())
			->method('_add')->with($this->isType('array'));

		$saver[7] = $this->genRecord(18);
		$this->assertCount(8, $saver);
	}

	public function testOffsetExists()
	{
		$saver = $this->_getFilledSaver(17);

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
