<?php

namespace DbUtils\Adapter\Mysql;

use DbUtils\Adapter\AdapterInterface;
use DbUtils\Adapter\AdapterTrait;
use DbUtils\Adapter\DBSingletonTrait;
use DbUtils\Select\Mysql\Select as MysqlSelect;

final class Adapter extends \Mysqli implements AdapterInterface {

	protected static $_tableClass = 'DbUtils\Table\Mysql\Table';

	private static $_options = [
		'host'		=>	'localhost',
		'user'		=>	'root',
		'password'	=>	'',
		'dbname'	=>	'',
	];

	use AdapterTrait;
	use DBSingletonTrait;

	/**
	 * Обычный mysqli_prepare, только можно связывать массив
	 *
	 * @param sql $sql текст sql-запроса
	 * @return Stmt
	 */
	public function prepare($sql) {
		return new Stmt($this, $sql);
	}

	/**
	 * query
	 *
	 * @param string $sql
	 * @param int $resultMode
	 * @access public
	 * @return MysqlSelect | true
	 * @throws \Exception
	 */
	public function query($sql, $resultMode = \MYSQLI_STORE_RESULT) {
		$r = parent::query($sql, $resultMode);
		if (!$r instanceof \mysqli_result) {
			return $r;
		}
		return new MysqlSelect($r);
	}


	protected function _init() {

		$o = static::$_options;

		$driver = new \Mysqli_Driver;
		$driver->report_mode =
			MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

		parent::__construct($o['host'], $o['user'],
			$o['password'], $o['dbname']);

		$this->set_charset('utf-8');
	}

	private function __construct() {
		$this->_init();
	}

	public function fetchAll($sql) {
		return parent::query($sql)->fetch_all(MYSQLI_ASSOC);
	}

    /**
     * Обертка \mysqli::$info
	 *
     * @return array массив с некоторыми фиксированными ключами
     * @access public
     */
	public function info() {
		$info = $this->info;

		//	по умолчанию неизвестно
		$def = [
			'Records'		=> null,
			'Duplicates'	=> null,
			'Warnings'		=> null,
			'Skipped'		=> null,
			'Deleted'		=> null,
			'Rows matched'	=> null,
			'Changed'		=> null,
		];

		if (empty($info)) {
			return $def;
		}
		$pattern = '/(' . implode('|', array_keys($def)) .'): (\d+)/';
		preg_match_all($pattern, $info, $matches);
		$info = array_combine($matches[1], $matches[2]);

		return array_merge($def, $info);
	}

	public function quote($text) {
		return "'{$this->real_escape_string($text)}'";
	}
}
