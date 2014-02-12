<?php

namespace DbUtils\Adapter;

/**
 * Интерфейс запроса, возвращающего набор записей.
 * Требование только одно - traversable
 *
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
interface SelectInterface extends \IteratorAggregate
{
	/**
	 * @return mixed
	 */
	public function getResource();
}
