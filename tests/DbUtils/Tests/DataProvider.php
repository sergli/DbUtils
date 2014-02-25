<?php

namespace DbUtils\Tests;

class DataProvider implements \Iterator
{
	private $_guesser;
	private $_columns = [
		'id',
		'group_id',
		'name',
		'content',
		'date',
		'bindata',
	];

	private $_position = 0;

	public function rewind()
	{
		$this->_position = 0;
	}

	public function valid()
	{
		return true;
	}

	public function current()
	{
		return $this->getRecord();
	}

	public function key()
	{
		return $this->_position;
	}

	public function next()
	{
		++$this->_position;
	}


	public function __construct(array $columns = null,
		$seed = null)
	{
		$faker = \Faker\Factory::create('ru_RU');

		if (!isset($seed) && !empty($_SERVER['seed']))
		{
			$seed = $_SERVER['seed'];
		}
		if ($seed = (int) $seed)
		{
			$faker->seed($seed);
		}
		if ($seed)
		{
			$faker->seed($seed);
		}

		$this->_guesser = new Guesser($faker);

		if ($columns)
		{
			$this->_columns = $columns;
		}
	}

	public function getRecord()
	{
		$ret = [];
		foreach ($this->_columns as $columnName)
		{
			$ret[$columnName] = call_user_func(
				$this->_guesser->guessFormat($columnName));
		}

		return $ret;
	}
}
