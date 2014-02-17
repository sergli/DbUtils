<?php

namespace DbUtils\Saver\Postgres;

use DbUtils\Adapter\PostgresAdapterInterface;
use DbUtils\Adapter\Pgsql\Pgsql;
use DbUtils\Saver\AbstractSaver;

abstract class AbstractPostgresSaver extends AbstractSaver
{
	const OPT_ASYNC			= 0b10000;

	/**
	 * Опции запроса.
	 * По умолчанию - никаких.
	 *
	 * @var int
	 */
	protected $_options		= 0b0000;


	public function __construct(
		PostgresAdapterInterface $adapter,
		$tableName, array $columns = null)
	{
		parent::__construct($adapter, $tableName, $columns);
	}
}

