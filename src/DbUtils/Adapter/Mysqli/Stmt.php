<?php

namespace DbUtils\Adapter\Mysqli;

/**
 * Класс, расширяющий возможности mysqli_stmt.
 *
 */
class Stmt extends \mysqli_stmt
{

	/**
	 * @param Adapter $db
	 * @param string $sql
	 */
	public function __construct(Mysqli $db, $sql)
	{
		parent::__construct($db, $sql);
	}

	/**
	 * Связывает массив с подготовл. выражением
	 *
	 * @param array $row
	 * @access public
	 * @return void
	 */
	public function bind_result_array(array &$row = null)
	{
		$meta = $this->result_metadata();
		$row = [];
		$vars = [];
		while($field = $meta->fetch_field())
		{
			$vars[] = &$row[$field->name];
		}
		call_user_func_array([ $this, 'bind_result' ], $vars);
	}
}
