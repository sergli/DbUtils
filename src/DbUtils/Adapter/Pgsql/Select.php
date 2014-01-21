<?php

namespace DbUtils\Adapter\Pgsql;

use DbUtils\Adapter\SelectInterface;

class Select implements SelectInterface {

	/**
	 * @var resource of type pgsql result
	 */
	private $_resource;

	/**
	 * Конструктор
	 *
	 * @param resource $resource pgsql result
	 * @access public
	 */
	public function __construct($resource) {
		if (is_resource($resource) &&
			get_resource_type($resource) == 'pgsql result') {
			$this->_resource = $resource;
		}
		else {
			throw new \InvalidArgumentException(
				'Expects $resource to be resource of type pgsql result');
		}
	}

	public function count() {
		return pg_num_rows($this->_resource);
	}

	public function free() {
		return pg_free_result($this->_resource);
	}


	public function getIterator() {
		return new SelectIterator($this);
	}

	/**
	 * Возвращает внутреннее представление: pgsql select
	 *
	 * @return resource
	 */
	public function getResource() {
		return $this->_resource;
	}
}
