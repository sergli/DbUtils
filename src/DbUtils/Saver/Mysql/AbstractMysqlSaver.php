<?php

namespace DbUtils\Saver\Mysql;

use DbUtils\Adapter\MysqlAdapterInterface;
use DbUtils\Adater\Mysqli\Mysqli;
use DbUtils\Saver\AbstractSaver;

abstract class AbstractMysqlSaver extends AbstractSaver
{
	const OPT_IGNORE		= 0b00001;

	const OPT_DELAYED		= 0b00010;

	const OPT_ASYNC			= 0b00100;

	/**
	 * Опции запросов.
	 *
	 * @var int
	 */
	protected $_options		= 0b00000;

	public function __construct(
		MysqlAdapterInterface $adapter,
		$tableName, array $columns = null)
	{
		parent::__construct($adapter, $tableName, $columns);
	}

	public function setOptIgnore($val = true)
	{
		return $this->_setOption(static::OPT_IGNORE, $val);
	}

	public function setOptDelayed($val = true)
	{
		return $this->_setOption(static::OPT_DELAYED, $val);
	}
}
