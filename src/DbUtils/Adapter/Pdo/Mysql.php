<?php

namespace DbUtils\Adapter\Pdo;

use DbUtils\Adapter\MysqlAdapterInterface;

class Mysql extends Pdo implements MysqlAdapterInterface {

	const DRIVER_NAME = 'mysql';

	protected function _getDriverOptions(array $opts) {

		$charset = !empty($opt['charset'])
			? $opt['charset'] : 'utf8';

		return [
			self::MYSQL_ATTR_INIT_COMMAND =>
				'SET NAMES ' . $charset
		];
	}

	public function getDriverName() {
		return static::DRIVER_NAME;
	}
}
