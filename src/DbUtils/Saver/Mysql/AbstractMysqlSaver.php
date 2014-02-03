<?php

namespace DbUtils\Saver\Mysql;

use DbUtils\Adapter\MysqlAdapterInterface;
use DbUtils\Adater\Mysqli\Mysqli;
use DbUtils\Saver\AbstractSaver;

abstract class AbstractMysqlSaver extends AbstractSaver
{
	const OPT_IGNORE 		= 0b00001;

	const OPT_DELAYED 		= 0b00010;

	const OPT_LOW_PRIORITY	= 0b00100;

	const OPT_CONCURRENT	= 0b01000;

	const OPT_ASYNC			= 0b10000;

	/**
	 * Опции запросов.
	 * По умолчанию ignore, delayed для BulkInsertSaver,
	 * concurrent - для LoadDataSaver
	 *
	 * @var int
	 */
	protected $_options		= 0b01011;

	public function __destruct()
	{
		parent::__destruct();

		//	если есть выполняющийся асинх. запрос,
		//	дождёмся его завершения
		if ($this->_options & static::OPT_ASYNC)
		{
			$this->_db->reap_async_query();
		}
	}

	public function __construct(MysqlAdapterInterface $adapter,
		$tableName, array $columns = null)
	{
		parent::__construct($adapter, $tableName, $columns);

		$this->_init();
	}

	protected function _init()
	{
	}

	public function setOptIgnore($val = true)
	{
		return $this->_setOption(static::OPT_IGNORE, $val);
	}

	public function setOptDelayed($val = true)
	{
		return $this->_setOption(static::OPT_DELAYED, $val);
	}

	public function setOptLowPriority($val = true)
	{
		return $this->_setOption(
			static::OPT_LOW_PRIORITY, $val);
	}

	public function setOptConcurrent($val = true)
	{
		return $this->_setOption(static::OPT_CONCURRENT, $val);
	}

	public function setOptAsync($val = true)
	{
		if ( !$val || (
			$this->_db instanceof \Mysqli
			&& method_exists('mysqli', 'reap_async_query') ))
		{
			return $this->_setOption(static::OPT_ASYNC, $val);
		}

		//	Драйвер не mysqli => поддержки асинх. нет

		$this->_logger->addWarning('Asynchronious queries are only available with mysqlnd');

		return $this;
	}
}
