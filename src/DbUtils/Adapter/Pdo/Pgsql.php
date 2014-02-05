<?php

namespace DbUtils\Adapter\Pdo;

use DbUtils\Adapter\PostgresAdapterInterface;

class Pgsql extends Pdo implements PostgresAdapterInterface {

	const DRIVER_NAME = 'pgsql';

	protected function _getDriverOptions(array $opts) {
		return [];
	}

	public function getDriverName() {
		return static::DRIVER_NAME;
	}
}
