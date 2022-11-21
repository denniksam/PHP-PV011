<?php 
$path = explode( '?', $_SERVER[ 'REQUEST_URI' ] )[0] ;     // адрес запроса - начало маршрутизации
/* Создание диспетчера доступа приводит к тому, что запросы к файлам,
   которые раньше автоматически "отдавал" Apache, теперь приходят
   к нам 
*/
$local_path = '.' . $path ;             // file_exists - и файлы, и папки. is_file - только файлы
if( is_file( $local_path ) ) {          // запрос - существующий файл
    if( flush_file( $local_path ) )     // наша функция отправки файла (см. ниже)
       exit ;                           // останавливаем работу если файл передан
    // else {}                          // файл есть, а расширение недопустимо
}

// echo "<pre>" ; print_r( $_GET ) ; exit ;

$path_parts = explode( '/', $path ) ;    // ~split - разбивает строку по разделителю

// ~MiddleWare
include "dbms.php" ;
if( empty( $connection ) ) {
    echo "DB error"; 
    exit ;
}
include "auth.php" ;


// ~View
include "_layout.php" ;

function flush_file( $filename ) {
    ob_clean() ;                               // очищаем буферизацию
    // простая передача файла в ответ (без заголовков) может его неправильно отображать
    // определяем расширение файла
    $pos = strrpos( $filename, '.' ) ;         // последняя позиция точки
    $ext = substr( $filename, $pos + 1 ) ;     // расширение - часть строки от точки (+1) до конца
    switch( $ext ) {                           // проверяем расширение на допустимые
        case 'css' :
        case 'html': 
            $content_type = "text/$ext" ; 
            break ;
        case 'png' :
        case 'jpg' :
        case 'gif' :
        case 'ico' :
            $content_type = "image/$ext" ; 
            break ;

        default:  return false ;               // недопустимое расширение - не отдаем файл
    }
    header( "Content-Type: $content_type" ) ;  // заголовок с типом контента
    readfile( $filename ) ;                    // копируем файл в ответ сервера            
    return true ;                     
}

// суперглобальные массивы - массивы, доступные из любой "точки" РНР
// (в ф-циях не нужно дописывать global)
// $_SERVER - основные данные от сервера