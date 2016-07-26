<?php
// DIC configuration
$container = $app->getContainer();

$container['renderer'] = function ($c) {
    $settings = $c->get('settings')['renderer'];
    return new Slim\Views\PhpRenderer($settings['template_path']);
};

$container['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $c['view']->render($response, 'exception/404.twig');
    };
};

// twig
$container['view'] = function ($c) {
    $view = new \Slim\Views\Twig( __DIR__ . '/templates', [
        // 'cache' => __DIR__ . '/../templates/cache',
        'debug' => true
    ]);
    $view->addExtension(new \Slim\Views\TwigExtension(
        $c['router'],
        $c['request']->getUri()
    ));
    return $view;
};

// monolog
$container['logger'] = function ($c) {
    $settings = $c->get('settings')['logger'];
    $logger = new \Monolog\Logger($settings['name']);
    $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
    $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], Monolog\Logger::DEBUG));
    return $logger;
};

// illuminate
$container['db'] = function ($container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

// Controller
$container[Hrgruri\Icd3\Controller\DetailController::class] = function ($c) {
    $view       = $c->get('view');
    $logger     = $c->get('logger');
    $capsule    = $c->get('db');
    return new \Hrgruri\Icd3\Controller\DetailController($view, $logger, $capsule);
};
