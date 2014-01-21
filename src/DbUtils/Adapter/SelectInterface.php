<?php

namespace DbUtils\Adapter;

/**
 * Интерфейс запроса, возвращающего набор записей
 *
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
interface SelectInterface extends \IteratorAggregate,
							\Countable {

	/**
	 * Освободить ресурсы
	 *
	 * @access public
	 * @return void
	 */
	public function free();

	/**
	 * Вернуть внутренний экземпляр запроса
	 *
	 * @access public
	 * @return mixed
	 */
	public function getResult();

	/**
	 * @return mixed
	 */
	public function getResource();
}
