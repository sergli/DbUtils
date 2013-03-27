<?php

namespace db_utils\saver\mysql;

use db_utils\saver\DBDataSaver,
	db_utils\table\mysql\MysqlTable;

require_once __DIR__ . '/../DBDataSaver.class.php';
require_once __DIR__ . '/../../table/mysql/MysqlTable.class.php';

class MysqlBulkInsertSaver extends DBDataSaver {

	protected $_values = array();

	protected $_sql = '';

	const OPT_IGNORE = 1;
	const OPT_DELAYED = 2;

	/**
	 * Доп. опции. По умолчанию включены IGNORE и DELAYED
	 * 
	 * @var int
	 */
	protected $_options = 3;

	public function setOptions($options) {
		$options = (int) $options;
		if ($options !== $this->_options) {
			$this->_options = $options;
			$this->_generateSql();
		}
		
	}


	protected function _quote($column, $value) {
		if (null === $value) {
			return 'NULL';
		}
		else if (true === $value) {
			return 1;
		}
		else if (false === $value) {
			return 0;
		}

		return "'{$this->_db->real_escape_string($value)}'";
	}

	/**
	 * Конструктор 
	 * 
	 * @param MysqlTable $table 
	 * @param array $columns 
	 * @access public
	 * @return void
	 */
	public function __construct(MysqlTable $table, array $columns =null) {
		parent::__construct($table, $columns);
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
	}

	protected function _add(array $record) {
	
		$values = '';
		$br = '';
		foreach ($record as $field) {
			$values .= $br . $field;
			$br = ', ';
		}

		$values = "($values)";

		$this->_values[] = $values;
	}

	public function reset() {
		$this->_values = array();
		$this->_count = 0;
	}

	public function save() {

		if (empty($this->_values)) {
			return 0;
		}
		$sql = $this->_sql . 
			"\nVALUES \n\t" . implode(",\n\t", $this->_values) . ";";
	var_dump($sql);
		$this->_values = array();
		$this->_count = 0;

		try {
			$r = $this->_db->query($sql);
			return $this->_db->affected_rows;
		}
		catch (\mysqli_sql_exception $e) {
			throw new \Exception(
				"Ошибка при вставке данных:\n{$e->getMessage()}",
				$e->getCode()
			);
		}
	}
}
