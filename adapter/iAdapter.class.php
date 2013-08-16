<?php

namespace db_utils\adapter;

use db_utils\select\iSelect;


/**
 * Интерфейс адаптера рел. базы данных
 * 
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
interface iAdapter {

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
	 * @todo возвращать итератор ?
	 */
	public function fetchPairs($sql);

	/**
	 * Возвращает набор значений заданной колонки
	 * 
	 * @param string $sql 
	 * @param int $colNum номер колонки (от 1)
	 * @access public
	 * @return array
	 * @throws \Exception
	 */
	public function fetchColumn($sql, $colNum = 1);

	/**
	 * Возвращает объект класса Таблица
	 * Если таблица не существует, бросает исключение
	 * 
	 * @param string $tableName имя таблицы
	 * @access public
	 * @return iTable
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
