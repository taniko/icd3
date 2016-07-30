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
        $nishikie   = new Nishikie($this->capsule);
        $param      = $nishikie->correctParam($request->getQueryParams());
        $assets     = $nishikie->search($param);
        $url = "/nishikie/search?keyword={$param['keyword']}&title{$param['title']}=&author{$param['author']}=&count={$param['count']}";
        $prev_link = ($param['page'] > 1) ? ("{$url}&page=".($param['page']-1)) : null;
        $next_link = "{$url}&page=". ($param['page']+1);
        $this->view->render($response, 'nishikie/search.twig', [
            'title'     =>  '浮世絵データベース',
            'assets'    =>  $assets,
            'prev_link' =>  $prev_link,
            'next_link' =>  $next_link
        ]);
    }

    public function books(Request $request, Response $response, $args)
    {
        $session = new Session($this->capsule);
        $session->start();
        $book   = new Book($this->capsule);
        $param  = $book->correctParam($request->getQueryParams());
        $assets = $book->search($param);
        $url = "/books/search?id={$param['id']}&title{$param['title']}=&author{$param['author']}=&count={$param['count']}";
        $prev_link = ($param['page'] > 1) ? ("{$url}&page=".($param['page']-1)) : null;
        $next_link = "{$url}&page=". ($param['page']+1);
        $this->view->render($response, 'books/search.twig', [
            'title'     =>  '古典書籍データベース',
            'assets'    =>  $assets,
            'prev_link' =>  $prev_link,
            'next_link' =>  $next_link
        ]);
    }
}
