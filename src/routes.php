<?php
// Routes
$app->get('/', function ($request, $response, $args){
    return $this->view->render($response, 'index.twig', []);
});

$app->get('/{db}/detail/{id}[/]', Hrgruri\Icd3\Controller\DetailController::class);
$app->get('/nishikie[/]', Hrgruri\Icd3\Controller\NishikieController::class);
$app->get('/books[/]', Hrgruri\Icd3\Controller\BookController::class);
