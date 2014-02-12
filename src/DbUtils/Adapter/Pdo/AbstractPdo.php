<?php

namespace DbUtils\Adapter\Pdo;

use DbUtils\Adapter\AdapterInterface;
use DbUtils\Adapter\AdapterTrait;

abstract class AbstractPdo extends \PDO implements
	AdapterInterface
{
	use AdapterTrait;

	abstract public function getDriverName();

	protected function _getDriverOptions(array $opts)
	{
		return [];
	}

	final public function __construct(array $opts = [])
	{
		$dsn = $this->_getDSN($opts);

		$driverOpts = [
			self::ATTR_ERRMODE	=> self::ERRMODE_EXCEPTION
		];
		$driverOpts += $this->_getDriverOptions($opts);

		$user = !empty($opts['user']) ?
			$opts['user'] : null;
		$password = isset($opts['password']) ?
			$opts['password'] : null;

		parent::__construct($dsn, $user, $password, $driverOpts);

		$charset = !empty($opts['charset'])
			? $opts['charset'] : 'utf8';

		$this->exec("SET NAMES '$charset'");
	}

	public function query($sql)
	{
		$stmt = $this->query($sql, parent::FETCH_ASSOC);
		return new Select($stmt);
	}

	protected function _getDSN(array $opts)
	{
		unset($opts['user']);
		unset($opts['password']);

		if (isset($opts['socket']))
		{
			$opts['unix_socket'] = $opts['socket'];
			unset($opts['socket']);
		}

		$driverName = $this->getDriverName();

		if (!in_array($driverName, $this->getAvailableDrivers()))
		{
			throw new \UnexpectedValueException(sprintf(
				'Unknown driver: %s', $driverName));
		}

		$dsn = $driverName . ':' .
			http_build_query($opts, null, ';');

		return $dsn;
	}
}
