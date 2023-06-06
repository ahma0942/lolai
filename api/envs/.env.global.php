<?php
$ENV['APPNAME'] = 'Default_php';
$ENV['APP'] = 'default_php.localhost';
$ENV['ENVS'] = ['LOCAL', 'DEV', 'PROD'];
foreach ($ENV['ENVS'] as $v) {
    $ENV[$v] = $ENV['ENV'] == $v;
}
