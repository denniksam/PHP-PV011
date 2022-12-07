<?php 
$_LOGGER_FILE = "logs/pv011_log.txt" ;
$_CONTEXT = [] ;   // наши глобальные данные - контекст запроса
$path = explode( '?', urldecode( $_SERVER[ 'REQUEST_URI' ] ) )[0] ;     // адрес запроса - начало маршрутизации
$_CONTEXT[ 'path' ] = $path ;   // сохраняем в контексте для доступа из других файлов
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
$_CONTEXT[ 'path_parts' ] = $path_parts ;
$_CONTEXT[ 'logger' ] = make_logger() ;
$_CONTEXT[ 'show500' ] = function() { header( "Location: /page500.html" ) ; exit ; } ;

// ~MiddleWare
include "dbms.php" ;
if( empty( $connection ) ) {
    $_CONTEXT['show500']() ;   // exit - inside function
}
$_CONTEXT[ 'connection' ] = $connection ;

include "auth.php" ;


// ~Controllers
$controller_file = "controllers/" . $path_parts[1] . "_controller.php" ;  // [1] - первая непустая часть (суть контроллер)
if( is_file( $controller_file ) ) {
    include $controller_file ;
}
else {
    // ~View
    include "_layout.php" ;
}

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
        case 'svg' :
            $content_type = "image/svg+xml" ;
            break ;  

        default:  return false ;               // недопустимое расширение - не отдаем файл
    }
    header( "Content-Type: $content_type" ) ;  // заголовок с типом контента
    readfile( $filename ) ;                    // копируем файл в ответ сервера            
    return true ;                     
}

function make_logger() {
    return function( $msg, $code = 500 ) {
        global $_LOGGER_FILE ;
        $f = fopen( $_LOGGER_FILE, "at" ) ;
        fwrite( $f, date( 'Y-m-d H:i:s ' ) . $code . ' ' . $msg . "\n" ) ;
        fclose( $f ) ;
    } ;
}

// суперглобальные массивы - массивы, доступные из любой "точки" РНР
// (в ф-циях не нужно дописывать global)
// $_SERVER - основные данные от сервера