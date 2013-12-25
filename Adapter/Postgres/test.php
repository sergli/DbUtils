<?php

use DbUtils\Adapter\Postgres\Adapter as PostgresAdapter;

require_once __DIR__ . '/Adapter.php';

$opts = include __DIR__ . '/../../config.php';
$opts = $opts['postgres'];

PostgresAdapter::setOptions($opts);

$db = PostgresAdapter::getInstance();

$sql = "select id, name from test";

$r = $db->query($sql);

var_dump(count($r));
foreach ($r as $row) { var_dump ($row); }
