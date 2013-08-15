<?php

namespace db_utils\select\postgres;

use db_utils\adapter\postgres\Postgres;

require_once __DIR__ . '/../../adapter/postgres/Postgres.class.php';

$opts = include __DIR__ . '/../../config.php';
$opts = $opts['postgres'];

Postgres::setOptions($opts);

$db = Postgres::getInstance();

$it = $db->query("select * from generate_series(now(), now() + interval '1 year', '10 day') as y(date)");
foreach ($it as $key => $row) {
	var_dump($row);
}
