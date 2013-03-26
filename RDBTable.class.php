<?php

namespace autocomplete\complete\generate\utils\db;

error_reporting(E_ALL);

require_once __DIR__ . '/iRDBTable.class.php';
require_once __DIR__ . '/RDBTableException.class.php';


/**
 * Класс, описывающий таблицу в реляц. базе данных
 * 
 * @uses iRDBTable
 * @abstract
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
abstract class RDBTable implements iRDBTable {

	const CONTYPE_UNIQUE = 'UNIQUE';
	const CONTYPE_FOREIGN = 'FOREIGN KEY';
	const CONTYPE_PRIMARY = 'PRIMARY KEY';
	const CONTYPE_CHECK = 'CHECK';

	protected $_name;
	protected $_schema;

	protected $_indexes = null;
	protected $_columns = null;
	protected $_constraints = null;

	abstract protected function _getBaseInfo($tableName);
	abstract protected function _getIndices();
	abstract protected function _getColumns();
	abstract protected function _getConstraints();

	/**
	 * Возвращает ограничения, кеширует результат
	 *
	 * @see _getConstraints()
	 * @access public
	 * @return array
	 */
	public function getConstraints() {
		if (is_null($this->_constraints)) {
			$this->_constraints = $this->_getConstraints();
		}
		return $this->_constraints;
	}

	public function getColumns() {
		if (is_null($this->_columns)) {
			$this->_columns = $this->_getColumns();
		}
		return $this->_columns;
	}

	public function getIndices() {
		if (is_null($this->_indexes)) {
			$this->_indexes = $this->_getIndices();
		}
		return $this->_indexes;
	}
	
	public function getName() {
		return $this->_name;
	}

	public function getSchema() {
		return $this->_schema;
	}

	public function getFullName() {
		return $this->_schema . '.' . $this->_name;
	}


	public static function exists($db, $tableName) {
		try {
			new static($db, $tableName);
			return true;
		}
		catch (RDBTableException $e) {
			return false;
		}
	}

	public function recalculate() {
		$this->_indexes = null;
		$this->_columns = null;
		$this->_constraints = null;
	}

	public function getPrimaryKey() {
		foreach ($this->getConstraints() as $con) {
			if (self::CONTYPE_PRIMARY == $con['type']) {
				return $con;
			}
		}
		return null;
	}

	public function getPK() {
		return $this->getPrimaryKey();
	}

	public function getUniques() {
		$fk = self::CONTYPE_UNIQUE;
		return array_filter($this->getConstraints(), 
			function ($val) use ($fk) {
				return $fk == $val['type'];
			}
		);
	}

	public function getConnection() {
		return $this->_db;
	}
}
