<?php

namespace DbUtils\Saver;

use DbUtils\Adapter\AdapterInterface;
use DbUtils\Table\TableInterface;

use Monolog\Logger;
use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;


/**
 * @todo возможно стоит разрешить добавлять записи без указания колонок (
 *	включать каким-нибудь флагом такое поведение)
 */

/**
 * Абстрактный класс для сохранения данных в БД
 *
 * @uses iSaver
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
abstract class AbstractSaver implements SaverInterface,
	\ArrayAccess {

	protected $_availableAdapters = null;

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
	 * @var AdapterInterface
	 * @access protected
	 */
	protected $_db = null;
	/**
	 * Таблица
	 *
	 * @var TableInterface
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
	protected $_batchSize = self::DEFAULT_BATCH_SIZE;

	/**
	 * sql-запрос для вставки данных (или его статическая часть)
	 *
	 * @var string
	 * @access protected
	 */
	protected $_sql = '';

	/**
	 * @var Monolog\Logger
	 */
	protected $_logger;

	const E_NO_TABLE_NAME = 10;
	const E_NO_STRUCTURE = 11;
	const E_INCORRECT_COLUMNS_COUNT = 12;
	const E_COLUMN_NOT_EXISTS = 13;
	const E_TABLE_NOT_EXISTS = 14;
	const E_NONE_KEY = 16;

	/**
	 * @type int	0 - отмена автомат. сохранения
	 */
	const MIN_BATCH_SIZE = 0;
	const MAX_BATCH_SIZE = 50000;
	const DEFAULT_BATCH_SIZE = 5000;

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
	 * @param array $record уже нужным образом заквоченная запись
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

	abstract protected function _reset();

	public function reset() {
		$this->_reset();
		$this->_logger->addInfo(sprintf(
			'Reset saver. New buffer size is %d', $this->_count));
	}

	/**
	 * Сохраняет буфер, обнуляет его
	 *
	 * @access public
	 * @return int
	 * @throws \Exception
	 */
	public function save() {

		if (0 === $this->_count) {
			$this->_logger->addInfo('Saving... buffer is empty');
			return 0;
		}

		try {
			$cnt = $this->_save();
			$this->reset();
			$this->_logger->addInfo('Saving...', [ 'count' => $cnt ]);
			return $cnt;
		}
		catch (\Exception $e) {
			$this->_logger->addError('Saving... Exception!', [ 'exception' => $e ]);
			//todo бросать другой тип исключения
			throw new \Exception(
				"Ошибка при вставке данных:\n{$e->getMessage()}"
			);
		}
	}


	/**
	 * Устанавливает дополнительные опции
	 * ЕСЛИ был установлен $_sql, пересчитывает его
	 *
	 * @param int $option битовая маска констант
	 */
	public function setOptions($options) {
		$options = (int) $options;
		if ($options !== $this->_options) {
			$this->_options = $options;
			//	это может быть первый вызов, ещё до _setColumns
			//	в этом случае ещё нельзя вызывать _generateSql
			if (!$this->_sql) {
				$this->_generateSql();
			}
		}
	}

	/**
	 * При уничтожении объекта сохраняем оставшееся
	 *
	 * @access public
	 * @return void
	 */
	public function __destruct() {
		$this->_logger->addInfo('Destruct.');
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
			$this->_generateSql();
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

		$this->_logger->addDebug('Add record', [ 'count' => $this->_count ]);

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
	}

	private function _setAdapter(AdapterInterface $adapter) {

		if (is_null($this->_availableAdapters)) {
			$this->_db = $adapter;
			return true;
		}

		foreach ($this->_availableAdapters as $adapterClass) {
			if ($adapter instanceof $adapterClass) {
				$this->_db = $adapter;
				return true;
			}
		}

		throw new \Exception(sprintf(
			'Адаптер %s не поддерживается сейвером %s',
				get_class($adapter), get_class($this)));
	}

	/**
	 * Создаёт экземпляр сейвера
	 *
	 * @param AdapterInterface $adapter
	 * @param string $tableName
	 * @param string[] $columns поля, в кот. будут сохраняться данные
	 * если не указано - определятеся по первому запуску add()
	 *
	 * @return void
	 */

	public function __construct(AdapterInterface $adapter,
		$tableName, array $columns = null) {

		$this->_setAdapter($adapter);

		$this->_table = $this->_db->getTable($tableName);

		$this->_count = 0;

		$this->setLogger();
		$this->setBatchSize();

		if ($columns) {
			$this->_setColumns($columns);
			$this->_generateSql();
			$all = $this->_table->getColumns();
		}
	}

	/**
	 * Устанавливает логгер
	 *
	 * @param Logger $logger
	 * @return void
	 */
	public function setLogger(Logger $logger = null) {
		if (is_null($logger)) {
			$channel = get_class($this);
			$logger = new Logger($channel);
			$logger->pushHandler(new NullHandler);
		}

		$this->_logger = $logger;

		$this->_logger->addInfo('New logger',
			[ 'class' => get_class($logger) ]);
	}

	/**
	 * Возврвщает установленный логгер
	 *
	 * @return Logger
	 */
	public function getLogger() {
		return $this->_logger;
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
	public function setBatchSize($size = self::DEFAULT_BATCH_SIZE) {
		$size = (int) $size;
		if ($size >= self::MIN_BATCH_SIZE &&
			$size <= self::MAX_BATCH_SIZE) {

			$this->_batchSize = $size;

			$this->_logger->addInfo(sprintf(
				'New batch size is %d', $this->_batchSize));

			return $this->_batchSize;

		}
		throw new \OutOfRangeException(
			'Размер буфера должен быть в пределах от ' .
			self::MIN_BATCH_SIZE . ' до ' . self::MAX_BATCH_SIZE);
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
		return $this->getSize();
	}
}
