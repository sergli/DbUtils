<?php

namespace db_utils\select\postgres;

class PostgresResultIterator implements \Iterator {

	private $_pos = 0;
	private $_current = null;
	private $_result;


	public function __construct($result) {
		$this->_result = $result;
	}

	public function current() {
		return $this->_current;
	}

	public function key() {
		return $this->_pos;
	}


	public function valid() {
		return false !== $this->_current;
	}

	public function rewind() {
		if ($this->_pos !== 0) {
			pg_result_seek($this->_result, 0);
		}
		$this->_current = array();
		$this->next();
		$this->_pos = 0;
	}

	public function next() {
		$this->_pos++;
		$this->_current = pg_fetch_assoc($this->_result);
	}
}