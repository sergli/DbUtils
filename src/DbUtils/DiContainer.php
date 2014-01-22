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


class DiContainer extends Pimple {

	public function __construct() {

		parent::__construct();

		$this['config'] = function($ci) {
			return require __DIR__ . '/config.php';
		};

		//	новое соединение с mysql по mysqli (force-new)
		$this['mysql-new'] = $this->factory(function($ci) {
			return new Mysqli($ci['config']['mysql']);
		});

		//	синглтон mysqli
		$this['mysql'] = function($ci) {
			return $ci['mysql-new'];
		};

		//	новое соединение с postgres по php_pgsql
		$this['postgres-new']= $this->factory(function($ci) {
			return new Pgsql($ci['config']['postgres']);
		});

		//	синглтон php_pgsql
		$this['postgres'] = function($ci) {
			return $ci['postgres-new'];
		};

		$this['postgres-wiki'] = function($ci) {
			$config = $ci['config']['postgres'];
			$config['dbname'] = 'wiki';
			return new Pgsql($config);
		};

		$this['monolog'] = function($ci) {
			$logger = new Logger('DbUtils');
			$logger->pushHandler(
				new StreamHandler('php://stderr',
				\Monolog\Logger::INFO));
			$logger->pushProcessor(new MemoryPeakUsageProcessor);

			return $logger;
		};

		$this['pdo-mysql'] = function($ci) {
			return new Pdo\Mysql($ci['config']['mysql']);
		};

		$this['pdo-postgres'] = function($ci) {
			return new Pdo\Pgsql($ci['config']['postgres']);
		};

		$this['pdo-pgsql'] = $this->raw('pdo-postgres');
	}
}
