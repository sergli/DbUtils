<?php

namespace DbUtils\Adapter\Mysqli;

use DbUtils\Adapter\MysqlAdapterInterface;
use DbUtils\Adapter\AdapterTrait;

final class Mysqli extends \Mysqli
	implements MysqlAdapterInterface {

	use AdapterTrait;

	public function getPlatformName() {
		return self::PLATFORM_MYSQL;
	}

	public function stmt_init() {
		return new Stmt($this);
	}

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
	 * @return Select | true
	 * @throws \Exception
	 */
	public function query($sql, $resultMode = \MYSQLI_STORE_RESULT) {
		$r = parent::query($sql, $resultMode);
		if (!$r instanceof \Mysqli_Result) {
			return $r;
		}
		return new Select($r);
	}


	public function __construct(array $opt = []) {
		$driver = new \Mysqli_Driver;
		$driver->report_mode =
			MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT;

		$o = [];
		$o['host'] = isset($opt['host'])
			? $opt['host'] : ini_get('mysqli.default_host');
		$o['user'] = isset($opt['user'])
			? $opt['user'] : ini_get('mysqli.default_user');
		$o['password'] = isset($opt['password'])
			? $opt['password']: ini_get('mysqli.default_pw');
		$o['dbname'] = isset($opt['dbname'])
			? $opt['dbname'] : '';

		$o['port'] = isset($opt['port'])
			? $opt['port'] : ini_get('mysqli.default_port');
		$o['port'] = (int) $o['port'];


		$o['socket'] = isset($opt['socket'])
			? $opt['socket'] : ini_get('mysqli.default_socket');
		$o['charset'] = !empty($opt['charset'])
			? $opt['charset'] : 'utf8';

		parent::__construct(
			$o['host'],
			$o['user'],
			$o['password'],
			$o['dbname'],
			$o['port'],
			$o['socket']
		);

		$this->set_charset($o['charset']);
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
