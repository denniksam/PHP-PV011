<?php
// одна из задача контроллера - разделить работу по методам запроса
// echo "<pre>" ; print_r( $_SERVER ) ;

session_start() ;   // сессии - перед обращением к сессии обязательно. $_SESSION[] - формируется стартом
switch( strtoupper( $_SERVER[ 'REQUEST_METHOD' ] ) ) {
case 'GET'  :
    $view_data = [] ;
    if( isset( $_SESSION[ 'reg_error' ] ) ) {
        $view_data['reg_error'] = $_SESSION[ 'reg_error' ] ;
        unset( $_SESSION[ 'reg_error' ] ) ;
        // при ошибке сохраняются введенные данные - восстанавливаем
        $view_data['login']    = $_SESSION[ 'login' ] ;
        $view_data['email']    = $_SESSION[ 'email' ] ;
        $view_data['userName'] = $_SESSION[ 'userName' ] ;
    }
    if( isset( $_SESSION[ 'reg_ok' ] ) ) {
        $view_data['reg_ok'] = $_SESSION[ 'reg_ok' ] ;
        unset( $_SESSION[ 'reg_ok' ] ) ;
    }
    include "_layout.php" ;  // ~return View
    break ;

case 'POST' :
    // данные формы регистрации - обрабатываем
    if( empty( $_POST['login'] ) ) {
        $_SESSION[ 'reg_error' ] = "Empty login" ;
    }
    else if( empty( $_POST['userPassword1'] ) ) {
        $_SESSION[ 'reg_error' ] = "Empty userPassword1" ;
    }
    else if( empty( $_POST['email'] ) ) {
        $_SESSION[ 'reg_error' ] = "Empty email" ;
    }
    else if( $_POST['userPassword1'] !== $_POST['confirm'] ) {
        $_SESSION[ 'reg_error' ] = "Passwords mismatch" ;
    } else {
        try {
            $prep = $connection->prepare( 
                "SELECT COUNT(id) FROM Users u WHERE u.`login` = ? " ) ;
            $prep->execute( [ $_POST['login'] ] ) ;
            $cnt = $prep->fetch( PDO::FETCH_NUM )[0] ;
        }
        catch( PDOException $ex ) {
            $_SESSION[ 'reg_error' ] = $ex->getMessage() ;
        }
        if( $cnt > 0 ) {
            $_SESSION[ 'reg_error' ] = "Login in use" ;
        }
    }
    if( empty( $_SESSION[ 'reg_error' ] ) ) {  // не было ошибок выше
        // $_SESSION[ 'reg_error' ] = "OK" ;
        $salt = md5( random_bytes(16) ) ;
        $pass = md5( $_POST['confirm'] . $salt ) ;
        $confirm_code = bin2hex( random_bytes(3) ) ;
        $sql = "INSERT INTO Users(`id`,`login`,`name`,`salt`,`pass`,`email`,`confirm`) 
                VALUES(UUID(),?,?,'$salt','$pass',?,'$confirm_code')" ;
        try {
            $prep = $connection->prepare( $sql ) ;
            $prep->execute( [ $_POST['login'], $_POST['userName'], $_POST['email'] ] ) ;
            $_SESSION[ 'reg_ok' ] = "Reg ok" ;
        }
        catch( PDOException $ex ) {
            $_SESSION[ 'reg_error' ] = $ex->getMessage() ;
        }
    }
    else {  // были ошибки - сохраняем в сессии все введенные значения (кроме пароля)
        $_SESSION['login']    = $_POST['login'] ;
        $_SESSION['email']    = $_POST['email'] ;
        $_SESSION['userName'] = $_POST['userName'] ;
    }

    // echo "<pre>" ; print_r( $_POST ) ;
    // на запросы кроме GET (кроме API) всегда возвращается redirect
    header( "Location: /" . $path_parts[1] ) ;
    break ;
}
/*
Д.З. Реализовать валидацию эл. почты пользователя
стилизовать вывод сообщения об успешной регистрации
*/