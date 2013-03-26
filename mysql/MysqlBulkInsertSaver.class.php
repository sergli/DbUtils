<?php

namespace autocomplete\complete\generate\utils\db\mysql;
use autocomplete\complete\generate\utils\db;

require_once __DIR__ . '/../DBDataSaver.class.php';
require_once __DIR__ . '/MysqlTable.class.php';

class MysqlBulkInsertSaver extends db\DBDataSaver {

	protected $_values = array();

	protected $_delayed = true;
	protected $_ignore = true;

	protected $_sql = '';

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

		return $this->_db->real_escape_string($value);
	}

	public function __construct(MysqlTable $table,
		array $columns = null, Mysql $db = null) {
		parent::__construct($table, $db, $columns);
	}
	protected function _generateSql() {

		$sql = 'INSERT';
		if ($this->_delayed) {
			$sql .= ' DELAYED';
		}
		if ($this->_ignore) {
			$sql .= ' IGNORE';
		}
		$sql .= "INTO {$this->_table->getFullName()}";
		$sql .= "\n (" . implode(',', array_keys($this->_columns)) . ")";

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
			"\n VALUES \n" . implode(",\n", $this->_values) . ";";
		
		$this->_values = array();
		$this->_count = 0;

		try {
			$this->_db->query($sql);
			return $this->_db->affected_rows;
		}
		catch (\mysqli_sql_exception $e) {
			throw new db\DBDataSaverException(
				"Ошибка при вставке данных:\n{$e->getMessage()}",
				$e->getCode()
			);
		}
	}
}


//	----------	TEST	----------	//
$saver = new MysqlBulkInsertSaver(new MysqlTable(Mysql::getInstance('clicklog'), 'test'));
var_dump(Mysql::getInstance('clicklog', 'A'), Mysql::getInstance('clicklog', 'B'), Mysql::getInstance('clicklog', 'A'));

