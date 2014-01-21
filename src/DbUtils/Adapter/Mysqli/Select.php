<?php

namespace DbUtils\Adapter\Mysqli;

use DbUtils\Adapter\SelectInterface;

class Select implements SelectInterface {

	/**
	 * @var \Mysqli_resource
	 */
	private $_resource;

	public function __construct(\Mysqli_Result $resource) {
		$this->_resource = $resource;
	}

	/**
	 * getIterator
	 *
	 * @access public
	 * @return \Traversable
	 */
	public function getIterator() {
		return $this->_resource;
	}

	/**
	 * count
	 *
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
