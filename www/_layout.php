<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css" />
    <title>PV011  <?= $_CONTEXT['page_title'] ?? '' ?></title>
</head>
<body>
    <nav>
        <img src="/img/php.png" alt="logo" class="logo" />
        <a href="/basics">Введение в РНР</a>
        <a href="/fundamentals">Основы РНР</a>
        <a href="/layout">Шаблонизация</a>
        <a href="/formdata">Данные форм</a>
        <a href="/db">Работа с БД</a>
        <a style="color:maroon" href="/email_test">E-mail</a>
        
        <?php include "_auth.php" ?>
    </nav>

    <h1>PHP</h1>    

    <!-- Render body -->
    <?php
    if( $path_parts[1] === '' ) $path_parts[1] = 'index' ;
    switch( $path_parts[1] ) {   // [1] - первая непустая часть (суть контроллер)
        case 'index'        : 
        case 'basics'       : 
        case 'fundamentals' : 
        case 'layout'       : 
        case 'db'           :
        case 'register'     :
        case 'email_test'   :
        case 'formdata'     : include "{$path_parts[1]}.php" ; break ;

        case 'shop'    :
        case 'profile' : include "views/{$path_parts[1]}.php" ; break ;

        default :
            echo "404" ;
    }
    ?>
<hr/>
    <?php $x = 10 ; $i = 20 ; include "footer.php" ?>
</body>
</html>