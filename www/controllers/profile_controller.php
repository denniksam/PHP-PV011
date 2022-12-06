<?php
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
    ] ;
}
else {
    // просмотр чужого профиля
    $sql = "SELECT u.* FROM Users u WHERE u.login = ?" ;
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
