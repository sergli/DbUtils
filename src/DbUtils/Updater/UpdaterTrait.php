<?php

namespace DbUtils\Updater;

trait UpdaterTrait {

	/**
	 * Колонки уник. ключа
	 *
	 * @var string[]
	 * @access protected
	 */
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
	private function _findKey() {
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


	protected function _setColumns(array $columns) {
		parent::_setColumns($columns);
		$key = $this->_findKey();

		if (!$key) {
			throw new \Exception('Нет подходящего ключа',
				self::E_NONE_KEY);
		}
		$this->_key = $key;
	}
}