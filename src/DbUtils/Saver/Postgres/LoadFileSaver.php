<?php

namespace DbUtils\Saver\Postgres;

use DbUtils\Table\PostgresTable;
use DbUtils\Saver\LoadFileTrait;
use DbUtils\Saver\Postgres\AbstractPostgresSaver;

class LoadFileSaver extends AbstractPostgresSaver
{
	use LoadFileTrait;

	public function genSqlSkel()
	{
		$sql = 'COPY ' . $this->_table->getFullName() . ' ';
		if (!is_null($this->_columns))
		{
			$sql .= "\n(\n" .
				implode(",\n\t", array_keys($this->_columns)) .
				"\n)";
		}
		$sql .= "\nFROM '" . $this->getFileName() . "'";
		$sql .= <<<'EOT'
WITH
(
	FORMAT CSV,
	DELIMITER E'\t',
	NULL '',
	QUOTE '"',
	ESCAPE '"'
);
EOT;
		$this->_sql = $sql;
	}

	protected function _quote($column, $value)
	{
		if (!isset($value))
		{
			return '';
		}

		switch ($this->_columns[$column])
		{
			case 'bytea':
				//	"\x4142" - это hex от AB
				//	"x4142" - а так это escape от x4142
				$value = '"\x' . bin2hex($value) . '"';
				break;

			default:
				$value = '"' .
					str_replace('"', '""', $value) . '"';
				break;
		}

		return $value;

	}
}
