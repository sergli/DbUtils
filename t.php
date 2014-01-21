<?php

include 'vendor/autoload.php';

$ci = new DbUtils\DIC;

$config = $ci['config']['postgres'];

$db = new DbUtils\Adapter\PDO\Postgres($config);

var_dump($db->getTable('documents'));
var_dump($db->fetchCol("select * from generate_series(1,10,2)"));
