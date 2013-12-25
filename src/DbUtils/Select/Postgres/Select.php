<?php

namespace DbUtils\Select\Postgres;

use DbUtils\Select\SelectInterface;

class Select implements SelectInterface {

	/**
	 * @var resource of type pgsql result
	 */
	private $_result;

	/**
	 * Конструктор
	 *
	 * @param resource $result pgsql result
	 * @access public
	 */
	public function __construct($result) {
		if (is_resource($result) &&
			get_resource_type($result) == 'pgsql result') {
			$this->_result = $result;
		}
		else {
			throw new \InvalidArgumentException(
				'Expects $result to be resource of type pgsql result');
		}
	}

	public function count() {
		return pg_num_rows($this->_result);
	}

	public function free() {
		return pg_free_result($this->_result);
	}


	public function getIterator() {
		return new ResultIterator($this);
	}

	/**
	 * Возвращает внутреннее представление: pgsql select
	 *
	 * @return resource
	 */
	public function getResult() {
		return $this->_result;
	}
}
