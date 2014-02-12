<?php

namespace DbUtils\Adapter;

use DbUtils\Select\SelectInterface;
use DbUtils\Table\TableInterface;
use DbUtils\Table\AbstractTable;
use DbUtils\Table\TableNotExistsException;

trait AdapterTrait
{
	/**
	 * Возвращает объект таблицы
	 *
	 * @param string $tableName
	 * @access public
	 * @return TableInterface
	 * @throws TableNotExistsException
	 */
	public function getTable($tableName)
	{
		return AbstractTable::factory($this, $tableName);
	}

	/**
	 * Проверяет, существует ли таблица
	 *
	 * @param string $tableName имя таблицы
	 * @return boolean
	 */
	public function tableExists($tableName)
	{
		try
		{
			$this->getTable($tableName);
			return true;
		}
		catch (TableNotExistsException $e)
		{
			return false;
		}
	}

	/**
	 * fetchRow
	 *
	 * @param string $sql
	 * @access public
	 * @return array
	 */
	public function fetchRow($sql)
	{
		$it = $this->query($sql);
		if (!$it)
		{
			return null;
		}
		//	Возвращаем первую же строку
		foreach ($it as $row)
		{
			$it = null;
			return $row;
		}
	}

	/**
	 * fetchOne
	 *
	 * @param string $sql
	 * @access public
	 * @return string|null
	 */
	public function fetchOne($sql)
	{

		$row = $this->fetchRow($sql);
		if (empty($row))
		{
			return null;
		}

		return current($row);
	}

	public function fetchPairs($sql)
	{
		$it = $this->query($sql);
		$pairs = [];
		foreach ($it as $row)
		{
			if (count($row) < 2)
			{
				throw new \Exception('Количество колонок меньше двух');
			}
			$pairs[current($row)] = next($row);
		}
		$it = null;

		return $pairs;
	}


	public function fetchAll($sql)
	{
		$it = $this->query($sql);
		$all = iterator_to_array($it);
		$it = null;
		return $all;
	}

	/**
	 * fetchColumn
	 *
	 * @param string $sql
	 * @param int $colNum
	 * @access public
	 * @return array
	 * @throws \Exception
	 */
	public function fetchColumn($sql, $colNum = 1)
	{
		if ($colNum < 1)
		{
			throw new \Exception('Неверный номер колонки');
		}

		$it = $this->query($sql);
		$ret = [];
		$flag = true;	//	чтоб сто раз не вызывать count
		foreach ($it as $row)
		{
			if ($flag && count($row) < $colNum)
			{
				throw new \Exception('Неверный номер колонки');
			}
			$flag = false;
			$ret[] = $row[array_keys($row)[$colNum - 1]];
		}
		$it = null;
		return $ret;
	}

	public function fetchCol($sql, $column = 1)
	{
		return $this->fetchColumn($sql, $column);
	}
}
