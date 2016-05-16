<?php
require __DIR__. '/../vendor/autoload.php';

$app    = new \Slim\App();

\Hrgruri\Icd3\Route::register($app, __DIR__ . '/../templates');

$app->run();
