<?php

namespace DbUtils\Adapter\PDO;

class Postgres extends AbstractPDO {

	protected static $_tableClass =
		'DbUtils\Table\Postgres\Table';

	protected $_driverName = 'pgsql';

	public function getTableClass() {
		return static::$_tableClass;
	}
	protected function _getDriverName() {
		return $this->_driverName;
	}

	protected function _getDriverOptions(array $opt) {

		return [];
	}
}
