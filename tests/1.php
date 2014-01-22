<?php

use DbUtils\Adapter\Mysqli\Mysqli;

require_once '../vendor/autoload.php';

$ci = new DbUtils\DiContainer;

$db = $ci['mysql'];

$sql = 'select query, url, count from clicklog.popularity limit 10';
/*
var_dump('db', $db);
var_dump('table_class', $db::getTableClass());

$r = $db->query($sql);

var_dump('select', $r);

$all = $db->fetchAll($sql);

var_dump('all', $all);
$pairs = $db->fetchPairs($sql);

var_dump('pairs', $pairs);

$row = $db->fetchRow($sql);

var_dump('row', $row);


$cell = $db->fetchOne($sql);

var_dump('cell', $cell);
*/

$tableName = 'documents';

try {
	$table = $db->getTable($tableName);
	var_dump('table', $table);
}
catch (\Exception $e) {
	var_dump('table', $e->getMessage());
}

var_dump('table_exists', $db->tableExists($tableName),
	$db->tableExists('lalala'));
var_dump('table_columns', $db->getTable($tableName)->getColumns());

/*
var_dump('FOREACH');

foreach ($db->query($sql) as $key => $val) {
	var_dump($key, $val);
}
*/
