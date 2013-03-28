<?php

namespace db_utils\updater;

use db_utils\saver\Saver;
use db_utils\table\Table;

require_once __DIR__ . '/../saver/Saver.class.php';

abstract class Updater extends Saver {

	const E_NONE_KEY = 71;

	protected $_key = [];


	public function update() {
		return $this->save();
	}

	
	/**
	 * Ищем, по какому уник. ключу мы будем обновлять
	 *
	 * Если подходит primary key - то по нему,
	 * иначе - берём первый подходящий unique
	 * 
	 * @access protected
	 * @return string[]|null
	 */
	protected function _findKey() {
		$pk = $this->_table->getPrimaryKey();
		$columns = array_keys($this->_columns);
		if ($pk) {
			$cols = $pk['columns'];
			if (count(array_intersect(
				$cols, $columns)) == count($cols)) {
				return $cols;
			}
		}

		foreach ($this->_table->getUniques() as $unique) {
			$cols = $unique['columns'];
			if (count(array_intersect(
				$cols, $columns)) == count($cols)) {
				return $cols;
			}
		}
		return null;
	}


	public function __construct(Table $table, array $columns = null) {
		
		parent::__construct($table, $column);

		$key = $this->_findKey();
		if (!$key) {
			throw new \Exception('Нет подходящего ключа', 
				self::E_NONE_KEY);
		}
		$this->_key = $key;
	}
}
