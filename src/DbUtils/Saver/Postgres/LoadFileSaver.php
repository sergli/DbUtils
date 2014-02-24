<?php

namespace DbUtils\Saver\Postgres;

use DbUtils\Table\PostgresTable;
use DbUtils\Saver\LoadFileTrait;
use DbUtils\Saver\Postgres\AbstractPostgresSaver;

class LoadFileSaver extends AbstractPostgresSaver
{
	use LoadFileTrait;

	protected function _generateSql()
	{
		$sql = <<<'EOT'
COPY {tableName}
(
	{columns}
)
FROM '{fileName}'
WITH
(
	FORMAT CSV,
	DELIMITER E'\t',
	NULL '',
	QUOTE '"',
	ESCAPE '"'
);
EOT;
		$sql = strtr($sql, [
			'{tableName}'	=> $this->_table->getFullName(),
			'{columns}'		=>
				implode(",\n\t", array_keys($this->_columns)),
			'{fileName}'	=> $this->getFileName()
		]);

		$this->_sql = $sql;
	}

	protected function _quote($column, $value)
	{
		if (null === $value)
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
