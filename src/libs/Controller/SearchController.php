<?php
namespace Hrgruri\Icd3\Controller;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Hrgruri\Icd3\Session;
use Hrgruri\Icd3\Exception\ParamException;
use Hrgruri\Icd3\Model\{
    Nishikie,
    Book
};

class SearchController
{
    protected $view;
    protected $logger;
    protected $capsule;

    public function __construct($c) {
        $this->view     = $c->get('view');
        $this->logger   = $c->get('logger');
        $this->capsule  = $c->get('db');
    }

    public function nishikie(Request $request, Response $response, $args)
    {
        $session = new Session($this->capsule);
        $session->start();
        $nishikie   = new Nishikie($this->capsule, $this->logger);
        $param      = $nishikie->correctParam($request->getQueryParams());
        $assets     = $nishikie->search($param);
        $url = "/nishikie/search?keyword={$param['keyword']}&title{$param['title']}=&author{$param['author']}=&count={$param['count']}";
        $prev_link = ($param['page'] > 1) ? ("{$url}&page=".($param['page']-1)) : null;
        $next_link = "{$url}&page=". ($param['page']+1);
        $this->view->render($response, 'nishikie/search.twig', [
            'title'     =>  '浮世絵データベース',
            'assets'    =>  $assets,
            'prev_link' =>  $prev_link,
            'next_link' =>  $next_link,
            'db'        =>  'nishikie'
        ]);
    }

    public function books(Request $request, Response $response, $args)
    {
        $session = new Session($this->capsule);
        $session->start();
        $book   = new Book($this->capsule, $this->logger);
        $param  = $book->correctParam($request->getQueryParams());
        $assets = $book->search($param);
        $url = "/books/search?id={$param['id']}&title{$param['title']}=&author{$param['author']}=&count={$param['count']}";
        $prev_link = ($param['page'] > 1) ? ("{$url}&page=".($param['page']-1)) : null;
        $next_link = "{$url}&page=". ($param['page']+1);
        $this->view->render($response, 'books/search.twig', [
            'title'     =>  '古典書籍データベース',
            'assets'    =>  $assets,
            'prev_link' =>  $prev_link,
            'next_link' =>  $next_link,
            'db'        =>  'book'
        ]);
    }

    public function api(Request $request, Response $response, $args)
    {
        try {
            $result = ['status' => true];
            if ($args['db'] == 'nishikie') {
                $nishikie   = new Nishikie($this->capsule);
                $param      = $nishikie->correctParam($request->getQueryParams());
                $result['result'] = $nishikie->search($param);
            } elseif ($args['db'] == 'books') {
                $book   = new Book($this->capsule);
                $param  = $book->correctParam($request->getQueryParams());
                $result['result'] = $book->search($param);
            } else {
                throw new \ParamException();
            }
            $response->withJson($result);
        } catch (\PDOException $e) {
            $this->logger->addAlert("{$e->getMessage()} at {$request->getUri()}");
            $response->withJson(['status' => false, 'text' => 'Database error']);
        } catch (ParamException $e) {
            $response->withJson(['status' => false, 'text'=> $e->getMessage()]);
        } catch(\Exception $e) {
            $param = $request->getQueryParams();
            $param['db'] = $args['db'] ;
            $this->logger->addError('api_search', $param);
            $response->withJson(['status' => false, 'text'=> 'error']);
        }
        return $response;
    }
}
