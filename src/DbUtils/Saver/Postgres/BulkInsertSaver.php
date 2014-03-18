<?php

namespace DbUtils\Saver\Postgres;

use DbUtils\Table\PostgresTable;
use DbUtils\Saver\Postgres\AbstractPostgresSaver;

/**
 * Вставляет данные в таблицу пачками, используя
 * синтаксис insert ... values (...), (...), ...
 *
 * @uses Saver
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
class BulkInsertSaver extends AbstractPostgresSaver
{
	protected $_values = [];

	protected function _quote($column, $value)
	{
		if (!isset($value))
		{
			return 'NULL';
		}

		switch ($this->_columns[$column])
		{
			case 'integer':
				return (int) $value;

			case 'boolean':
				return (int) (boolean) $value;

			case 'bytea':
				$text = bin2hex($value);
				return "decode('$text', 'hex')";

			default:
				break;
		}

		return $this->_db->quote($value);
	}

	protected function _reset()
	{
		$this->_values = [];
		$this->_count = 0;
	}

	public function genSqlSkel()
	{
		$sql = 'INSERT INTO ' . $this->_table->getFullName() . ' ';
		if (!is_null($this->_columns))
		{
			$sql .= "\n(\n\t" . implode(",\n\t",
				array_keys($this->_columns)) . "\n)";
		}

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

		$this->_execSql($sql);
		unset($sql);
	}
}

