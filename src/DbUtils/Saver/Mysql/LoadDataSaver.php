<?php

namespace DbUtils\Saver\Mysql;

use DbUtils\Adapter\MysqlAdapterInterface;
use DbUtils\Saver\AbstractSaver;
use DbUtils\Table\MysqlTable;

/**
 * Загружает записи в таблицу, используя
 * временный файл и синтаксис load data infile ...
 *
 * @uses AbstractSaver
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
class LoadDataSaver extends AbstractSaver {

	protected $_availableAdapters = null;

    /**
     * Файл, в кот. записываются данные
	 * И из которого они будут загружаться в mysql
     *
     * @var \SplFileObject
     */
    private $_file;

	/**
	 * Доп. опции
	 * По умолчанию INSERT CONCURRENT
	 *
	 * @var int
	 * @access protected
	 */
	protected $_options = 5;

    /**
     * Размер пакета данных.
	 * По умолчанию неограничен
     *
     * @var int
     * @access protected
     */
    protected $_batchSize = 0;

	/**
	 * @type int добавляет к запросу слова LOW_PRIORITY
	 */
	const OPT_LOW_PRIORITY = 1;

	/**
	 * @type int добавляет к запросу слово CONCURRENT
	 */
	const OPT_CONCURRENT = 2;

	/**
	 * @type int добавляет к запросу слово IGNORE
	 */
	const OPT_IGNORE = 4;

	/**
	 * @type int добавляет к запросу слово DELAYED
	 */
	const OPT_DELAYED = 8;

	public function __construct(MysqlAdapterInterface $adapter,
		$tableName, array $columns = []) {

		//	Вызов деструктора по <C-c>
		//	NOTE: обнуляет ранее объявленный обработчик
		pcntl_signal(SIGINT, function() {
			exit;
		});

		parent::__construct($adapter, $tableName, $columns);

		$this->_createTempFile();
	}

	/**
	 * Имя используемого временного файла
	 *
	 * @access public
	 * @return string
	 */
	public function getFileName() {
		return $this->_file->getPathName();
	}

	protected function _quote($column, $value) {
		if (null === $value) {
			return '\N';
		}

		if (is_bool($value)) {
			return (int) $value;
		}

		if (is_numeric($value)) {
			return $value;
		}

		if ('\N' === $value) {
			return '\\\N';
		}

		//todo или всё же addcslashes ?
		$value = str_replace(
			["\\", "\0", "\t", "\n"],
			["\\\\", "\\\0", "\\\t", "\\\n"],
			$value
        );
//		$value = addcslashes($value, "\0\n\t\\");

		return $value;
    }


	protected function _reset() {
		$this->_file->ftruncate(0);
        $this->_count = 0;;
    }


	protected function _generateSql() {

        $sql = 'LOAD DATA';
		if ($this->_options & static::OPT_LOW_PRIORITY) {
            $sql .= ' LOW_PRIORITY';
        }
        else if ($this->_options & static::OPT_CONCURRENT) {
            $sql .= ' CONCURRENT';
        }
        $sql .= " INFILE '{$this->_file->getPathName()}'";

		if ($this->_options & static::OPT_IGNORE) {
			$sql .= ' IGNORE';
		}
		$sql .= " INTO TABLE {$this->_table->getFullName()}";
		$sql .= "\n(\n\t" .
			implode(",\n\t", array_keys($this->_columns)) . "\n)";

		$this->_sql = $sql;
		unset($sql);
	}


	public function _add(array $record) {
		$this->_file->fwrite(implode("\t", $record) . "\n");
    }

	public function _save() {
		//todo	или выполнять всё же?
		/*
		$this->_db->query('SET SESSION net_write_timeout := 1200');
		$this->_db->query(
			'SET SESSION table_lock_wait_timeout := 600');
		*/

		pcntl_signal_dispatch();

		$this->_db->query($this->_sql);

		if ( $info = $this->_db->info() ) {

			return ($info['Records'] - $info['Skipped']);
		}

		return $this->_db->getAffectedRows();
    }

    public function __destruct() {

        parent::__destruct();

		if (!is_object($this->_file)) {
			return;
		}

		$this->_file->flock(LOCK_UN);

		$fileName = $this->_file->getPathName();
		$this->_file = null;
        @unlink($fileName);

		$this->_logger->addNotice(sprintf(
			'Remove temp file: %s', $fileName));
    }

	/**
	 * Создаёт и открывает для записи файл
	 * Имя уникальное - исп. uniqid()
	 *
	 * @access private
	 * @return void
	 * @see $_file
	 */
	private function _createTempFile() {
		$dirName = sys_get_temp_dir();
		$fileName = uniqid('PHP.' .
			$this->_table->getFullName() . '_');
		$fileName = $dirName . '/' . $fileName . '.txt';

		$this->_file = new \SplFileObject($fileName, 'a+b');
		//	Не хотелось бы, чтоб другой php-процесс запорол файл
		if ($this->_file->flock(LOCK_EX)) {
			$this->_file->ftruncate(0);
		}
		else {
			throw new \Exception(sprintf(
				'Couldn\'t lock file: %s', $fileName));
		}
	}
}
