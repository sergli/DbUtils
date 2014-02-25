<?php

namespace DbUtils\Saver\Mysql;

use DbUtils\Table\MysqlTable;
use DbUtils\Saver\LoadFileTrait;
use DbUtils\Saver\Mysql\AbstractMysqlSaver;

/**
 * Загружает записи в таблицу, используя
 * временный файл и синтаксис load data infile ...
 *
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
class LoadFileSaver extends AbstractMysqlSaver
{
	use LoadFileTrait;

	protected function _quote($column, $value)
	{
		if (!isset($value))
		{
			return '\N';
		}

		if (is_bool($value))
		{
			return (int) $value;
		}

		if (is_numeric($value))
		{
			return $value;
		}

		return addcslashes($value, "\n\t\\");
    }


	protected function _generateSql()
	{

        $sql = 'LOAD DATA';
		if ($this->_options & static::OPT_LOW_PRIORITY)
		{
            $sql .= ' LOW_PRIORITY';
        }
		else if ($this->_options & static::OPT_CONCURRENT)
		{
            $sql .= ' CONCURRENT';
        }

        $sql .= " INFILE '" . $this->getFileName() . "'";

		if ($this->_options & static::OPT_IGNORE)
		{
			$sql .= ' IGNORE';
		}

		$sql .= ' INTO TABLE ' . $this->_table->getFullName();
		$sql .= ' CHARACTER SET binary';
		$sql .= ' (' . implode(', ', array_keys($this->_columns)) . ')';

		$this->_sql = $sql;
		unset($sql);
	}
}
