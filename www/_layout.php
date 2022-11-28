<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/style.css" />
    <title>PV011</title>
</head>
<body>
    <nav>
        <img src="/img/php.png" alt="logo" class="logo" />
        <a href="/basics">Введение в РНР</a>
        <a href="/fundamentals">Основы РНР</a>
        <a href="/layout">Шаблонизация</a>
        <a href="/formdata">Данные форм</a>
        <a href="/db">Работа с БД</a>
        
        <?php if( is_array( $_CONTEXT[ 'auth_user' ] ) ) { ?>
            <b>Hello, <?= $_CONTEXT[ 'auth_user' ][ 'name' ] ?></b>
            <?=  $_CONTEXT[ 'auth_interval' ] ?>
            <!-- Кнопка выхода из авторизованного режима - ссылка передающая параметр "logout" -->
            <a class="logout" href="?logout">Log out</a>
        <?php } else {  ?>
            <form method="post">
                <label><input name="userlogin" placeholder="login" /></label>
                <label><input name="userpassw" type="password" /></label>
                <button>Log in</button>
            </form>
            <?php if( isset( $_CONTEXT[ 'auth_error' ] ) ) { echo $_CONTEXT[ 'auth_error' ] ; } ?>
            <a href="/register">Регистрация</a>
        <?php }  ?>
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
        case 'formdata'     : include "{$path_parts[1]}.php" ; break ;
        default :
            echo "404" ;
    }
    ?>
<hr/>
    <?php $x = 10 ; $i = 20 ; include "footer.php" ?>
</body>
</html>