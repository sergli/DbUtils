<?php

namespace db_utils\updater;

use db_utils\saver\iSaver;

require_once __DIR__ . '/../saver/iSaver.class.php';

/**
 * Интерфейс Updater'а
 * Повторяет Saver
 * 
 * @uses iSaver
 * @author Sergey Lisenkov <sergli@nigma.ru> 
 */
interface iUpdater extends iSaver {

	public function update();
}

