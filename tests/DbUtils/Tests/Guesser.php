<?php

namespace DbUtils\Tests;

class Guesser extends \Faker\Guesser\Name
{
	public function guessFormat($name)
	{
		$generator = $this->generator;

		switch ($name)
		{
		case 'id':
			return function() use ($generator)
			{
				return $generator->unique()->randomNumber;
			};
		case 'group_id':
			return function() use ($generator)
			{
				return $generator->randomNumber;
			};
		case 'name':
			return function() use ($generator)
			{
				return $generator->unique()->name;
			};
		case 'content':
			return function() use ($generator)
			{
				return $generator->text;
			};
		case 'date':
			return function() use ($generator)
			{
				return $generator->datetime->format('Y-m-d H:i:s');
			};
		case 'bindata':
			return function()
			{
				return implode('', array_map(function($i)
				{
					return chr(mt_rand(0, 255));
				}, range(0, 30)));
			};
		}

		if (! $formatter = parent::guessFormat($name))
		{
			return function() use ($generator)
			{
				return $generator->text;
			};
		}
	}
}
