<?php

require_once __DIR__ . '/PgSaver.php';

/**
 * Класс предназначен для выполнения массового обновления
 * данных postgresql-таблице. 
 *
 * Необходимо предоставить значения primary/unique полей
 * 
 * @uses PgSaver
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
class PgUpdater extends PgSaver {
	
	/**
	 * Поля primary/unique constraint
	 * 
	 * @var string[]
	 * @access private
	 */
	private $_key = array();

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
	 * Ищем, по какому уник. ключу мы будем обновлять
	 *
	 * Если подходит primary key - то по нему,
	 * иначе - берём первый подходящий unique
	 * 
	 * @access private
	 * @return string[]|null
	 */
	private function _findKey() {
		$pk = $this->_table->getPrimaryKey();
		$columns = array_keys($this->_columns);
		if ($pk) {
			$cols = $pk['columns'];
			if (count(array_intersect(
				$cols, $columns)) == count($cols)) {
				return $cols;

			}
		}

		foreach ($this->_table->getUniques() as $unique) {
			$cols = $unique['columns'];
			if (count(array_intersect(
				$cols, $columns)) == count($cols)) {
				return $cols;
			}
		}

		return null;
	}
	
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

	public function __construct(PDO $conn, PgTable $table, 
		array $columns) {

		parent::__construct($conn, $table, $columns);

		$key = $this->_findKey();
		if (!$key) {
			throw new PgSaverException("Нет подходящего уникального ключа");
		}
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
	
	public function save() {
		
		if (empty($this->_values)) {
			return;
		}

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
		$values = null;
		self::_log("Сохраняю {$this->_count} записей\n");

		try {
			$this->_db->beginTransaction();
			$this->_db->exec($sql);
			$this->_db->commit();
			$this->truncate();
		}
		catch (PDOException $e) {
			$this->_db->rollback();
			echo $sql;
			throw $e;
		}
	}
}
