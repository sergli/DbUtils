<?php

namespace autocomplete\complete\generate\utils\mysqli;

die('TODO');

require_once __DIR__ . '/../../init.php';


/**
 * Сохраняет данные в указанной таблице, используя load data infile
 * @author Lisenkov Sergey <sergli@nigma.ru>
 */
class MysqliLoadDataSaver extends MysqliSaver {
    
    /**
     * Путь к временному файлу с данными
     *
     * Играет роль буфера. Необходимы права mysql на чтение этого файла
     * Удаляется при разрушении $this 
     * @see __destruct()
     * @see truncate()
     * @var string
     * @access private
     */
    private $infile = null;
    
    /**
     * Файловый дескриптор
     * @see $infile
     * @var resource
     * @access private
     */
    private $fin = null;

    /**
     * Добавляет к запросу LOAD DATA указание LOW_PRIORITY
     *
     * Взаимоисключено с CONCURRENT
     * @var boolean
     * @access public
     * @see $concurrent
     */
    public $lowPriority = false;
    
    /**
     * Добавляет к запросу LOAD DATA указание CONCURRENT
     *
     * Взаимоисключено с LOW_PRIORITY
     * @var boolean
     * @access public
     * @see $low_priority
     */
    public $concurrent = true;

    /**
     * Макс. размер файла
     *
     * При превышении данные записываются в таблицу
     * Если 0, то размер файла неограничен
     * @var int
     * @access private
     * @see $infile
     */
    protected $chunkSize = 0;

    /**
     * Файл недоступен для записи
     */
    const E_FILE_NOT_WRITABLE = 13;

    /**
     * @param Mysqli $mysqli
     * @param string|array $structure
     * @param string $file_prefix optional префикс файла
     * @access public
     */
    public function __construct(Mysqli $mysqli, 
        $table = null, $structure = null, $file_prefix = null) {
        parent::__construct($mysqli, $table, $structure);
        
        $this->setInfile($file_prefix);
    }

    /**
     * Усекает файл, обнуляет счетчик строк
     * @see $infile
     * @access private
     */
    public function truncate() {
        if (!is_null($this->fin)) {
            ftruncate($this->fin, 0);
        }
        $this->count = 0;;
    }

    /*
     * Устанавливает файл
     *
     * Слегка проверяет, что передано корректное имя,
     * что файл доступен для записи.
     * @param string $file_prefix optional префикс файла
     * @access private
     */
    private function setInfile($file_prefix = null) {

        if (is_null($file_prefix)) $file_prefix = getmypid();
        $file_prefix = str_replace(
            array('..', '/'), '', $file_prefix
        );

        $infile = str_replace(
            array('..', '/'), '', $this->table
        );

        $infile = $file_prefix . '_' . $infile . '.txt';
       	$tmp_dir = \NigmaConfig::getInstance()->autocomplete_tmp_dir;
        $infile = $tmp_dir . $infile;

        $fin = fopen($infile, 'ab');
        if (false === $fin ) {
            throw new MysqliSaverException(
                'Файл ' . $infile . ' недоступен для записи', 
                self::E_FILE_NOT_WRITABLE
            );
        }

        $this->infile = $infile;
        $this->fin = $fin;

        $this->truncate();
    }

    /**
     * Добавляет запись в файл
     * @param array $row
     * @access public
     */
    public function addRow(Array $row) {
        $record = $this->cleanRow($row, function($text) {
			if (is_null($text)) return '\N';
			// ===  !!!
			if ('\N' === $text) return '\\N';
			//	только так
			$text = str_replace(
                array("\\", "\0", "\t", "\n"),
                array("\\\\", "\\\0", "\\\t", "\\\n"),
                $text
            );
//			$text = addcslashes($text, "\0\n\t\\");
			return $text;
        });
        unset($row);

        fwrite(
            $this->fin,
            implode("\t", $record) . "\n"
        );

        $this->count++;
		
		//	костыль против mysql server has gone
		//if (0 === $this->count % 100000) {
		//	$this->mysqli->ping();
		//}
        //  иногда нужно поберечь память
        if ($this->chunkSize > 0 && $this->count >= $this->chunkSize) {
            $this->save();
        }
    }

    /**
     * Сохраняет данные из файла в базу
     * @access public
     */
    public function save() {
        $this->progress && fwrite(STDERR, "\nsave to {$this->table}.. ");

        $mysqli = $this->mysqli;
        
        if ($this->count == 0) return 0;

        $sql = 'LOAD DATA';
        if ($this->lowPriority) {
            $sql .= ' LOW_PRIORITY';
        }
        else if ($this->concurrent) {
            $sql .= ' CONCURRENT';
        }
        $sql .= " INFILE '" . $this->infile . "'";

		if ($this->insertIgnore) {
			$sql .= ' IGNORE';
		}
		$sql .= " INTO TABLE " . $this->table . 
        ' (`' . implode('`,`', $this->structure) . '`)';
        try {
            /*$mysqli->queries(<<<SQL
SET GLOBAL net_write_timeout := 1200;
SET GLOBAL table_lock_wait_timeout := 600;
SQL
            );
			*/
			$mysqli->ping();
            $mysqli->query($sql);
            unset($sql);
            $info = $mysqli->info();
            $records = $info['Records'] - $info['Skipped'];
            $this->progress && fwrite(STDERR, $records);
            $this->truncate();
            return $records;
        }
        catch (\mysqli_sql_exception $e) {
            throw new MysqliSaverException(
                $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * Уничтожает себя, оставшиеся в файле записи сохраняет в таблицу
     * @access public
     */
    public function __destruct() {
        parent::__destruct();
		if (is_resource($this->fin)) {
	        fclose($this->fin);
		}
        @unlink($this->infile);
    }
}

