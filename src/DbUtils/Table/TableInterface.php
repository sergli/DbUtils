<?php

namespace DbUtils\Table;

use DbUtils\Adapter\AdapterInterface;

/**
 * Интерфейс классов для работы с реляц. таблицами
 *
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
interface TableInterface
{

	/**
	 * Получить экземпляр соединения с БД
	 *
	 * @access public
	 * @return AdapterInterface
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

}
