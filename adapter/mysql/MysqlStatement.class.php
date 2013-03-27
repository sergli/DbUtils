<?php

namespace db_utils\adapter\mysql;

require_once __DIR__ . '/Mysql.class.php';

class MysqlStatement extends \mysqli_stmt {


	public function __construct(Mysql $db, $sql) {
		parent::__construct($db, $sql);
	}


	/**
	 * Привязывает массив к подготов. выражению
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
