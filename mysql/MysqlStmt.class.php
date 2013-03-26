<?php

namespace autocomplete\complete\generate\utils\db\mysql;

require_once __DIR__ . '/../../../init.php';
require_once __DIR__ . '/Mysql.class.php';

/**
 * Расширяет функ-ть стандартного mysqli_stmt 
 * 
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
class MysqlStmt extends \Mysqli_Stmt {

	/**
	 * Создаём экземпляр mysqli_stmt
	 * Не должен вызываться вручную.
	 * 
	 * @param Mysq $mysql
	 * @param string $sql sql-запрос (возможно, с плейсхолдерами)
	 * @access public
	 */
	public function __construct(Mysql $mysql, $sql) {
		parent::__construct($mysql, $sql);
	}

	/**
	 * Привязывает массив к подготов. выражению
	 * 
	 * @param array $row 
	 * @access public
	 * @return boolean успех/неудача
	 */
	public function bind_result_array(array &$row) {
		$meta = $this->result_metadata();
		$row = array();
		$vars = array();
		while($field = $meta->fetch_field()) {
			$vars[] = &$row[$field->name];
		}
		call_user_func_array(array($this, 'bind_result'), $vars);
	}
}
