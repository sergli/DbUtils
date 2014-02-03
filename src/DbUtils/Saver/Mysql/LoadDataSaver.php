<?php

namespace DbUtils\Saver\Mysql;

use DbUtils\Table\MysqlTable;
use DbUtils\Saver\Mysql\AbstractMysqlSaver;

/**
 * Загружает записи в таблицу, используя
 * временный файл и синтаксис load data infile ...
 *
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
class LoadDataSaver extends AbstractMysqlSaver
{
    /**
     * Файл, в кот. записываются данные
	 * И из которого они будут загружаться в mysql
     *
     * @var \SplFileObject
     */
    private $_file;

    /**
     * Размер пакета данных.
	 * По умолчанию неограничен
     *
     * @var int
     * @access protected
     */
    protected $_batchSize = 0;

	protected function _init()
	{
		//	Вызов деструктора по <C-c>
		//	NOTE: обнуляет ранее объявленный обработчик
		pcntl_signal(SIGINT, function() { exit; });

		$this->_createTempFile();
	}

	/**
	 * Имя используемого временного файла
	 *
	 * @access public
	 * @return string
	 */
	public function getFileName()
	{
		return $this->_file->getPathName();
	}

	protected function _quote($column, $value)
	{
		if (null === $value)
		{
			return '\N';
		}

		if (is_bool($value))
		{
			return (int) $value;
		}

		if (is_numeric($value))
		{
			return $value;
		}

		if ('\N' === $value)
		{
			return '\\\N';
		}

		//todo или всё же addcslashes ?
		$value = str_replace(
			["\\", "\0", "\t", "\n"],
			["\\\\", "\\\0", "\\\t", "\\\n"],
			$value
        );
		//$value = addcslashes($value, "\0\n\t\\");

		return $value;
    }


	protected function _reset()
	{
		$this->_file->ftruncate(0);
        $this->_count = 0;;
    }


	protected function _generateSql()
	{
        $sql = 'LOAD DATA';
		if ($this->_options & static::OPT_LOW_PRIORITY)
		{
            $sql .= ' LOW_PRIORITY';
        }
		else if ($this->_options & static::OPT_CONCURRENT)
		{
            $sql .= ' CONCURRENT';
        }
        $sql .= " INFILE '" . $this->_file->getPathName() . "'";

		if ($this->_options & static::OPT_IGNORE)
		{
			$sql .= ' IGNORE';
		}
		$sql .= ' INTO TABLE ' . $this->_table->getFullName();
		$sql .= "\n(\n\t" . implode(",\n\t",
			array_keys($this->_columns)) . "\n)";

		$this->_sql = $sql;
		unset($sql);
	}


	public function _add(array $record)
	{
		$this->_file->fwrite(implode("\t", $record) . "\n");
    }

	public function _save()
	{
		//todo	или выполнять всё же?
		/*
		$this->_db->query('SET SESSION net_write_timeout := 1200');
		$this->_db->query(
			'SET SESSION table_lock_wait_timeout := 600');
		*/

		pcntl_signal_dispatch();

		$resultMode = \MYSQLI_STORE_RESULT;

		if ($this->_options & self::OPT_ASYNC)
		{
			@$this->_db->reap_async_query();
			$resultMode = \MYSQLI_ASYNC;
		}

		$this->_db->query($this->_sql, $resultMode);

		unset($sql);
    }

	public function __destruct()
	{
        parent::__destruct();

		if (!is_object($this->_file))
		{
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
	private function _createTempFile()
	{
		$dirName = sys_get_temp_dir();
		$fileName = uniqid('PHP.' .
			$this->_table->getFullName() . '_');
		$fileName = $dirName . '/' . $fileName . '.txt';

		$this->_file = new \SplFileObject($fileName, 'a+b');
		//	Не хотелось бы, чтоб другой php-процесс запорол файл
		if ($this->_file->flock(LOCK_EX))
		{
			$this->_file->ftruncate(0);
		}
		else
		{
			throw new \Exception(sprintf(
				'Couldn\'t lock file: %s', $fileName));
		}
	}
}
