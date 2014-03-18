<?php

namespace DbUtils\Updater;

trait UpdaterTrait
{

	/**
	 * Колонки уник. ключа
	 *
	 * @var string[]
	 * @access protected
	 */
	protected $_key = [];

	public function update()
	{
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
	private function _findKey()
	{
		//TODO а not null разве не нужно проверять ?
		$pk = $this->_table->getPrimaryKey();
		$columns = array_keys($this->_columns);
		if ($pk)
		{
			$cols = $pk['columns'];
			if (count(array_intersect(
				$cols, $columns)) == count($cols))
			{
				return $cols;
			}
		}

		foreach ($this->_table->getUniques() as $unique)
		{
			$cols = $unique['columns'];
			if (count(array_intersect(
				$cols, $columns)) == count($cols))
			{
				return $cols;
			}
		}

		return null;
	}


	protected function _setColumns(array $columns)
	{
		parent::_setColumns($columns);

		$key = $this->_findKey();

		if (!$key)
		{
			throw new UpdaterException(sprintf(
				'There is no unique constraint found in this set of columns: %s',
					implode(array_keys($this->_columns))));
		}

		$this->_key = $key;

		$this->genSqlSkel();
	}

	public function getUniqueConstraint()
	{
		return $this->_key ?: null;
	}
}
