<?php

namespace DbUtils\Adapter\Postgres;

use DbUtils\Adapter\AdapterInterface;
use DbUtils\Adapter\AdapterTrait;
use DbUtils\Select\Postgres\Select as PostgresSelect;

final class Adapter implements AdapterInterface {

	protected static $_tableClass =
		'DbUtils\Table\Postgres\Table';

	use AdapterTrait;

	private $_db;

	public function getTableClass() {
		return static::$_tableClass;
	}

	public static function errorHandler($errno, $errstr,
			$errfile, $errline) {

		throw new \ErrorException($errstr, $errno,
				0, $errfile, $errline);
	}

	public function __construct(array $opt = []) {
		$o = [];

		$o['host'] = isset($opt['host'])
			? $opt['host'] : 'localhost';
		if (isset($opt['dbname'])) {
			$o['dbname'] = $opt['dbname'];
		}
		if (isset($opt['port'])) {
			$o['port'] = $opt['port'];
		}
		if (isset($opt['user'])) {
			$o['user'] = $opt['user'];
		}
		if (isset($opt['password'])) {
			$o['password'] = $opt['password'];
		}

		$dsn = '';
		foreach ($o as $key => $val) {
			$dsn .= " $key=$val";
		}
		$dsn = ltrim($dsn);

		set_error_handler('static::errorHandler');

		$this->_db = pg_connect($dsn,
			PGSQL_CONNECT_FORCE_NEW);

		$o['charset'] = !empty($opt['charset'])
			? $opt['charset'] : 'UTF8';

		pg_query($this->_db,
			"SET client_encoding TO {$o['charset']}");

		restore_error_handler();

	}

	public function query($sql) {

		set_error_handler('static::errorHandler');

		$r = pg_query($this->_db, $sql);

		restore_error_handler();

		return new PostgresSelect($r, $sql);
	}

	/**
	 * @param string $text
	 * @access public
	 * @return string
	 * @fixme pg_escape_literal ? bytea ?
	 */
	public function quote($text) {
		return "'" . pg_escape_string($this->_db, $text) . "'";
	}
}
