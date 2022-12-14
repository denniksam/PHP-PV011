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
    // условие сортировки
    if( isset( $_GET['sort'] ) ) {
        $view_data[ 'sort' ] = $_GET['sort'] ;
        $order_part = "ORDER BY " ;
        switch( $view_data[ 'sort' ] ) {
            case 2  : $order_part .= 'p.price  ASC' ; break ;
            case 3  : $order_part .= 'p.rating DESC' ; break ;
            default : $order_part .= 'p.add_dt DESC' ;
        }
    } else $order_part = "" ;
    // перечень товаров
    // пагинация
    // 1. сколько всего товаров
    $sql = "SELECT COUNT(*) FROM Products " ;
    try { $total = $_CONTEXT[ 'connection' ]->query( $sql )->fetch(PDO::FETCH_NUM)[0] ; }
    catch( PDOException $ex ) {
        $_CONTEXT['logger']( 'shop_controller1 ' . $ex->getMessage() . $sql ) ;
        $view_data[ 'add_error' ] = "Server error try later" ;
    }
    if( empty( $view_data[ 'add_error' ] ) ) {
        // 2. номер страницы и кол-во элементов на странице
        $perpage = 4 ;
        $lastpage = ceil( $total / $perpage ) ;
        @$page = intval( $_GET['page'] ) ?? 1 ;         //  $page  1      2      3      4       
        if( $page < 1 ) $page = 1 ;                     //  nums   1-4    5-8    9-12   13,14,15 (всего 15)
        if( $page > $lastpage ) $page = $lastpage ;     //  $skip  0      4      8      12
        $skip = ( $page - 1 ) * $perpage ;
        $view_data[ 'paginator' ] = [
            'page' => $page,
            'perpage' => $perpage,
            'lastpage' => $lastpage
        ] ;
        $sql = "SELECT * FROM Products p    $order_part     LIMIT $skip, $perpage" ;
        try {
            $view_data[ 'products' ] = 
                $_CONTEXT[ 'connection' ]->query( $sql )->fetchAll( PDO::FETCH_ASSOC ) ;
        }
        catch( PDOException $ex ) {
            $_CONTEXT['logger']( 'shop_controller2 ' . $ex->getMessage() . $sql . var_export( $params, true ) ) ;
            $view_data[ 'add_error' ] = "Server error try later" ;
        }
    }
    include "_layout.php" ;  // ~return View
}

/*
Пагинация (от англ. Pagination) - разбиение на страницы
Когда контента много показывается часть и
собирается элемент-пагинатор, переключающий страницы
 <--  <- 1 2  ... 31 [32] 33 ... 131 132 ->  -->

 Д.З. Скрыть форму добавления товара от всех, кроме админа
 при отсутствии рейтинга товара выводить рейтинг "0" или "нет оценок" 
 аналогично выводить при отсутствии описания
*/
