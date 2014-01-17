<?php

namespace DbUtils\Adapter\PDO;



class Mysql extends AbstractPDO {

	protected static $_tableClass =
		'DbUtils\Table\Mysql\Table';

	protected $_driverName = 'mysql';

	public function getTableClass() {
		return static::$_tableClass;
	}

	protected function _getDriverName() {
		return $this->_driverName;
	}

	protected function _getDriverOptions(array $opt) {
		$charset = !empty($opt['charset'])
			? $opt['charset'] : 'utf8';

		return [
			self::MYSQL_ATTR_INIT_COMMAND =>
				"SET NAMES $charset",
			//self::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
		];
	}
}
