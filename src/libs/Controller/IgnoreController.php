<?php
namespace Hrgruri\Icd3\Controller;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Hrgruri\Icd3\Session;
use Hrgruri\Icd3\Model\IgnoreAsset;

class IgnoreController
{
    public function __construct($c) {
        $this->view     = $c->get('view');
        $this->logger   = $c->get('logger');
        $this->capsule  = $c->get('db');
        $this->session = new Session($this->capsule);
        $this->session->start();
    }

    public function commit(Request $request, Response $response, $args)
    {
        $user   = $this->session->get();
        $db     = $request->getParam('db');
        $asset  = $request->getParam('arc_no');
        $ia     = new IgnoreAsset($this->capsule);
        $ia->insertIgnoreAsset($user['id'], $asset, $db);
    }
}
