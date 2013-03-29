<?php

use db_utils\adapter\mysql\Mysql;
use db_utils\updater\mysql\MysqlBulkUpdater;

error_reporting(E_ALL);

require_once __DIR__ . '/../../adapter/mysql/Mysql.class.php';
require_once __DIR__ . '/MysqlBulkUpdater.class.php';

$db = Mysql::getInstance(1, $opts = [
	'user'		=> 'root',
	'password'	=> 'Chipikavoc5',
	'dbname'	=>	'test',
]);

$tableName = 'documents';
$table = Mysql::getInstance(2, $opts)->getTable($tableName);

var_dump($table->getFullName(),
	$table->getColumns(), $table->getConstraints());

$updater = new MysqlBulkUpdater($table);


var_dump($updater->getSize(), $updater->getBatchSize());

$updater->setBatchSize(5000);

$updater::$_debug = true;
var_dump($updater->getBatchSize());

var_dump($table->getConnection()->fetchColumn('show tables', 1));

$db = $table->getConnection();

//$r = $db->query('truncate table ' . $tableName);

$keys = [
	'id',
	'group_id',
	'title',
	'content',
];


for ($i = 1; $i <= 10; $i++) {
	$rec = array_combine($keys, [
		$i,
		mt_rand(1,20),
		substr(md5($i),0,4) . "-tit'le",
		str_repeat('*', $i),
	]);
	$updater[] = $rec;
}
var_dump('count of updater: ' . count($updater));


/*
try {
	$updater['lalaa'] = 'test';
	$updater[count($updater) - 1] = 'test';
}
catch (\Exception $e) {
	var_dump($e->getMessage());
}
*/
$updater[] = array_combine($keys, [1,2,3,4]);
$updater[] = array_combine($keys, [1,2,3,4]);
$updater[] = array_combine($keys, [1,2,3,4]);
$updater[] = array_combine($keys, [1,2,3,4]);

var_dump('lalalalalalalalala');
//$updater->setOptions($updater::OPT_DELAYED*0);
var_dump(count($updater));
$updater->save();
var_dump('COUNT', count($updater));



