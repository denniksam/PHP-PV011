<h2>Установка ПО</h2>
<ul>
    <li>Apache - веб-сервер для РНР</li>
    <li>РНР (интерпретатор)</li>
    <li>Настраиваем Apache на скачанный РНР</li>
    <li>ИЛИ устанавливаем сборку, например, XAMPP</li>
</ul>
<h2>Настройка окружения (проект)</h2>
<ul>
    <li>Простой вариант - находим папку htdocs (/xampp), удаляем
        из нее все содержимое и заменяем на свой проект. 
        Проект будет доступен при включенном Apache по адресу
        localhost
    </li>
    <li>Более сложный вариант</li>
    <ul>
        <li>Создаем новую папку для проекта</li>
        <li>Открываем файл конфигурации Apache httpd-vhosts.conf
            (apache/conf/extra), создаем в нем определение для 
            виртуального хоста используя закомментированные 
            примеры. Указываем расположение новой папки проекта
        </li>
        <li>Создаем для проекта доменное имя (pv011.local),
            указываем его в конфигурации, а также вносим в 
            локальную службу DNS (C:\Windows\System32\drivers\etc)
            также раскомментируя примеры.
        </li>
    </ul>
</ul>
<br/> <?= uuid_v4() ?>
<br/> <?= uuid_v1() ?>

<?php 
function uuid_v4() {
    // https://www.rfc-editor.org/rfc/rfc4122#section-4.4
    $rnd = random_bytes(16);                    //
    $rnd[6] = chr(ord($rnd[6]) & 0x0f | 0x40);  // ver 4 - random UUID
    $rnd[8] = chr(ord($rnd[8]) & 0x3f | 0x80);  //
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($rnd), 4));
}
function uuid_v1() {
    // https://www.rfc-editor.org/rfc/rfc4122#section-4.5
    $rnd = '00000000' . random_bytes(8);
    $t = hrtime() ;
    $rnd[0] = chr( ($t[1] & 0xFF000000) >> 24 ) ;   // time_low 
    $rnd[1] = chr( ($t[1] & 0x00FF0000) >> 16 ) ;   // time_low 
    $rnd[2] = chr( ($t[1] & 0x0000FF00) >> 8  ) ;   // time_low 
    $rnd[3] = chr( ($t[1] & 0x000000FF) ) ;         // time_low 

    $rnd[4] = chr( ($t[0] & 0xFF000000) >> 24 ) ;   // time_mid
    $rnd[5] = chr( ($t[0] & 0x00FF0000) >> 16 ) ;   // time_mid

    $rnd[6] = chr( ( ($t[0] & 0x0000FF00) >> 8 ) & 0x0f | 0x80 ) ;  // time_hi_and_version ( 1 -> 8...)
    $rnd[7] = chr( ($t[0] & 0x000000FF) ) ;                         // time_hi_and_version
    //  node ID - random (#section-4.5)
    $rnd[10] = chr( ord($rnd[10]) | 0x01 ) ;  // the least significant bit of the first octet of the node ID set to one
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($rnd), 4));
}

// echo "<pre>" ; print_r( $_SERVER ) ;
// echo "<pre>" ; print_r( hrtime() ) ;
?>