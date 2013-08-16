<?php

namespace db_utils\select;

/**
 * Интерфейс запроса, возвращающего набор записей
 * 
 * @abstract
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
abstract class iSelect implements \IteratorAggregate, \Countable {
	
	/**
	 * Освободить ресурсы
	 * 
	 * @abstract
	 * @access public
	 * @return void
	 */
	public abstract function free();

	/**
	 * Вернуть внутренний экземпляр запроса
	 * 
	 * @abstract
	 * @access public
	 * @return mixed mysqli_result, resource, etc
	 */
	public abstract function getResult();

}
