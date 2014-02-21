<?php

namespace DbUtils\Saver;

use DbUtils\Adapter\AdapterInterface;
use DbUtils\Adapter\AsyncExecInterface;
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
 * @uses SaverInterface
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
abstract class AbstractSaver implements SaverInterface,
	\ArrayAccess
{

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
	protected $_batchSize =
		self::DEFAULT_BATCH_SIZE;

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

	/**
	 * @type int	0 - отмена автомат. сохранения
	 */
	const MIN_BATCH_SIZE = 0;
	const MAX_BATCH_SIZE = 500000;
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
	 * @return int кол-во реально добавленных записей
	 */
	abstract protected function _add(array $record);
	/**
	 * Формирует скелет sql-запроса.
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

	/**
	 * Дополнительный действия по инициализации класса
	 * до первого вызова _setColumns() и _generateSql()
	 *
	 * @return void
	 */
	protected function _initBeforeSql()
	{
	}

	public function getColumns()
	{
		return $this->_columns
			? array_keys($this->_columns)
			: null;
	}

	public function reset()
	{
		$this->_logger->addInfo('Reset saver');
		$this->_reset();
	}

	/**
	 * Сохраняет буфер, обнуляет его
	 *
	 * @access public
	 * @return void
	 * @throws SaverException
	 */
	public function save()
	{
		if (0 === $this->_count)
		{
			$this->_logger->addInfo('Saving: buffer is empty');
			return;
		}

		try
		{
			$ts = microtime(true);
			$this->_save();

			$this->_logger->addInfo('Saving', [
				'count' => $this->getSize(),
				'sql'	=>
					preg_replace('/[\n\t]/', ' ', $this->_sql),
				'time'	=> round(microtime(true) - $ts, 3)
			]);

			$this->reset();
		}
		catch (\Exception $e)
		{
			$this->_logger->addError('Saving: error', [
				'sql'	=>
					preg_replace('/[\n\t]/', ' ', $this->_sql),
				'time'	=> round(microtime(true) - $ts, 3)
			]);
			throw new SaverException(sprintf(
				'Error while saving data: %s',
				$e->getMessage()));
		}
	}

	/**
	 * Установить или снять опцию
	 *
	 * @param int $option одна из констант static::OPT_
	 * @param bool $switch установить/снять
	 * @return this
	 */
	protected function _setOption($option, $switch)
	{
		$option = (int) $option;

		switch ($switch)
		{
			case true:
				$this->_options |= $option;
				break;
			case false:
				$this->_options &= ~$option;
				break;
		}

		//	пересчитываем $_sql (кроме первого запуска)
		if (!$this->_sql && !empty($this->_columns))
		{
			$this->_generateSql();
		}

		return $this;
	}

	public function setOptAsync($val = true)
	{
		if (!$val || $this->_db instanceof AsyncExecInterface )
		{
			//	на всякий случай - если, например, неск. раз меняли
			$this->_db->wait();

			return $this->_setOption(static::OPT_ASYNC, $val);
		}

		$this->_logger->addWarning(sprintf(
			'Adapter %s does not support async execution',
			get_class($this->_db)));

		return $this;
	}
	/**
	 * При уничтожении объекта сохраняем оставшееся
	 *
	 * @access public
	 * @return void
	 */
	public function __destruct()
	{
		$this->_logger->addInfo('Destruct');
		$this->save();

		if ($this->_options & static::OPT_ASYNC)
		{
			//	ждём завершения последнего запроса
			$this->_db->wait();
		}
	}

	/**
	 * Подготавливает запись
	 *
	 * @param array $row
	 * @access private
	 * @return array
	 * @see _quote()
	 * @throws SaverException
	 */
	private function _cleanRow(array $row)
	{
		$record = array();

		foreach ($this->_columns as $field => $dataType)
		{
			if (!array_key_exists($field, $row))
			{
				throw new SaverException(sprintf(
					'Необходимо поле %s у записи \n%s',
						$field, print_r($row, true)));
			}
			$record[$field] =
				$this->_quote($field, $row[$field]);
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
	 * @throws SaverException
	 */
	public function add(array $row)
	{
		/*
			Уже начинаем сохранять данные, но
			столбцы до сих пор не указаны -
			значит устанавливаем по умолчанию - все
		*/
		if (empty($this->_columns))
		{
			$this->_setColumns(array_keys($row));
		}

		if (count($row) !== count($this->_columns))
		{
			throw new SaverException(sprintf(
				'Неверное кол-во полей у записи\n%s',
					print_r($row, true)));
		}

		$record = $this->_cleanRow($row);
		unset($row);

		$this->_add($record);

		$this->_count++;

		$this->_logger->addDebug('Add record', [
			'record' => $record,
			'count' => $this->_count
		]);

		if (0 !== $this->_batchSize
			&& $this->_count >= $this->_batchSize)
		{
			$this->save();
		}

		return true;
	}

	/**
	 * Устанавливает, в какие поля будут сохраняться данные.
	 *
	 * @param string[] $columns названия полей
	 * @access protected
	 * @return void
	 * @see _generateSql()
	 * @throws \Exception
	 */
	protected function _setColumns(array $columns)
	{
		if (empty($columns))
		{
			throw new SaverException('Пустой массив полей');
		}

		$all = $this->_table->getColumns();
		$columns = array_unique($columns);
		foreach ($columns as $column)
		{
			if (!is_string($column))
			{
				throw new SaverException(sprintf(
					'Поле не является строкой: %s', $column));
			}
			if (!isset($all[$column]))
			{
				throw new SaverException(sprintf(
					'Поле не существует: %s', $column));
			}

			//	запоминаем тип
			$this->_columns[$column] = $all[$column];
		}

		$this->_generateSql();
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
		$tableName, array $columns = null)
	{
		$this->_db = $adapter;

		$this->_count = 0;

		$this->_logger = new Logger('', [ new NullHandler ]);

		$this->_initTable($tableName, $columns);
	}

	/**
	 * Инициализация и считывание метаданных о таблице
	 *
	 * @param string $tableName
	 * @param array $columns
	 * @return void
	 */
	private function _initTable($tableName,
		array $columns = null)
	{
		$this->_table = $this->_db->getTable($tableName);

		$this->_initBeforeSql();

		//	указан конкретный набор столбцов
		if ($columns)
		{
			$this->_setColumns($columns);
		}
	}

	/**
	 * Устанавливает логгер
	 *
	 * @param Logger $logger
	 * @return void
	 */
	public function setLogger(Logger $logger = null)
	{
		if (is_null($logger))
		{
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
	public function getLogger()
	{
		return $this->_logger;
	}

	/**
	 * Возвращает текущий размер буфера
	 *
	 * @access public
	 * @return int
	 */
	public function getSize()
	{
		return $this->_count;
	}

	/**
	 * Возвращает размер порции
	 *
	 * @access public
	 * @return int
	 */
	public function getBatchSize()
	{
		return $this->_batchSize;
	}

	/**
	 * Устанавливает размер порции (0 - бесконечный)
	 *
	 * @param int $size новый размер
	 * @access public
	 * @return int новый размер
	 * @throws \OutOfRangeException
	 */
	public function setBatchSize($size)
	{
		$size = (int) $size;
		if ($size >= static::MIN_BATCH_SIZE &&
			$size <= static::MAX_BATCH_SIZE)
		{

			$this->_batchSize = $size;

			$this->_logger->addInfo(sprintf(
				'New batch size is %d', $this->_batchSize));

			return $this->_batchSize;

		}
		throw new \OutOfRangeException(
			'Размер буфера должен быть в пределах от ' .
			self::MIN_BATCH_SIZE . ' до ' . static::MAX_BATCH_SIZE);
	}


	//////////////////////	ArrayAccess	//////////////////////////

	/**
	 * @param mixed $offset
	 * @return void
	 * @throws \OutOfBoundsException
	 */
	public function offsetExists($offset)
	{
		throw new \OutOfBoundsException(
			'Чтение внутренних данных запрещено реализацией'
		);
	}

	/**
	 * @param mixed $offset
	 * @return void
	 * @throws \OutOfBoundsException
	 */
	public function offsetGet($offset)
	{
		throw new \OutOfBoundsException(
			'Чтение внутренних данных запрещено реализацией');
	}

	/**
	 * @param mixed $offset
	 * @param array $row
	 * @return void
	 * @throws \OutOfBoundsException
	 */
	public function offsetSet($offset, $row)
	{
		if (is_null($offset))
		{	//	в конец
			$offset = $this->_count;
		}
		else
		{
			$offset = (int) $offset;
		}
		if ($offset !== $this->_count)
		{
			throw new \OutOfBoundsException(
				'Добавить новую запись можно только в конец очереди');
		}
		$this->add($row);
	}

	/**
	 * @param mixed $offset
	 * @return void
	 * @throws \OutOfBoundsException
	 */
	public function offsetUnset($offset)
	{
		throw new \OutOfBoundsException(
			'Удаление из внутренних данных запрещено реализацией');
	}

	/////////////////////	Countable	/////////////////////////

	/**
	 * @return int
	 */
	public function count()
	{
		return $this->getSize();
	}
}
