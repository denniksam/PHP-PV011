<?php 

// суперглобальные массивы - массивы, доступные из любой "точки" РНР
// (в ф-циях не нужно дописывать global)
// echo "<pre>" ; print_r( $_SERVER ) ;
// $_SERVER - основные данные от сервера
$path = $_SERVER[ 'REQUEST_URI' ] ;      // адрес запроса - начало маршрутизации
$path_parts = explode( '/', $path ) ;    // ~split - разбивает строку по разделителю
// echo "<pre>" ; print_r( $path_parts ) ;  // массив частей пути, [0] всегда пустой

include "_layout.php" ;
