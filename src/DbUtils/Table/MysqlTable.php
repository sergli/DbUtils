<?php

namespace DbUtils\Table;

use DbUtils\Adapter\MysqlAdapterInterface;

class MysqlTable extends AbstractTable
{
	public function __construct(MysqlAdapterInterface $db,
		$tableName)
	{

		parent::__construct($db, $tableName);
	}

	/**
	 * @param string $tableName
	 * @return string[]
	 * @throws TableNotExistsException
	 */
	protected function _getBaseInfo($tableName)
	{
		/*
			TODO:
			Это не общий регексп. Различные экзотические
			варианты названий таблиц он не учитывает.
		*/
		$regex = '/^(?:`?([a-z0-9_]+)`?\.)?([a-z0-9_]+)$/i';
		if (!preg_match($regex, $tableName, $matches))
		{
			throw new TableNotExistsException($tableName);
		}
		$name = $matches[2];
		$schema = $matches[1];

		$tableName = $schema ? "$schema.$name" : $name;
		/*
			Сразу и узнаём текущую базу, и проверяем сущ-ие таблицы.
			union select 1 - на случай, если таблица пустая
		*/
		$sql = 'SELECT DATABASE() db ' .
			' UNION SELECT NULL FROM ' . $tableName .
			' ORDER BY db DESC LIMIT 1';
		try
		{
			$schema = $this->_db->fetchOne($sql);
		}
		catch (\Exception $e)
		{
			/*
				Может быть mysqli_sql_exception или
				PDOException, в зависимости от драйвера
				соотв-ет sqlstate 42S02
			*/

			if (preg_match("/Table .* doesn't exist/i", $e->getMessage()))
			{
				throw new TableNotExistsException($tableName);
			}

			//	Неожиданное исключение бросаем дальше
			throw $e;
		}

		return [
			'schema' => $schema,
			'name' => $name
		];
	}

	protected function _getConstraints()
	{
		//	корявый sql из-за того, что
		//	mysql не может нормально выполнить join без ключей
		$sql = "
		SELECT
			k.constraint_name,
			k.column_name,
			k.referenced_table_name,
			k.referenced_column_name,
			c.constraint_type
		FROM
			information_schema.key_column_usage k
			INNER JOIN (
				SELECT
					constraint_name,
					constraint_type
				FROM
					information_schema.table_constraints
				WHERE
					table_name = ':tableName'
					AND table_schema = ':tableSchema'
			) c
		USING (constraint_name)
		WHERE
			k.table_name = ':tableName'
			AND k.table_schema = ':tableSchema'
		ORDER BY
			k.constraint_name ASC,
			k.ordinal_position ASC
		";

		$sql = strtr($sql,
			[
				':tableName'	=> $this->_name,
				':tableSchema'	=> $this->_schema
			]
		);

		$result = $this->_db->fetchAll($sql);

		$constraints = array();
		foreach ($result as $row)
		{
			$con = [];
			$name = $row['constraint_name'];

			if (!isset($constraints[$name]))
			{
				$con['name'] = $name;
				$con['columns'] = [];
				$con['ref_columns'] = [];
				switch ($row['constraint_type'])
				{
					case 'UNIQUE':
						$con['type'] = self::CONTYPE_UNIQUE;
						break;
					case 'PRIMARY KEY':
						$con['type'] = self::CONTYPE_PRIMARY;
						break;
					case 'FOREIGN KEY':
						$con['type'] = self::CONTYPE_FOREIGN;
						$con['ref_table'] = $row['referenced_table_name'];
						break;
				}
				$constraints[$name] = $con;
			}

			//	соберём имена колонок, участвующих в ограничении
			$constraints[$name]['columns'][] = $row['column_name'];
			//	для внешних ключей соберём и связанные колонки
			if ($ref_col = $row['referenced_column_name'])
			{
				$constraints[$name]['ref_columns'][] = $ref_col;
			}
		}

		return $constraints;
	}

	protected function _getIndices()
	{
        $sql = "SHOW INDEX FROM {$this->getFullName()};";
		$rows = $this->_db->fetchAll($sql);
		$indices = array();
		foreach ($rows as $row)
		{
			$index = array();
			$name = $row['Key_name'];
			if (!isset($indices[$name]))
			{
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


	public function _getColumns()
	{
		$sql = "SHOW COLUMNS FROM {$this->getFullName()};";
		$rows = $this->_db->fetchAll($sql);
		$columns = array();
		foreach ($rows as $row)
		{
			$columns[$row['Field']] = $row['Type'];
		}

		return $columns;
	}
}

