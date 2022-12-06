<?php
// контроллер подтверждения почты
// сюда можно попасть а) по ссылке из письма, тогда в параметрах есть email
// б) со страницы ввода кода, тогда в параметрах только код, но должна быть авторизация

if( empty( $_GET[ 'code' ] ) ) {   // несанкционированный переход
    echo "Неправильный код" ;
    exit ;
}
if( empty( $_CONTEXT ) ) {  // массив, создаваемый в диспетчере доступа, если его нет - неправильный запуск
    echo "Неправильный запуск" ;
    exit ;
}

if( isset( $_GET[ 'email' ] ) ) {  // переход по ссылке из письма
    $sql = "SELECT COUNT(u.id) FROM Users u WHERE u.email = ? AND u.confirm = ?" ;
    try {
        $prep = $_CONTEXT[ 'connection' ]->prepare( $sql ) ;
        $prep->execute( [ $_GET[ 'email' ], $_GET[ 'code' ] ] ) ;
        $cnt = $prep->fetch( PDO::FETCH_NUM )[0] ;
        if( $cnt == 0 ) {
            echo "Неправильный код подтверждения" ;
            exit ;
        }
        else {
            $sql = "UPDATE Users u SET u.confirm = NULL WHERE u.email = ? AND u.confirm = ?" ;
            $prep = $_CONTEXT[ 'connection' ]->prepare( $sql ) ;
            $prep->execute( [ $_GET[ 'email' ], $_GET[ 'code' ] ] ) ;
            echo "Почта подтверждена" ;
            exit ;
        }
    }
    catch( PDOException $ex ) {
        echo $ex->getMessage() ;
        exit ;
    }
}
else {   // б) со страницы ввода кода - должна быть авторизация и $_CONTEXT[ 'auth_user' ]
         // но возможен прямой переход по ссылке - нужно проверять факт авторизации
    if( empty( $_CONTEXT[ 'auth_user' ] ) ) {
        echo "Авторизуйтесь для подтверждения почты";
        exit ;
    }
    // Извлекаем код подтверждения по id пользователя
    $sql = "SELECT u.confirm FROM Users u WHERE u.id = '{$_CONTEXT['auth_user']['id']}' " ;
    try {
        $db_code = $_CONTEXT[ 'connection' ]->query( $sql )->fetch( PDO::FETCH_NUM )[0] ;
        if( $db_code == NULL ) {
            echo "Почта подтверждена, действий не требуется" ;
            exit ;
        }
        else if( $db_code == $_GET[ 'code' ] ) {
            // Код подтвержден - сбрасываем в БД
            $sql = "UPDATE Users u SET u.confirm = NULL WHERE u.id = '{$_CONTEXT['auth_user']['id']}' ";
            $_CONTEXT[ 'connection' ]->query( $sql ) ;
            echo "Почта подтверждена" ;
            exit ;
        }
        else {
            echo "Неправильный код подтверждения" ;
            exit ;
        }
    }
    catch( PDOException $ex ) {
        echo $ex->getMessage() ;
        exit ;
    }
}
// print_r( $_GET ) ;
/* Составить запрос для подтверждения кода при известном коде и
почте (email). Подтверждением будем считать сброс сохраненного кода
в NULL
*/
