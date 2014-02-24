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
				//	with some nulls
				return $generator->optional(0.7)->randomNumber;
			};
		case 'name':
			return function() use ($generator)
			{
				return $generator->unique()->name;
			};
		case 'content':
			return function() use ($generator)
			{
				return $generator->optional(0.7)->text;
			};
		case 'date':
			return function() use ($generator)
			{
				//	в timestamp нули не нужны,
				//	тк mysql не допускает
				return $generator->datetime
					->format('Y-m-d H:i:s');
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
