<?php
namespace Hrgruri\Icd3\Controller;

use Slim\Container;
use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

abstract class Controller
{
    protected $view;
    protected $logger;
    protected $capsule;

    public function __construct(
        Container $c
    ) {
        $this->view     = $c->get('view');
        $this->logger   = $c->get('logger');
        $this->capsule  = $c->get('db');
    }

    abstract public function __invoke(Request $request, Response $response, $args);
}
