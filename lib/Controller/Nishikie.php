<?php
namespace Hrgruri\Icd3\Controller;

class Nishikie
{
    public static function showIndex($args, $twig, $param)
    {
        self::setSession();
        $assets = (new \Hrgruri\Icd3\Model\Recommend())->getUserRecommend('nishikie', $_SESSION['id']);
        return 'index';
    }

    public static function showSearch($args, $twig, $param)
    {
        self::setSession();
        $html = ($twig->loadTemplate('nishikie.twig'))->render([
            'title'     => '浮世絵データベース',
            'assets'    => \Hrgruri\Icd3\Model\Nishikie::search($param)
        ]);
        return $html;
    }

    private static function setSession()
    {
        $session = (new \Hrgruri\Icd3\Model\Session())->get();
        $_SESSION['id'] = $session['id'];
        $_SESSION['token'] = $session['token'];
    }
}
