<?php

namespace DbUtils\Table;

use DbUtils\Adapter\AdapterInterface;

class MysqlTable extends AbstractTable {

	protected function _getBaseInfo($tableName) {

		$regex = '/^(?:`?([a-z0-9_]+)`?\.)?([a-z0-9_]+)$/i';
		if (!preg_match($regex, $tableName, $matches)) {
			throw new \Exception("Плохое имя таблицы: $tableName");
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
		} //todo exception
		catch (\Exception $e) {
			if (1146 == $e->getCode()) {
				throw new \Exception(
					"Таблица $tableName не существует");
			}
			//	else throw
			throw $e;
		}

		return [
			'schema' => $schema,
			'name' => $name
		];
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
			$con = [];
			$name = $row['CONSTRAINT_NAME'];

			if (!isset($constraints[$name])) {
				$con['name'] = $name;
				$con['columns'] = [];
				$con['ref_columns'] = [];
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
					break;
				}
				$constraints[$name] = $con;
			}

			//	соберём имена колонок, участвующих в ограничении
			$constraints[$name]['columns'][] = $row['COLUMN_NAME'];
			//	для внешних ключей соберём и связанные колонки
			if ($ref_col = $row['REFERENCED_COLUMN_NAME']) {
				$constraints[$name]['ref_columns'][] = $ref_col;
			}
		}

		return $constraints;
	}

	protected function _getIndices() {

        $sql = "SHOW INDEX FROM {$this->getFullName()};";
		$rows = $this->_db->fetchAll($sql);
		$indices = array();
		foreach ($rows as $row) {
			$index = array();
			$name = $row['Key_name'];
			if (!isset($indices[$name])) {
				$index['is_primary'] = ($name == 'PRIMARY');
				$index['is_unique'] = !$row['Non_unique'];
				$index['type'] = $row['Index_type'];
				$index['columns'] = array();

				$indices[$row['Key_name']] = $index;
			}
			$indices[$name]['columns'][$row['Column_name']] =
				$row['Sub_part'];
		}

		return $indices;
    }


	public function _getColumns() {
		$sql = "SHOW COLUMNS FROM {$this->getFullName()};";
		$rows = $this->_db->fetchAll($sql);
		$columns = array();
		foreach ($rows as $row) {
			$columns[$row['Field']] = $row['Type'];
		}

		return $columns;
	}
}

