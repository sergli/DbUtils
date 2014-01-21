<?php

namespace DbUtils\Adapter\Pdo;

use DbUtils\Adapter\MysqlAdapterInterface;

class Pgsql extends Pdo implements PostgresAdapterInterface {

	const DRIVER_NAME = 'pgsql';

	protected function _getDriverOptions(array $opts) {
		return [];
	}

	public function getDriverName() {
		return static::DRIVER_NAME;
	}

	public function getPlatformName() {
		return self::PLATFORM_POSTGRES;
	}
}
