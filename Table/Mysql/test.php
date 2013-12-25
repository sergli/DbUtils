<?php

namespace DbUtils\Table\Mysql;

use DbUtils\Adapter\Mysql\Adapter as MysqlAdapter;
use DbUtils\Table\Mysql\Table as MysqlTable;

require_once __DIR__ . '/Table.php';

$opts = include __DIR__ . '/../../config.php';
$opts = $opts['mysql'];

$opts['dbname'] = 'test';

$db =  MysqlAdapter::getInstance(1, $opts);


$table = new MysqlTable($db, 'tableB');

var_dump('table', $table);

var_dump('connection', $table->getConnection());

var_dump('name', $table->getName());

var_dump('full_name', $table->getFullName());

var_dump('schema', $table->getSchema());

var_dump('recalculate', $table->recalculate());

var_dump('primary_key', $table->getPrimaryKey(), $table->getPK());

var_dump('constraints', $table->getConstraints());
