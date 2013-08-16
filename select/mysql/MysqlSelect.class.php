<?php

namespace db_utils\select\mysql;

use db_utils\select\iSelect;

require_once __DIR__ . '/../iSelect.class.php';

class MysqlSelect extends iSelect {

	private $_result;

	public function __construct(\mysqli_result $result) {
		$this->_result = $result;
	}

	/**
	 * getIterator 
	 * 
	 * @access public
	 * @return Traversable
	 */
	public function getIterator() {
		return $this->_result;
	}

	/**
	 * count 
	 * 
	 * @access public
	 * @return int
	 */
	public function count() {
		return $this->_result->num_rows;
	}

	public function free() {
		return $this->_result->free();
	}

	/**
	 * getResult 
	 * 
	 * @access public
	 * @return mysqli_result
	 */
	public function getResult() {
		return $this->_result;
	}
}
