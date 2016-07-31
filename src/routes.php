<?php
// Routes
$app->get('/', function ($request, $response, $args){
    return $this->view->render($response, 'index.twig', []);
});

$app->get('/{db}/detail/{id}[/]', Hrgruri\Icd3\Controller\DetailController::class);
$app->get('/nishikie[/]', Hrgruri\Icd3\Controller\NishikieController::class);
$app->get('/books[/]', Hrgruri\Icd3\Controller\BookController::class);
$app->get('/nishikie/search[/]', '\Hrgruri\Icd3\Controller\SearchController:nishikie');
$app->get('/books/search[/]', '\Hrgruri\Icd3\Controller\SearchController:books');
$app->post('/log/commit[/]', '\Hrgruri\Icd3\Controller\LogController:commit');
$app->post('/ignore/commit[/]', '\Hrgruri\Icd3\Controller\IgnoreController:commit');
