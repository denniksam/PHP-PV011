<?php

if( $_SERVER[ 'REQUEST_METHOD' ] == 'POST' ) {
    // region прием файла
    if( isset( $_FILES[ 'image' ] ) ) {
        if( $_FILES[ 'image' ][ 'error' ] == 0    // файл есть и загружен
         && $_FILES[ 'image' ][ 'size' ] > 0 ) {  // без ошибок
        // проверяем расширение файла на допустимые,
        // заменяем имя и переносим в папку загрузок "images"
            $dot_position = strrpos( $_FILES['image']['name'], '.' ) ;  // strRpos ~ lastIndexOf
            if( $dot_position == -1 ) {  // нет расширения у файла
                $_SESSION[ 'add_error' ] = "File without type not supported" ;
            }
            else {
                $extension = substr( $_FILES[ 'image' ][ 'name' ], $dot_position ) ;  // расширение файла (с точкой ".png")
                if( ! array_search( $extension, [ '.jpg', '.png', '.jpeg', '.svg' ] ) ) {
                    $_SESSION[ 'add_error' ] = "File extension '{$extension}' not supported" ;
                }
                else {
                    $add_path = 'images/' ;
                    do {
                        $add_saved_name = bin2hex( random_bytes(8) ) . $extension ;
                    } while( file_exists( $add_path . $add_saved_name ) ) ;
                    if( ! move_uploaded_file( $_FILES[ 'image' ][ 'tmp_name' ], $add_path . $add_saved_name ) ) {
                        $_SESSION[ 'add_error' ] = "File (image) uploading error" ;
                    }
                }
            }
        }
        else {   // файл не передан, или загружен с ошибкой
            $_SESSION[ 'add_error' ] = "файл не передан, или загружен с ошибкой" ;
        }
    }
    else {   // на форме вообще нет файлового поля image
        $_SESSION[ 'add_error' ] = "на форме вообще нет файлового поля image" ;
    }
    // endregion

    // прием других данных формы
    if( empty( $_SESSION[ 'add_error' ] ) ) {
        if( empty( $_POST[ 'name' ] ) ) {
            $_SESSION[ 'add_error' ] = "Empty name" ;
        }
        else if( empty( $_POST[ 'price' ] ) ) {
            $_SESSION[ 'add_error' ] = "Empty price" ;
        }
        $sql = "INSERT INTO Products( `id`, `name`,`descr`,  
            `price`,`discount`,`image` ) VALUES( UUID(), ?, ?, ?, ?, ? ) " ;
        $params = [
            $_POST[ 'name' ],
            $_POST[ 'descr' ] ?? null,
            $_POST[ 'price' ],
            $_POST[ 'discount' ] ?? null,
            $add_saved_name
        ] ;
        try {
            $prep = $_CONTEXT[ 'connection' ]->prepare( $sql ) ;
            $prep->execute( $params ) ;
        }
        catch( PDOException $ex ) {
            $_CONTEXT['logger']( 'shop_controller ' . $ex->getMessage() . $sql . var_export( $params, true ) ) ;
            $_SESSION[ 'add_error' ] = "Server error try later" ;
        }
    }
    if( empty( $_SESSION[ 'add_error' ] ) ) {
        $_SESSION[ 'add_error' ] = "Добавлено успешно" ;
    }

    header( "Location: /shop" ) ;
    exit ;
    // print_r( $_FILES ) ;
}
else if( $_SERVER[ 'REQUEST_METHOD' ] == 'GET' ) {
    $view_data = [] ;
    if( isset( $_SESSION[ 'add_error' ] ) ) {
        $view_data[ 'add_error' ] = $_SESSION[ 'add_error' ] ;
        unset( $_SESSION[ 'add_error' ] ) ;
        // при ошибке сохраняются введенные данные - восстанавливаем
        $view_data['login']    = $_SESSION[ 'login' ] ;
        $view_data['email']    = $_SESSION[ 'email' ] ;
        $view_data['userName'] = $_SESSION[ 'userName' ] ;
    }
    // перечень товаров
    // Д.З. Из анализа параметра $_GET['sort'] определить способ сортировки товаров
    // и сформировать соответствующий запрос (часть запроса) на выборку из БД
    $sql = "SELECT * FROM Products p    ORDER BY p.add_dt DESC     LIMIT 0, 10" ;
    try {
        $view_data[ 'products' ] = 
            $_CONTEXT[ 'connection' ]->query( $sql )->fetchAll( PDO::FETCH_ASSOC ) ;
    }
    catch( PDOException $ex ) {
        $_CONTEXT['logger']( 'shop_controller ' . $ex->getMessage() . $sql . var_export( $params, true ) ) ;
        $view_data[ 'add_error' ] = "Server error try later" ;
    }
    include "_layout.php" ;  // ~return View
}