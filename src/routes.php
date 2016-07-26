<?php
// Routes
$app->get('/', function ($request, $response, $args){
    return $this->view->render($response, 'index.twig', []);
});

$app->get('/{db}/detail/{id}[/]', Hrgruri\Icd3\Controller\DetailController::class);
