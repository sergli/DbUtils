<?php

namespace DbUtils\Adapter\Pdo;

use DbUtils\Adapter\PostgresAdapterInterface;

class Pgsql extends AbstractPdo implements PostgresAdapterInterface
{
	const DRIVER_NAME = 'pgsql';

	public function getDriverName()
	{
		return static::DRIVER_NAME;
	}
}
