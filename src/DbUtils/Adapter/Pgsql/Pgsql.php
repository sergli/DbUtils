<?php

namespace DbUtils\Adapter\Pgsql;

use DbUtils\Adapter\PostgresAdapterInterface;
use DbUtils\Adapter\AdapterTrait;

final class Pgsql implements PostgresAdapterInterface {

	use AdapterTrait;

	private $_db;

	public function getPlatformName() {
		return self::PLATFORM_POSTGRES;
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

		$dsn = http_build_query($o, null, ' ');

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

		return new Select($r, $sql);
	}

	/**
	 * @param string $text
	 * @access public
	 * @return string
	 * @todo pg_escape_bytea()
	 */
	public function quote($text) {
		return pg_escape_literal($this->_db, $text);
	}
}
