<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require '../src/config/db.php';
require '../src/config/fire.php';

$app = new \Slim\App;

require '../src/routes/tetris.php';
$app->run();
