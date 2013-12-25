<?php

namespace DbUtils\Table\Postgres;
use DbUtils\Adapter\Postgres\Adapter as PostgresAdapter;
use DbUtils\Table\Postgres\Table as PostgresTable;

require_once __DIR__ . '/Table.php';

$opts = include __DIR__ . '/../../config.php';
$opts = $opts['postgres'];
$opts['dbname'] = 'wiki';

$db =  PostgresAdapter::getInstance(1, $opts);


$table = new PostgresTable($db, 'ru.dayly_20071231');

var_dump('table', $table);

var_dump('connection', $table->getConnection());

var_dump('name', $table->getName());

var_dump('full_name', $table->getFullName());

var_dump('schema', $table->getSchema());

var_dump('recalculate', $table->recalculate());

var_dump('primary_key', $table->getPrimaryKey(), $table->getPK());

var_dump('constraints', $table->getConstraints());
