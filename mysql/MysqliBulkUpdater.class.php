<?php

namespace autocomplete\complete\generate\utils\mysqli;

die('TODO');
require_once __DIR__ . '/../../init.php';

class MysqliBulkUpdater extends MysqliBulkInsertSaver {

    /**
     * PRIMARY KEY таблицы
     * 
     * @var array
     * @access private
     */
    private $pk = array();

    const E_NONE_PK = 71;

    public function __construct(Mysqli $mysqli,$table,$structure = null) {

        parent::__construct($mysqli, $table, $structure);

        $indexes = $mysqli->getAllIndexes($table);
        if (!array_key_exists('PRIMARY', $indexes)) {
            throw new MysqliSaverException(
                'У таблицы ' . $table . ' нет первичного ключа',
                self::E_NONE_PK
            );
        }

        $this->pk = array_keys($indexes['PRIMARY']);
    }

    public function save() {
        $this->progress && fwrite(STDERR, "\nupdate {$this->table}.. ");

        $mysqli = $this->mysqli;
        
        if ($this->count == 0) $this->count = count($this->values);
        if ($this->count == 0) return 0;

        $values = "\n" . implode(",\n", $this->values);

        $this->truncate();  //  обнуляем хранилище

        $sql = 'INSERT';
        
        if ($this->insertDelayed) {
            $sql .= ' DELAYED';
        }

        $sql .= ' INTO ' . $this->table;

        if ($this->structure) {
            $sql .= "\n (" . implode(',', $this->structure) . ')';
        }

        $sql .= "\n VALUES {$values} ";

        $sql .= "\nON DUPLICATE KEY UPDATE";
        $br = "\n";

        foreach(array_diff($this->structure, $this->pk) as $field) {
            $sql .= $br . $field . ' = VALUES(' . $field . ')';
            $br = ', ';
        }

        unset($values);

        try {
            $mysqli->query($sql);
            unset($sql);
            $info = $mysqli->info();
            $records = $info['Duplicates'];
            $this->progress && fwrite(STDERR, $records);
            return $records;
        }
        catch (\mysqli_sql_exception $e) {
            throw new MysqliSaverException(
                $e->getMessage(),
                $e->getCode()
            );
        }
    }
}

