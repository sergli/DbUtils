<?php

error_reporting(E_ALL);

include 'vendor/autoload.php';

$ci = new DbUtils\DIC;

//var_dump($ci['mysql-new'], $ci['mysql'], $ci['mysql'], $ci['mysql-new'], $ci['mysql-new'], $ci['mysql']);
//
//var_dump($ci['postgres'], $ci['postgres'], $ci['postgres-new'], $ci['postgres-new'], $ci['postgres']);
//

//var_dump($ci['mysql']->fetchColumn('show tables'));

$ci['mysql.table_name'] = 'documents';

var_dump($ci['mysql.table']->getColumns());
var_dump($ci['mysql.table']->getIndices());

$ci['postgres.table_name'] = 'documents';

//var_dump($ci['postgres.table']);
//var_dump($ci['postgres.table']);

//$saver = new DbUtils\Saver\Mysql\LoadDataSaver(
//	$ci['mysql.table']);
$saver = new DbUtils\Saver\Mysql\BulkInsertSaver(
	$ci['mysql.table']);
$saver->setLogger($ci['monolog']);
$saver->setBatchSize(50000);

$ci['mysql.table']->truncate();

//var_dump($ci['mysql.table']->getColumns());
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
