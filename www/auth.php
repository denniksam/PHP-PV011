<?php
// Аутентификация: после скрипта появляется $_AUTH, если false - не было проверки,
//  если строка, то это сообщение об отказе (ошибке), если массив, то успешно
session_start() ;  // включаем работу с сессиями
$_CONTEXT[ 'auth_user' ] = false ;

// традиционно первым проверяется выход
if( isset( $_GET[ 'logout' ] ) ) {
    logout() ;
}

if( isset( $_POST[ 'userlogin' ] ) 
 && isset( $_POST[ 'userpassw' ] ) ) {   // переданы данные аутентификации
    // находим данные в БД по логину
    $sql = "SELECT * FROM Users u WHERE u.`login` = '{$_POST['userlogin']}'  AND u.delete_dt IS NULL " ;
    try {
        $res = $connection->query( $sql ) ;
        $row = $res->fetch( PDO::FETCH_ASSOC ) ;
        if( $row ) {   // пользователь найден
            $salt = $row[ 'salt' ] ;  // берем соль
            $hash = md5( $_POST[ 'userpassw' ] . $salt ) ;  // хешируем переданный пароль и соль
            if( $hash == $row[ 'pass' ] ) {  // сравниваем с сохраненным хешем
                // авторизация успешна
                // $_AUTH = $row ;   // все данные из БД оставляем в проекте (массив)
                // сохраняем в сессии факт авторизации - id пользователя
                $_SESSION[ 'auth_id' ] = $row[ 'id' ] ;
                // также сохраняем метку времени начала авторизованного режима
                $_SESSION[ 'auth_time' ] = time() ;
            }
            else {  // пароль неправильный
                // $_AUTH = "access denied" ;
                $_SESSION[ 'auth_error' ] = "access denied" ;
            }
        }
        else {  // такого логина нет в БД
            // $_AUTH = "access restricted" ;
            $_SESSION[ 'auth_error' ] = "access restricted" ;
        }

    }
    catch( PDOException $ex ) {
        echo $ex->getMessage() ;
        exit ;
    }
    header( "Location: " . $_CONTEXT[ 'path' ] ) ;
    exit ;
}

if( isset( $_SESSION[ 'auth_error' ] ) ) {
    $_CONTEXT[ 'auth_error' ] = $_SESSION[ 'auth_error' ] ;
    unset( $_SESSION[ 'auth_error' ] ) ;
}

if( isset( $_SESSION[ 'auth_id' ] ) ) {   // есть сохраненные данные аутентификации
    // проверяем длительность авторизованного режима
    $auth_interval = time() - $_SESSION[ 'auth_time' ] ;
    $_CONTEXT[ 'auth_interval' ] = $auth_interval ;
    if( $auth_interval > 10000 ) {
        logout() ;
    }
    // если интересует только время простоя (Idle), то здесь нужно обновить сохраненное время
    // $_SESSION[ 'auth_time' ] = time() ;

    // извлекаем данные о пользователе по сохраненному id
    $sql = "SELECT * FROM Users u WHERE u.`id` = ? AND u.delete_dt IS NULL" ;
    try {
        $prep = $_CONTEXT[ 'connection' ]->prepare( $sql ) ;
        $prep->execute( [ $_SESSION[ 'auth_id' ] ] ) ;
        $row = $prep->fetch( PDO::FETCH_ASSOC ) ;
        $_CONTEXT[ 'auth_user' ] = $row ;
        if( $row ) {
            unset( $_CONTEXT[ 'auth_user' ][ 'pass' ] ) ;
            unset( $_CONTEXT[ 'auth_user' ][ 'salt' ] ) ;
        }
        else {
            // Есть сохраненный в сессии ID, но запрос не дал результата. Вероятно пользователь был удален
            unset( $_SESSION[ 'auth_id' ] ) ;   // убираем ID из сессии
        }
    }
    catch( PDOException $ex ) {
        echo $ex->getMessage() ;
        exit ;
    }
}

function logout() {
    unset( $_SESSION[ 'auth_id' ] ) ;
    // по требованиям безопасности после смены авторизации необходимо перезагрузить
    // и желательно перевести на заведомо не требующую авторизации страницу - на главную
    header( "Location: /" ) ;
    exit ;
}

/*
Разработать и сверстать форму регистрации со всеми необходимыми для БД полями
добавить ссылку на форму (страницу) рядом c Log In
* реализовать добавление нового пользователя в БД с рег. данными.
*/


/*
Таблица пользователей
CREATE TABLE Users (
    `id`      CHAR(36)     NOT NULL  PRIMARY KEY   COMMENT 'UUID' ,
    `login`   VARCHAR(64)  NOT NULL,
    `name`    VARCHAR(64)  NULL,
    `salt`    CHAR(32)     NOT NULL  COMMENT 'random 128 bit hex-string',
    `pass`    CHAR(32)     NOT NULL  COMMENT 'password hash',
    `email`   VARCHAR(64)  NOT NULL,
    `confirm` CHAR(6)      NULL      COMMENT 'email confirm code',
    `reg_dt`  DATETIME     NOT NULL  DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB, DEFAULT CHARSET = UTF8

INSERT INTO Users VALUES( UUID(), 'admin', 'Root Administrator', )

CHAR(N) строка фиксированной длины (ровно N символов). Если передается меньше,
         то дополняется. Хранится ровно N символов. Подходит для хеш-строк,
         в т.ч. UUID
VARCHAR(N) строка переменной длины (от 0 до N символов). Хранится столько,
         сколько передано + один символ, отвечающий за реальную длину. 
         Подходит для имен, фамилий, почты
         
ALTER TABLE Users ADD COLUMN `avatar` VARCHAR(64) COMMENT 'avatar filename';

try {
    $connection->query( <<<SQL
CREATE TABLE Users (
    `id`      CHAR(36)     NOT NULL  PRIMARY KEY   COMMENT 'UUID' ,
    `login`   VARCHAR(64)  NOT NULL,
    `name`    VARCHAR(64)  NULL,
    `salt`    CHAR(32)     NOT NULL  COMMENT 'random 128 bit hex-string',
    `pass`    CHAR(32)     NOT NULL  COMMENT 'password hash',
    `email`   VARCHAR(64)  NOT NULL,
    `confirm` CHAR(6)      NULL      COMMENT 'email confirm code',
    `reg_dt`  DATETIME     NOT NULL  DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB, DEFAULT CHARSET = UTF8  
SQL
) ;
echo "User OK" ;
}
catch( PDOException $ex ) {
    echo $ex->getMessage() ;
}
exit ;        


$salt = md5( random_bytes(16) ) ;
$pass = md5( '123' . $salt ) ;
$sql = "INSERT INTO Users VALUES( UUID(), 'admin', 'Root Administrator', 
'$salt', '$pass', 'admin@i.ua', '123456', CURRENT_TIMESTAMP )" ;
try {
    $connection->query( $sql ) ;
    echo "INSERT OK" ;
}
catch( PDOException $ex ) {
    echo $ex->getMessage() ;
}
exit ;     */
