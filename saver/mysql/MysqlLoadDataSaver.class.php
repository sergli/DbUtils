<?php

namespace db_utils\saver\mysql;

use db_utils\saver\Saver,
	db_utils\table\mysql\MysqlTable;

require_once __DIR__ . '/../Saver.class.php';
require_once __DIR__ . '/../../table/mysql/MysqlTable.class.php';

class MysqlLoadDataSaver extends Saver {

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

		$value = str_replace(
			["\\", "\0", "\t", "\n"],
			["\\\\", "\\\0", "\\\t", "\\\n"],
			$value
        );
//		$value = addcslashes($value, "\0\n\t\\");

		return $value;
    }

	/**
	 * Конструктор
	 * 
	 * @param MysqlTable $table 
	 * @param array $columns 
	 * @access public
	 * @return void
	 */
	public function __construct(MysqlTable $table, array $columns =null) {
		parent::__construct($table, $columns);

		$this->_createTempFile();
	}


	public function reset() {
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
	}


    public function _add(array $record) {
		$this->_file->fwrite(implode("\t", $record) . "\n");
    }

	public function _save() {
		/*
		$this->_db->query('SET SESSION net_write_timeout := 1200');
		$this->_db->query(
			'SET SESSION table_lock_wait_timeout := 600');
		*/
		
		$this->_db->query($this->_sql);
		$info = $this->_db->info();

		return ($info['Records'] - $info['Skipped']);
    }

    public function __destruct() {

        parent::__destruct();
		
		if (!is_object($this->_file)) {
			return;
		}

		$fileName = $this->_file->getPathName();
		$this->_file = null;
        @unlink($fileName);
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
		$fileName = uniqid('PHP.' . $this->_table->getFullName() . '_');
		$fileName = $dirName . '/' . $fileName . '.txt';

		$this->_file = new \SplFileObject($fileName, 'w');
	}
}
