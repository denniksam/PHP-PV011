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
    $where_part = "" ;
    // либо фильтры, либо поиск
    if( isset( $_GET[ 'search' ] ) ) {
        // поисковый фрагмент может нести опасность при вставке в запрос, поэтому требует экранирования
        // (escaping). Если нет возможности использовать подготовленные запросы (а у нас уже 
        //  всё построено без них), то нужно искать док-цию драйвера БД по экранированию
        // Например, в одних БД символ ' экранируется как \', а в других как ''
        $fragment = $_CONTEXT[ 'connection' ]->quote( $_GET[ 'search' ] ) ;
        // Обычно поиск проводится по всем вариативным полям (тексты, артикулы, коды товара)
        $where_part = " WHERE INSTR( p.name, $fragment ) OR  INSTR( p.descr, $fragment ) " ;

        $view_data[ 'search' ] = $_GET[ 'search' ] ;
    // echo $where_part; exit ;
    }
    else {
        //////////////////////////////// примененные фильтры //////////////////////////////////////////
        // макс-мин цены
        $filters = [] ;
        if( isset( $_GET['minprice'] ) && is_numeric( $_GET['minprice'] ) ) {   // фильтр на мин. цену
            $where_part = " WHERE p.price >= {$_GET['minprice']}" ;
            $filters[ 'minprice' ] = $_GET[ 'minprice' ] ;
        }
        if( isset( $_GET['maxprice'] ) && is_numeric( $_GET['maxprice'] ) ) {   // фильтр на мин. цену
            $where_part .= 
                ( ($where_part == "") ? " WHERE " : " AND " )
                . " p.price <= {$_GET['maxprice']}" ;
            $filters[ 'maxprice' ] = $_GET[ 'maxprice' ] ;
        }       
        // группы товаров - по принципу id=grp -- ищем параметры со значением grp и берем их имена
        $filters[ 'product_groups_id' ] = [] ;
        $filters[ 'product_groups_name' ] = [] ;
        foreach( $_GET as $k => $v ) {
            if( $v == 'grp' ) {
                $filters[ 'product_groups_id' ][] = $k ;
            }
        }
        if( count( $filters[ 'product_groups_id' ] ) > 0 ) {  // AND p.id_grp IN ( '111','222','333' )
            $where_part .=  
                ( ($where_part == "") ? " WHERE " : " AND " )
                . "p.id_grp IN ( '" . implode( "','", $filters[ 'product_groups_id' ] ) . "' ) " ;
        }

        $view_data[ 'filters' ] = $filters ;
    }

    // echo $where_part ; exit ;
    /////////////////////////////////////////  ДАННЫЕ ДЛЯ ФИЛЬТРОВ /////////////////////////////////
    // макс и мин цены
    $sql = "SELECT MIN(p.price), MAX(p.price) FROM Products p" ;
    try { 
        $row = $_CONTEXT[ 'connection' ]->query( $sql )->fetch( PDO::FETCH_NUM ) ;
        $view_data[ 'minprice' ] = $row[0] ;
        $view_data[ 'maxprice' ] = $row[1] ;
    }
    catch( PDOException $ex ) {
        $_CONTEXT['logger']( 'shop_controller3 ' . $ex->getMessage() . $sql ) ;
        $view_data[ 'add_error' ] = "Server error try later" ;
    }
    // Категории (группы) товаров
    $sql = "SELECT g.id, MAX(g.name) AS name, COUNT(p.id) AS cnt FROM `product_groups` g JOIN products p ON g.id=p.id_grp GROUP BY 1" ;
    try { 
        $table = $_CONTEXT[ 'connection' ]->query( $sql ) ;
        $view_data[ 'product_groups' ] = [] ;
        while( $row = $table->fetch( PDO::FETCH_ASSOC ) ) {
            $view_data[ 'product_groups' ][] = $row ;
            if( in_array( $row['id'], $filters[ 'product_groups_id' ] ) )  {
                $filters[ 'product_groups_name' ][] = $row['name'] ;  // имена выбранных групп для отображения фильтра
            }
        }
    }
    catch( PDOException $ex ) {
        $_CONTEXT['logger']( 'shop_controller4 ' . $ex->getMessage() . $sql ) ;
        $view_data[ 'add_error' ] = "Server error try later" ;
    }


    ///////////////////////////////////////////// пагинация //////////////////////////////////////////
    // 1. сколько всего товаров
    $sql = "SELECT COUNT(p.id) FROM Products p  $where_part " ;
    try { $total = $_CONTEXT[ 'connection' ]->query( $sql )->fetch(PDO::FETCH_NUM)[0] ; }
    catch( PDOException $ex ) {
        $_CONTEXT['logger']( 'shop_controller1 ' . $ex->getMessage() . $sql ) ;
        $view_data[ 'add_error' ] = "Server error try later" ;
    }
    if( empty( $view_data[ 'add_error' ] ) ) {
        // 2. номер страницы и кол-во элементов на странице
        $perpage = 4 ;
        $lastpage = ceil( $total / $perpage ) ;
        if( $lastpage == 0 ) $lastpage = 1 ;
        @$page = intval( $_GET['page'] ) ?? 1 ;         //  $page  1      2      3      4       
        if( $page < 1 ) $page = 1 ;                     //  nums   1-4    5-8    9-12   13,14,15 (всего 15)
        if( $page > $lastpage ) $page = $lastpage ;     //  $skip  0      4      8      12
        $skip = ( $page - 1 ) * $perpage ;
        $view_data[ 'paginator' ] = [
            'page' => $page,
            'perpage' => $perpage,
            'lastpage' => $lastpage,
            'total' => $total
        ] ;
 // echo $total, ' ', $_GET['page'], '<br/>' ; print_r( $view_data[ 'paginator' ] ) ; exit ; 

        $sql = "SELECT p.*, g.name as `grp_name` FROM Products p JOIN product_groups g ON p.id_grp = g.id   $where_part   $order_part     LIMIT $skip, $perpage" ;
        try {
            $view_data[ 'products' ] = 
                $_CONTEXT[ 'connection' ]->query( $sql )->fetchAll( PDO::FETCH_ASSOC ) ;
        }
        catch( PDOException $ex ) {
            $_CONTEXT['logger']( 'shop_controller2 ' . $ex->getMessage() . $sql  ) ;
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

Фильтры - по цене: выводить поля выбора минимальной и максимальной цены

CREATE TABLE product_groups ( 
    id CHAR(36) PRIMARY KEY  DEFAULT UUID(),
    name TINYTEXT NOT NULL
) ENGINE = InnoDB, DEFAULT CHARSET = UTF8 ;

INSERT INTO product_groups(name) VALUES ('Программное обеспечение');

Таблица типа "журнал" - постоянно изменяемая таблица
В таблицах типа "словарь" (с почти неизменными данными) id можно вибирать не UUID, а по семантике
CREATE TABLE user_roles (  
    id VARCHAR(16) PRIMARY KEY,
    descr TINYTEXT NOT NULL
) ENGINE = InnoDB, DEFAULT CHARSET = UTF8 ;
INSERT INTO user_roles VALUES( 'admin', 'Системный администратор - доступ к DDL' )
INSERT INTO user_roles VALUES( 'moderator', 'Контент-модератор - доступ к DML' )
INSERT INTO user_roles VALUES( 'guest', 'Гость - минимальные права на просмотр открытого контента' )

ALTER TABLE users ADD COLUMN role_id VARCHAR(16) NOT NULL DEFAULT 'guest'
ALTER TABLE users ADD FOREIGN KEY (role_id) REFERENCES user_roles (id)

Д.З. Создать таблицу product_groups ( 
    id CHAR(36) PRIMARY KEY  DEFAULT UUID(),
    name TINYTEXT NOT NULL
) ENGINE = InnoDB, DEFAULT CHARSET = UTF8 ;
Заполнить ее несколькими категориями (хотя бы 3) - для будущего фильтра
Для товаров (таблица products) указать id группы 

Д.З. (Экзаменационное): Приложить архив итогового проекта
Доработать: фильтр по группам товаров
Ожидаемое поведение:
 - если  выбраны все группы, то сбросить фильтр групп (как будто нет выбранных вообще)
 - также учитывать этот факт в пагинаторе
*/
