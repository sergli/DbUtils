<?php

namespace DbUtils\Saver\Mysql;

use DbUtils\Table\MysqlTable;
use DbUtils\Saver\Mysql\AbstractMysqlSaver;

/**
 * Вставляет данные в таблицу пачками, используя
 * синтаксис insert ... values (...), (...), ...
 *
 * @uses AbstractSaver
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
class BulkInsertSaver extends AbstractMysqlSaver
{
	/**
	 * Временный буфер с данными
	 *
	 * @var array
	 */
	protected $_values = [];

	protected function _quote($column, $value)
	{
		if (null === $value)
		{
			return 'NULL';
		}
		if (true === $value)
		{
			return 1;
		}
		if (false === $value)
		{
			return 0;
		}
		if (is_numeric($value))
		{
			return $value;
		}
		return $this->_db->quote($value);
	}

	protected function _reset()
	{
		$this->_values = array();
		$this->_count = 0;
	}

	protected function _generateSql()
	{
		$sql = 'INSERT';
		if ($this->_options & static::OPT_DELAYED)
		{
			$sql .= ' DELAYED';
		}
		if ($this->_options & static::OPT_IGNORE)
		{
			$sql .= ' IGNORE';
		}
		$sql .= ' INTO ' . $this->_table->getFullName();
		$sql .= "\n(\n\t" . implode(",\n\t",
			array_keys($this->_columns)) .  "\n)";

		$this->_sql = $sql;
		unset($sql);
	}

	protected function _add(array $record)
	{
		$values = '';
		$br = '';
		foreach ($record as $field)
		{
			$values .= $br . $field;
			$br = ', ';
		}
		unset($record);

		$values = "($values)";

		$this->_values[] = $values;
	}

	protected function _save()
	{
		$sql = $this->_sql .
			"\nVALUES \n\t" .
			implode(",\n\t", $this->_values) . ";";

		if ($this->_options & static::OPT_ASYNC)
		{
			$this->_db->wait();
			$this->_db->asyncExec($sql);
		}
		else
		{
			$this->_db->query($sql);
		}
		unset($sql);
	}
}
