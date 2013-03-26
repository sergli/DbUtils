<?php


namespace db_utils\table;

use db_utils\adapter;

/**
 * Интерфейс классов для работы с реляц. таблицами
 * 
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
interface iRDBTable {
	
	/**
	 * Получить экземпляр соединения с БД
	 * 
	 * @access public
	 * @return iRDBAdapter
	 */
	public function getConnection();
	/**
	 * Получить имя таблицы
	 * 
	 * @access public
	 * @return string
	 */
	public function getName();

	/**
	 * Получить схему/базы таблицы
	 * 
	 * @access public
	 * @return string
	 */
	public function getSchema();

	/**
	 * Получить полное имя: схема + таблица
	 * 
	 * @access public
	 * @return string
	 */
	public function getFullName();

	/**
	 * Обновить кеш информации
	 * 
	 * @access public
	 * @return string
	 */
	public function recalculate();

	/**
	 * Получить нформацию о первичном ключе
	 * 
	 * @access public
	 * @return array
	 */
	public function getPrimaryKey();
	
	/**
	 * Получить информацию об ограничениях уникальности
	 * 
	 * @access public
	 * @return array
	 */
	public function getUniques();

	/**
	 * Получить все ограничения
	 * 
	 * @access public
	 * @return array
	 */
	public function getConstraints();

	/**
	 * Получить индексы
	 * 
	 * @access public
	 * @return array
	 */
	public function getIndices();

	/**
	 * Получить информацию о колонках
	 * 
	 * @access public
	 * @return array
	 */
	public function getColumns();

	/**
	 * Проверить, существует ли таблица с указ. именем
	 * 
	 * @param mysqli|PDO|resource $db ресурс соединения с БД
	 * @param string $tableName 
	 * @static
	 * @access public
	 * @return boolean
	 */
	public static function exists($db, $tableName);
}
