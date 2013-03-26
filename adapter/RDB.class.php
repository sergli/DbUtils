<?php

namespace db_utils\adapter;
use db_utils\table;


/**
 * RDB 
 * 
 * @uses iRDB
 * @abstract
 * @package 
 * @version $id$
 * @copyright 
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 * @license 
 */
abstract class RDB implements iRDB {

	protected $_tableClass = 'db_utils\table\RDBTable';

	/**
	 * query 
	 * 
	 * @param string $sql 
	 * @abstract
	 * @access public
	 * @return Traversable
	 */
	abstract public function query($sql);

	/**
	 * fetchRow 
	 * 
	 * @param string $sql 
	 * @access public
	 * @return array
	 */
	public function fetchRow($sql) {
		$it = $this->query($sql);
		$row = current($it);
		if (!$row) {
			return array();
		}
		
		return $row;
	}

	/**
	 * fetchOne 
	 * 
	 * @param mixed $sql 
	 * @access public
	 * @return mixed
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
		$pairs = array();
		foreach ($it as $row) {
			if (count($row) < 2) {
				throw new \Exception("Количество колонок меньше двух");
			}
			$pairs[current($row)] = next($row);
		}

		return $pairs;
	}


	public function tableExists($tableName) {
		$tableClass = $this->_tableClass;
		return $tableClass::exists();
	}

	public function fetchAll($sql) {
		$it = $this->query($sql);
		return iterator_to_array($it);
	}


	public function getTableColumns($tableName) {
		return $this->getTable($tableName)->getColumns();
	}

	public function getTableIndices($tableName) {
		return $this->getTable($tableName)->getIndices();
	}
}
