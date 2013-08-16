<?php

namespace db_utils\adapter;
use db_utils\table;

require_once __DIR__ . '/iAdapter.class.php';

trait Adapter {

	/**
	 * query 
	 * 
	 * @param string $sql 
	 * @abstract
	 * @access public
	 * @return db_utils\select\iSelect
	 */
	abstract public function query($sql);

	public static function getTableClass() {
		return static::$_tableClass;
	}


	/**
	 * getTable 
	 * 
	 * @param string $tableName 
	 * @access public
	 * @return db_utils\table\Table
	 * @throws \Exception
	 */
	public function getTable($tableName) {
		$tableClass = static::getTableClass();
		return new $tableClass($this, $tableName);
	}

	public function tableExists($tableName) {
		$tableClass = static::getTableClass();
		return $tableClass::exists($this, $tableName);
	}

	/**
	 * fetchRow 
	 * 
	 * @param string $sql 
	 * @access public
	 * @return array
	 */
	public function fetchRow($sql) {
		$it = $this->query($sql);
		if (!$it) {
			return null;
		}
		//	Возвращаем первую же строку
		foreach ($it as $row) {
			$it->free();
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
	public function fetchOne($sql) {
		
		$row = $this->fetchRow($sql);
		if (empty($row)) {
			return null;
		}
		
		return current($row);
	}

	public function fetchPairs($sql) {
		$it = $this->query($sql);
		$pairs = [];
		foreach ($it as $row) {
			if (count($row) < 2) {
				throw new \Exception("Количество колонок меньше двух");
			}
			$pairs[current($row)] = next($row);
		}
		$it->free();

		return $pairs;
	}


	public function fetchAll($sql) {
		$it = $this->query($sql);
		$all = iterator_to_array($it);
		$it->free();
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
	public function fetchColumn($sql, $colNum = 1) {
		
		if ($colNum < 1) {
			throw new \Exception('Неверный номер колонки');
		}

		$it = $this->query($sql);
		$ret = [];
		$flag = true;	//	чтоб сто раз не вызывать count
		foreach ($it as $row) {
			if ($flag && count($row) < $colNum) {
				throw new \Exception('Неверный номер колонки');
			}
			$flag = false;
			$ret[] = $row[array_keys($row)[$colNum - 1]];
		}
		$it->free();
		return $ret;
	}
}
