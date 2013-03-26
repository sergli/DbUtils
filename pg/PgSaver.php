<?php

require_once __DIR__ . '/../Utils.php';
require_once __DIR__ . '/iSqlDataSaver.php';
require_once __DIR__ . '/PgSaverException.php';
require_once __DIR__ . '/PgTable.php';



/**
 * Класс для массовых вставок данных в postgresql
 * 
 * @uses iSqlDataSaver
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
class PgSaver implements iSqlDataSaver {

	protected static $_debug = false;

	/**
	 * Адаптер БД
	 * 
	 * @var PDO
	 */
	protected $_db = null;

	/**
	 * 
	 * Таблица, куда будут выполняться вставки
	 * 
	 * @var PgTable
	 */
	protected $_table = null;

	/**
	 * Массив с названиями колонок, в кот. будем вставлять
	 * вида column => dataType
	 * 
	 * @var string[]
	 */
	protected $_columns = array();

	/**
	 * Текущее кол-во записей в буфере
	 * 
	 * @var int
	 */
	protected $_count = 0;

	/**
	 * Размер порций с данными
	 * 
	 * @var int
	 */
	protected $_batchSize = 5000;

	
	/**
	 * Буфер промежуточного хранения записей
	 * 
	 * @var array
	 */
	protected $_values = array();

	
	const E_NO_TABLE_NAME = 10;

	const E_NO_STRUCTURE = 11;

	const E_INCORRECT_FIELDS_COUNT = 12;

	const E_FIELD_NOT_EXISTS = 13;

	const E_TABLE_NOT_EXISTS = 14;

	const E_INCORRECT_BATCH_SIZE = 15;



	const MIN_CHUNK_SIZE = 0;	//	0 - отмена автомат. сохранений

	const MAX_CHUNK_SIZE = 50000;



	public function addRow(Array $row) {
		
		$record = $this->_cleanRow($row);
		unset($row);

		$values = '';
		$br = '';
		foreach ($record as $field) {
			$values .= $br . $field;
			$br = ', ';
		}

		$values = "($values)";

		$this->_values[] = $values;
		$this->_count++;

		//self::_log("Запись добавлена\n");

		if (0 !== $this->_batchSize 
			&& $this->_count >= $this->_batchSize) {
			$this->save();
		}
	}

	public function truncate() {
		$this->_values = array();
		$this->_count = 0;
	}

	public function save() {
		
		if (empty($this->_values)) {
			return;
		}

		$values = "\n" . implode(",\n", $this->_values);


		$sql = "INSERT INTO {$this->_table->getFullName()}";
		$sql .= "\n (" . implode(',', array_keys($this->_columns)) . ")";
		$sql .= "\n VALUES $values;";

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
			throw $e;
		}
	}


	public function getSize() {
		return $this->_count;
	}
	
	public function getBatchSize() {
		return $this->_batchSize;
	}

	public function setBatchSize($size) {
		$size = (int) $size;
		if ($size < self::MIN_CHUNK_SIZE || $size > self::MAX_CHUNK_SIZE) {
			throw new PgSaverException("Неверное значение параметра",
				self::E_INCORRECT_CHUNK_SIZE
			);
		}

		$this->_batchSize = $size;
	}


	public function __construct(PDO $conn, PgTable $table, 
		array $columns) {
	
		$this->_values = array();
		$this->_count = 0;

		$this->_db = $conn;
		
		$this->_db->setAttribute(
			PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->_db->setAttribute(
			PDO::ATTR_TIMEOUT, 24 * 3600);
		$this->_table = $table;

		$allColumns = $this->_table->getColumns();

		$this->_columns = array();
		foreach ($columns as $column) {
			if (!isset($allColumns[$column])) {
				throw new PgSaverException(
					"Поле не существуют: $column", 
						self::E_FIELD_NOT_EXISTS
				);
			}
			$this->_columns[$column] = $allColumns[$column];
		}
		
		$this->_count = 0;
	}
	
	public function __destruct() {
		$this->_values = null;
//		$this->save();
	}

	/**
	 * Приводим запись в пригодный для вставки вид
	 * 
	 * @param Array $row 
	 * @access protected
	 * @return array
	 * @throw PgSaverExeption
	 * при обработке исключения можно сохранить то, что в буфере,
	 * а можно обнулить
	 */
	protected function _cleanRow(Array $row) {
		
		if (count($row) != count($this->_columns)) {
			throw new PgSaverException(
				"Неверное кол-во полей у записи\n" . 
					substr(print_r($row, 1), 5), 
				self::E_INCORRECT_FIELDS_COUNT
			);
		}
		$record = array();
		foreach ($this->_columns as $field => $dataType) {
			if (!array_key_exists($field, $row)) {
				throw new PgSaverException(
					"Необходимо поле $field у записи " . 
						substr(print_r($row, 1), 5),
					self::E_FIELD_NOT_EXISTS
				);
			}
			
			$record[$field] = $this->_quote($field, $row[$field]);
		}

		return $record;
	}
	
	/**
	 * Кавычит текстовые данные, остальное (bool/null) -
	 * приводит их в пригодный для postgres вид
	 * 
	 * @param mixed $text 
	 * @access protected
	 * @return string
	 */
	protected function _quote($column, $text) {
		if (null === $text) {
			return 'NULL';
		}
		else if (true === $text) {
			return 'true';
		}
		else if (false === $text) {
			return 'false';
		}
		
		switch ($this->_columns[$column]) {
		case 'integer':
			return (int) $text;
		case 'boolean':
			return (boolean) $text;
		}

		return $this->_db->quote($text);
	}

	protected static function _log($text) {
		if (!self::$_debug) {
			return;
		}
		echo $text;
	}
}
