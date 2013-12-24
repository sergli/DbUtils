<?php

namespace db_utils\saver\mysql;

use db_utils\saver\Saver,
	db_utils\table\mysql\MysqlTable;

require_once __DIR__ . '/../Saver.class.php';
require_once __DIR__ . '/../../table/mysql/MysqlTable.class.php';

class MysqlBulkInsertSaver extends Saver {

	/**
	 * Временный буфер с данными
	 * 
	 * @var array
	 */
	protected $_values = [];

	/**
	 * @type int добавляет к запросу слово IGNORE
	 */
	const OPT_IGNORE = 1;
	/**
	* @type int добавляет к запросу слово DELAYED
	*/
	const OPT_DELAYED = 2;

	/**
	 * Доп. опции. По умолчанию включены IGNORE и DELAYED
	 * 
	 * @var int
	 */
	protected $_options = 3;

	protected function _quote($column, $value) {
		if (null === $value) {
			return 'NULL';
		}
		if (true === $value) {
			return 1;
		}
		if (false === $value) {
			return 0;
		}
		if (is_numeric($value)) {
			return $value;
		}
		return $this->_db->quote($value);
	}

	/**
	 * Конструктор 
	 * 
	 * @param MysqlTable $table 
	 * @param array $columns 
	 * @access public
	 * @return void
	 */
	public function __construct(MysqlTable $table, 
		array $columns = null) {
		parent::__construct($table, $columns);
	}

	public function reset() {
		$this->_values = array();
		$this->_count = 0;
	}

	protected function _generateSql() {

		$sql = 'INSERT';
		if ($this->_options & static::OPT_DELAYED) {
			$sql .= ' DELAYED';
		}
		if ($this->_options & static::OPT_IGNORE) {
			$sql .= ' IGNORE';
		}
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

		return $this->_db->affected_rows;
	}
}
