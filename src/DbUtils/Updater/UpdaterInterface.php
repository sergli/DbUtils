<?php

namespace DbUtils\Updater;

use DbUtils\Saver\SaverInterface;

/**
 * Интерфейс Updater'а
 * По факту повторяет Saver
 *
 * @uses SaverInterface
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
interface UpdaterInterface extends SaverInterface
{
	/**
	 * Возвращает набор колонок, кот. обеспечивают уникальность
	 *
	 * @return string[] | null
	 */
	public function getUniqueConstraint();

	/**
	 * Выполнить обновление, используя текущий буфер данных
	 *
	 * @return void
	 */
	public function update();
}

