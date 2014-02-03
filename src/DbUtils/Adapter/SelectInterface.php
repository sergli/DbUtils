<?php

namespace DbUtils\Adapter;

/**
 * Интерфейс запроса, возвращающего набор записей
 *
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
interface SelectInterface extends \IteratorAggregate, \Countable
{
	/**
	 * Освободить ресурсы
	 *
	 * @access public
	 * @return void
	 */
	public function free();

	/**
	 * @return mixed
	 */
	public function getResource();
}
