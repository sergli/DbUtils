<?php

namespace DbUtils\Adapter\Pdo;

use DbUtils\Adapter\AdapterInterface;
use DbUtils\Adapter\AdapterTrait;

final class Pdo extends \PDO implements AdapterInterface {

	use AdapterTrait;

	public function getPlatformName() {
		$platform = $this->getAttribute(self::ATTR_DRIVER_NAME);

		switch ($platform) {
		case 'mysql':
			return self::PLATFORM_MYSQL;

		case 'pgsql':
			return self::PLATFORM_POSTGRES;

		default:
			throw new \UnexpectedValueException(sprintf(
				'Unknown platform: %s', $platform));
		}
	}

	public function __construct($driverName,
		array $opts = []) {

		$dsn = $this->_getDSN($opts);
		$driverOpts = $this->_getDriverOptions();

		$user = !empty($opts['user']) ?
			$opts['user'] : null;
		$password = isset($opts['password']) ?
			$opts['password'] : null;

		parent::__construct($dsn, $user, $password, $driverOpts);

		$charset = !empty($opts['charset'])
			? $opts['charset'] : 'utf8';

		$this->exec("SET NAMES '$charset'");
	}

	public function query($sql) {
		$stmt = parent::query($sql, parent::FETCH_ASSOC);
		return new Select($stmt);
	}

	protected function _getDSN($driverName, array $opts) {
		unset($opts['user']);
		unset($opts['password']);

		if (isset($opts['socket'])) {
			$opts['unix_socket'] = $opts['socket'];
			unset($opts['socket']);
		}

		if (!in_array($driverName,
			$this->getAvailableDrivers())) {
			throw new Exception(sprintf(
				'Драйвер "%s" не определён', $driverName));
		}

		$dsn = $driverName . ':' .
			http_build_query($opts, null, ';');

		return $dsn;
	}

	private function _getDriverOptions($driverName,array $opts) {		$driverOpts = [
			self::ATTR_ERRMODE	=> self::ERRMODE_EXCEPTION
		];

		switch ($driverName) {
		case 'mysql':
			$charset = !empty($opt['charset'])
				? $opt['charset'] : 'utf8';
			$driverOpts[self::MYSQL_ATTR_INIT_COMMAND] =
				"SET NAMES $charset";
			break;
		}

		return $driverOpts;
	}
}
