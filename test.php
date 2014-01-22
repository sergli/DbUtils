<?php

error_reporting(E_ALL);

include 'vendor/autoload.php';

$ci = new DbUtils\DiContainer;

$ci['mysql-new'];
$ci['mysql'];
$ci['mysql'];
$ci['mysql-new'];
$ci['mysql-new'];
$ci['mysql'];
$ci['postgres'];
$ci['postgres'];
$ci['postgres-new'];
$ci['postgres-new'];
$ci['postgres'];

$db = $ci['pdo-postgres'];
$table = $db->getTable('documents');

var_dump($table->getColumns());
var_dump($table->getIndices());

$db = $ci['pdo-pgsql'];

var_dump($db->getTable('documents')->getPrimaryKey());

$db = $ci['pdo-mysql'];

$tableName = 'documents';
$table = $db->getTable('documents');

$table->truncate();

$saver = new DbUtils\Saver\Mysql\LoadDataSaver(
	$db, 'documents');

$saver->setLogger($ci['monolog']);
$saver->setBatchSize(5000);

$saver->setOptions(0);

for ($i = 0; $i < 100000; $i++) {
	$record = array(
		'id'		=> $i + 1,
		'group_id'	=> (100 - $i) % 10,
		'title'		=> str_repeat(sha1($i), 10),
		'content'	=> str_repeat(crc32($i), 10),
	);

	$saver[] = $record;
}

$saver->save();
