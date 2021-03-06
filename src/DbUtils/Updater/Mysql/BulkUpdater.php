<?php

namespace DbUtils\Updater\Mysql;

use DbUtils\Updater\UpdaterInterface;
use DbUtils\Updater\UpdaterTrait;
use DbUtils\Saver\Mysql\BulkInsertSaver as MysqlBulkInsertSaver;

class BulkUpdater extends MysqlBulkInsertSaver implements
	UpdaterInterface
{
	use UpdaterTrait;

	private $_sqlEnding  = '';

	public function genSqlSkel()
	{
		parent::genSqlSkel();

		$sql = 'ON DUPLICATE KEY UPDATE';

		$br = "\n\t";
		foreach (array_diff(
			array_keys($this->_columns), $this->_key) as $field)
		{
			$sql .= $br . $field . ' = VALUES(' . $field . ')';
			$br = ",\n\t";
		}

		$this->_sqlEnding = $sql;
	}

	protected function _save()
	{
		$sql = $this->_sql . "\nVALUES\n\t" .
			implode(",\n\t", $this->_values) . "\n" .
			$this->_sqlEnding;

		$this->_execSql($sql);

		unset($sql);
	}
}
