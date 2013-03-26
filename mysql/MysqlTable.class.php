<?php

namespace autocomplete\complete\generate\utils\db\mysql;
use autocomplete\complete\generate\utils\db;

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/Mysql.class.php';
require_once __DIR__ . '/../RDBTable.class.php';

class MysqlTable extends db\RDBTable {

	protected function _getBaseInfo($tableName) {

		$regex = '/^(?:`?([a-z0-9_]+)`?\.)?([a-z0-9_]+)$/i';
		if (!preg_match($regex, $tableName, $matches)) {
			throw new db\RDBTableException("Плохое имя таблицы: $tableName");
		}
		$name = $matches[2];
		$schema = $matches[1];

		$tableName = $schema ? "$schema.$name" : $name;
		//	попробуем сразу и тек. базу узнать и сущ-ие таблицы проверить
		$sql = "SELECT DATABASE() FROM $tableName LIMIT 1;";
		try {
			$schema = $this->_db->fetchOne($sql);
			if (is_null($schema)) { // таблица есть, но пустая
				$schema = $this->_db->fetchOne("SELECT DATABASE();");
			}
		}
		catch (\mysqli_sql_exception $e) {
			if (1146 == $e->getCode()) {
				throw new db\RDBTableException(
					"Таблица $tableName не существует");
			}
			//	else throw
			throw $e;
		}

		return array(
			'schema' => $schema,
			'name' => $name
		);
	}

	protected function _getConstraints() {
		//	корявый sql из-за того, что 
		//	mysql не может нормально выполнить join без ключей
		$sql = <<<SQL
SELECT
	k.CONSTRAINT_NAME,
	k.COLUMN_NAME,
	k.REFERENCED_TABLE_NAME,
	k.REFERENCED_COLUMN_NAME,
	c.CONSTRAINT_TYPE
FROM
	INFORMATION_SCHEMA.KEY_COLUMN_USAGE k
	INNER JOIN (
		SELECT
			CONSTRAINT_NAME,
			CONSTRAINT_TYPE
		FROM
			INFORMATION_SCHEMA.TABLE_CONSTRAINTS
		WHERE
			TABLE_NAME = ?
			AND TABLE_SCHEMA = ?
	) c
USING (CONSTRAINT_NAME)
WHERE
	k.TABLE_NAME = ?
	AND k.TABLE_SCHEMA = ?
ORDER BY
	k.CONSTRAINT_NAME ASC,
	k.ORDINAL_POSITION ASC;
SQL;
		$stmt = $this->_db->prepare($sql);
		$stmt->bind_param('ssss', 
			$this->_name, $this->_schema, $this->_name, $this->_schema);
		$stmt->execute();
		$stmt->bind_result_array($row);

		$constraints = array();
		while ($stmt->fetch()) {
			$con = array();
			$name = $row['CONSTRAINT_NAME'];
			if (!isset($constraints[$name])) {
				$con['name'] = $name;
				switch ($row['CONSTRAINT_TYPE']) {
				case 'UNIQUE':
					$con['type'] = self::CONTYPE_UNIQUE;
					break;
				case 'PRIMARY KEY':
					$con['type'] = self::CONTYPE_PRIMARY;
					break;
				case 'FOREIGN KEY':
					$con['type'] = self::CONTYPE_FOREIGN;
					$con['ref_table'] = $row['REFERENCED_TABLE_NAME'];
					$con['ref_col'] = $row['REFERENCED_COLUMN_NAME'];
					break;
				}
				$con['columns'] = array();
				$constraints[$name] = $con;
			}
			
			$constraints[$name] ['columns'][] = $row['COLUMN_NAME'];
		}

		return $constraints;
	}

	protected function _getIndices() {

        $sql = "SHOW INDEX FROM {$this->getFullName()};";
		$r = $this->_db->query($sql);
		$indexes = array();
		while ($row = $r->fetch_assoc()) {
			$index = array();
			$name = $row['Key_name'];
			if (!isset($indexes[$name])) {
				$index['is_primary'] = ($name == 'PRIMARY');
				$index['is_unique'] = !$row['Non_unique'];
				$index['type'] = $row['Index_type'];
				$index['columns'] = array();

				$indexes[$row['Key_name']] = $index;
			}
			$indexes[$name]['columns'][$row['Column_name']] =
				$row['Sub_part'];
		}
		
		return $indexes;
    }


	public function _getColumns() {
		$sql = "SHOW COLUMNS FROM {$this->getFullName()};";
		$r = $this->_db->query($sql);
		$columns = array();
		while ($row = $r->fetch_assoc()) {
			$columns[$row['Field']] = $row['Type'];
		}

		return $columns;
	}


	public function __construct(Mysql $db, $tableName) {
		$this->_db = $db;
		$info = $this->_getBaseInfo($tableName);
		$this->_name = $info['name'];
		$this->_schema = $info['schema'];
	}
}


/*
$db = Mysqli::getInstance('clicklog');
$table = new MysqlTable($db, 'test');
var_dump('exists', MysqlTable::exists($db, 'clicklog.test'));
var_dump('indexes', $table->getIndices());
//var_dump('columns', $table->getColumns());
//var_dump('constraints', $table->getConstraints());
var_dump('pk', $table->getPrimaryKey());
var_dump('uniques', $table->getUniques());
$table->recalculate();
var_dump($table->getFullName(), $table->getName(), $table->getSchema());
*/
