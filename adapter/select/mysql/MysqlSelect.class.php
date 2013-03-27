<?php

namespace db_utils\adapter\select\mysql;

use db_utils\adapter\select\iSelect;

require_once __DIR__ . '/../iSelect.class.php';

class MysqlSelect extends iSelect {

	private $_result;

	public function __construct(\mysqli_result $result) {
		$this->_result = $result;
	}

	public function getIterator() {
		return $this->_result;
	}

	public function free() {
		$this->_result->free();
	}
}
