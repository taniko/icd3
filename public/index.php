<?php
require __DIR__. '/../vendor/autoload.php';
session_start();

$app    = new \Slim\App();

\Hrgruri\Icd3\Route::register($app, __DIR__ . '/../templates');

$app->run();
