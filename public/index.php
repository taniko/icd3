<?php
require __DIR__. '/../vendor/autoload.php';
session_start();

$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app    = new \Slim\App($c);

\Hrgruri\Icd3\Route::register($app, __DIR__ . '/../templates');

$app->run();
