<?php

use DbUtils\Adapter\Postgres\Adapter as PostgresAdapter;

require_once '../vendor/autoload.php';

$opts = include '../config.php';
$opts = $opts['postgres'];

PostgresAdapter::setOptions($opts);

$db = PostgresAdapter::getInstance();

$sql = "select id, name from test";

$r = $db->query($sql);

var_dump(count($r));
foreach ($r as $row) { var_dump ($row); }
