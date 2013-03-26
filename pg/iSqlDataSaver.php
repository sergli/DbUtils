<?php

/**
 * Интерфейс для классов, сохраняющих данные в таблицы
 * 
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
interface  iSqlDataSaver {

	/**
	 * Добавить новую запись в буфер хранения
	 * 
	 * @param Array $row запись
	 * @access public
	 * @return void
	 */
	public function addRow(Array $row);

	/**
	 * Сохраняет накопившиеся записи и обнуляет буфер
	 * 
	 * @access public
	 * @return void
	 */
	public function save();

	/**
	 * Обнуляет буфер
	 * 
	 * @access public
	 * @return void
	 */
	public function truncate();

	/**
	 * Возвращает кол-во записей в буфере хранения
	 * 
	 * @access public
	 * @return int
	 */
	public function getSize();

	/**
	 * Возвращает значение размера порции
	 * 
	 * @access public
	 * @return int
	 */
	public function getBatchSize();

	/**
	 * Устанавливает значение размера порции
	 * 
	 * @param int $size 
	 * @access public
	 * @return void
	 */
	public function setBatchSize($size);

}
