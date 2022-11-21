<?php
try {
    $connection = new PDO( 
        "mysql:host=localhost;port=3306;dbname=pv011;charset=utf8", 
        "pv011_user", "pv011_pass", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true
        ] ) ;
}
catch( PDOException $ex ) {
    $connection = null ;
}
