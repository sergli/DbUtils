<?php

namespace DbUtils\Adapter\Pgsql;

use DbUtils\Adapter\PostgresAdapterInterface;
use DbUtils\Adapter\AdapterTrait;
use DbUtils\Adapter\AsyncExecInterface;

class Pgsql implements PostgresAdapterInterface,
		AsyncExecInterface
{
	use AdapterTrait;

	private $_db;

	public function asyncExec($sql)
	{
		$this->callCarefully(function() use ($sql)
		{
			pg_send_query($this->_db, $sql);
		});
	}

	public function wait()
	{
		$this->callCarefully(function()
		{
			do
			{
				$r = pg_get_result($this->_db);
			}
			while ($r);
		});
	}

	public function getResource()
	{
		return $this->_db;
	}

	public static function callCarefully(Callable $function)
	{
		set_error_handler(
			function($errno, $errstr, $errfile, $errline)
			{
				throw new \ErrorException($errstr, $errno,
					0, $errfile, $errline);
			}
		);

		$r = call_user_func($function);

		restore_error_handler();

		return $r;
	}

	public function __construct(array $opt = [])
	{
		$o = [];

		$o['host'] = isset($opt['host'])
			? $opt['host'] : '127.0.0.1';
		if (isset($opt['dbname']))
		{
			$o['dbname'] = $opt['dbname'];
		}
		if (isset($opt['port']))
		{
			$o['port'] = $opt['port'];
		}
		if (isset($opt['user']))
		{
			$o['user'] = $opt['user'];
		}
		if (isset($opt['password']))
		{
			$o['password'] = $opt['password'];
		}

		$dsn = http_build_query($o, null, ' ');

		$this->callCarefully(function() use ($dsn, $o, $opt)
		{
			$this->_db = pg_connect($dsn,
				PGSQL_CONNECT_FORCE_NEW);

			$o['charset'] = !empty($opt['charset'])
				? $opt['charset'] : 'UTF8';

			pg_query($this->_db,
				"SET client_encoding TO {$o['charset']}");
		});
	}

	public function query($sql)
	{
		$r = $this->callCarefully(function() use ($sql)
		{
			return pg_query($this->_db, $sql);
		});

		return new Select($r);
	}

	/**
	 * @param string $text
	 * @access public
	 * @return string
	 */
	public function quote($text)
	{
		return ltrim(pg_escape_literal($this->_db, $text));
	}
}
