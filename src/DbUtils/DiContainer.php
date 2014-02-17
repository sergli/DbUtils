<?php

namespace DbUtils;

use Pimple;

use DbUtils\Adapter\Mysqli\Mysqli;
use DbUtils\Adapter\Pgsql\Pgsql;
use DbUtils\Adapter\Pdo;
use DbUtils\Table\MysqlTable;
use DbUtils\Table\PostgresTable;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;


class DiContainer extends Pimple
{
	public function __construct()
	{
		parent::__construct();

		//	весь массив конфига
		$this['config'] = function($ci)
		{
			return require __DIR__ . '/config.php';
		};

		//	новое соединение с mysql по mysqli (force-new)
		$this['db.mysqli.force'] = $this->factory(function($ci)
		{
			return new Mysqli($ci['config']['mysql']);
		});

		//	синглтон mysqli
		$this['db.mysqli'] = function($ci)
		{
			return $ci['db.mysqli.force'];
		};

		//	новое соединение с postgres по php_pgsql
		$this['db.pgsql.force']= $this->factory(function($ci)
		{
			return new Pgsql($ci['config']['postgres']);
		});

		//	синглтон php_pgsql
		$this['db.pgsql'] = function($ci)
		{
			return $ci['db.pgsql.force'];
		};

		//	логгер по умолчанию, INFO-only
		$this['monolog'] = function($ci)
		{
			$logger = new Logger('DbUtils');
			$logger->pushHandler(new StreamHandler(
				'php://stderr', Logger::INFO));
			$logger->pushProcessor(
				new MemoryPeakUsageProcessor);

			return $logger;
		};

		//	синглтон pdo_mysql
		$this['db.pdo_mysql'] = function($ci)
		{
			return new Pdo\Mysql($ci['config']['mysql']);
		};

		//	синглтон pdo_pgsql
		$this['db.pdo_pgsql'] = function($ci)
		{
			return new Pdo\Pgsql($ci['config']['postgres']);
		};
	}
}
