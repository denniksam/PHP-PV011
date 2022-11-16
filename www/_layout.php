<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PV011</title>
</head>
<body>
    <h1>PHP</h1>
    <a href="/basics">Введение в РНР</a><br/>
    <a href="/fundamentals">Основы РНР</a><br/>
    <a href="/layout">Шаблонизация</a><br/>

    <!-- Render body -->
    <?php
    switch( $path_parts[1] ) {   // [1] - первая непустая часть (суть контроллер)
        case ''             :
        case 'index'        : include 'index.php' ;        break ; 
        case 'basics'       : include 'basics.php' ;       break ;
        case 'fundamentals' : include 'fundamentals.php' ; break ;
        case 'layout'       : include 'layout.php' ;       break ;
        default :
            echo "404" ;
    }
    ?>
<hr/>
    <?php $x = 10 ; $i = 20 ; include "footer.php" ?>
</body>
</html>