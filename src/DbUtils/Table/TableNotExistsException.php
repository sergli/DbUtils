<?php

namespace DbUtils\Table;

class TableNotExistsException extends \Exception
{
	public function __construct($tableName)
	{
		parent::__construct(sprintf(
			'Table %s not exists', $tableName));
	}
}
