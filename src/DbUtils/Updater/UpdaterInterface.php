<?php

namespace DbUtils\Updater;

use DbUtils\Saver\SaverInterface;

require_once __DIR__ . '/../Saver/SaverInterface.php';

/**
 * Интерфейс Updater'а
 * Повторяет Saver
 *
 * @uses SaverInterface
 * @author Sergey Lisenkov <sergli@nigma.ru>
 */
interface UpdaterInterface extends SaverInterface {

	public function update();
}

