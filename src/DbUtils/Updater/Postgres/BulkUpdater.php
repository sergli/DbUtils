<?php

namespace DbUtils\Updater\Postgres;

use DbUtils\Updater\UpdaterInterface;
use DbUtils\Updater\UpdaterTrait;
use DbUtils\Saver\Postgres\BulkInsertSaver as PostgresBulkInsertSaver;

class BulkUpdater extends PostgresBulkInsertSaver implements
	UpdaterInterface
{

	use UpdaterTrait;

	/**
	 * Подготовленное выражение из полей $_key
	 * типа (where) a = b AND c = d::e
	 *
	 * @var string
	 * @access private
	 */
	private $_keyExpr = '';

	/**
	 * Типы данных, приведение к которым из
	 * text производится автоматически
	 * @var string[]
	 * @access private
	 * @static
	 */
	private static $_autoCastingDataTypes =
	[
		'text',
		'character varying',
		'integer',	//	уже приведено (см PgSaver::_quote())
		'character',
		'boolean',	//	уже приведено
	];


	/**
	 * Если это нужно - то приводит имя колонки к имя::тип.
	 *
	 * @param string $column
	 * @access private
	 * @return string
	 * @see $_autoCastingDataTypes
	 */
	private function _castColumn($column, $dataType)
	{
		//	varchar(255) -> varchar
		$dt = preg_replace('/\s*\(.*$/', '', $dataType);
		if (in_array($dt, self::$_autoCastingDataTypes))
		{
			return $column;
		}
		return "$column::$dataType";
	}


	protected function _generateSql()
	{
		$keyExpr = [];

		foreach ($this->_key as $col)
		{
			$col = self::_castColumn($col, $this->_columns[$col]);
			$keyExpr[] = "f.$col = v.$col";
		}
		$this->_keyExpr = implode("\n\tAND ", $keyExpr);

		$setEqExpr = [];

		foreach ($this->_columns as $column => $dataType)
		{
			$col = self::_castColumn($column, $dataType);
			$setEqExpr[] = "$column = v.$col";
		}

		$this->_sql = "
UPDATE " . $this->_table->getFullName() . " AS f
SET
" . implode(",\n\t", $setEqExpr) . "
FROM ";
	}

	protected function _save()
	{
		$sql = $this->_sql . "(
	VALUES " . implode(",\n\t\t", $this->_values) . "
) AS v (" . implode(',', array_keys($this->_columns)) . ")
WHERE
	" . $this->_keyExpr;

		$this->_execSql($sql);

		unset($sql);
	}
}
