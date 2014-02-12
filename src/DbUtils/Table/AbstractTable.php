<?php

namespace DbUtils\Table;

use DbUtils\Adapter\AdapterInterface;
use DbUtils\Adapter\MysqlAdapterInterface;
use DbUtils\Adapter\PostgresAdapterInterface;

/**
 * Абстрактный класс, описывающий таблицу в реляц. базе данных
 *
 * @uses TableInterface
 * @abstract
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
abstract class AbstractTable implements TableInterface {

	const CONTYPE_UNIQUE = 'UNIQUE';
	const CONTYPE_FOREIGN = 'FOREIGN KEY';
	const CONTYPE_PRIMARY = 'PRIMARY KEY';
	const CONTYPE_CHECK = 'CHECK';

	protected $_db;

	protected $_name;
	protected $_schema;
	protected $_relId = null;

	protected $_indices = null;
	protected $_columns = null;
	protected $_constraints = null;

	abstract protected function _getBaseInfo($tableName);
	abstract protected function _getIndices();
	abstract protected function _getColumns();
	abstract protected function _getConstraints();

	public static function factory(AdapterInterface $db,
		$tableName)
	{
		if ($db instanceof MysqlAdapterInterface)
		{
			return new MysqlTable($db, $tableName);
		}
		else if ($db instanceof PostgresAdapterInterface)
		{
			return new PostgresTable($db, $tableName);
		}

		throw new \UnexpectedValueException(sprintf(
			'Table class for adapter %s is unknown',
				get_class($db)));

	}

	public function __construct(AdapterInterface $db,
		$tableName)
	{
		$this->_db = $db;
		$info = $this->_getBaseInfo($tableName);

		$this->_name = $info['name'];
		$this->_schema = $info['schema'];
		if (isset($info['oid']) && is_numeric($info['oid']))
		{
			$this->_relId = (int) $info['oid'];
		}
	}

	/**
	 * Возвращает ограничения, кеширует результат
	 *
	 * @see _getConstraints()
	 * @access public
	 * @return array
	 */
	public function getConstraints()
	{
		if (is_null($this->_constraints))
		{
			$this->_constraints = $this->_getConstraints();
		}

		return $this->_constraints;
	}

	public function getColumns()
	{
		if (is_null($this->_columns))
		{
			$this->_columns = $this->_getColumns();
		}
		return $this->_columns;
	}

	public function getIndices()
	{
		if (is_null($this->_indices))
		{
			$this->_indices = $this->_getIndices();
		}
		return $this->_indices;
	}

	public function getName()
	{
		return $this->_name;
	}

	public function getSchema()
	{
		return $this->_schema;
	}

	public function getRelationId()
	{
		return $this->_relId;
	}

	public function getFullName()
	{
		return $this->_schema . '.' . $this->_name;
	}

	/**
	 * Обновить кеш информации о таблице
	 *
	 * @return void
	 */
	public function recalculate()
	{
		$this->_indices = null;
		$this->_columns = null;
		$this->_constraints = null;
	}

	/**
	 * @access public
	 * @return array
	 */
	public function getPrimaryKey()
	{
		foreach ($this->getConstraints() as $con)
		{
			if (self::CONTYPE_PRIMARY == $con['type'])
			{
				return $con;
			}
		}

		return null;
	}

	public function getPk()
	{
		return $this->getPrimaryKey();
	}

	public function getUniques()
	{
		/*
			Фильтруем именно индексы, а не ограничения,
			Т.к в postgres можно создавать просто уник.
			индексы (не связано с constraint).
			А вот наличие uniq constraint гарантирует также
			и наличие uniq index (в обеих субд)
		*/
		$fk = self::CONTYPE_UNIQUE;
		return array_filter($this->getConstraints(),
			function ($val) use ($fk)
			{
				return $fk == $val['type'];
			}
		);
	}

	public function truncate()
	{
		$sql = "TRUNCATE TABLE {$this->getFullName()}";
		$this->_db->query($sql);
	}

	/**
	 * Возвращает используемое таблицей соединение с БД
	 *
	 * @access public
	 * @return AdapterInterface
	 */
	public function getConnection()
	{
		return $this->_db;
	}
}
