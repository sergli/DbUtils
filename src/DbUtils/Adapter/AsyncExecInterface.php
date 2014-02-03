<?php

namespace DbUtils\Adapter;

/**
 * Интерфейс заявляет о поддержке асинхронного
 * выполнения sql-команд.
 * Возврат результата не предусмотрен (для простоты).
 *
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
interface AsyncExecInterface
{
	public function asyncExec($sql);

	public function wait();
}
