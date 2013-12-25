<?php

namespace DbUtils\Select\Mysql;

use DbUtils\Select\SelectInterface;

class Select implements SelectInterface {

	/**
	 * @var \Mysqli_Result
	 */
	private $_result;

	public function __construct(\Mysqli_Result $result) {
		$this->_result = $result;
	}

	/**
	 * getIterator
	 *
	 * @access public
	 * @return \Traversable
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
	 * @return \Mysqli_Result
	 */
	public function getResult() {
		return $this->_result;
	}
}
