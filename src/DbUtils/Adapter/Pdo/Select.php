<?php

namespace DbUtils\Adapter\Pdo;

use DbUtils\Adapter\SelectInterface;

class Select implements SelectInterface, \Countable
{
	/**
	 * @var \PDOStatement
	 */
	private $_stmt;

	public function __construct(\PDOStatement $stmt)
	{
		$this->_stmt = $stmt;
	}

	/**
	 * @return \Traversable
	 */
	public function getIterator()
	{
		return $this->_stmt;
	}

	/**
	 * @return \PDOStatement
	 */
	public function getResource()
	{
		return $this->_stmt;
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return $this->_stmt->rowCount();
	}
}
