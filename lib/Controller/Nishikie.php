<?php
namespace Hrgruri\Icd3\Controller;

use Hrgruri\Icd3\Model\Detail as DB;

class Nishikie
{
    public static function show($args, $twig)
    {
        $html = ($twig->loadTemplate('nishikie.twig'))->render([
            'title'     => '浮世絵データベース',
            'result'    => null
        ]);
        return $html;

    }
}
