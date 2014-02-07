<?php

namespace DbUtils\Saver\Postgres;

use DbUtils\Adapter\Pgsql\Pgsql;
use DbUtils\Saver\Postgres\AbstractPostgresSaver;
use DbUtils\Table\PostgresTable;

class PgCopyFromSaver extends AbstractPostgresSaver
{
	protected $_values = [];

	protected function _generateSql()
	{
	}

	public function __construct(Pgsql $adapter,
		$tableName, array $columns = null)
	{
		parent::__construct($adapter, $tableName, $columns);
	}

	protected function _quote($column, $value)
	{
		if (null === $value)
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
		pg_copy_from(
			$this->_db->getResource(),
			$this->_table->getFullName(),
			$this->_values
		);
	}
}
