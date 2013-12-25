<?php

namespace DbUtils\Adapter\Mysql;

require_once __DIR__ . '/Adapter.php';

class Stmt extends \Mysqli_Stmt {


	/**
	 * Конструктор
	 *
	 * @param Adapter $db
	 * @param string $sql
	 */
	public function __construct(Adapter $db, $sql) {
		parent::__construct($db, $sql);
	}


	/**
	 * Связывает массив с подготовл. выражением
	 *
	 * @param array $row
	 * @access public
	 * @return boolean успех/неудача
	 */
	public function bind_result_array(array &$row = null) {
		$meta = $this->result_metadata();
		$row = array();
		$vars = array();
		while($field = $meta->fetch_field()) {
			$vars[] = &$row[$field->name];
		}
		call_user_func_array(array($this, 'bind_result'), $vars);
	}
}