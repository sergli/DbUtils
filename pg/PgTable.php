<?php

require_once __DIR__ . '/PgTableException.php';

class PgTable {

	private $_db;

	private $_name = '';

	private $_schema = '';

	private $_relId;

	const CONTYPE_UNIQUE = 'UNIQUE';
	const CONTYPE_FOREIGN = 'FOREIGN KEY';
	const CONTYPE_PRIMARY = 'PRIMARY KEY';
	const CONTYPE_CHECK = 'CHECK';


	/**
	 * Кешированные данные об ограничениях
	 *
	 * @var array
	 * @access private
	 * @see getConstraints()
	 */
	private $_constraints = null;

	/**
	 * Кешированные данные об индексах
	 * 
	 * @var array
	 * @access private
	 * @see getIndexes()
	 */
	private $_indexes = null;

	/**
	 * Кешированные данные о структуре
	 * 
	 * @var array
	 * @access private
	 * @see getColumns()
	 */
	private $_columns = null;

	/**
	 * Возвращает имя таблицы (без схемы)
	 * 
	 * @access public
	 * @return string
	 */
	public function getName() {
		return $this->_name;
	}

	/**
	 * Возвращает схему таблицы
	 * 
	 * @access public
	 * @return string
	 */
	public function getSchema() {
		return $this->_schema;
	}

	/**
	 * Возвращает полное имя таблицы (со схемой)
	 *
	 * Без всяких "кавычек" - так удобнее
	 * 
	 * @access public
	 * @return string
	 */
	public function getFullName() {
		return $this->_schema . '.' . $this->_name;
	}
	
	/**
	 * Возаращает oid
	 * 
	 * @access public
	 * @return int
	 */
	public function getRelationId() {
		return $this->_relId;
	}

	/**
	 * Обнуляет кешированную информацию о таблице
	 * Пересчёт произойдёт в момент след. вызова соотв. методов
	 *
	 * Имеет смысл вызывать после ALTER TABLE и т.п
	 * 
	 * @access public
	 * @return void
	 */
	public function recalculate() {
		$this->_indexes = null;
		$this->_columns = null;
		$this->_constraints = null;
	}

	/**
	 * Возвращает primary key
	 * 
	 * @access public
	 * @return array|null
	 */
	public function getPrimaryKey() {
		foreach ($this->getConstraints() as $con) {
			if (self::CONTYPE_PRIMARY == $con['type']) {
				return $con;
			}
		}
		return null;
	}

	/**
	 * Алиас для getPrimaryKey
	 * 
	 * @access public
	 * @return array|null
	 */
	public function getPK() {
		return $this->getPrimaryKey();
	}
	
	public function getUniques() {
		$fk = self::CONTYPE_UNIQUE;
		return array_filter($this->getConstraints(), 
			function ($val) use ($fk) {
				return $fk == $val['type'];
			}
		);
	}


	public function getConstraints() {
		if (!is_null($this->_constraints)) {
			return $this->_constraints;
		}

		$sql = "
		SELECT
			conname,
			contype,
			contype,
			condeferrable,
			condeferred,
			conrelid,
			contypid,
			confrelid,
			confrelid::regclass confrelname,
			confupdtype,
			confdeltype,
			confmatchtype,
			conislocal,
			pg_catalog.pg_get_constraintdef(r.oid, true) AS condef
		FROM
			pg_catalog.pg_constraint r
		WHERE
			r.conrelid = {$this->_relId}
		ORDER BY
			conname ASC;
		";
		
		$cons = array();
		$r = $this->_db->query($sql, PDO::FETCH_ASSOC);

		foreach ($r as $row) {
			$con = array(
				'name'	=>	$row['conname'],
			);
			switch ($row['contype']) {
			case 'u':
				$con['type'] = self::CONTYPE_UNIQUE;
				preg_match('/^UNIQUE \((.+?)\)/i', $row['condef'], $m);
				$con['columns'] = preg_split('/, ?/', $m[1]);
				break;
			case 'p':
				$con['type'] = self::CONTYPE_PRIMARY;
				preg_match('/^PRIMARY KEY \((.+?)\)/',$row['condef'], $m);
				$con['columns'] = preg_split('/, ?/', $m[1]);
				break;
			case 'c':
				$con['type'] = self::CONTYPE_CHECK;
				//	TODO
				break;
			case 'f':
				$con['type'] = self::CONTYPE_FOREIGN;
				//TODO
				break;
			}
			$con['def'] = $row['condef'];
			$cons[$row['conname']] = $con;
		}

		return $this->_constraints = $cons;
	}


	public function getIndexes() {
		if (!is_null($this->_indexes)) {
			return $this->_indexes;
		}

		$sql = "
		SELECT 
			c2.relname AS name,
			i.indisprimary AS is_primary,
			i.indisunique AS is_unique,
			i.indisclustered AS is_clustered,
			i.indisvalid AS is_valid,
			pg_catalog.pg_get_indexdef(i.indexrelid, 0, true) AS def,
			c2.reltablespace AS tablespace
		FROM 
			pg_catalog.pg_class c
			INNER JOIN pg_catalog.pg_index i
				ON c.oid = i.indrelid
			INNER JOIN pg_catalog.pg_class c2
				ON i.indexrelid = c2.oid
		WHERE 
			c.oid = {$this->_relId} 
		ORDER BY 
			i.indisprimary DESC, 
			i.indisunique DESC, 
			c2.relname;
		";
		$indexes = array();
		$r = $this->_db->query($sql, PDO::FETCH_ASSOC);

		foreach ($r as $row) {
			$indexes[$row['name']] = $row;
		}

		return $this->_indexes = $indexes;
	}

	public function getColumns() {
		if (!is_null($this->_columns)) {
			return $this->_columns;
		}
		$sql = "
		SELECT 
			a.attname,
			pg_catalog.format_type(a.atttypid, a.atttypmod),
			(
				SELECT 
					substring(pg_catalog.pg_get_expr(d.adbin, d.adrelid) for 128)
				FROM 
					pg_catalog.pg_attrdef d
				WHERE 
					d.adrelid = a.attrelid 
					AND d.adnum = a.attnum 
					AND a.atthasdef
			),
			a.attnotnull, 
			a.attnum
		FROM 
			pg_catalog.pg_attribute a
		WHERE 
			a.attrelid = {$this->_relId}
			AND a.attnum > 0 
			AND NOT a.attisdropped
		ORDER BY a.attnum;
		";

		$r = $this->_db->query($sql);
		$columns = array();
		foreach ($r as $row) {
			$columns[$row['attname']] = $row['format_type'];
		}

		return $this->_columns = $columns;
	}

	public static function exists($db, $tableName) {
		try {
			new static($db, $tableName);
			return true;
		}
		catch (PgTableException $e) {
			return false;
		}
	}

	/**
	 * Возвращает основную информацию о таблице: имя, схема, oid
	 *
	 * Заодно проверяет, существует ли таблица
	 * 
	 * @param mixed $tableName 
	 * @access private
	 * @return array массив [name, schema, oid]
	 */
	private function _getBaseInfo($tableName) {
	
		$regex = '/^(?:"?([a-z0-9_]+)"?\.)?([a-z0-9_]+)$/i';
		if (!preg_match($regex, $tableName, $matches)) {
			throw new PgTableException("Плохое имя таблицы: $tableName");
		}
		$name = $matches[2];
		$schema = $matches[1];
		
		if (!$schema) {
			$where = "
			c.relname = '$name'
			AND pg_catalog.pg_table_is_visible(c.oid)";
			
			$tableName = $name;
		}
		else {
			$where = "
			c.relname = '$name'
			AND n.nspname = '$schema'";

			$tableName = "$schema.$name";
		}
		
		$sql = "
		SELECT 
			c.oid,
			n.nspname AS schema,
			c.relname AS name
		FROM 
			pg_catalog.pg_class c
			LEFT JOIN pg_catalog.pg_namespace n 
				ON n.oid = c.relnamespace
		WHERE 
			$where	
		ORDER BY 
			2, 3;
		";

		$row = $this->_db->query($sql)->fetch(PDO::FETCH_ASSOC);
		if (!$row) {
			throw new PgTableException("Таблица $tableName не существует");
		}
		
		return $row;
	}

	public function __construct(PDO $db, $tableName) {
		
		$this->_db = $db;

		$info = $this->_getBaseInfo($tableName);
		$this->_relId = $info['oid'];
		$this->_name = $info['name'];
		$this->_schema = $info['schema'];
	}
}
