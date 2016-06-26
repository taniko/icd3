<?php
namespace Hrgruri\Icd3\Controller;

use Hrgruri\Icd3\Model\{Nishikie, Books};
use Hrgruri\Icd3\Model\Recommend;

class Detail
{
    public static function show($args, $twig)
    {
        try {
            if ($args['db'] == 'nishikie') {
                $info       = Nishikie::getDetail($args['id']);
            } elseif ($args['db'] == 'books') {
                $info       = Books::getDetail($args['id']);
            } else {
                throw new \Exception();
            }
            $recommend  = Recommend::getByDetail($args['db'], $args['id'], 4);
            $html = ($twig->loadTemplate('detail.twig'))->render([
                'db'        =>  $args['db'],
                'title'     =>  '浮世絵データベース',
                'info'      =>  $info,
                'recommend' =>  $recommend
            ]);
        } catch(\Exception $e) {
            $html = 'error';
        }
        return $html;
    }
}
