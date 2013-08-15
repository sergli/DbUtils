<?php

namespace db_utils\table\mysql;
use db_utils\adapter\mysql\Mysql;

require_once __DIR__ . '/MysqlTable.class.php';

$opts = include __DIR__ . '/../../config.php';
$opts = $opts['mysql'];

$db =  Mysql::getInstance(1, $opts);


$table = new MysqlTable($db, 'popularity');

var_dump('table', $table);

var_dump('connection', $table->getConnection());

var_dump('name', $table->getName());

var_dump('full_name', $table->getFullName());

var_dump('schema', $table->getSchema());

var_dump('recalculate', $table->recalculate());

var_dump('primary_key', $table->getPrimaryKey(), $table->getPK());
