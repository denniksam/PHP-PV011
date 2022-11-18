<h2>Работа с базами данных</h2>
<h3>Настройка СУБД</h3>
<p>
    При работе с БД обычно на хостинге выдается
    логин/пароль и готовая БД. Поэтому, и для локальных
    сайтов желательно создать отдельного пользователя
    и отдельную БД для каждого из сайтов. 
</p>
<pre>
    Подключаемся к СУБД:<br/>
    а) через консоль (терминал)<br/>
    б) phpmyadmin (http://localhost/phpmyadmin)<br/>
    в) стороннее ПО для БД: MySQL Workbench, DBeaver, ...<br/>
    <br/>
    (дальше на примере терминала)
    <br/>
    Запускаем терминал (кнопкой Shell на панели XAMPP / cmd)
    Переходим в папку 
    > cd mysql/bin
    Запускаем консоль БД
    > mysql -u root        (если нет пароля - новая установка)
    > mysql -u root -p     (если пароль установлен)
    -- попадаем в СУБД-клиент (консоль)
    Создаем БД для сайта (pv011)
    >> CREATE DATABASE pv011;
    Создаем пользователя и даем ему доступ к новой БД
    (логин - pv011_user, пароль - pv011_pass)
    >> GRANT ALL PRIVILEGES ON pv011.* 
       TO pv011_user@localhost 
       IDENTIFIED BY 'pv011_pass';
    Проверяем: выходим из консоли
    >> exit
    Опять Запускаем консоль, только от имени нового пользователя
    > mysql -u pv011_user -p
    password:  (вводим) pv011_pass
    Если вход успешный - пользователь создан, пароль корректный
    Проверяем видимость БД
    >> SHOW DATABASES;
    +--------------------+
    | Database           |
    +--------------------+
    | information_schema |
    | pv011              |  (признак видимости БД)
    | test               |
    +--------------------+
</pre>
<h3>Подключение к БД из РНР</h3>
<p>
    Для работы с БД в РНР есть несколько вариантов:
    набор команд для конкретной БД (mysql_...,  ib_...)
    или более современный инструмент - PDO (аналог ADO .NET)
</p>
<p> Подключение:
<?php 
try {
    $connection = new PDO( 
        "mysql:host=localhost;port=3306;dbname=pv011;charset=utf8", 
        "pv011_user", "pv011_pass", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true
        ] ) ;
    echo "Connection OK" ;
}
catch( PDOException $ex ) {
    echo $ex->getMessage() ;
}?>
</p>
<p>
    Выполнение запросов. DDL.
    Data Definition Language - язык "разметки" данных: создание баз, 
    таблиц и т.п. 
    Особенности MySQL:
     нет отдельного типа для UUID, (но есть функции-генераторы)
      используем CHAR(36)
     нет N-типов (Юникод), кодировка текстовых полей задается 
      CHARSET-ом для таблицы в целом (либо для каждого поля отдельно)
     есть несколько "движков" в рамках  MySQL (MyISAM, InnoDB, ...)
     наличие условий IF EXISTS / IF NOT EXISTS позволяющих
      выполнять команду условно

     <br/>
     Результат запроса:
<?php 
$sql = <<<SQL
 CREATE TABLE  IF NOT EXISTS  demo (
    id      CHAR(36)   NOT NULL   PRIMARY KEY,
    val_int INT,
    val_str VARCHAR(128)
 ) Engine = InnoDB, DEFAULT CHARSET = utf8   
SQL;
try {
    $connection->query( $sql ) ;
    echo "Table 'demo' OK" ;
}
catch( PDOException $ex ) {
    echo $ex->getMessage() ;
}?></p>
<p>
    DML - язык манипулирования данными
<?php
$x = random_int(1000, 10000) ;
$s = bin2hex( random_bytes(8) ) ;
$sql = "INSERT INTO demo VALUES( UUID(), $x, '$s' ) " ;
try {
    $connection->query( $sql ) ;
    echo "INSERT OK" ;
}
catch( PDOException $ex ) {
    echo $ex->getMessage() ;
}?>
</p>
<p>
    DML. SELECT
<?php
$sql = "SELECT * FROM `demo` " ;  // ``(MySQL) - аналог [] (MS SQL)
try {
    $res = $connection->query( $sql ) ;   // ~table (таблица рез-тов)
    while( $row = $res->fetch( PDO::FETCH_ASSOC ) ) {   // строка таблицы
        // print_r( $row ) ;  // данные дублируются - по индексу и по имени
        echo "{$row['id']} {$row['val_str']} <br/>" ;
    }  // PDO::FETCH_ASSOC - только с именами, 
       // PDO::FETCH_NUM - только с индексами, 
       // PDO::FETCH_BOTH - дублирование (по умолчанию)
}
catch( PDOException $ex ) {
    echo $ex->getMessage() ;
}?>   
</p>
Д.З. Реализовать запрос к БД на выдачу данных
отобразить данные в виде таблицы (HTML)
** предполагать, что количество и названия полей заранее не известны
+--------------------------------------+---------+------------------+
| id                                   | val_int | val_str          |
+--------------------------------------+---------+------------------+
| 3daf30f0-676d-11ed-a78a-14857fd97497 |    8320 | fc271461377e8525 |
| 900ae3ec-676d-11ed-a78a-14857fd97497 |    1804 | c2ab8a241e0d5545 |
+--------------------------------------+---------+------------------+
