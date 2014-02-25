<?php

include '../../../../vendor/autoload.php';

$dic = new \DbUtils\DiContainer;

$db = $dic['db.pgsql'];

$tableName = 'test.documents';
$table = $db->getTable($tableName);

$table->truncate();


$columns = [ 'group_id', 'name' ];

$all = $table->getColumns();

$saverClass = '\DbUtils\Saver\Postgres\PgCopyFromSaver';
//$saverClass = '\DbUtils\Saver\Postgres\LoadFileSaver';
//$saverClass = '\DbUtils\Saver\Postgres\BulkInsertSaver';

$saver = new $saverClass($db, $tableName);
$saver->setOptAsync();

for ($i = 0; $i < 100000; $i++)
{
	$row = [
		'id'		=> $i,
		'group_id'	=> $i % 100,
		'name'		=> 'Name #' . $i,
		'content'	=> 'Content #' . $i,
		'date'		=> '2013-10-10 01:01:00',
		'bindata'	=> null
	];
	$saver[] = $row;
}
$saver->save();


