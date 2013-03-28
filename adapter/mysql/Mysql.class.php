<?php

namespace db_utils\adapter\mysql;

use db_utils;
use	db_utils\adapter;

require_once __DIR__ . '/../Adapter.class.php';
require_once __DIR__ . '/../../Singleton.class.php';
require_once __DIR__ . '/../select/mysql/MysqlSelect.class.php';
require_once __DIR__ . '/MysqlStatement.class.php';
require_once __DIR__ . '/../../table/mysql/MysqlTable.class.php';



final class Mysql extends \mysqli implements adapter\iAdapter {

	protected static $_tableClass = 'db_utils\table\mysql\MysqlTable';

	private static $_options = [
		'host'		=>	'localhost',
		'user'		=>	'root',
		'password'	=>	'',
		'dbname'	=>	'',
	];

	use db_utils\adapter\Adapter;
	use db_utils\Singleton {
		db_utils\Singleton::getInstance as private _getInstance;
	}

	public function prepare($sql) {
		return new db_utils\adapter\mysql\MysqlStatement($this, $sql);
	}

	/**
	 * query 
	 * 
	 * @param string $sql 
	 * @param int $resultMode 
	 * @access public
	 * @return db_autils\adapter\select\mysql\MysqlSelect | true
	 * @throws \Exception
	 */
	public function query($sql, $resultMode = \MYSQLI_STORE_RESULT) {
		$r = parent::query($sql, $resultMode);
		if (!$r instanceof \mysqli_result) {
			return $r;
		}
		return new db_utils\adapter\select\mysql\MysqlSelect($r);
	}

	public static function getInstance($tag = 0, $options = []) {
		if (is_string($options)) {
			$options = [ 'dbname' => $options ];
		}
		$options = (array) $options;

		static::setOptions($options);

		return static::_getInstance($tag);
	}


	public static function setOptions(array $options = []) {

		if (!$options) {
			return;
		}
		//	только валидные ключи
		$options = array_intersect_key($options, static::$_options);

		//	ничего нового
		if (!array_diff_assoc($options, static::$_options)) {
			return;
		}
		
		if (!empty(static::$_instances)) {
			throw new \Exception('Соединения уже установлены');
		}

		static::$_options = $options + static::$_options;
	}

	protected function _init() {
		
		$o = static::$_options;
		
		$driver = new \mysqli_driver;
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

		$def = [
			'Records' => 0,
			'Duplicates' => 0,
			'Warnings' => 0,
			'Skipped' => 0,
			'Deleted' => 0,
			'Rows matched' => 0,
			'Changed' => 0
		];

		if (empty($info)) {
			return $def;
		}
		$pattern = '/(' . implode('|', array_keys($def)) .'): (\d+)/';
		preg_match_all($pattern, $info, $matches);
		$info = array_combine($matches[1], $matches[2]);
		
		return array_merge($def, $info);
	}

}
