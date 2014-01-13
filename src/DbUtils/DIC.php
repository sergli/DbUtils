<?php

namespace DbUtils;

use DbUtils\Adapter\Mysql\Adapter as MysqlAdapter;
use DbUtils\Adapter\Postgres\Adapter as PostgresAdapter;
use DbUtils\Table\Mysql\Table as MysqlTable;
use DbUtils\Table\Postgres\Table as PostgresTable;


class DIC extends \Pimple {

	public function __construct() {

		parent::__construct();

		$this['config'] = function($ci) {
			return require __DIR__ . '/config.php';
		};

		//	новое соединение с mysql по mysqli (force-new)
		$this['mysql-new'] = $this->factory(function($ci) {
			return new MysqlAdapter($ci['config']['mysql']);
		});

		//	синглтон mysqli
		$this['mysql'] = function($ci) {
			return $ci['mysql-new'];
		};

		//	новое соединение с postgres по php_pgsql
		$this['postgres-new']= $this->factory(function($ci) {
			return new PostgresAdapter(
				$ci['config']['postgres']
			);
		});

		//	синглтон php_pgsql
		$this['postgres'] = function($ci) {
			return $ci['postgres-new'];
		};

		$this['mysql.table'] = function($ci) {
			return new MysqlTable(
				$ci['mysql-new'], $ci['mysql.table_name']
			);
		};

		$this['postgres.table'] = function($ci) {
			return new PostgresTable(
				$ci['postgres-new'], $ci['postgres.table_name']
			);
		};
	}
}
