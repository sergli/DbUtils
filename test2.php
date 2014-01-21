<?php

include 'vendor/autoload.php';

$logger = new Monolog\Logger('ErrLogger');
$logger->pushHandler(
	new Monolog\Handler\StreamHandler('php://stderr'));

Monolog\ErrorHandler::register($logger);

strpos();

exit(1);
