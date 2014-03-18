<?php

namespace DbUtils\Saver\Postgres;

use DbUtils\Adapter\Pgsql\Pgsql;
use DbUtils\Table\PostgresTable;
use DbUtils\Saver\Postgres\AbstractPostgresSaver;
use DbUtils\Saver\SaverException;

class PgCopyFromSaver extends AbstractPostgresSaver
{
	protected $_values = [];

	public function genSqlSkel()
	{
	}

	public function __construct(Pgsql $adapter,
		$tableName, array $columns = null)
	{
		parent::__construct($adapter, $tableName, $columns);
	}

	/**
	 * Проверяем и запоминаем колонки.
	 * pg_copy_from() может принимать только полный
	 * набор колонок.
	 *
	 * @param string[] $columns
	 * @throws SaverException
	 */
	protected function _setColumns(array $columns)
	{
		parent::_setColumns($columns);

		$all = $this->_table->getColumns();

		if (count($this->_columns) !== count($all))
		{
			throw new SaverException(
				'You should not specify a partial set of columns for this saver');
		}
	}


	protected function _quote($column, $value)
	{
		if (!isset($value))
		{
			return '\N';
		}

		switch ($this->_columns[$column])
		{
			case 'bytea':
				return '\\\x' . bin2hex($value);

			default:
				return addcslashes($value, "\n\r\t\\");
		}

		return $value;
	}

	protected function _add(array $record)
	{
		$line = implode("\t", $record);
		$this->_values[] = $line . "\n";
	}

	protected function _reset()
	{
		$this->_values = [];
		$this->_count = 0;
	}

	protected function _save()
	{
		$this->_db->callCarefully(function()
		{
			pg_copy_from(
				$this->_db->getResource(),
				$this->_table->getFullName(),
				$this->_values
			);
		});
	}
}
