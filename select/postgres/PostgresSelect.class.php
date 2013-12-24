<?php

namespace db_utils\select\postgres;

use db_utils\select\iSelect;

require_once __DIR__ . '/../iSelect.class.php';
require_once __DIR__ . '/PostgresResultIterator.class.php';

class PostgresSelect implements iSelect {

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
			throw new InvalidArgumentException(
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
		return new PostgresResultIterator($this);
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
