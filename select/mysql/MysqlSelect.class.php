<?php

namespace db_utils\select\mysql;

use db_utils\select\iSelect;

require_once __DIR__ . '/../iSelect.class.php';

class MysqlSelect extends iSelect {

	private $_result;

	public function __construct(\mysqli_result $result) {
		$this->_result = $result;
	}

	public function getIterator() {
		return $this->_result;
	}

	public function count() {
		return $this->_result->num_rows;
	}

	public function free() {
		return $this->_result->free();
	}
}
