<?php

namespace db_utils\adapter;

use db_utils\adapter\select\iSelect;


/**
 * Интерфейс адаптера рел. базы данных
 * 
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
interface iRDBAdapter {

	/**
	 * Выполняет sql-запрос
	 * 
	 * @param string $sql 
	 * @access public
	 * @return iSelect итератор
	 * @throws \Exception
	 */
	public function query($sql);

	/**
	 * Извлекает первую ячейку из набора записей
	 * 
	 * @param string $sql 
	 * @access public
	 * @return mixed
	 * @throws \Exception
	 */
	public function fetchOne($sql);

	/**
	 * Возвращает первую запись из набора
	 * 
	 * @param string $sql 
	 * @access public
	 * @return array
	 * @throws \Exception
	 */
	public function fetchRow($sql);

	/**
	 * Возвращает весь результ. набор данных 
	 * 
	 * @param string $sql 
	 * @access public
	 * @return array[]
	 * @throws \Exception
	 */
	public function fetchAll($sql);

	/**
	 * Возвращает набор пар типа ячейка1=>ячейка2
	 * 
	 * @param string $sql 
	 * @access public
	 * @return array
	 * @throws \Exception
	 */
	public function fetchPairs($sql);

	/**
	 * Возвращает объект класса Таблица
	 * Если таблица не существует, бросает исключение
	 * 
	 * @param string $tableName имя таблицы
	 * @access public
	 * @return iRDBTable
	 * @throws \Exception
	 */
	public function getTable($tableName);

	/**
	 * Проверяет, существует ли таблица
	 * 
	 * @param string $tableName имя таблицы
	 * @access public
	 * @return boolean
	 * @throws \Exception
	 */
	public function tableExists($tableName);
}
