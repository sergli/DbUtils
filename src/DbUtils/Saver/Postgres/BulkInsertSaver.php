<?php

namespace DbUtils\Saver\Postgres;

use DbUtils\Saver\AbstractSaver;
use DbUtils\Table\Postgres\Table as PostgresTable;;

/**
 * BulkInsertSaver
 *
 * @uses Saver
 * @author Sergey Lisenkov <sergli@nigma.ru>
 * @todo Занаследовать saver\mysql\BulkInsertSaver ?
 */
class BulkInsertSaver extends AbstractSaver {

	protected $_values = [];

	protected function _quote($column, $value) {
		if (null === $value) {
			return 'NULL';
		}
		if (true === $value) {
			return 'true';
		}
		if (false === $value) {
			return 'false';
		}
		if (is_numeric($value)) {
			return $value;
		}

		switch ($this->_columns[$column]) {
			case 'integer':
				return (int) $value;
			case 'boolean':
				return (boolean) $value;
			case 'bytea':
				$text = bin2hex($text);
				return "decode('$text', 'hex')";
			default:
				break;
		}

		return $this->_db->quote($value);
	}

	public function __construct(PostgresTable $table,
		array $columns = null) {
		parent::__construct($table, $columns);
	}

	protected function _reset() {
		$this->_values = array();
		$this->_count = 0;
	}

	protected function _generateSql() {

		$sql = 'INSERT';
		$sql .= " INTO {$this->_table->getFullName()}";
		$sql .= "\n(\n\t" .
			implode(",\n\t", array_keys($this->_columns)) . "\n)";

		$this->_sql = $sql;
		unset($sql);
	}

	protected function _add(array $record) {

		$values = '';
		$br = '';
		foreach ($record as $field) {
			$values .= $br . $field;
			$br = ', ';
		}
		unset($record);

		$values = "($values)";

		$this->_values[] = $values;
	}

	protected function _save() {

		$sql = $this->_sql .
			"\nVALUES \n\t" . implode(",\n\t", $this->_values) . ";";

		$r = $this->_db->query($sql);

		return pg_affected_rows($r->getResult());
	}
}


