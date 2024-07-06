<?php
$ENV['APPNAME'] = 'lolai';
$ENV['APP'] = 'lolai.localhost';
$ENV['ENVS'] = ['LOCAL', 'DEV', 'PROD'];
foreach ($ENV['ENVS'] as $v) {
    $ENV[$v] = $ENV['ENV'] == $v;
}
