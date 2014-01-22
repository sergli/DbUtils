<?php

require_once '../vendor/autoload.php';

$db = (new DbUtils\DiContainer)['postgres'];

$it = $db->query("select * from generate_series(now(), now() + interval '1 year', '10 day') as y(date)");
foreach ($it as $key => $row) {
	var_dump($row);
}
