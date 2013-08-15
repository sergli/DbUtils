<?php

namespace db_utils\select\postgres;

use db_utils\select\iSelect;

require_once __DIR__ . '/../iSelect.class.php';
require_once __DIR__ . '/PostgresResultIterator.class.php';

class PostgresSelect extends iSelect {

	private $_result;

	public function __construct($result) {
		$this->_result = $result;
	}

	public function count() {
		return pg_num_rows($this->_result);
	}

	public function free() {
		return pg_free_result($this->_result);
	}

	public function getIterator() {
		return new PostgresResultIterator($this->_result);
	}
}
