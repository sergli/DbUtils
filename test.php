<?php

namespace autocomplete\complete\generate\utils\mysqli;

require_once __DIR__ . '/../../init.php';

$db = Mysqli::getInstance(2);
$db2 = Mysqli::getInstance(1, 'Chemistry');

$db2->query('create table if not exists test (id int, text text)');

$saver = MysqliSaverFactory::getSaver('bulk_update', array($db2, 'test'));
$saver->setChunkSize(1000);
$saver->progress = true;

for ($i = 0; $i < 100000; $i++) {
	$row = array('id' => $i, 'text' => mt_rand(1,100) % 10 === 0 ? md5($i) : 0);
	$saver->addRow($row);
}
$saver->save();
