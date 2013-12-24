<?php

namespace db_utils\saver\postgres;
use db_utils\adapter\postgres\Postgres;

error_reporting(E_ALL);
ini_set('memory_limit', '256M');

require_once __DIR__ . '/PostgresBulkInsertSaver.class.php';

$opts = include __DIR__ . '/../../config.php';
$opts = $opts['postgres'];
$opts['dbname'] = 'sergli';

$db = Postgres::getInstance(1, $opts);

$tableName = 'documents';
//	это уже другое соединение! (должно быть)
$table = Postgres::getInstance(2, $opts)->getTable($tableName);

var_dump($table->getFullName(),
	$table->getColumns(), $table->getConstraints());
$saver = new PostgresBulkInsertSaver($table);

var_dump($saver->getSize(), $saver->getBatchSize());

$saver->setBatchSize(5000);

$saver::$_debug = true;
var_dump($saver->getBatchSize());

var_dump($table->getConnection()->fetchColumn('show search_path'));

$db = $table->getConnection();

$r = $db->query('truncate table ' . $tableName);

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

$db->query('truncate table ' . $tableName);

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

$saver[] = array_combine($keys, [1,2,3,4]);
$saver[] = array_combine($keys, [1,2,3,4]);
$saver[] = array_combine($keys, [1,2,3,4]);

var_dump('lalalalalalalalala');
//$saver->setOptions($saver::OPT_DELAYED*0);
$saver->save();
var_dump('COUNT', count($saver));

