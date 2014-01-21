<?php

namespace DbUtils\Adapter\Pdo;

use DbUtils\Adapter\SelectInterface;

class Select implements SelectInterface {
	/**
	 * @var \PDOStatement
	 */
	private $_stmt;

	public function __construct(\PDOStatement $stmt) {
		$this->_stmt = $stmt;
	}

	/**
	 * @access public
	 * @return \Traversable
	 */
	public function getIterator() {
		return $this->_stmt;
	}

	/**
	 * @access public
	 * @return int
	 */
	public function count() {
		return $this->_stmt->rowCount();
	}

	public function free() {
		return $this->_stmt->closeCursor();
	}

	/**
	 * @access public
	 * @return \PDOStatement
	 */
	public function getResource() {
		return $this->_stmt;
	}
}
