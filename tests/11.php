<?php

include '../vendor/autoload.php';

$dic = new DbUtils\DiContainer;
$tableName = 'documents';
$db = $dic['mysql-new'];

$table = $db->getTable($tableName);

var_dump($table->getFullName());

$saver = new DbUtils\Saver\Mysql\BulkInsertSaver($db, $tableName);

$saver->setBatchSize(100);

$saver->setLogger($dic['monolog']);



$table->truncate();

for ($i = 0; $i < 1000; $i++) {
	$saver->add(gen_rec($i));
}





function gen_rec($i) {
	return [
		'group_id'	=> $i % 1000,
		'title'		=> bin2hex($i),
		'content'	=> sha1($i),
	];
}
