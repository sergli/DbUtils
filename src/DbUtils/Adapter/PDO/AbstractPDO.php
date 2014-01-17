<?php

namespace DbUtils\Adapter\PDO;

use DbUtils\Adapter\AdapterInterface;
use DbUtils\Adapter\AdapterTrait;
use DbUtils\Select\PDO\Select as PDOSelect;

abstract class AbstractPDO extends \PDO
			implements AdapterInterface {

	use AdapterTrait;

	protected $_driverName;

	final public function __construct(array $opt = []) {

		$dsn = $this->_getDSN($opt);
		$driverOpts = [
			self::ATTR_ERRMODE	=> self::ERRMODE_EXCEPTION
		];

		$driverOpts = $driverOpts +
			$this->_getDriverOptions($opt);
		$user = !empty($opt['user']) ?
			$opt['user'] : null;
		$password = isset($opt['password']) ?
			$opt['password'] : null;

		parent::__construct($dsn, $user, $password, $driverOpts);

		$charset = !empty($opt['charset'])
			? $opt['charset'] : 'utf8';

		$this->exec("SET NAMES '$charset'");
	}

	public function query($sql) {
		$stmt = parent::query($sql, parent::FETCH_ASSOC);
		return new PDOSelect($stmt);
	}

	protected function _getDSN(array $opt) {
		unset($opt['user']);
		unset($opt['password']);

		if (isset($opt['socket'])) {
			$opt['unix_socket'] = $opt['socket'];
			unset($opt['socket']);
		}

		$driver = $this->_getDriverName();

		if (!in_array($driver, $this->getAvailableDrivers())) {
			throw new Exception(sprintf(
				'Драйвер "%s" не определён', $driver));
		}

		$dsn = [];
		foreach($opt as $key => $val) {
			$dsn[] = "$key=$val";
		}

		$dsn = implode(';', $dsn);
		$dsn = "$driver:$dsn";

		return $dsn;
	}

	abstract protected function _getDriverName();
	abstract protected function _getDriverOptions(array $opt);
}
