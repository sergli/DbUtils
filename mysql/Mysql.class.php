<?php

namespace autocomplete\complete\generate\utils\db\mysql;

Error_Reporting(E_ALL);

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/MysqlException.class.php';
require_once __DIR__ . '/MysqlTable.class.php';


/**
 * Синглтон, расширяет функц-ть mysqli 
 * 
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
class Mysql extends \Mysqli {
    /**
     * @var mysqli[]
     */
    protected static $instances = array();
    /**
     * Опции для установки соединения
     * @var array $options (host, user, password, charset)
     * @access private
	 * @todo fixme нафиг они нужны
     */
	protected static $options = array();

	/**
	 * Устанавливает соединение
	 * Если параметры не указаны, использует дефолтные
	 * 
	 * @param string $dbname имя базы данных
	 * @access private
	 * @return void
	 * @throws MysqlException
	 */
	private function __construct($dbname = '') {
		$o = self::$options;
        $defaults = 
			\NigmaConfig::getInstance()->Autocomplete['LocalMysql'];
		
		$o['host'] = $o['host'] ?: $defaults['host'];
		$o['user'] = $o['user'] ?: $defaults['user'];
		$o['password'] = $o['password'] ?: $defaults['password'];
		$o['charset'] = $o['charset'] ?: 'utf8';
		$o['dbname'] = $dbname ?: $defaults['dbname'];
		

		try {
        	\mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
	        //$mysqli->options(
			//	MYSQLI_READ_DEFAULT_GROUP, 'max_allowed_packet=50M');
	        parent::__construct($o['host'], $o['user'], 
				$o['password'], $o['dbname']);
    		
			$this->set_charset($o['charset']);
		}
		catch (\mysqli_sql_exception $e) {
			throw new MysqlException("Не удалось установить соединение: \n" .
				$e->getMessage());
		}
	}

    private function __clone() { }
	
    private function __wakeup() { }
	
    private function __sleep() { }

    /**    
     * Возвращает соединение к заданной БД
	 *
	 * Можно открыть несколько, если вызывать метод с различными $tag
	 *
	 * @param string $dbname имя базы
	 * @param string $tag тэг соединения в пуле
     * @access public
	 * @return Mysqli
     */
    public static function getInstance($dbname = '', $tag = 1) {
		$dbname = (string) $dbname;
		$tag = (string) $tag;
		if (!isset(self::$instances[$tag])) {
			self::$instances[$tag] = @new self($dbname);
		}
		return self::$instances[$tag];
	}

    /**
     * Устанавливает параметры соединения
     *
     * Этот метод должен быть вызван до любого getInstance!
     * @access public
     * @param array $opt
     * @static
	 * @fixme
     */
	public static function setOptions(array $opt) {
		if (!empty(self::$instances)) {
			throw new MysqlException(
				"Этот метод не может быть вызван после установки соединения!");
		}
		self::$options = array_merge(self::$options, $opt);
	}

    /**
     * Расширение для стандартного mysqli::stmt()
     * @param string $sql  запрос, который нужно подготовить
     * @return MysqliStmt
     * @access public
     */
	public function prepare($sql) {
		require_once __DIR__ . '/MysqlStmt.class.php';
		return new MysqlStmt($this, $sql);
	}

    /**
     * Обертка \mysqli::$info
	 *
     * @return array массив с некоторыми фиксированными ключами
     * @access public
     */
	public function info() {
		$info = $this->info;

		$def = array(
			'Records' => 0,
			'Duplicates' => 0,
			'Warnings' => 0,
			'Skipped' => 0,
			'Deleted' => 0,
			'Rows matched' => 0,
			'Changed' => 0
		);

		if (empty($info)) {
			return $def;
		}
		$pattern = '/(' . implode('|', array_keys($def)) .'): (\d+)/';
		preg_match_all($pattern, $info, $matches);
		$info = array_combine($matches[1], $matches[2]);
		
		return array_merge($def, $info);
	}
	
    /**
     * Обертка \mysqli::multi_query
	 *
     * @see \mysqli::multi_query
     * @param string $sql строка с разделенными ; запросами
     * @access public
	 * @throws MysqlException
	 * @return void
     */
    public function queries($sql) {
        $this->multi_query($sql);
        while($this->more_results() && $this->next_result());

        if ($this->error) {
            throw new MysqlException(
				"Ошибка при выполнении мульти-запроса:\n{$this->error}");
        }
    }


	public function tableExists($tableName) {
		return MysqlTable::exists($this, $tableName);
	}

    public function getTableColumns($tableName) {
		return $this->getTable($tableName)->getColumns();
    }

	public function getTableIndices($tableName) {
		return $this->getTable($tableName)->getIndices();
	}

    /**
     * Архивирует таблицу
     * Сбрасывает ключи и устанавливает ENGINE=ARCHIVE
	 *
     * @param string|array $tables имя таблицы или массив имён
     * @return boolean
     * @access public
	 * @return void
     */
    public function archive($tables) {
        if (!is_array($tables)) {
			$tables = array( $tables );
		}
		foreach ($tables as $table) {
	        $this->dropAllIndexes($table);
	        $this->query("ALTER TABLE $table ENGINE=ARCHIVE;");
		}
    }


    /**
     * Создаёт пустую таблицу в соответствии с шаблоном
     * Если таблица уже существует, предварительно удаляет её
     *
     * @NOTE: имена таблиц используются КАК ЕСТЬ
     * @param string $table имя таблицы
     * @param string $layout имя таблицы-шаблона
     * @access public
     */
    public function createTableLike($table, $layout) {
        $this->query("DROP TABLE IF EXISTS $table");
		$this->query("CREATE TABLE $table LIKE $layout");
    }
       
    /**
     * Сбрасывает у таблицы все индексы
	 *
	 * @NOTE проверка имени таблицы не производится
     * @param string $table имя таблицы
     * @return boolean false в случае ошибки, true иначе
     * @access public
	 * @fixme @todo убрать
     */
    public function dropAllIndexes($table) {
           
        $indexes = $this->getAllIndexes($table);

        if (empty($indexes)) {
            return true;
        }

        $keys = array_keys($indexes);

        $sql = "ALTER TABLE $table";
        $br = '';
        foreach($keys as $key) {
            $sql .= $br;
            $br = ',';
            if ('PRIMARY' == $key) {
                $sql .= ' DROP PRIMARY KEY';
            }
            else {
                $sql .= ' DROP INDEX `' . $key . '`';
            }
        }

        return !is_null($this->query($sql));
    }

    /**
     * Возвращает значение единственной ячейки результатов
     *
	 * @param string $sql SQL-запрос
     * @return mixed значение этой ячейки
     * @access public
     */
    public function fetchOne($sql) {
        $row = $this->fetchRow($sql);
        if (is_null($row)) {
            return null;
        }
        return array_shift($row);
    }

    /**
     * Возвращает массив - единственную строку результата
	 *
	 * @param string $sql SQL-запрос
     * @return array
     * @access public
     */
    public function fetchRow($sql) {
        $ar = $this->query($sql, MYSQLI_USE_RESULT)->fetch_assoc();
        if (is_null($ar)) {
            return array();
        }
        return $ar;
    }

	/**
	 * Возвращает весь результирующий набор данных
	 * 
	 * @param string $sql SQL-запрос
	 * @access public
	 * @return array
	 */
	public function fetchAll($sql) {
		$r = $this->query($sql, MYSQLI_USE_RESULT);
		if (!$r) {
			return array();
		}
		
		//	mysqlnd only
		if (method_exists('mysqli', 'fetch_all')) {
			return $r->fetch_all(MYSQLI_ASSOC);
		}

		$arr = array();
		while($row = $r->fetch_assoc()) {
			$arr[] = $row;
		}

		return $arr;
	}

	public function fetchPairs($sql) {
		$r = $this->query($sql, MYSQLI_USE_RESULT);
		$arr = array();
		while ($row = $r->fetch_row()) {
			$arr[$row[0]] = $arr[$row[1]];
		}
		return $arr;
	}
	
	public function getTable($tableName) {
		return new MysqlTable($this, $tableName);
	}
}

