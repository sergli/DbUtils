<?php

namespace db_utils\updater;

use db_utils\saver\iSaver;

require_once __DIR__ . '/../saver/iSaver.class.php';

interface iUpdater extends iSaver {

	public function update();
}

