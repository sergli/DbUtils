<?php

namespace DbUtils\Saver\Mongo;

use DbUtils\Saver\SaverInterface;


class BatchInsertSaver implements SaverInterface {

	/**
	 * @var \MongoCollection
	 */
	protected $_collection;
	protected $_count = 0;

	public function setOptions($options) {
	}

	public function add(array $row) {
	}

	public function save() {
	}

	public function reset() {
	}

	public function getBatchSize() {
	}

	public function setBatchSize() {
	}

	public function count() {
		return $this->getBatchSize();
	}

	public function __construct(\MongoCollection $collection)
	{
		$this->_collection = $collection;
		$this->_count = 0;
	}
}
