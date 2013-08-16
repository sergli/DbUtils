<?php

namespace db_utils\adapter\postgres;

use db_utils\adapter\iAdapter;
use db_utils\adapter\Adapter;
use db_utils\DBSingleton;
use db_utils\select\postgres\PostgresSelect;

require_once __DIR__ . '/../iAdapter.class.php';
require_once __DIR__ . '/../Adapter.class.php';
require_once __DIR__ . '/../../DBSingleton.class.php';
require_once __DIR__ . '/../../select/postgres/PostgresSelect.class.php';


final class Postgres implements iAdapter {

	protected static $_tableClass = 'db_utils\table\postgres\PgTable';

	private static $_options = [
		'host'		=>	'127.0.0.1',
		'user'		=>	'sergli',
		'password'	=>	'',
		'dbname'	=>	'sergli',
	];

	use Adapter;
	use DBSingleton;

	private $_db;

	protected function _init() {

		$o = static::$_options;

		$dsn = "host={$o['host']}";
		if (!empty($o['port'])) {
			$dsn .= " port={$o['port']}";
		}
		if (!empty($o['dbname'])) {
			$dsn .= " dbname={$o['dbname']}";
		}
		if (!empty($o['user'])) {
			$dsn .= " user={$o['user']}";
		}
		if (isset($o['password'])) {
			$dsn .= " password={$o['password']}";
		}
	
		//	fixme надо что-то с этим делать. Восстанавливать кто будет ?
		set_error_handler(function($errno, $errstr, $errfile, $errline) {
			throw new \ErrorException($errstr, $errno, 0, 
				$errfile, $errline);
		});
		$this->_db = pg_connect($dsn, PGSQL_CONNECT_FORCE_NEW);

		pg_query($this->_db, 'SET client_encoding TO UTF8');

	}

	public function query($sql) {
		try {
			return new PostgresSelect(pg_query($sql));
		}
		catch (\ErrorException $e) {
			throw $e;
		}
	}

	/**
	 * quote 
	 * 
	 * @param string $text 
	 * @access public
	 * @return string
	 * @fixme pg_escape_literal ? bytea ?
	 */
	public function quote($text) {
		return "'" . pg_escape_string($this->_db, $text) . "'";
	}
}
