<?php

namespace DbUtils\Updater\Postgres;

use DbUtils\Updater\UpdaterInterface;
use DbUtils\Updater\UpdaterTrait;
use DbUtils\Saver\Postgres\BulkInsertSaver as PostgresBulkInsertSaver;

require_once __DIR__ . '/../UpdaterInterface.php';
require_once __DIR__ . '/../UpdaterTrait.php';
require_once __DIR__ .  '/../../Saver/Postgres/BulkInsertSaver.php';

class BulkUpdater extends PostgresBulkInsertSaver
	implements UpdaterInterface {

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
	 * Подготовленно выражение из полей $_columns
	 * типа (set) a = b, c = d, e = f
	 *
	 * @var string
	 * @access private
	 */
	private $_setEqExpr = '';

	const E_NONE_KEY = 71;

	/**
	 * Типы данных, приведение к которым из
	 * text производится автоматически
	 * @var string[]
	 * @access private
	 * @static
	 */
	private static $_autoCastingDataTypes = array(
		'text',
		'character varying',
		'integer',	//	уже приведено (см PgSaver::_quote())
		'character',
		'boolean',	//	уже приведено
	);


	/**
	 * Если это нужно - то приводит имя колонки к имя::тип.
	 *
	 * @param string $column
	 * @param string $dataType
	 * @access private
	 * @return string
	 * @see $_autoCastingDataTypes
	 */
	private function _castColumn($column, $dataType) {
		//	varchar(255) -> varchar
		$dt = preg_replace('/\(.*$/', '', $dataType);
		if (in_array($dt, self::$_autoCastingDataTypes)) {
			return $column;
		}
		return "$column::$dataType";
	}


	protected function _generateSql() {
		$this->_key = $key;
		$this->_keyExpr = array();

		foreach ($this->_key as $col) {
			$col = self::_castColumn($col, $this->_columns[$col]);
			$this->_keyExpr[] = "f.$col = v.$col";
		}
		$this->_keyExpr = implode("\n\tAND ", $this->_keyExpr);

		$this->_setEqExpr = array();

		foreach ($this->_columns as $column => $dataType) {
			$col = self::_castColumn($column, $dataType);
			$this->_setEqExpr[] = "$column = v.$col";
		}

		$this->_setEqExpr = implode(",\n\t", $this->_setEqExpr);
	}

	protected function _save() {

		$sql = "
UPDATE {$this->_table->getFullName()} AS f
SET
	{$this->_setEqExpr}
FROM (
	VALUES " . implode(",\n\t\t", $this->_values) . "
) AS v (" . implode(',', array_keys($this->_columns)) . ")
WHERE
	{$this->_keyExpr};
";
		$values = [];

		//	todo exception
		if ($r = $this->_db->query($sql)) {
			return pg_affected_rows($r->getResult());
		}

		return 0;
	}
}
