<?php

namespace DbUtils\Updater\Mysql;

use DbUtils\Updater\UpdaterInterface;
use DbUtils\Updater\UpdaterTrait;
use DbUtils\Saver\Mysql\BulkInsertSaver as MysqlBulkInsertSaver;

require_once __DIR__ . '/../UpdaterInterface.php';
require_once __DIR__ . '/../UpdaterTrait.php';
require_once __DIR__ .  '/../../Saver/Mysql/BulkInsertSaver.php';


class BulkUpdater extends MysqlBulkInsertSaver
		implements UpdaterInterface {

	use UpdaterTrait;

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
