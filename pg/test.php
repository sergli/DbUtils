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

$table = '"wiki_source_ru_1_20121107".test';


try {
	$class = 'PgSaver';
	$class = 'PgUpdater';
	if ($class == 'PgSaver') {
		$conn->exec("truncate table $table restart identity;");
	}
	$saver = new $class($conn, new PgTable($conn, $table), 
		array('name', 'date'));
	$saver->setBatchSize(100);

//	$saver->addRow(array('name' => false, 'date' => null));

//	$saver->addrow(array('name' => true, 'date' => false));

//	$saver->addrow(array('date'=>null,'name'=>"'2012-10-10'"));

	$saver->addRow(array(
		'name' => 'Евгений', 
		'date' => '2012-04-30 00:01:02'
	));

	$saver->addRow(array(
		'name' => 'Петросян',
		'date' => '2011-01-02 11:00:10'
	));

	$saver->addRow(array(
		'name' => 'Алла',
		'date' => '1970-01-02 14:01:20'
	));

//	$saver->addRow(array(
//		'name2' => 'Пугачёва',
//		'date' => '2000-03-14 02:30:00'
//	));
	
	$saver->addRow(array(
		'name' => 'Алла22',
		'date' => '2084-01-01 14:40:55'
	));

}
catch (Exception $e) {
	echo get_class($e) .": " . $e->getMessage();
	exit;
}

$saver->save();

var_dump($conn->query("select * from $table")->fetchAll(PDO::FETCH_ASSOC));

