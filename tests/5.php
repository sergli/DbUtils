<?php

require_once '../vendor/autoload.php';

$tableName = 'documents';
$db = (new DbUtils\DiContainer)['mysql-new'];
$table = $db->getTable($tableName);

var_dump($table->getFullName(),
	$table->getColumns(), $table->getConstraints());

//$saver = new MysqlBulkInsertSaver($table);
//$saver = new MysqlLoadDataSaver($table);
$filename = '';
$saver = new DbUtils\Saver\Mysql\BulkInsertSaver($db, $tableName);

$logger = $saver->getLogger();
$logger->pushHandler(new Monolog\Handler\StreamHandler('./app.log', Monolog\Logger::INFO));

$saver->setLogger($logger);

//var_dump('filename', $filename = $saver->getFileName());

var_dump($saver->getSize(), $saver->getBatchSize());

$saver->setBatchSize(5000);

var_dump($saver->getBatchSize());

var_dump($table->getConnection()->fetchColumn('show tables', 1));

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
exit;
/*
try {
	$saver['lalaa'] = 'test';
	$saver[count($saver) - 1] = 'test';
}
catch (\Exception $e) {
	var_dump($e->getMessage());
}
*/
$saver[] = array_combine($keys, [1,2,3,4]);
$saver[] = array_combine($keys, [1,2,3,4]);
$saver[] = array_combine($keys, [1,2,3,4]);
$saver[] = array_combine($keys, [1,2,3,4]);

var_dump('lalalalalalalalala');
//$saver->setOptions($saver::OPT_DELAYED*0);
var_dump(count($saver));
$saver->save();
var_dump('COUNT', count($saver));

var_dump('filename', stat($filename));
