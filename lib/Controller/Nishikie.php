<?php
namespace Hrgruri\Icd3\Controller;

use Hrgruri\Icd3\Model\Detail as DB;

class Nishikie
{
    public static function showIndex($args, $twig, $param)
    {
        return 'index';
    }

    public static function showSearch($args, $twig, $param)
    {
        $html = ($twig->loadTemplate('nishikie.twig'))->render([
            'title'     => '浮世絵データベース',
            'assets'    => \Hrgruri\Icd3\Model\Nishikie::search($param)
        ]);
        return $html;
    }
}
