<?php

ini_set('memory_limit', '256M');

require_once '../vendor/autoload.php';

$tableName = 'documents';
//	это уже другое соединение! (должно быть)
$dic = new DbUtils\DiContainer;
$db = $dic['postgres'];
$table = $db->getTable($tableName);

var_dump($table->getFullName(),
	$table->getColumns(), $table->getConstraints());
$saver = new DbUtils\Saver\Postgres\BulkInsertSaver($db, $tableName);
$saver->setLogger($dic['monolog']);

var_dump($saver->getSize(), $saver->getBatchSize());

$saver->setBatchSize(5000);

var_dump($saver->getBatchSize());

var_dump($table->getConnection()->fetchColumn('show search_path'));

$db = $table->getConnection();

$table->truncate();

$keys = [
	'id',
	'group_id',
	'title',
	'content',
];


for ($i = 1; $i <= 10000; $i++) {
	$rec = array_combine($keys, [
		$i,
		mt_rand(1,20),
		substr(md5($i),0,4) . "-tit'le",
		str_repeat('*', $i),
	]);
	$saver[] = $rec;
}
var_dump('count of saver: ' . count($saver));

$table->truncate();

/*
try {
	$saver['lalaa'] = 'test';
	$saver[count($saver) - 1] = 'test';
}
catch (\Exception $e) {
	var_dump($e->getMessage());
}
*/
$saver->setBatchSize(1);
$saver[] = array_combine($keys, [1,2,3,4]);

$saver[] = array_combine($keys, [10,2,3,4]);
$saver[] = array_combine($keys, [100,2,3,4]);
$saver[] = array_combine($keys, [1000,2,3,4]);

var_dump('lalalalalalalalala');
//$saver->setOptions($saver::OPT_DELAYED*0);
$saver->save();
var_dump('COUNT', count($saver));

