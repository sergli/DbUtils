<?php

namespace DbUtils\Select\Postgres;

use DbUtils\Adapter\Postgres\Adapter as PostgresAdapter;

require_once __DIR__ . '/../../Adapter/Postgres/Adapter.php';

$opts = include __DIR__ . '/../../config.php';
$opts = $opts['postgres'];

PostgresAdapter::setOptions($opts);

$db = PostgresAdapter::getInstance();

$it = $db->query("select * from generate_series(now(), now() + interval '1 year', '10 day') as y(date)");
foreach ($it as $key => $row) {
	var_dump($row);
}
