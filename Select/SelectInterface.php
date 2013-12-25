<?php

namespace DbUtils\Select;

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
	 * @return mixed \mysqli_result, resource, etc
	 */
	public function getResult();

}
