<?php

require_once '../vendor/autoload.php';

$dic = new DbUtils\DiContainer;
$db = $dic['postgres-new'];

$sql = "select id, name from test";

$r = $db->query($sql);

var_dump(count($r));
foreach ($r as $row) { var_dump ($row); }
