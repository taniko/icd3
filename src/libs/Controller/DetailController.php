<?php
namespace Hrgruri\Icd3\Controller;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Hrgruri\Icd3\Model\Nishikie;

class DetailController extends Controller
{
    public function __invoke(Request $request, Response $response, $args)
    {
        try {
            if ($args['db'] == 'nishikie') {
                $nishikie   = new Nishikie($this->capsule);
                $info       = $nishikie->getInfo($args['id']);
            } elseif ($args['db'] == 'books') {
                // $info       = Books::getDetail($args['id']);
                throw new \Exception();
            } else {
                throw new \Exception();
            }
        } catch(\Exception $e) {
            $this->view->render($response, 'exception/404.twig');
        }
        $this->view->render($response, 'index.twig', []);
        return $response;
    }
}
