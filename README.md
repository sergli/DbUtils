DbUtils - bulk load/update data into mysql/postgres (php 5.4+)
==============================

**Версия 0.1.3**

Библиотека предоставляет классы Saver и Updater, позволяющие прозрачно осуществлять загрузку в базы mysql и postgres больших объёмов данных. 

**Saver** накапливает предоставленные ему записи и по заполнении своего буфера выполняет запись. Это может быть в зависимости от выбора bulk-insert sql-запрос, либо загрузка временного csv-файла и т.д.

**Updater** предназначен для обновления **уже имеющихся** данных. Требует наличия primary или unique ограничения в таблице.

###Пример использования
`get_record()` - простая функция для генераии фиктивных данных. Сохраняем 10000 записей в таблицу _test.documents_. 
```php
<?php

use DbUtils\Adapter\Mysqli\Mysqli;
use DbUtils\Saver\Mysql\BulkInsertSaver;

function get_record()
{
    static $j = 0;

    $j++;

	return [
		'id'		=> $j,
		'group_id'	=> (int) $j / 100,
		'name'		=> "Name #$j",
		'content'	=> "Content #$j"
	];
}

$config = include 'config.php';

$adapter = new Mysqli($config['mysql']);

$tableName = 'test.documents';
$adapter->getTable($tableName)->truncate();

$saver = new BulkInsertSaver($adapter, $tableName);

for ($j = 1; $j <= 10000; $j++)
{
    $saver[] = get_record();
}
$saver->save(); //  сохраним остаток буфера
```

#### Адаптеры

Для открытия соединения доступны следующие адаптеры:
* Mysql
 1. `DbUtils\Adapter\Mysqli\Mysqli` на базе расширения **mysqli**. Доступна асинхронная загрузка.
 2. `DbUtils\Adapter\Pdo\Mysql` на базе расширения **pdo**

* Postgres
 1. `DbUtils\Adapter\Pgsql\Pgsql` на базе расширения **php_pgsql**. Доступна асинхронная загрузка, а также сэйвер PgCopyFromSaver.
 2. `DbUtils\Adapter\Pdo\Pgsql` на базе расширения **pdo**


### Методы записи/обновления
* Mysql
 1. `DbUtils\Saver\Mysql\BulkInsertSaver` Выполняет bulk-insert запросы вида 
 _INSERT INTO ... VALUES ..._
 2. `DbUtils\Saver\Mysql\LoadFileSaver` Выполняет запросы вида _LOAD DATA INFILE_
 3. `DbUtils\Updater\Mysql\BulkUpdater` Выполняет sql-запросы вида _INSERT ... ON DUPLICATE KEY UPDATE ..._ Предназначено только для обновления **уже имеющихся** в базе данных.
* Postgres
 1. `DbUtils\Saver\Postgres\BulkInsertSaver` Выполняет bulk-insert запросы вида _INSERT INTO ... VALUES ..._
 2. `DbUtils\Saver\Postgres\LoadFileSaver` Выполняет sql запросы вида _COPY ... FROM ... _
 3. `DbUtils\Saver\Postgres\PgCopyFromSaver` Загрузка с помощью выполнения функций pg_copy_from() (только для драйвера php_pgsql)
 4. `DbUtils\Updater\Postgres\BulkUpdater` Выполняет sql-запросы вида _UPDATE ... FROM (VALUES ( ... ))_

**Важно**: _bulk-insert_-сэйверы могут быть требовательны к памяти (особенно, при большом batchSize - размере буфера); load-file-сэйверы выполняют загрузку в БД из csv-файла и требуют минимум памяти.

#### Асинхронная запись
Адаптеры _mysqli_ и _pgsql_ поддерживают эту возможность. Использования асинхронных запросов может свести на нет оверхед на ожидание выполнения запросов. В случае, если большая часть времени тратится на генерацию данных (а не на выполнение sql), использование асинхронной записи не оправдано.

**Пример**
```php
/* .... */
$saver = new \DbUtils\Saver\Postgres\LoadFileSaver($adapter, $tableName);
$saver->setOptAsync();
```






