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

		//	FIXME надо что-то с этим делать.
		//	Восстанавливать кто будет ?
		//
		set_error_handler(function($errno, $errstr,
			$errfile, $errline) {
			throw new \ErrorException($errstr, $errno, 0,
				$errfile, $errline);
		});
		$this->_db = pg_connect($dsn,
			PGSQL_CONNECT_FORCE_NEW);

		$o['charset'] = !empty($opt['charset'])
			? $opt['charset'] : 'UTF8';

		pg_query($this->_db,
			"SET client_encoding TO {$o['charset']}");

	}

	//FIXME переделать.
	public function query($sql) {
		try {
			$r = pg_query($this->_db, $sql);
			return new PostgresSelect($r, $sql);
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
