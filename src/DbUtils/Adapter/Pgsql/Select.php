<?php

namespace DbUtils\Adapter\Pgsql;

use DbUtils\Adapter\SelectInterface;

class Select implements SelectInterface, \Countable
{
	/**
	 * @var resource of type pgsql result
	 */
	private $_resource;

	/**
	 * @param resource $resource pgsql result
	 */
	public function __construct($resource)
	{
		if (is_resource($resource) &&
			get_resource_type($resource) == 'pgsql result')
		{
			$this->_resource = $resource;
		}
		else
		{
			throw new \InvalidArgumentException(
				'Expects $resource to be resource of type pgsql result');
		}
	}

	public function getIterator()
	{
		return new SelectIterator($this);
	}

	/**
	 * @return resource
	 */
	public function getResource()
	{
		return $this->_resource;
	}

	public function count()
	{
		return Pgsql::callCarefully(function()
		{
			return pg_num_rows($this->_resource);
		});
	}
}
