<?php
namespace Hrgruri\Icd3\Controller;

use Slim\Views\Twig;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Hrgruri\Icd3\Exception\ParamException;
use Hrgruri\Icd3\Model\{
    Nishikie,
    Book,
    Recommend
};

class DetailController extends Controller
{
    public function __invoke(Request $request, Response $response, $args)
    {
        try {
            if ($args['db'] == 'nishikie') {
                $nishikie   = new Nishikie($this->capsule);
                $info       = $nishikie->getInfo($args['id']);
            } elseif ($args['db'] == 'books') {
                $book       = new Book($this->capsule);
                $info       = $book->getInfo($args['id']);
            } else {
                throw new \Exception();
            }
            $recommend  = (new recommend($this->capsule))
                ->getRecommendByAsset($args['id'], $args['db'], 4);
            $this->view->render($response, 'detail.twig', [
                'db'        =>  $args['db'],
                'title'     =>  '浮世絵データベース',
                'info'      => $info,
                'recommend' => $recommend
            ]);
        } catch(\Exception $e) {
            $this->logger->addNotice('undefined_detail_db', ['db' => $args['db']]);
            $this->view->render($response, 'exception/404.twig');
        }
        return $response;
    }

    public function api(Request $request, Response $response, $args)
    {
        try {
            $result = ['status' => true];
            if (is_null($request->getQueryParam('arc_no'))) {
                throw new ParamException('does not set query param');
            } elseif ($args['db'] == 'nishikie') {
                $nishikie       = new Nishikie($this->capsule);
                $result['info'] = $nishikie->getInfo($request->getQueryParam('arc_no'));
            } elseif ($args['db'] == 'books') {
                $book           = new Book($this->capsule);
                $result['info'] = $book->getInfo($request->getQueryParam('arc_no'));
            } else {
                throw new \Exception();
            }
            if (boolval($request->getQueryParam('recommend'))) {
                $result['recommend']  = (new recommend($this->capsule))
                    ->getRecommendByAsset($request->getQueryParam('arc_no'),
                    $args['db'],
                    $request->getQueryParam('count')
                );
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
            $this->logger->addError('api_detail', $param);
            $response->withJson(['status' => false, 'text'=> 'error']);
        }
        return $response;
    }
}
