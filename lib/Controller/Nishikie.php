<?php
namespace Hrgruri\Icd3\Controller;

use Hrgruri\Icd3\Model\Session;
use Hrgruri\Icd3\Model\Nishikie as M;
use Hrgruri\Icd3\Model\Recommend;

class Nishikie
{
    const NAME = 'nishikie';

    public static function showIndex($args, $twig, $param)
    {
        $session = self::setSession();
        $assets = Recommend::getByUser(self::NAME, $session['id']);
        return 'index';
    }

    public static function showSearch($args, $twig, $param)
    {
        self::setSession();
        $param['count'] = 1;
        $assets = M::search($param);
        $html = ($twig->loadTemplate('nishikie.twig'))->render([
            'title'     => '浮世絵データベース',
            'assets'    => $assets,
            'db'        => self::NAME
        ]);
        return $html;
    }

    private static function setSession()
    {
        $session = Session::get();
        $_SESSION['id']     = $session['id'];
        $_SESSION['token']  = $session['token'];
        return $session;
    }
}
