<?php
$ENV = [
    'ENV' => 'LOCAL',

    'DBHOST' => 'mysql',
    'DBUSER' => 'lolai',
    'DBPASS' => 'lolai',
    'DBNAME' => 'lolai',

    'URL' => 'api.localhost',
    'API' => 'api.localhost',
    'APP' => 'lolai.localhost',
    'APPNAME' => 'lolai',

    'MAILER' => [
        'HOST' => 'mail',
        'USER' => '',
        'PASS' => '',
        'PORT' => '1025',
        'MAIL' => 'info@lolai.dk',
    ],

    'MONGODB' => 'mongodb://lolai:lolai@mongo:27017',

    'REDIS' => [
        'scheme' => 'tcp',
        'host' => 'redis',
        'port' => 6379
    ],
];
