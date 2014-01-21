<?php

include '../vendor/autoload.php';

$dic = new DbUtils\DiContainer;
$db = $dic['postgres-wiki'];


$table = new DbUtils\Table\PostgresTable($db, 'ru.dayly_20071231');

var_dump('table', $table);

var_dump('connection', $table->getConnection());

var_dump('name', $table->getName());

var_dump('full_name', $table->getFullName());

var_dump('schema', $table->getSchema());

var_dump('recalculate', $table->recalculate());

var_dump('primary_key', $table->getPrimaryKey(), $table->getPK());

var_dump('constraints', $table->getConstraints());
