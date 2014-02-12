<?php

namespace DbUtils\Adapter\Mysqli;

use DbUtils\Adapter\SelectInterface;

/**
 * Класс, представляющий итерируемый результат Mysql-запроса.
 *
 * Сам по себе mysqli_result уже реализует traversable,
 * этот класс нужен только для соответствия SelectInterface.
 */
class Select implements SelectInterface, \Countable
{
	/**
	 * @var \mysqli_result
	 */
	private $_resource;

	public function __construct(\mysqli_result $resource)
	{
		$this->_resource = $resource;
	}

	public function getIterator()
	{
		return $this->_resource;
	}

	/**
	 * @return \mysqli_result
	 */
	public function getResource()
	{
		return $this->_resource;
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return $this->_resource->num_rows;
	}
}
