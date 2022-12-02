<?php
// одна из задача контроллера - разделить работу по методам запроса
// echo "<pre>" ; print_r( $_SERVER ) ;

// @ - подавление вывода ошибок
@session_start() ;   // сессии - перед обращением к сессии обязательно. $_SESSION[] - формируется стартом
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
    // echo "<pre>" ; print_r( $_FILES ) ; exit ;
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
        
        // Проверяем наличие аватарки и загружаем файл:
        // Берем имя файла, отделяем расширение, проверяем на допустимые (изображения)
        // сохраняем расширение, но меняем имя файла (генерируем случайно)
        //  использовать переданные имена опасно (возможный конфликт - перезапись,
        //  возможные DT-атаки со спецсимволами в именах файлов (../../) )
        // Файлы храним в отдельной папке, их имена (с расширениями) - в БД

        if( isset( $_FILES['avatar'] ) ) {  // наличие файлового поля на форме
            if( $_FILES['avatar']['error'] == 0 && $_FILES['avatar']['size'] != 0 ) {
                // есть переданный файл
                $dot_position = strrpos( $_FILES['avatar']['name'], '.' ) ;  // strRpos ~ lastIndexOf
                if( $dot_position == -1 ) {  // нет расширения у файла
                    $_SESSION[ 'reg_error' ] = "File without type not supported" ;
                }
                else {
                    $extension = substr( $_FILES['avatar']['name'], $dot_position ) ;  // расширение файла (с точкой ".png")
                    /* Загрузка аватарки:
                        v проверить расширение файла на допустимый перечень
                        v сгенерировать случайное имя файла, сохранить расширение
                        v загрузить файл в папку www/avatars
                        его имя добавить в параметры SQL-запроса и передать в БД
                    */
                    // echo $extension ; exit ;
                    if( ! array_search( $extension, ['.jpg', '.png', '.jpeg', '.svg'] ) ) {
                        $_SESSION[ 'reg_error' ] = "File extension '{$extension}' not supported" ;
                    }
                    else {
                        $avatar_path = 'avatars/' ;
                        do {
                            $avatar_saved_name = bin2hex( random_bytes(8) ) . $extension ;
                        } while( file_exists( $avatar_path . $avatar_saved_name ) ) ;
                        if( ! move_uploaded_file( $_FILES['avatar']['tmp_name'], $avatar_path . $avatar_saved_name ) ) {
                            $_SESSION[ 'reg_error' ] = "File (avatar) uploading error" ;
                        }
                    }
                }
            }
        }
    }

    if( empty( $_SESSION[ 'reg_error' ] ) ) {
        // подключаем фукнцию отправки почты
        @include_once "helper/send_email.php" ;
        if( ! function_exists( "send_email" ) ) {
            $_SESSION[ 'reg_error' ] = "Inner error" ;
        }
    }
    
    if( empty( $_SESSION[ 'reg_error' ] ) ) {  // не было ошибок выше
        // $_SESSION[ 'reg_error' ] = "OK" ;
        $salt = md5( random_bytes(16) ) ;
        $pass = md5( $_POST['confirm'] . $salt ) ;
        $confirm_code = bin2hex( random_bytes(3) ) ;

        // отправляем код на указанную почту        
        send_email( $_POST['email'], 
            "pv011.local Email verification", 
            "<b>Hello, {$_POST['userName']}</b><br/>
            Type code <strong>$confirm_code</strong> to confirm email<br/>
            Or follow next <a href='https://pv011.local/confirm?code={$confirm_code}&email={$_POST['email']}'>link</a>" ) ;

        $sql = "INSERT INTO Users(`id`,`login`,`name`,`salt`,`pass`,`email`,`confirm`,`avatar`) 
                VALUES(UUID(),?,?,'$salt','$pass',?,'$confirm_code',?)" ;
        try {
            $prep = $connection->prepare( $sql ) ;
            $prep->execute( [ 
                $_POST['login'], 
                $_POST['userName'], 
                $_POST['email'],
                isset( $avatar_saved_name ) ? $avatar_saved_name : null
            ] ) ;
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