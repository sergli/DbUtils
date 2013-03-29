<?php

namespace db_utils\updater\mysql;

use db_utils\updater\iUpdater;
use db_utils\updater\Updater;
use db_utils\saver\mysql\MysqlBulkInsertSaver;
use db_utils\table\mysql\MysqlTable;

require_once __DIR__ . '/../iUpdater.class.php';
require_once __DIR__ . '/../Updater.class.php';
require_once __DIR__ . 
	'/../../saver/mysql/MysqlBulkInsertSaver.class.php';
require_once __DIR__ . '/../../table/mysql/MysqlTable.class.php';

class MysqlBulkUpdater extends MysqlBulkInsertSaver 
	implements iUpdater {

	use Updater;

	private $_sqlEnding  = '';

	protected function _generateSql() {
		parent::_generateSql();

		$sql = "ON DUPLICATE KEY UPDATE";

		$br = "\n\t";
		foreach (array_diff(
				array_keys($this->_columns), $this->_key) as $field) {
			$sql .= $br . $field . ' = VALUES(' . $field . ')';
			$br = ",\n\t";
		}

		$this->_sqlEnding = $sql;
	}

	protected function _save() {
		
		$sql = $this->_sql . "\nVALUES\n\t" .
			implode(",\n\t", $this->_values) . "\n" .
			$this->_sqlEnding;

		$this->_db->query($sql);
		unset($sql);

		return $this->_db->info()['Duplicates'];
	}
}
