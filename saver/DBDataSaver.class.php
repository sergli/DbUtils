<?php

namespace db_utils\saver;

use db_utils\table\RDBTable;
use db_utils\adapter\RDBAdapter;

/**
 * @todo autoloader 
 * @todo возможно стоит разрешить добавлять записи без указания колонок (
 *	включать каким-нибудь флагом такое поведение)
 */

require_once __DIR__ . '/iDBDataSaver.class.php';

/**
 * Абстрактный класс для сохранения данных в БД
 * 
 * @uses iDBDataSaver
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
abstract class DBDataSaver implements iDBDataSaver, 
									\ArrayAccess,
									\Countable {
	/**
	 * Дополнительные опции сейвера. Битовая маска
	 * 
	 * @var int
	 * @access protected
	 */
	protected $_options = 0;

	/**
	 * Адаптер для работы с БД
	 * 
	 * @var RDBAdapter
	 * @access protected
	 */
	protected $_db = null;
	/**
	 * Таблица
	 * 
	 * @var RDBTable
	 * @access protected
	 */
	protected $_table = null;

	/**
	 * Колонки, в которые сохраняем данные
	 * 
	 * @var string[]
	 * @access protected
	 */
	protected $_columns = null;

	/**
	 * Текущее кол-во записей в буфере
	 * 
	 * @var int
	 * @access protected
	 */
	protected $_count = 0;

	/**
	 * Размер порции для вставки (0 - неограниченный)
	 * 
	 * @var int
	 * @access protected
	 */
	protected $_batchSize = 5000;

	/**
	 * sql-запрос для вставки данных (или его статическая часть) 
	 * 
	 * @var string
	 * @access protected
	 */
	protected $_sql = '';

	public static $_debug = false;

	const E_NO_TABLE_NAME = 10;
	const E_NO_STRUCTURE = 11;
	const E_INCORRECT_COLUMNS_COUNT = 12;
	const E_COLUMN_NOT_EXISTS = 13;
	const E_TABLE_NOT_EXISTS = 14;
	const E_INCORRECT_BATCH_SIZE = 15;

	const MIN_BATCH_SIZE = 0;	//	0 - отмена автомат. сохранения
	const MAX_BATCH_SIZE = 50000;

	/**
	 * Экранирует спец.символы
	 * 
	 * @param string $column столбец
	 * @param mixed $value значение столбца
	 * @abstract
	 * @access protected
	 * @return string значение, пригодное для использ. в sql
	 */
	abstract protected function _quote($column, $value);
	/**
	 * Добавляет новую запись в буфер
	 *
	 * @param array $record 
	 * @abstract
	 * @access protected
	 * @return void
	 * @see add()
	 */
	abstract protected function _add(array $record);
	/**
	 * Создаёт необходимый sql-запрос (возможно, часть sql)
	 * 
	 * @abstract
	 * @access protected
	 * @return void
	 * @see $_sql
	 */
	abstract protected function _generateSql();

	/**
	 * Сохраняет буфер
	 * 
	 * @abstract
	 * @access protected
	 * @return int кол-во добавленных записей
	 */
	abstract protected function _save();

	/**
	 * Сохраняет буфер, обнуляет его
	 * 
	 * @access public
	 * @return int
	 * @throws \Exception
	 */
	public function save() {
		
		if (0 === $this->_count) {
			return 0;
		}

		try {
			$cnt = $this->_save();
			$this->reset();
			return $cnt;
		}
		catch (\Exception $e) {
			throw new \Exception(
				"Ошибка при вставке данных:\n{$e->getMessage()}"
			);
		}
	}


	/**
	 * Устанавливает дополнительные опции
	 * 
	 * @param int $option битовая маска констант
	 */
	public function setOptions($options) {
		$options = (int) $options;
		if ($options !== $this->_options) {
			$this->_options = $options;
			$this->_generateSql();
		}
	}

	/**
	 * При уничтожении объекта сохраняем остатки в буфере
	 * 
	 * @access public
	 * @return void
	 */
	public function __destruct() {
		$this->save();
	}

	/**
	 * Подготавливает запись
	 *
	 * @param array $row 
	 * @access private
	 * @return array
	 * @see _quote()
	 * @throws \Exception
	 */
	private function _cleanRow(array $row) {
		$record = array();

		foreach ($this->_columns as $field => $dataType) {
			if (!array_key_exists($field, $row)) {
				throw new \Exception(
					"Необходимо поле $field у записи " . 
						substr(print_r($row, 1), 5),
					self::E_COLUMN_NOT_EXISTS
				);
			}
			$record[$field] = $this->_quote($field, $row[$field]);
		}

		return $record;
	}

	/**
	 * Добавляет запись в буфер
	 *
	 * @param array $row 
	 * @access public
	 * @return void
	 * @see _add()
	 * @see _cleanRow()
	 * @throws \Exception
	 */
	public function add(array $row) {

		if (empty($this->_columns)) {
			$this->_setColumns(array_keys($row));
		}
		
		if (count($row) !== count($this->_columns)) {
			throw new \Exception(
				"Неверное кол-во полей у записи\n" . print_r($row, true),
				self::E_INCORRECT_COLUMNS_COUNT
			);
		}

		$record = $this->_cleanRow($row);
		unset($row);

		$this->_add($record);
		
		$this->_count++;

		if (0 !== $this->_batchSize 
			&& $this->_count >= $this->_batchSize) {
			$this->save();
		}

	}
	
	/**
	 * Устанавливает, в какие поля будут сохраняться данные
	 * 
	 * @param string[] $columns названия полей
	 * @access protected
	 * @return void
	 * @see _generateSql()
	 * @throws \Exception
	 */
	protected function _setColumns(array $columns) {
		if (empty($columns)) {
			throw new \Exception("Пустой массив полей",
				self::E_NO_COLUMNS);
		}
		
		$all = $this->_table->getColumns();
		foreach ($columns as $column) {
			if (is_string($column) && isset($all[$column])) {
				//	запоминаем тип
				$this->_columns[$column] = $all[$column];
			}
			else {
				throw new \Exception(
					"Поле не существует: $column",
					self::E_COLUMN_NOT_EXISTS
				);
			}
		}
		$this->_generateSql();
	}

	/**
	 * Создаёт экземпляр сейвера
	 *
	 * @param RDBTable экземпляр таблицы
	 * @param string[] $columns поля, в кот. будут сохраняться данные
	 * 	если не указано - определятеся по первому запуску add()
	 * return void
	 * @see getColumns()
	 * @see getConnection()
	 */

	public function __construct(RDBTable $table,  array $columns = null) {
		
		$this->_table = $table;
		$this->_count = 0;

		//	вообщето плохая идея, тк, бывает, новый запрос не будет
		//	работать, пока не закроется пред. курсор
		// note нужно просто неск. соединений открывать
		$this->_db = $this->_table->getConnection();

		if ($columns) {
			$this->_setColumns($columns);
			$all = $this->_table->getColumns();
		}
	}

	/**
	 * Возвращает текущий размер буфера
	 * 
	 * @access public
	 * @return int
	 */
	public function getSize() {
		return $this->_count;
	}

	/**
	 * Возвращает размер порции
	 * 
	 * @access public
	 * @return int
	 */
	public function getBatchSize() {
		return $this->_batchSize;
	}

	/**
	 * Устанавливает размер порции (0 - бесконечный)
	 *
	 * @param int $size новый размер
	 * @access public
	 * @return int новый размер
	 * @throws \Exception
	 */
	public function setBatchSize($size) {
		$size = (int) $size;
		if ($size >= self::MIN_BATCH_SIZE && 
				$size <= self::MAX_BATCH_SIZE) {
			return $this->_batchSize = $size;
		}
		throw new \Exception("Неверное значение для параметра",
			self::E_INCORRECT_BANCH_SIZE);
	}

	protected static function _log($text) {
		if (!static::$_debug) {
			return;
		}
		echo $text . "\n";
	}

	//////////////////////	ArrayAccess	//////////////////////////

	public function offsetExists($offset) {
		throw new \Exception(
			'Чтение внутренних данных запрещено реализацией'
		);
	}

	public function offsetGet($offset) {
		throw new \Exception(
			'Чтение внутренних данных запрещено реализацией');
	}

	public function offsetSet($offset, $row) {
		if (is_null($offset)) {	//	в конец
			$offset = $this->_count;
		}
		else {
			$offset = (int) $offset;
		}
		if ($offset !== $this->_count) {
			throw new \Exception(
				'Добавить новую запись можно только в конец очереди');
		}
		$this->add($row);
	}

	public function offsetUnset($offset) {
		throw new \Exception(
			'Удаление из внутренних данных запрещено реализацией');
	}

	/////////////////////	Countable	/////////////////////////
	public function count() {
		return $this->_count;
	}
}
