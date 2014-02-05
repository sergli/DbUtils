<?php

namespace DbUtils\Adapter\Pgsql;

class SelectIterator implements \Iterator
{
	private $_pos = 0;
	private $_current = null;
	/**
	 * @var Select
	 */
	private $_select;


	public function __construct(Select $select)
	{
		$this->_select = $select;
	}

	public function current()
	{
		return $this->_current;
	}

	public function key()
	{
		return $this->_pos;
	}


	public function valid()
	{
		return false !== $this->_current;
	}

	public function rewind()
	{
		if ($this->_pos !== 0)
		{
			pg_result_seek($this->_select->getResource(), 0);
		}
		$this->_current = array();
		$this->next();
		$this->_pos = 0;
	}

	public function next()
	{
		$this->_pos++;
		$this->_current = pg_fetch_assoc(
			$this->_select->getResource());
	}
}
