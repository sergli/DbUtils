<?php

namespace DbUtils\Table;

use DbUtils\Adapter\PostgresAdapterInterface;

class PostgresTable extends AbstractTable
{
	public function __construct(
		PostgresAdapterInterface $db, $tableName)
	{
		parent::__construct($db, $tableName);
	}

	protected function _getBaseInfo($tableName)
	{
		$regex = '/^(?:"?([a-z0-9_]+)"?\.)?([a-z0-9_]+)$/i';
		if (!preg_match($regex, $tableName, $matches))
		{
			throw new TableNotExistsException($tableName);
		}
		$name = $matches[2];
		$schema = $matches[1];

		if (!$schema)
		{
			$where = "
			c.relname = '$name'
			AND pg_catalog.pg_table_is_visible(c.oid)";

			$tableName = $name;
		}
		else
		{
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

		if (!$row = $this->_db->fetchRow($sql))
		{
			throw new TableNotExistsException($tableName);
		}

		return $row;
	}

	protected function _getConstraints()
	{
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

		$r = $this->_db->query($sql);

		$constraints = array();
		foreach ($r as $row)
		{
			$con = [];
			$name = $row['conname'];
			$con['name'] = $name;
			//	определение создания ограничения
			$condef = $row['condef'];
			switch ($row['contype'])
			{
				case 'u':
					$con['type'] = self::CONTYPE_UNIQUE;
					preg_match('/^UNIQUE \((.+?)\)/i', $condef, $m);
					$con['columns'] = preg_split('/, ?/', $m[1]);
					break;
				case 'p':
					$con['type'] = self::CONTYPE_PRIMARY;
					preg_match('/^PRIMARY KEY \((.+?)\)/', $condef, $m);
					$con['columns'] = preg_split('/, ?/', $m[1]);
					break;
				case 'c':
					$con['type'] = self::CONTYPE_CHECK;
					//	TODO
					break;
				case 'f':
					$con['type'] = self::CONTYPE_FOREIGN;
					$con['ref_table'] = $row['confrelname'];
					//fixme имена могут в принципе содержать скобки
					preg_match('/ REFERENCES [^(]+\((.+?)\)/', $condef, $m);
					$con['ref_columns'] = preg_split('/, ?/', $m[1]);
					break;
			}
			$con['def'] = $row['condef'];
			$constraints[$name] = $con;
		}

		return $constraints;
	}

	protected function _getIndices()
	{
		$sql = "
		SELECT
			c2.relname AS name,
			i.indisprimary::int AS is_primary,
			i.indisunique::int AS is_unique,
			i.indisclustered::int AS is_clustered,
			i.indisvalid::int AS is_valid,
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

		$r = $this->_db->query($sql);

		$indices = array();
		foreach ($r as $row)
		{
			$row['is_primary'] = (bool) $row['is_primary'];
			$row['is_unique'] = (bool) $row['is_unique'];
			$row['is_clustered'] = (bool) $row['is_clustered'];
			$row['is_valid'] = (bool) $row['is_valid'];
			$indices[$row['name']] = $row;
		}

		return $indices;
	}

	protected function _getColumns()
	{
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
		foreach ($r as $row)
		{
			$columns[$row['attname']] = $row['format_type'];
		}

		return $columns;
	}

	public function truncate()
	{
		$sql = 'TRUNCATE TABLE ' .
			$this->getFullName() . ' RESTART IDENTITY;';
		$this->_db->query($sql);
	}
}
