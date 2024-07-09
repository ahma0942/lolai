<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
ini_set('html_errors', 1);
error_reporting(E_ALL);

include "vendor/autoload.php";
include "funcs/funcs.util.php";
cors();
include "envs/.env.php";
include "envs/.env.global.php";
include "models/index.php";
include "dependencies/index.php";
include "controllers/index.php";
http(404);
