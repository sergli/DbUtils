<?php

namespace DbUtils\Adapter\Mysqli;

use DbUtils\Adapter\SelectInterface;

class Select implements SelectInterface {

	/**
	 * @var \mysqli_result $resource
	 */
	private $_resource;

	public function __construct(\mysqli_result $resource) {
		$this->_resource = $resource;
	}

	/**
	 * @access public
	 * @return \Traversable
	 */
	public function getIterator() {
		return $this->_resource;
	}

	/**
	 * @access public
	 * @return int
	 */
	public function count() {
		return $this->_resource->num_rows;
	}

	public function free() {
		return $this->_resource->free();
	}

	public function getResource() {
		return $this->_resource;
	}
}
