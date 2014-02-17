<?php

namespace DbUtils\Saver;


trait LoadFileTrait
{
	private $_file;

	private $_delimiter = "\t";

	protected function _initBeforeSql()
	{
		//	Вызов деструктора по <C-c>
		//	NOTE: обнуляет предыдущий обработчик
		pcntl_signal(SIGINT, function()
		{
			exit;
		});

		$this->_createTempFile();
	}

	protected function _reset()
	{
		$this->_file->ftruncate(0);
        $this->_count = 0;;
    }

	protected function _add(array $record)
	{
		$line = implode($this->_delimiter, $record);
		$this->_file->fwrite($line . "\n");
    }

	protected function _save()
	{
		/*
			NOTE:
				1. для mysql, возможно, придётся повысить
				значения net_write_timeout,
					table_lock_wait_timeout и т.п
				2. для postgresql - выставить DateStyle = ISO
		*/

		pcntl_signal_dispatch();

		if ($this->_options & self::OPT_ASYNC)
		{
			$this->_db->wait();
			$this->_db->asyncExec($this->_sql);
		}
		else
		{
			$this->_db->query($this->_sql);
		}
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
}
