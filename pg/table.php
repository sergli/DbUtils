<?php

include_once __DIR__ . '/PgSaver.php';
include_once __DIR__ . '/PgUpdater.php';
include_once __DIR__ . '/PgTable.php';

$dsn = 'pgsql:dbname=wiki_search;host=localhost';
$user = 'sergli';
$password = 'bibEgOk2';

try {
    $conn = new PDO($dsn, $user, $password);
	echo "Подключение успешно\n";
}
catch (PDOException $e) {
    echo 'Подключение не удалось: ' . $e->getMessage();
}
$conn->query("SET search_path=wiki_structured_ru_1_20121107,wiki_source_ru_1_20121107,wiki_objects_ru_1_20121107");
$tableName = '"wiki_source_ru_1_20121107".test';
//$conn->exec("truncate table $table restart identity;");

$tableName = 'wiki_structured_ru_1_20110815.articles';

try {

	$Table = new PgTable($conn, $tableName);

//	print_r($Table->getColumns());

//	print_r($Table->getConstraints());

//	print_r($Table->getIndexes());
	
	print_r($Table->getPrimaryKey());
	print_r($Table->getUniques());
	print_r($Table->getConstraints());

}
catch (Exception $e) {
	echo $e->getMessage() . "\n\n";
	exit;
}



