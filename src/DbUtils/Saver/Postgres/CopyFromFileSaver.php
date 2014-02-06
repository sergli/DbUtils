<?php

namespace DbUtils\Saver\Postgres;

use DbUtils\Table\PostgresTable;
use DbUtils\Saver\Postgres\AbstractPostgresSaver;

class CopyFromFileSaver extends AbstractPostgresSaver
{
	protected $_file;

	protected $_batchSize = 0;

	protected $_sql = '';

	protected function _save()
	{
		pcntl_signal_dispatch();

		//	SET DateStyle TO ISO  ??

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

	protected function _generateSql()
	{
		$sql = <<<'EOT'
COPY {tableName}
(
	{columns}
)
FROM '{fileName}'
WITH
(
	FORMAT CSV,
	DELIMITER E'\t',
	NULL '',
	QUOTE '"',
	ESCAPE '"'
);
EOT;
		$sql = strtr($sql, [
			'{tableName}'	=> $this->_table->getFullName(),
			'{columns}'		=>
				implode(",\n\t", array_keys($this->_columns)),
			'{fileName}'	=> $this->getFileName()
		]);

		$this->_sql = $sql;
	}

	protected function _quote($column, $value)
	{
		if (null === $value)
		{
			return '\N';
		}

		switch ($this->_columns[$column])
		{
			case 'bytea':
				//	"\x4142" - это hex от AB
				//	"x4142" - а так это escape от x4142
				$value = '"\x' . bin2hex($value) . '"';
				break;

			default:
				$value = '"' .
					str_replace('"', '""', $value) . '"';
				break;
		}

		return $value;

	}

	protected function _add(array $record)
	{
		$line = implode("\t", $record);
		$this->_file->fwrite($line . "\n");
    }


	protected function _init()
	{
		pcntl_signal(SIGINT, function() { exit; });

		$this->_createTempFile();
	}

	private function _createTempFile()
	{
		$dirName = sys_get_temp_dir();
		$fileName = uniqid('PHP.' .
			$this->_table->getFullName() . '_');
		$fileName = $dirName . '/' . $fileName . '.txt';

		$this->_file = new \SplFileObject($fileName, 'a+b');

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

	protected function _reset()
	{
		$this->_file->ftruncate(0);
        $this->_count = 0;;
    }

	public function getFileName()
	{
		return $this->_file->getPathName();
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

}
