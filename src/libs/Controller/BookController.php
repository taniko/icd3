<?php
namespace Hrgruri\Icd3\Controller;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Hrgruri\Icd3\Session;
use Hrgruri\Icd3\Model\{
    Nishikie,
    Book,
    Recommend
};

class BookController extends Controller
{
    public function __invoke(Request $request, Response $response, $args)
    {
        try {
            $session = new Session($this->capsule);
            $session->start();
            $id     = ($session->get())['id'];
            $date   = $request->getQueryParam('date') ?? date("Y-m-d");
            $recommend   = new Recommend($this->capsule);
            $assets     = $recommend->getRecommendByUser($id, 'books');
            if (count($assets) <= 0) {
                $assets = $recommend->getRecommendByPopular($id, 'books');
            }
            $date_recommends = $recommend->getRecommendByDate($date, $id, 'books');
            $this->view->render($response, 'books/index.twig', [
                'title'     =>  '古典書籍データベース',
                'assets'    =>  $assets,
                'date_recommends'    => $date_recommends,
                'date'      =>  $date,
                'db'        =>  'books',
                'uri'       =>  $request->getUri()
            ]);
        } catch (\PDOException $e) {
            $this->logger->addAlert("{$e->getMessage()} at {$request->getUri()}");
            $this->view->render($response, 'exception/404.twig');
        } catch(\Exception $e) {
            $this->view->render($response, 'exception/404.twig');
        }
        return $response;
    }
}
