<?php

namespace DbUtils\Adapter\Postgres;

use DbUtils\Adapter\AdapterInterface;
use DbUtils\Adapter\AdapterTrait;
use DbUtils\DBSingletonTrait;
use DbUtils\Select\Postgres\Select as PostgresSelect;

require_once __DIR__ . '/../../Table/Postgres/Table.php';
require_once __DIR__ . '/../AdapterInterface.php';
require_once __DIR__ . '/../AdapterTrait.php';
require_once __DIR__ . '/../../DBSingletonTrait.php';
require_once __DIR__ . '/../../Select/Postgres/Select.php';


final class Adapter implements AdapterInterface {

	protected static $_tableClass =
		'DbUtils\Table\Postgres\Table';

	private static $_options = [
		'host'		=>	'127.0.0.1',
		'user'		=>	'sergli',
		'password'	=>	'12345',
		'dbname'	=>	'sergli',
	];

	use AdapterTrait;
	use DBSingletonTrait;

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
