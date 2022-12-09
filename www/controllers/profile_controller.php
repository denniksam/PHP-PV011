<?php
if( $_SERVER[ 'REQUEST_METHOD' ] == 'DELETE' ) {
    if( ! is_array( $_CONTEXT[ 'auth_user' ] ) ) {  // неавторизованный запрос
        http_response_code( 401 ) ;
        echo "Unauthorized" ;
        exit ;
    }
    $sql = "UPDATE Users u SET u.delete_dt = CURRENT_TIMESTAMP WHERE u.id = '{$_CONTEXT['auth_user']['id']}' " ;
    try {
        $_CONTEXT['connection']->query( $sql ) ;
    }
    catch( PDOException $ex ) {       
        $_CONTEXT['logger'](          
            'profile_controller '     
            . $ex->getMessage()       
            . $sql ) ;   
        http_response_code( 500 ) ;          
        echo "Internal Server Error" ;   
        exit ;                
    }
    echo "Ok" ;
    exit ;
}

if( $_SERVER[ 'REQUEST_METHOD' ] == 'PUT' ) {
    if( ! is_array( $_CONTEXT[ 'auth_user' ] ) ) {  // неавторизованный запрос
        http_response_code( 401 ) ;
        echo "Unauthorized" ;
        exit ;
    }
    // print_r( $_REQUEST ) ;  // PUT запрос не разбирает тело автоматически
    $body = file_get_contents( "php://input" ) ;  // служебное имя входного потока тела запроса
    // данные приходят в формате JSON - декодируем их json_decode. без (true) создается объект
    // с объектами РНР работает чуть хуже, чем с массивами
    $data = json_decode( $body, true ) ;  // преобразование полученных данных в ассоц.массив(true)
    
    // TODO: проверить новый логин на занятость, если меняется почта, то высылать код подтверждения
    $sql = "SELECT u.login, u.name, u.email FROM Users u WHERE u.id = ?" ;

    $sql = "UPDATE Users u SET u.login = ?, u.name = ?, u.email = ? WHERE u.id = ?" ;
    $pars = [ $data["login"], $data["name"], $data["email"], $_CONTEXT['auth_user']['id'] ] ;
    try {
        $prep = $_CONTEXT['connection']->prepare( $sql ) ;
        $prep->execute( $pars ) ;
    }
    catch( PDOException $ex ) {                 // В случае ошибки - логируем
        $_CONTEXT['logger'](                    // записываем:
            'profile_controller '               //   файл или имя скрипта
            . $ex->getMessage()                 //   сообщение ошибки
            . $sql                              //   текст запроса
            . var_export( $pars, true ) ) ;     //   данные, подставленные в запрос
        // $_CONTEXT['show500']() ;             // Вариант для view
        http_response_code( 500 ) ;             // Вариант для API/fetch                   
        echo "Internal Server Error" ;          //                         
        exit ;                                  //     
    }
    echo "Ok" ;
    exit ;
}

$_CONTEXT[ 'page_title' ] = "Profile" ;

if( empty( $_CONTEXT[ 'path_parts' ][2] ) ) {
    echo "Профиль не найден";
    echo "<script>setTimeout( ()=>{window.location='/'} ,2000 )</script>";
    exit ;
}

if( is_array( $_CONTEXT[ 'auth_user' ] ) 
 && $_CONTEXT[ 'auth_user' ][ 'login' ] == $_CONTEXT[ 'path_parts' ][2] ) {
    // свой профиль - авторизованный режим
    $_PROF_DATA = [
        'avatar' => $_CONTEXT[ 'auth_user' ][ 'avatar' ] ,
        'login'  => $_CONTEXT[ 'auth_user' ][ 'login'  ] ,
        'name'   => $_CONTEXT[ 'auth_user' ][ 'name'   ] ,
        'email'  => $_CONTEXT[ 'auth_user' ][ 'email'  ] ,
        'title'  => "My profile"
    ] ;
}
else {
    // просмотр чужого профиля
    $sql = "SELECT u.* FROM Users u WHERE u.login = ? AND u.delete_dt IS NULL" ;
    try {
        $prep = $_CONTEXT[ 'connection' ]->prepare( $sql ) ;
        $prep->execute( [ $_CONTEXT[ 'path_parts' ][2] ] ) ;
        $row = $prep->fetch( PDO::FETCH_ASSOC ) ;
        if( $row ) {
            $_PROF_DATA = [
                'avatar' => $row[ 'avatar' ] ,
                'login'  => $row[ 'login'  ] ,
                'name'   => $row[ 'name'   ] ,
                'email'  => $row[ 'email'  ] ,
                'title'  => $row[ 'login'  ] . " profile"
            ] ;
        }
        else {
            $_PROF_DATA = [ ] ;
        }
    }
    catch( PDOException $ex ) {
        echo $ex->getMessage() ;
        exit ;
    }
}

include "_layout.php" ;
