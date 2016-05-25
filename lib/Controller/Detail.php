<?php
namespace Hrgruri\Icd3\Controller;

use Hrgruri\Icd3\Model\Nishikie;
use Hrgruri\Icd3\Model\Recommend;

class Detail
{
    public static function show($args, $twig)
    {
        $html = 'error';
        if ($args['db'] == 'nishikie') {
            $info       = Nishikie::getDetail($args['id']);
            $recommend  = Recommend::getByDetail($args['db'], $args['id'], 4);
            $html = ($twig->loadTemplate('detail.twig'))->render([
                'title'     => '浮世絵データベース',
                'info'      => $info,
                'recommend' => $recommend
            ]);
        }
        return $html;
    }
}
