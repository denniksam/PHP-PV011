<?php

$smtp = [
    'host'    => 'smtp.gmail.com',
    'port'    => 587,
    'secure'  => 'tls',
    'user'    => 'change@me',
    'pass'    => 'changeme',
    'options' => [
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
        ]
    ],
] ;
