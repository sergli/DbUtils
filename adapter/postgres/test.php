<?php

namespace db_utils\adapter\postgres;

require_once __DIR__ . '/Postgres.class.php';

$opts = include __DIR__ . '/../../config.php';
$opts = $opts['postgres'];

Postgres::setOptions($opts);

$db = Postgres::getInstance();

var_dump($db->fetchAll('lalala'));
